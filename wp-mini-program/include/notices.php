<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if( !defined( 'ABSPATH' ) ) exit;

if( get_option('comment_moderation') ) {
	add_action('comment_unapproved_to_approved', 'miniprogram_comment_moderation_action');
}

add_action( 'wp_insert_comment', function ( $id, $comment ) {
    $approved = (int)$comment->comment_approved;
    $comment_type = $comment->comment_type;
    if( $approved && $comment_type == 'comment' ) {
        we_miniprogram_comments_reply_action( $comment->comment_ID );
        if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) {
        	qq_miniprogram_comments_reply_action( $comment->comment_ID );
		}
		if( wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret') ) {
			we_miniprogram_comment_reply_message( $comment );
		}
    }
}, 10, 2 );

function miniprogram_comment_moderation_action( $comment ) {
	if( ! wp_next_scheduled( 'wp_miniprogram_wechat_comment_approved', array( "comment_id" => $comment->comment_ID ) ) ) {
        wp_schedule_single_event( time() + 30, 'wp_miniprogram_wechat_comment_approved', array( "comment_id" => $comment->comment_ID ) ); // 定时计划延时 3 分钟执行
    }
    if( ! wp_next_scheduled( 'wp_miniprogram_wechat_comment_reply', array( "comment_id" => $comment->comment_ID ) ) ) {
        wp_schedule_single_event( time() + 60, 'wp_miniprogram_wechat_comment_reply', array( "comment_id" => $comment->comment_ID ) ); // 定时计划延时 3 分钟执行
	}
	if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) {
		if( ! wp_next_scheduled( 'wp_miniprogram_tencent_comment_approved', array( "comment_id" => $comment->comment_ID ) ) ) {
			wp_schedule_single_event( time() + 120, 'wp_miniprogram_tencent_comment_approved', array( "comment_id" => $comment->comment_ID ) );
		}
		if( ! wp_next_scheduled( 'wp_miniprogram_tencent_comment_reply', array( "comment_id" => $comment->comment_ID ) ) ) {
			wp_schedule_single_event( time() + 300, 'wp_miniprogram_tencent_comment_reply', array( "comment_id" => $comment->comment_ID ) );
		}
	}
	if( wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret') ) {
		we_miniprogram_comment_reply_message( $comment );
	}
}

add_action( 'wp_miniprogram_wechat_comment_approved', 'we_miniprogram_comment_audit_message' );
add_action( 'wp_miniprogram_wechat_comment_reply', 'we_miniprogram_comments_reply_action' );
add_action( 'wp_miniprogram_tencent_comment_approved', 'qq_miniprogram_comment_audit_message' );
add_action( 'wp_miniprogram_tencent_comment_reply', 'qq_miniprogram_comments_reply_action' );

function we_miniprogram_comment_reply_message( $comment ) {
	$comment_id = $comment->comment_ID;
	if( $comment_id === 0 ) {
		return new WP_Error( 'error', '评论 ID 为空', array( 'status' => 403 ) );
	}
	$comment = get_comment( $comment_id );
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
		$platform = get_user_meta( $parents_user_id, 'platform', true );
		$form_id = get_comment_meta( $parents->comment_ID, 'formId', true );
		$parents_user = get_user_by( 'ID', $parents_user_id );
		$touser = $parents_user->openid ? $parents_user->openid : $parents_user->user_login;
		if( !$touser ) {
			return new WP_Error( 'error', 'OpenID 无效,无法推送', array( 'status' => 403 ) );
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
		if( $platform == 'baidu' ) {
			return bd_miniprogram_comment_notice_action( $args );
		}
	}
}

