<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Comments_Router extends WP_REST_Controller {

	protected $meta;

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = 'comments';
		$this->meta = new WP_REST_Comment_Meta_Fields();
	}

	public function register_routes() {
		
		register_rest_route( $this->namespace,  '/' . $this->resource_name, array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'wp_post_comments' ),
				'permission_callback' 	=> array( $this, 'wp_comment_permissions_check' ),
				'args'                	=> $this->wp_comment_collection_params()
			),
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'insert_wp_posts_comment' ),
				'permission_callback' 	=> array( $this, 'wp_insert_comment_permissions_check' ),
				'args'                	=> $this->wp_insert_collection_params()
			)
		) );

		register_rest_route( $this->namespace,  '/' . $this->resource_name . '/mark', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_mark_comments' ),
				'permission_callback' 	=> array( $this, 'wp_insert_comment_permissions_check' ),
				'args'                	=> array(
					'context'	=> $this->get_context_param( array( 'default' => 'view' ) )
				)
			)
		) );
		
	}

	public function wp_comment_permissions_check( $request ) {
		return true;
	}
	
	public function wp_insert_comment_permissions_check( $request ) {
		$access_token = isset($request['access_token'])?$request['access_token']:'';
		if( $access_token == '' || $access_token == null ) {
			return new WP_Error( 'error', 'Token 认证错误,未授权用户', array( 'status' => 403 ) );
		}
		$post_id = isset($request['id'])?$request['id']:'';
		if( $post_id == '' || $post_id == null || $post_id == 0) {
			return new WP_Error( 'error', '评论文章 ID 错误', array( 'status' => 403 ) );
		}
		return true;
	}

	public function wp_comment_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['id'] = array(
			'default'			 => 0,
			'description'        => __( '对象的唯一标识符。' ),
			'type'               => 'integer',
		);
		$params['page'] = array(
			'default'			 => 1,
			'description'        => __( '集合的当前页。' ),
			'type'               => 'integer',
		);
		$params['per_page'] = array(
			'default'		 => 10,
			'description'    => __( '结果集包含的最大项目数量。' ),
			'type'           => 'integer',
		);
		$params['type'] = array(
			'default'		 => 'comment',
			'description'    => __( '评论列表类型,默认comment' ),
			'type'           => 'string',
		);
		$params['status'] = array(
			'default'		 => 'approve',
			'description'    => __( '评论列表显示状态,默认:已审核。' ),
			'type'           => 'string',
		);
		return $params;
	}

	public function wp_insert_collection_params() {
		$params = array();
		$params['access_token'] = array(
			'required'			 => true,
			'default'			 => '',
			'description'        => __( '用户授权登录产生的 TOKEN' ),
			'type'               => 'string',
		);
		$params['id'] = array(
			'required'			 => true,
			'default'			 => 0,
			'description'        => __( '帖子 ID 。对象的唯一标识符。' ),
			'type'               => 'integer',
		);
		$params['content'] = array(
			'default'		 => '',
			'description'    => __( '发布的评论内容，默认为空。' ),
			'type'           => 'string',
		);
		$params['parent'] = array(
			'default'		 => 0,
			'description'    => __( '判断是否为评论或者回复评论。默认为 0' ),
			'type'           => 'integer',
		);
		$params['formid'] = array(
			'default'		 => '',
			'description'    => __( '发布评论推送消息通知凭证。默认为空' ),
			'type'           => 'string',
		);
		return $params;
	}

	public function wp_post_comments( $request ) {
		$uid = 0;
		$post_id = $request["id"];
		$page = $request["page"];
		$type = $request["type"];
		$number = $request["per_page"];
		$status = $request["status"];
		$offset = ($page * $number) - $number;
		$args = array(
			"post_id" => $post_id,
			"type" => $type, 
			"status" => $status,
			"number" => $number,
			"offset" => $offset,
			"parent" => 0,
			"orderby" => 'comment_date',
			"order" => 'DESC'
		);
		if( isset($request['access_token']) ) {
			$access_token = base64_decode($request['access_token']);
			$users = MP_Auth::login( $access_token );
			$uid = $users ? (int)$users->ID : 0;
		}
		$comments = get_comments($args);
		$data = array();
		foreach ($comments as $comment) {
			$comment_id = $comment->comment_ID;
			$user_id = $comment->user_id;
			$user_name = $comment->comment_author;
			$date = $comment->comment_date;
			$content = $comment->comment_content;
			$parent = $comment->comment_parent;
			$likes = (int)get_comment_meta( $comment_id, 'likes', true );
			$islike = get_comment_meta( $comment_id, '_like_comment_u_'.$uid, true );
			if($parent == 0) {
				$avatar = get_user_meta( $user_id, 'avatar', true );
				$_data["id"] = $comment_id;
				$_data["author"]["id"] = $user_id;
				$_data["author"]["name"] = ucfirst($user_name);
				if ($avatar) {
					$_data["author"]["avatar"] = $avatar;
				} else {
					$_data["author"]["avatar"] = get_avatar_url($user_id);
				}
				$_data["date"] = datetime_before($date);
				$_data["content"] = $content;
				$_data["parent"] = $parent;
				$_data["likes"] = $likes;
				$_data["islike"] = $islike ? true : false;
				$_data["reply"] = apply_filters( 'reply_comments', $post_id, $user_name, $comment_id );
				$data[] = $_data;
			}		
		}
		$response  = rest_ensure_response( $data );
		return $response;
	}
	
	public function insert_wp_posts_comment( $request ) {
		$approved = get_option('comment_moderation');
		$post_id = $request['id'];
		$type = isset($request['type'])?$request['type']:'comment';
		$content = isset($request['content'])?$request['content']:'';
		$parent_id = isset($request['parent'])?(int)$request['parent']:0;
		$formId = isset($request['formid'])?$request['formid']:'';
		$session = base64_decode( $request['access_token'] );
		$users = MP_Auth::login( $session );
		if ( !$users ) {
			return new WP_Error( 'error', '授权信息有误' , array( 'status' => 403 ) );
		}
		$user_id = (int)$users->ID;
		$user = get_user_by( 'ID', $user_id );
		$user_name = $user->display_name;
		$user_email = $user->user_email;
		$user_url = $user->user_url;
		$post_title = get_the_title( $post_id );
		if($type == 'comment') {
			if( $content == null || $content == "") {
				return new WP_Error( 'error', '内容不能为空', array( 'status' => 403 ) );
			}
			if( wp_miniprogram_option('security') ) {
				$msgCheck = apply_filters( 'security_msgSecCheck', $content );
				if( isset($msgCheck->errcode) && $msgCheck->errcode == 87014 ) {
					return new WP_Error( 'error', '内容含有违规关键词' , array( 'status' => 403 ) );
				}
			}
		} else {
			$comment_action = wp_miniprogram_comment_type( $type );
			$comment_posts = wp_miniprogram_comment_post( $post_id );
			$content = $comment_action."《".$post_title."》".$comment_posts;
		}
		if($type == 'comment') {
			$commentarr = array(
				'comment_post_ID' => $post_id, // to which post the comment will show up
				'comment_author' => ucfirst($user_name), //fixed value - can be dynamic 
				'comment_author_email' => $user_email, //fixed value - can be dynamic 
				'comment_author_url' => $user_url, //fixed value - can be dynamic 
				'comment_content' => $content, //fixed value - can be dynamic
				'comment_author_IP' => '',
				'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
				'comment_parent' => $parent_id, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
				'comment_approved' => $approved?0:1, // Whether the comment has been approved
				'user_id' => $user_id, //passing current user ID or any predefined as per the demand
			);
			$comment_id = wp_insert_comment( $commentarr );
			if($comment_id) {
				if( !$approved ) {
					$push = we_miniprogram_comment_reply_message( get_comment( $comment_id ) );
					$result["notice"] = $push;
				}
				$flag = false;
				if($formId != '' && $formId != 'the formId is a mock one') {
					$flag = add_comment_meta($comment_id, 'formId', $formId, true); 
				}
				$likes = (int)get_comment_meta( $comment_id, 'likes', true );
				if( !update_comment_meta($comment_id, 'likes', $likes) ) {
					add_comment_meta($comment_id, 'likes', $likes, true);
				}
				$result["status"] = 200;
				$result["id"] = $comment_id;
				$result["code"] = "success";
				$result["formid"] = $flag;
				$result["message"] = $approved ? "提交成功, 待人工审核通过" : "发布完成, 刷新页面查看评论"; 			
			} else {
				$result["code"] = "success";
				$result["message"] = "评论发布失败, 请检查";
				$result["status"] = 400;                   
			}
		} else {
			$message = wp_miniprogram_comment_type( $type );
			$eliminate = apply_filters( 'custom_comment_type_eliminate', $type );
			if( $eliminate ) {
				if( $parent_id != 0 ) {
					return new WP_Error( 'error', '父类 ID 错误, ID 必须为 0', array( 'status' => 403 ) );
				}
				$args = array('post_id' => $post_id, 'type__in' => array( $type ), 'user_id' => $user_id, 'parent' => 0, 'status' => 'approve', 'orderby' => 'comment_date', 'order' => 'DESC');
				$custom_comment = get_comments( $args );
				if( $custom_comment ) {
					foreach ( $custom_comment as $comment ) {
						$comment_id = $comment->comment_ID;
					}
					$comment_status = wp_delete_comment($comment_id, true);
					if ($comment_status) {
						$result["code"] = "success";
						$result["message"] = "取消".$message."成功";
						$result["status"] = 202; 
					} else {
						$result["code"] = "success";
						$result["message"] = "取消".$message."失败";
						$result["status"] = 400; 
					}
				} else {
					$customarr = array(
						'comment_post_ID' => $post_id,
						'comment_author' => ucfirst($user_name),
						'comment_author_email' => $user_email,
						'comment_author_url' => $user_url,
						'comment_content' => $content,
						'comment_author_IP' => '',
						'comment_type' => $type,
						'comment_parent' => $parent_id,
						'comment_approved' => 1,
						'user_id' => $user_id
					);
					$comment_id = wp_insert_comment( $customarr );
					if($comment_id) {
						$result["code"] = "success";
						$result["message"] = $message."成功";
						$result["status"] = 200;
					} else {
						$result["code"] = "success";
						$result["message"] = $message."失败";
						$result["status"] = 400;
					}
				}
			} else {
				$args = array('post_id' => $post_id, 'type__in' => array( $type ), 'user_id' => $user_id, 'parent' => $parent_id, 'status' => 'approve', 'orderby' => 'comment_date', 'order' => 'DESC');
				$custom_comment = get_comments( $args );
				if( $custom_comment ) {
					foreach ( $custom_comment as $comment ) {
						$parent_id = (int)$comment->comment_ID;
					}
				}
				$customarr = array(
					'comment_post_ID' => $post_id,
					'comment_author' => ucfirst($user_name),
					'comment_author_email' => $user_email,
					'comment_author_url' => $user_url,
					'comment_content' => $content,
					'comment_author_IP' => '',
					'comment_type' => $type,
					'comment_parent' => $parent_id,
					'comment_approved' => 1,
					'user_id' => $user_id
				);
				$comment_id = wp_insert_comment( $customarr );
				if($comment_id) {
					$result["code"] = "success";
					$result["message"] = $message."成功";
					$result["status"] = 200;
				} else {
					$result["code"] = "success";
					$result["message"] = $message."失败";
					$result["status"] = 400;
				}
			}
		}
		$response  = rest_ensure_response( $result );
		return $response;
	}

	public function wp_mark_comments( $request ) {
		$comment_id = $request['id'];
		$access_token = $request['access_token'];
		$users = MP_Auth::login( base64_decode( $access_token ) );
		if ( !$users ) {
			return new WP_Error( 'error', '授权信息有误,无法查询用户' , array( 'status' => 403 ) );
		}
		$user_id 	= $users->ID;
		$openid		= get_user_meta( $user_id, 'openid', true );
		$meta_key	= '_like_comment_u_'.$user_id;
		$meta_value = $openid ? $openid : $users->user_login;
		$likes 		= (int)get_comment_meta( $comment_id, 'likes', true );
		$islike 	= get_comment_meta( $comment_id, $meta_key, true );
		if( $islike ) {
			$status = delete_comment_meta( $comment_id, $meta_key );
			if( $status ) {
				$like_count = $likes ? $likes - 1 : 0;
				if( !update_comment_meta($comment_id, 'likes', $like_count) ) {
					add_comment_meta($comment_id, 'likes', $like_count, true);
				}
				$result = array( "status" => 202, "code" => "success", "success" => "取消点赞" );
			} else {
				$result = array( "status" => 400, "code" => "fail", "success" => "取消失败" );
			}
		} else {
			$status = add_comment_meta($comment_id, $meta_key, $meta_value, true);
			if( $status ) {
				$like_count = $likes ? $likes + 1 : 1;
				if( !update_comment_meta($comment_id, 'likes', $like_count) ) {
					add_comment_meta($comment_id, 'likes', $like_count, true);
				}
				$result = array( "status" => 200, "code" => "success", "success" => "点赞成功" );
			} else {
				$result = array( "status" => 400, "code" => "fail", "success" => "点赞失败" );
			}
		}
		$response  = rest_ensure_response( $result );
		return $response;
	}
	
}