<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Users_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = 'user';
	}

	public function register_routes() {
		
		register_rest_route( $this->namespace, '/'.$this->resource_name.'/login', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_user_login_by_code' ),
				'permission_callback' 	=> array( $this, 'wp_user_login_permissions_check' ),
				'args'                	=> $this->wp_user_auth_collection_params()
			)
		));
		
		register_rest_route( $this->namespace, '/'.$this->resource_name.'/openid', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_user_openid_by_code' ),
				'permission_callback' 	=> array( $this, 'wp_user_login_permissions_check' ),
				'args'                	=> $this->wp_user_openid_collection_params()
			)
		));

	}

	public function wp_user_login_permissions_check( $request ) {
		$code = isset($request['code'])?$request['code']:"";
		$encryptedData = isset($request['encryptedData'])?$request['encryptedData']:"";
		$iv = isset($request['iv'])?$request['iv']:"";
		if( empty($code) ) {
			return new WP_Error( 'error', '用户登录 code 参数错误', array( 'status' => 403 ) );
		}
		return true;
	}

	public function wp_user_login_by_code( $request ) {
		
		date_default_timezone_set(get_option('timezone_string'));
		
		$appid 			= wp_miniprogram_option('appid');
		$appsecret 		= wp_miniprogram_option('secretkey');
		$role 			= wp_miniprogram_option('use_role');
		
		$params = $request->get_params();

		$args = array(
			'appid' => $appid,
			'secret' => $appsecret,
			'js_code' => $params['code'],
			'grant_type' => 'authorization_code'
		);
		
		$url = 'https://api.weixin.qq.com/sns/jscode2session';
		
		$urls = add_query_arg($args,$url);
		
		$remote = wp_remote_get($urls);
		
		if( !is_array( $remote ) || is_wp_error($remote) ) {
			return new WP_Error( 'error', '授权 API 错误', array( 'status' => 403, 'message' => $remote ) );
		}

		$body = stripslashes( $remote['body'] );
		
		$session = json_decode( $body, true );
		
		$token = MP_Auth::generate_session();
		
		if( !$token ) {
			return new WP_Error( 'error', 'Tekon Session 错误', array( 'status' => 403 ) );
		}
		
		if ( empty($params['encryptedData']) && empty($params['iv']) ) {
			$response = rest_ensure_response( array( "code" => $params['code'] ) );
			return $response;
		}
		
		$auth_code = MP_Auth::decryptData($appid, $session['session_key'], urldecode($params['encryptedData']), urldecode($params['iv']), $data );

		if( $auth_code != 0 ) {
			return new WP_Error( 'error', '授权获取失败', array( 'status' => 403, 'code' => $auth_code ) );
		}
		
		$user_data = json_decode( $data, true );
		
		$user_id = 0;
		
		$openId = $session['openid'];
		$expire = $token['expire_in'];
		$user_pass = wp_generate_password(16,false);
		
		if( !username_exists($openId) ) {
			$userdata = array(
                'user_login' 			=> $openId,
				'nickname' 				=> $user_data['nickName'],
				'first_name'			=> $user_data['nickName'],
				'user_nicename' 		=> $openId,
				'display_name' 			=> $user_data['nickName'],
				'user_email' 			=> date('Ymdhms').'@qq.com',
				'role' 					=> $role,
				'user_pass' 			=> $user_pass,
				'gender'				=> $user_data['gender'],
				'openid'				=> $openId,
				'city'					=> $user_data['city'],
				'avatar' 				=> $user_data['avatarUrl'],
				'province'				=> $user_data['province'],
				'country'				=> $user_data['country'],
				'language'				=> $user_data['language'],
				'expire_in'				=> $expire
            );
			$user_id = wp_insert_user( $userdata );			
			if ( is_wp_error( $user_id ) ) {
				return new WP_Error( 'error', '创建用户失败', array( 'status' => 400 ) );				
			}
			add_user_meta( $user_id, 'session_key', $token['session_key'] );
			add_user_meta( $user_id, 'platform', 'wechat');
		} else {
			$user = get_user_by( 'login', $openId );
			$userdata = array(
                'ID'            		=> $user->ID,
				'nickname' 				=> $user_data['nickName'],
				'first_name'			=> $user_data['nickName'],
				'user_nicename'			=> $openId,
				'display_name' 			=> $user_data['nickName'],
				'user_email' 			=> $user->user_email,
				'gender'				=> $user_data['gender'],
				'openid'				=> $openId,
				'city'					=> $user_data['city'],
				'avatar' 				=> $user_data['avatarUrl'],
				'province'				=> $user_data['province'],
				'country'				=> $user_data['country'],
				'language'				=> $user_data['language'],
				'expire_in'				=> $expire
            );
			$user_id = wp_update_user($userdata);
			if(is_wp_error($user_id)) {
				return new WP_Error( 'error', '更新用户信息失败' , array( 'status' => 400 ) );
			}
			update_user_meta( $user_id, 'session_key', $token['session_key'] );
			update_user_meta( $user_id, 'platform', 'wechat');
		}
		
		wp_set_current_user( $user_id, $openId );
		wp_set_auth_cookie( $user_id, true );

		$current_user = get_user_by( 'ID', $user_id );
		if( is_multisite() ) {
			$blog_id = get_current_blog_id();
			$roles = ( array )$current_user->roles[$blog_id];
		} else {
			$roles = ( array )$current_user->roles;
		}
		
		$user = array(
			"user"	=> array(
				"userId"		=> $user_id,
				"nickName"		=> $user_data["nickName"],
				"openId"		=> $user_data["openId"],
				"avatarUrl" 	=> $user_data["avatarUrl"],
				"gender"		=> $user_data["gender"],
				"city"			=> $user_data["city"],
				"province"		=> $user_data["province"],
				"country"		=> $user_data["country"],
				"language"		=> $user_data["language"],
				"role"			=> $roles[0],
				"description"	=> $current_user->description
			),
			"access_token" => base64_encode( $token['session_key'] ),
			"expired_in" => $token['expire_in']
			
		);
		$response = rest_ensure_response( $user );
		return $response;
	}
	
	public function wp_user_openid_by_code( $request ) {
		
		$appid 		= wp_miniprogram_option('appid');
		$appsecret 	= wp_miniprogram_option('secretkey');
		
		$params = $request->get_params();

		$args = array(
			'appid' => $appid,
			'secret' => $appsecret,
			'js_code' => $params['code'],
			'grant_type' => 'authorization_code'
		);
		
		$url = 'https://api.weixin.qq.com/sns/jscode2session';
		
		$urls = add_query_arg($args,$url);
		
		$remote = wp_remote_get($urls);
		
		if( !is_array( $remote ) || is_wp_error($remote) ) {
			return new WP_Error( 'error', '授权 API 错误', array( 'status' => 403, 'message' => $remote ) );
		}

		$body = stripslashes( $remote['body'] );
		
		$response = json_decode( $body, true );
		
		return $response;
	}

	public function wp_user_auth_collection_params() {
		$params = array();
		$params['encryptedData'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "微信授权登录，包括敏感数据在内的完整用户信息的加密数据.",
			'type'	=>	 "string"
		);
		$params['code'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "用户登录凭证（有效期五分钟）",
			'type'	=>	 "string"
		);
		$params['iv'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "微信授权登录，加密算法的初始向量.",
			'type'	=>	 "string"
		);
		return $params;
	}

	public function wp_user_openid_collection_params() {
		$params = array();
		$params['code'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "用户登录凭证（有效期五分钟）",
			'type'	=>	 "string"
		);
		return $params;
	}

}