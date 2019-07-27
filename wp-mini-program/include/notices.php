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
		if( $platform == 'wechat' ) {
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
				"touser"		=> $touser,
				"page"			=> $post_path,
				"form_id"		=> $form_id,
				"template_id"	=> $template_id,
				"data"			=> array(
					"keyword1"	=> array( "value" => ucfirst($reply_name) ),
					"keyword2"	=> array( "value" => $reply_content ),
					"keyword3"	=> array( "value" => $reply_date )
				)
			);
				
			$header = array(
				"Content-Type: application/json;charset=UTF-8"
			);

			$output = get_content_by_curl($url,json_encode($data),$header);

			$result = json_decode($output,true);
			$code = $result['errcode'];
			$message = $result['errmsg'];
			if( $code=='0' ) {
				delete_comment_meta($parents->comment_ID, 'formId', $form_id);
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
		}
	}
	return $response;	
}