function we_miniprogram_comment_audit_message( $comment_id ) {
    $comment = get_comment( $comment_id );
    $post_id = $comment->comment_post_ID;
    $type = get_post_type( $post_id );
	$date = $comment->comment_date;
	$content = $comment->comment_content;
    $user_id = $comment->user_id;
    $thingData = wp_trim_words( $content, 16, '...' );
	$template_id = wp_miniprogram_option('auditing_id');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论审核通过订阅模板为空', array( 'status' => 403 ) );
	}
	$comment_user = get_user_by( 'ID', $user_id );
	$platform = get_user_meta( $user_id, 'platform', true );
    if( $platform != 'wechat' ) {
        return new WP_Error( 'error', '当前用户不属于微信授权注册', array( 'status' => 403 ) );
    }
	$touser = $comment_user->openid ? $comment_user->openid : $comment_user->user_login;
    $task = get_miniprogram_subscribe_recent_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
	if( $type == 'post' ) {
		$page = "/pages/detail/detail?id=".$post_id;
	} else {
		$page = "/pages/index/index";
    }
    $data = array(
        "phrase1"	=> array( "value" => '评论已通过' ),
		"thing2"	=> array( "value" => html_entity_decode( wp_strip_all_tags( $thingData ) ) ),
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

function we_miniprogram_comments_reply_action( $comment_id ) {
	$comment = get_comment( $comment_id );
	$post_id = $comment->comment_post_ID;
	$reply_name = $comment->comment_author;
	$reply_date = $comment->comment_date;
	$reply_content = $comment->comment_content;
	$parent_id = $comment->comment_parent;
    $post_type = get_post_type( $post_id );
    $post_title = get_the_title( $post_id );
    $task = get_miniprogram_subscribe_recent_task( );
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
	$platform = get_user_meta( $parents_user_id, 'platform', true );
    if( $platform != 'wechat' ) {
        return new WP_Error( 'error', '当前用户不属于微信授权注册', array( 'status' => 403 ) );
    }
	$touser = $parents_user->openid ? $parents_user->openid : $parents_user->user_login;
	$template_id = wp_miniprogram_option('template_id');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '文章评论回复提醒订阅模板为空', array( 'status' => 403 ) );
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

function qq_miniprogram_comment_audit_message( $comment_id ) {
	$comment = get_comment( $comment_id );
    $post_id = $comment->comment_post_ID;
    $type = get_post_type( $post_id );
    $user_id = $comment->user_id;
    $template_id = wp_miniprogram_option('qq_audit_tpl');
    if( !$template_id ) {
        return new WP_Error( 'error', '审核通过通知模板 ID 为空', array( 'status' => 403 ) );
    }
    $user = get_user_by( 'ID', $user_id );
    $platform = get_user_meta( $user_id, 'platform', true );
    if( $platform != 'tencent' ) {
        return new WP_Error( 'error', '当前用户不属于 QQ 授权注册', array( 'status' => 403 ) );
    }
    $touser = $user->openid ? $user->openid : $user->user_login;
    $task = get_miniprogram_subscribe_recent_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
	if( $type == 'post' ) {
		$page = "/pages/detail/detail?id=".$post_id;
	} else {
		$page = "/pages/index/index";
    }
    $data = array( );
    $data['keyword1'] = array( "value" => '评论审核通过' );
    $data['keyword2'] = array( "value" => "您发表的评论已审核" );
    $data['keyword3'] = array( "value" => current_time( 'mysql' ) );
	$contents = array(
		"touser"		=> $touser,
		"page"			=> $page,
		"template_id"	=> $template_id,
		"data"			=> $data
	);
    $status = qq_miniprogram_subscribe_message_action( $task_id, $contents );
    return $status;
}

function mp_qq_subscribe_comments_reply_notice( $comment ) {
	$post_id = $comment->comment_post_ID;
    $parent_id = $comment->comment_parent;
    $reply_name = $comment->comment_author;
	$reply_date = $comment->comment_date;
	$reply_content = $comment->comment_content;
    $post_type = get_post_type( $post_id );
    $task = get_miniprogram_subscribe_recent_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
	if( $post_type == 'post' ) {
		$page = "/pages/detail/detail?id=".$post_id;
	} else {
		$page = "/pages/index/index";
    }
	if( $parent_id != 0 ) {
		$parents = get_comment( $parent_id );
        $parents_user_id = $parents->user_id;
        $parents_user = get_user_by( 'ID', $parents_user_id );
        $platform = get_user_meta( $parents_user_id, 'platform', true );
        if( $platform != 'tencent' ) {
            return new WP_Error( 'error', '当前用户不属于 QQ 授权注册', array( 'status' => 403 ) );
        }
        $touser = $parents_user->openid ? $parents_user->openid : $parents_user->user_login;
        $template_id = wp_miniprogram_option('qq_reply_tpl');
        if( !$template_id ) {
            return new WP_Error( 'error', '文章评论回复通知模板 ID 为空', array( 'status' => 403 ) );
        }
		$data = array(
            "keyword1"	=> array( "value" => html_entity_decode( $parents->comment_content ) ),
            "keyword2"	=> array( "value" => html_entity_decode( $reply_content ) ),
            "keyword3"	=> array( "value" => html_entity_decode( $reply_name ) )
        );
		$contents = array(
            "touser"		=> $touser,
            "page"			=> $page,
            "template_id"	=> $template_id,
            "data"			=> $data
		);
		$status = qq_miniprogram_subscribe_message_action( $task_id, $contents );
    	return $status;
	}
}

function bd_miniprogram_comment_notice_action( $contents ) {
	$token = MP_Auth::bd_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 403 ) );
	}
	$template_id = wp_miniprogram_option('bd_reply_tpl');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论消息模板 ID 为空', array( 'status' => 403 ) );
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
	add_action('publish_post', 'wp_post_update_notice_event');
    add_action('publish_to_publish', function () {
        remove_action('publish_post', 'wp_post_update_notice_event');
    },11,1);
}

