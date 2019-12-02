<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

if( get_option('comment_moderation') ) {
	add_action('comment_unapproved_to_approved', 'we_miniprogram_comment_reply_message');
	add_action('comment_unapproved_to_approved', 'we_miniprogram_comment_audit_message');
}

function we_miniprogram_comment_reply_message( $comment ) {
	$current_id = $comment->comment_ID;
	if( $current_id === 0 ) {
		return new WP_Error( 'error', '评论 ID 为空', array( 'status' => 400 ) );
	}
	$post_id = $comment->comment_post_ID;
	$reply_name = $comment->comment_author;
	$reply_date = $comment->comment_date;
	$reply_content = $comment->comment_content;
	$parent_id = $comment->comment_parent;
	$approved = $comment->comment_approved;
	$post_path = "/pages/detail/detail?id=".$post_id;
	if( $parent_id != 0 ) {
		$parents = get_comment( $parent_id );
		$parents_user_id = $parents->user_id;
		$platform = get_the_author_meta( 'platform', $parents_user_id );
		$form_id = get_comment_meta( $parents->comment_ID, 'formId', true );
		$parents_user = get_user_by( 'ID', $parents_user_id );
		$touser = $parents_user->openid ? $parents_user->openid : $parents_user->user_login;
		if( !$touser ) {
			return new WP_Error( 'error', 'OpenID 无效,无法推送', array( 'status' => 400 ) );
		}
		$args = array(
			"id"			=> $parents->comment_ID,
			"touser"		=> $touser,
			"page"			=> $page,
			"form_id"		=> $form_id,
			"reply"			=> $reply_name,
			"content"		=> $reply_content,
			"date"			=> $reply_date
		);
		if( $platform == 'wechat' ) {
			$response = we_miniprogram_comments_reply_action( $comment );
		} else if( $platform == 'tencent' ) {
			$response = qq_miniprogram_comment_notice_action( $args );
		} else if( $platform == 'baidu' ) {
			$response = bd_miniprogram_comment_notice_action( $args );
		}
	}
	return $response;	
}
function we_miniprogram_comment_audit_message( $comment ) {
    $current_id = $comment->comment_ID;
    $post_id = $comment->comment_post_ID;
    $type = get_post_type( $post_id );
	$date = $comment->comment_date;
	$content = $comment->comment_content;
    $user_id = $comment->user_id;
    $thingData = wp_trim_words( $content, 16, '...' );
	$template_id = wp_miniprogram_option('auditing_id');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论审核通过订阅模板为空', array( 'status' => 400 ) );
	}
	$comment_user = get_user_by( 'ID', $user_id );
	$touser = $comment_user->openid ? $comment_user->openid : $comment_user->user_login;
    $task = MP_Subscribe::mp_subscribe_message_send_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
	if( $type == 'post' ) {
		$page = "/pages/detail/detail?id=".$post_id;
	} else {
		$page = "/pages/index/index";
    }
    $data = array(
        "phrase1"	=> array( "value" => '评论已通过' ),
		"thing2"	=> array( "value" => html_entity_decode( strip_tags( trim( $thingData ) ) ) ),
		"date4"	    => array( "value" => $date )
    );
	$contents = array(
		"touser"		=> $touser,
		"page"			=> $page,
		"template_id"	=> $template_id,
		"data"			=> $data
	);
	$status = we_miniprogram_subscribe_message_action( $task_id, $contents );
}
function we_miniprogram_comments_reply_action( $comment ) {
	$current_id = $comment->comment_ID;
	$post_id = $comment->comment_post_ID;
	$reply_name = $comment->comment_author;
	$reply_date = $comment->comment_date;
	$reply_content = $comment->comment_content;
	$parent_id = $comment->comment_parent;
    $post_type = get_post_type( $post_id );
    $post_title = get_the_title( $post_id );
    $task = MP_Subscribe::mp_subscribe_message_send_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
	if($post_type == 'post') {
		$page = "/pages/detail/detail?id=".$post_id;
	} else {
		$page = "/pages/index/index";
	}
	$parents = get_comment( $parent_id );
    $parents_user_id = $parents->user_id;
    $parents_content = $parents->comment_content;
	$parents_user = get_user_by( 'ID', $parents_user_id );
	$touser = $parents_user->openid ? $parents_user->openid : $parents_user->user_login;
	$template_id = wp_miniprogram_option('template_id');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '文章评论回复提醒订阅模板为空', array( 'status' => 400 ) );
	}
	$data = array(
        "thing1"	=> array( "value" => wp_trim_words( $post_title, 16, '...' ) ),
        "thing2"	=> array( "value" => wp_trim_words( $parents_content, 16, '...' ) ),
        "thing3"	=> array( "value" => wp_trim_words( $reply_content, 16, '...') ),
        "date4"	    => array( "value" => $reply_date )
    );
	$contents = array(
        "touser"		=> $touser,
        "page"			=> $page,
        "template_id"	=> $template_id,
        "data"			=> $data
    );
	$status = we_miniprogram_subscribe_message_action( $task_id, $contents );
}
function qq_miniprogram_comment_notice_action( $contents ) {
	$token = MP_Auth::qq_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 400 ) );
	}
	$template_id = wp_miniprogram_option('qq_reply_tpl');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论消息模板 ID 为空', array( 'status' => 400 ) );
	}
	$url = "https://api.q.qq.com/api/json/template/send?access_token=".$access_token;
	$data = array(
		"touser"		=> $contents['touser'],
		"page"			=> $contents['page'],
		"form_id"		=> $contents['form_id'],
		"template_id"	=> $template_id,
		"data"			=> array(
			"keyword1"	=> array( "value" => ucfirst($contents['reply']) ),
			"keyword2"	=> array( "value" => $contents['content'] ),
			"keyword3"	=> array( "value" => $contents['date'] )
		)
	);
	$header = array(
		"Content-Type" => "application/json"
	);
	$args = array(
		'method'  => 'POST',
		'body' 	  => wp_json_encode( $data ),
		'headers' => $header,
		'cookies' => array()
	);
	$remote = wp_remote_post( $url, $args );
	$content = wp_remote_retrieve_body( $remote );
	$result = json_decode( $content, true );
	$code = $result['errcode'];
	$message = $result['errmsg'];
	$response = array();
	if( $code == '0' ) {
		delete_comment_meta($contents['id'], 'formId', $contents['formid']);
		$response = array(
			'status'	=> 200,
			'success' 	=> true ,
			'message'	=> 'sent message success'
		);
	} else {
		$response = array(
			'status'	=> 500,
			'success' 	=> false ,
			'message'	=> $message
		);
	}
	return $response;
}
function bd_miniprogram_comment_notice_action( $contents ) {
	$token = MP_Auth::bd_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 400 ) );
	}
	$template_id = wp_miniprogram_option('bd_reply_tpl');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论消息模板 ID 为空', array( 'status' => 400 ) );
	}
	$url = "https://openapi.baidu.com/rest/2.0/smartapp/template/send?access_token=".$access_token;
	$data = array(
		"keyword1"	=> array( "value" => ucfirst($contents['reply']) ),
		"keyword2"	=> array( "value" => $contents['content'] ),
		"keyword3"	=> array( "value" => $contents['date'] )
	);
	$body = array(
		"access_token"	=> $access_token,
		"touser"		=> "",
		"touser_openId"	=> $contents['touser'],
		"page"			=> $contents['page'],
		"scene_id"		=> $contents['form_id'],
		"template_id"	=> $template_id,
		"scene_type"	=> 1,
		"data"			=> wp_json_encode( $data )
	);
	$header = array(
		"Content-Type" => "application/x-www-form-urlencoded"
	);
	$args = array(
		'method'  => 'POST',
		'body' 	  => $body,
		'headers' => $header,
		'cookies' => array()
	);
	$remote = wp_remote_post( $url, $args );
	$content = wp_remote_retrieve_body( $remote );
	$result = json_decode( $content, true );
	$code = $result['errno'];
	$response = array();
	if( $code == '0' ) {
		delete_comment_meta($contents['id'], 'formId', $contents['formid']);
		$response = array(
			'status'	=> 200,
			'success' 	=> true,
			'message'	=> 'sent message success'
		);
	} else {
		$response = array(
			'status'	=> 500,
			'success' 	=> false,
			'message'	=> $result
		);
	}
	return $response;
}

