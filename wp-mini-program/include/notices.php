<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

if(wp_miniprogram_option('approved')) {
	add_action('comment_unapproved_to_approved', 'we_miniprogram_comment_reply_message');
}

function we_miniprogram_comment_reply_message( $comment ) {
	date_default_timezone_set(get_option('timezone_string'));
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
		if(is_multisite()) {
			$touser = get_user_meta($parents_user_id, 'openid', true);
			if( !$touser ) {
				return new WP_Error( 'error', 'OpenID 无效,无法推送', array( 'status' => 400 ) );
			}
		} else {
			$parents_user = get_user_by( 'ID', $parents_user_id );
			$touser = $parents_user->user_login;
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
			$response = we_miniprogram_comment_notice_action( $args );
		} else if( $platform == 'tencent' ) {
			$response = qq_miniprogram_comment_notice_action( $args );
		}
	}
	return $response;	
}
function we_miniprogram_comment_notice_action( $contents ) {
	$token = MP_Auth::we_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 400 ) );
	}
	$template_id = wp_miniprogram_option('template_id');
	if( empty($template_id) ) {
		return new WP_Error( 'error', '评论消息模板 ID 为空', array( 'status' => 400 ) );
	}
	$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;
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
	$response = array();
	if( $code=='0' ) {
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
function qq_miniprogram_comment_notice_action( $contents ) {
	$token = MP_Auth::qq_miniprogram_access_token();
	$access_token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$access_token ) {
		return new WP_Error( 'error', '获取 ACCESS_TOKEN 失败', array( 'status' => 400 ) );
	}
	$template_id = get_minapp_option('qq_reply_tpl');
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