function wp_post_update_notice_event( $post_id ) {
    if( ! wp_next_scheduled( 'wp_post_wechat_notice_event', array( "post_id" => $post_id ) ) ) {
        wp_schedule_single_event( time() + 60, 'wp_post_wechat_notice_event', array( "post_id" => $post_id ) ); // 定时计划延时 3 分钟执行
	}
	if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) {
		if( ! wp_next_scheduled( 'wp_post_tencent_notice_event', array( "post_id" => $post_id ) ) ) {
			wp_schedule_single_event( time() + 120, 'wp_post_tencent_notice_event', array( "post_id" => $post_id ) );
		}
	}
}

add_action( 'wp_post_wechat_notice_event', 'we_miniprogram_posts_update_notice' );
add_action( 'wp_post_tencent_notice_event', 'mp_qq_subscribe_update_posts_notice' );

function we_miniprogram_posts_update_notice( $post_id ) {
    $post = get_post($post_id);
    if($post->post_title) {
        $title = wp_trim_words( wp_strip_all_tags( $post->post_title ), 12, '...' );
    } else {
        $title = wp_trim_words( wp_strip_all_tags( $post->post_content ), 12, '...' );
    }
    if($post->post_excerpt) {
        $content = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 12, '...' );
    } else {
        $content = wp_trim_words( wp_strip_all_tags( $post->post_content ), 12, '...' );
    }
    $page = "/pages/detail/detail?id=".$post_id;
	$template = wp_miniprogram_option('update_tpl_id');
	if( empty($template) ) {
		return new WP_Error( 'error', '文章更新提醒订阅模板为空', array( 'status' => 403 ) );
	}
    $task = get_miniprogram_subscribe_recent_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
    $subscriber = get_miniprogram_subscriber_openid_by_tpl( $template );
    $data = array(
        "thing1"	=> array( "value" => '推荐阅读' ),
		"thing2"	=> array( "value" => html_entity_decode( $title ) ),
		"thing3"	=> array( "value" => html_entity_decode( $content ) )
    );
    foreach( $subscriber as $subscribe ) {
        $openid = $subscribe->openid;
        $contents = array(
            'touser' => $openid,
            'page' => $page,
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
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 403 ) );
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
	$counts = get_miniprogram_subscribe_by_utplid( $contents['touser'], $contents['template_id'] );
	if( !$counts ) {
        return new WP_Error( 'error', '当前用户没有订阅该消息模板', array( 'status' => 403 ) );
	}
	if( isset($counts->count) && (int)$counts->count == 0 ) {
		return new WP_Error( 'error', '当前用户该消息模板订阅次数已用完', array( 'status' => 403 ) );
	}
	$remote = wp_remote_post( $url, $args );
	$content = wp_remote_retrieve_body( $remote );
	$result = json_decode( $content, true );
	$code = $result['errcode'];
    $message = $result['errmsg'];
    $task = array('task' => $task_id, 'openid' => $contents['touser'], 'template' => $contents['template_id'], 'pages' => $contents['page'], 'program' => 'WeChat', 'errcode' => $code, 'errmsg' => $message, 'date' => current_time( 'mysql' ));
    $insert_id = wp_insert_miniprogram_subscribe_tracks( $task );
    $update_id = wp_update_miniprogram_subscribe( array( 'openid' => $contents['touser'], 'template' => $contents['template_id'] ), array( 'count' => (int)$counts->count - 1 ) );
	return $update_id;
}