if( wp_miniprogram_option('update') ) {
    add_action('publish_post','we_miniprogram_posts_update_notice',10,1);
    add_action('publish_to_publish',function () {
        remove_action('publish_post','we_miniprogram_posts_update_notice',10,1);
    },11,1);
}

function we_miniprogram_posts_update_notice( $post_id ) {
    $post = get_post($post_id);
    if($post->post_title) {
        $title = wp_trim_words( $post->post_title, 16, '...' );
    } else {
        $title = wp_trim_words( $post->post_content, 16, '...' );
    }
    if($post->post_excerpt) {
        $content = wp_trim_words( $post->post_excerpt, 16, '...' );
    } else {
        $content = wp_trim_words( $post->post_content, 16, '...' );
    }
    $pages = "/pages/detail/detail?id=".$post_id;
	$template = wp_miniprogram_option('update_tpl_id');
	if( empty($template) ) {
		return new WP_Error( 'error', '文章更新提醒订阅模板为空', array( 'status' => 400 ) );
	}
    $task = MP_Subscribe::mp_subscribe_message_send_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
    $subscribe_user = MP_Subscribe::mp_list_subscribe_user_by_template( $template );
    $data = array(
        "thing1"	=> array( "value" => '推荐阅读' ),
		"thing2"	=> array( "value" => html_entity_decode( strip_tags( trim( $title ) ) ) ),
		"thing3"	=> array( "value" => html_entity_decode( strip_tags( trim( $content ) ) ) )
    );
    foreach( $subscribe_user as $user ) {
        $openid = $user->openid;
        $contents = array(
            'touser' => $openid,
            'page' => $pages,
            'template_id' => $template,
            'data' => $data
        );
        $status = we_miniprogram_subscribe_message_action( $task_id, $contents );
    }
}
function we_miniprogram_subscribe_message_action( $task_id, $contents ) {
    $token = MP_Auth::we_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 400 ) );
    }
    $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token;
	$data = array(
		"touser"		=> $contents['touser'],
		"page"			=> $contents['page'],
		"template_id"	=> $contents['template_id'],
		"data"			=> $contents['data']
	);
	$header = array(
		"Content-Type: application/json;charset=UTF-8"
	);
	$args = array(
		'method'  => 'POST',
		'body' 	  => wp_json_encode( $data ),
		'headers' => $header,
		'cookies' => array()
	);
	$remote = wp_remote_post( $url, $args );
	$content = wp_remote_retrieve_body( $remote );
	$result = json_decode( $content, true );
	$code = $result['errcode'];
    $message = $result['errmsg'];
    $task = array('task' => $task_id, 'openid' => $contents['touser'], 'template' => $contents['template_id'], 'pages' => $contents['page'], 'program' => 'WeChat', 'errcode' => $code, 'errmsg' => $message, 'date' => current_time( 'mysql' ));
    $insert_id = MP_Subscribe::mp_insert_subscribe_message_send( $task );
    $counts = MP_Subscribe::mp_user_subscribe_template_count( $contents['touser'], $contents['template_id'] );
    if( $counts->count ) {
        $update_id = MP_Subscribe::mp_update_subscribe_user( $contents['touser'], $contents['template_id'], array( 'count' => (int)$counts->count - 1 ) );
    } else {
        $update_id = 0;
    }
	return $update_id;
}