function qq_miniprogram_posts_update_notice( $post_id ) {
    $post = get_post($post_id);
    if($post->post_title) {
        $title = wp_trim_words( wp_strip_all_tags( $post->post_title ), 12, '...' );
    } else {
        $title = wp_trim_words( wp_strip_all_tags( $post->post_content ), 12, '...' );
    }
    if($post->post_excerpt) {
        $content = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 12, '...' );
    } else {
        $content = wp_trim_words( wp_strip_all_tags( $post->post_content ), 12, '...' );
    }
    $page = "/pages/detail/detail?id=".$post_id;
	$template = wp_miniprogram_option('qq_update_tpl');
	if( empty($template) ) {
		return new WP_Error( 'error', '文章更新提醒订阅模板为空', array( 'status' => 403 ) );
	}
    $task = get_miniprogram_subscribe_recent_task( );
    $task_id = $task ? (int)$task->task + 1 : 1;
    $subscriber = get_miniprogram_subscriber_openid_by_tpl( $template );
    $data = array( );
    $data['keyword1'] = array( "value" => $title );
    $data['keyword2'] = array( "value" => $content );
    $data['keyword3'] = array( "value" => $post_date );
    $data['keyword4'] = array( "value" => '点击当前卡片进入查看详情' );
    foreach( $subscriber as $subscribe ) {
        $openid = $subscribe->openid;
        if( !$openid ) {
            continue;
        }
        $contents = array(
            'touser' => $openid,
            'page' => $pages,
            'template_id' => $template,
            'data' => $data
        );
        $status = qq_miniprogram_subscribe_message_action( $task_id, $contents );
        sleep( 180 );
    }
}

function qq_miniprogram_subscribe_message_action( $task_id, $contents ) {
    $token = MP_Auth::qq_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
        return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 403 ) );
    }
    $url = "https://api.q.qq.com/api/json/subscribe/SendSubscriptionMessage?access_token=".$access_token;
	$data = array(
		"touser"		=> $contents['touser'],
		"page"			=> $contents['page'],
		"template_id"	=> $contents['template_id'],
		"data"			=> $contents['data']
	);
	$header = array(
		"content-type" => "application/json"
	);
	$args = array(
		'method'  => 'POST',
		'body' 	  => wp_json_encode( $data ),
		'headers' => $header,
		'cookies' => array( )
    );
    $counts = get_miniprogram_subscribe_by_utplid( $contents['touser'], $contents['template_id'] );
    if( !$counts ) {
        return new WP_Error( 'error', '当前用户没有订阅该消息模板', array( 'status' => 403 ) );
	}
	if( isset($counts->count) && (int)$counts->count == 0 ) {
		return new WP_Error( 'error', '当前用户该消息模板订阅次数已用完', array( 'status' => 403 ) );
	}
	$remote = wp_remote_post( $url, $args );
	$content = wp_remote_retrieve_body( $remote );
	$result = json_decode( $content, true );
	$code = $result['errcode'];
    $message = $result['errmsg'];
    $task = array('task' => $task_id, 'openid' => $contents['touser'], 'template' => $contents['template_id'], 'pages' => $contents['page'], 'program' => 'QQ', 'errcode' => $code, 'errmsg' => $message, 'date' => current_time( 'mysql' ));
    $insert_id = wp_insert_miniprogram_subscribe_tracks( $task );
    $update_id = wp_update_miniprogram_subscribe( array( 'openid' => $contents['touser'], 'template' => $contents['template_id'], 'count' => (int)$counts->count) );
	return $update_id;
}