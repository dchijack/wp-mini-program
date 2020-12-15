<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Auth_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
	}

	public function register_routes() {

		register_rest_route( $this->namespace, '/tencent/login', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_qq_user_auth_login' ),
				'permission_callback' 	=> array( $this, 'wp_login_permissions_check' ),
				'args'                	=> $this->wp_user_login_collection_params(),
			)
		));

		register_rest_route( $this->namespace, '/baidu/login', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_baidu_user_auth_login' ),
				'permission_callback' 	=> array( $this, 'wp_login_permissions_check' ),
				'args'                	=> $this->wp_user_login_collection_params(),
			)
		));

		register_rest_route( $this->namespace, '/toutiao/login', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_toutiao_user_auth_login' ),
				'permission_callback' 	=> array( $this, 'wp_login_permissions_check' ),
				'args'                	=> $this->wp_user_login_collection_params(),
			)
		));

	}

	public function wp_login_permissions_check( $request ) {

		return true;
		
	}

	public function wp_user_login_collection_params() {
		$params = array();
		$params['iv'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "授权登录，用户基本信息.",
			'type'	=>	 "string"
		);
		$params['code'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "登录凭证（有效期五分钟）",
			'type'	=>	 "string"
		);
		$params['encryptedData'] = array(
			'required' => true,
			'default'	=> '',
			'description'	=> "授权登录，用户基本信息",
			'type'	=>	 "string"
		);
		return $params;
	}

	public function wp_qq_user_auth_login( $request ) {

		date_default_timezone_set(get_option('timezone_string'));

		$iv = isset($request['iv'])?$request['iv']:'';
		$code = isset($request['code'])?$request['code']:'';
		$encryptedData = isset($request['encryptedData'])?$request['encryptedData']:'';
		if ( empty($iv) || empty($code) || empty($encryptedData) ) {
			return new WP_Error( 'error', '授权登录参数错误', array( 'status' => 403 ) );
		}
		
		$appid 			= wp_miniprogram_option('qq_appid');
		$appsecret 		= wp_miniprogram_option('qq_secret');
		$role 			= wp_miniprogram_option('use_role');

		$args = array(
			'appid' => $appid,
			'secret' => $appsecret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		);
		
		$url = 'https://api.q.qq.com/sns/jscode2session';
		
		$urls = add_query_arg( $args, $url );
		
		$remote = wp_remote_get( $urls );

		if( !is_array( $remote ) || is_wp_error($remote) || $remote['response']['code'] != '200' ) {
			return new WP_Error( 'error', '获取授权 OpenID 和 Session 错误', array( 'status' => 403, 'message' => $remote ) );
		}

		$body = stripslashes( $remote['body'] );
		
		$session = json_decode( $body, true );
		if( $session['errcode'] != 0 ) {
			return new WP_Error( 'error', '获取用户信息错误,请检查设置', array( 'status' => 403, 'message' => $session ) );
		}

		$auth = MP_Auth::decryptData($appid, $session['session_key'], urldecode($encryptedData), urldecode($iv), $data );
		if( $auth != 0 ) {
			return new WP_Error( 'error', '用户信息解密错误', array( 'status' => 403, 'errmsg' =>  $auth ) );
		}
		
		$user_data = json_decode( $data, true );
		
		$user_id = 0;
		
		$openId = $session['openid'];
		$expire = date('Y-m-d H:i:s',time()+7200);
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
			add_user_meta( $user_id, 'session_key', $session['session_key'] );
			add_user_meta( $user_id, 'platform', 'tencent');
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
			update_user_meta( $user_id, 'session_key', $session['session_key'] );
			update_user_meta( $user_id, 'platform', 'tencent');
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
				'platform'		=> 'tencent',
				"description"	=> $current_user->description
			),
			"access_token" => base64_encode( $session['session_key'] ),
			"expired_in" => $expire
			
		);
		$response = rest_ensure_response( $user );
		return $response;

	}

	public function wp_baidu_user_auth_login( $request ) {

		date_default_timezone_set(get_option('timezone_string'));

		$iv = isset($request['iv'])?$request['iv']:'';
		$code = isset($request['code'])?$request['code']:'';
		$encryptedData = isset($request['encryptedData'])?$request['encryptedData']:'';
		if ( empty($iv) || empty($code) || empty($encryptedData) ) {
			return new WP_Error( 'error', '授权登录参数错误', array( 'status' => 403 ) );
		}

		$appkey 		= wp_miniprogram_option('bd_appkey');
		$appsecret 		= wp_miniprogram_option('bd_secret');
		$role 			= wp_miniprogram_option('use_role');
		$expire 		= date('Y-m-d H:i:s',time()+7200);
		$user_pass 		= wp_generate_password(16,false);

		$args = array(
			'client_id' => $appkey,
			'sk' => $appsecret,
			'code' => $code
		);
		$url = 'https://spapi.baidu.com/oauth/jscode2sessionkey';
		$urls = add_query_arg( $args, $url );
		$bd_session = wp_remote_request( $urls, array( 'method' => 'POST' ) );
		$bd_session = wp_remote_retrieve_body($bd_session);

		$session = json_decode( $bd_session, true );
		$openId = $session['openid'];
		$session_key = $session['session_key'];

		$user_id = 0;
		$decrypt_data = MP_Auth::decrypt(urldecode($encryptedData), urldecode($iv), $appkey, $session_key);
		if( !$decrypt_data ) {
        	return new WP_Error( 'error', '用户信息解密错误', array( 'status' => 403, 'errmsg' =>  $decrypt_data ) );
        }
		$user_data = json_decode( $decrypt_data, true );

		if( !username_exists($openId) ) {
			$userdata = array(
                'user_login' 			=> $openId,
				'nickname' 				=> $user_data['nickname'],
				'first_name'			=> $user_data['nickname'],
				'user_nicename' 		=> $openId,
				'display_name' 			=> $user_data['nickname'],
				'user_email' 			=> date('Ymdhms').'@baidu.com',
				'role' 					=> $role,
				'user_pass' 			=> $user_pass,
				'gender'				=> $user_data['sex'],
				'openid'				=> $openId,
				'avatar' 				=> $user_data['headimgurl'],
				'expire_in'				=> $expire
            );
			$user_id = wp_insert_user( $userdata );			
			if ( is_wp_error( $user_id ) ) {
				return new WP_Error( 'error', '创建用户失败', array( 'status' => 400 ) );				
			}
			add_user_meta( $user_id, 'session_key', $session_key);
			add_user_meta( $user_id, 'platform', 'baidu');
		} else {
			$user = get_user_by( 'login', $openId );
			$userdata = array(
                'ID'            		=> $user->ID,
				'nickname' 				=> $user_data['nickname'],
				'first_name'			=> $user_data['nickname'],
				'user_nicename'			=> $openId,
				'display_name' 			=> $user_data['nickname'],
				'user_email' 			=> $user->user_email,
				'gender'				=> $user_data['sex'],
				'openid'				=> $openId,
				'avatar' 				=> $user_data['headimgurl'],
				'expire_in'				=> $expire
            );
			$user_id = wp_update_user($userdata);
			if(is_wp_error($user_id)) {
				return new WP_Error( 'error', '更新用户信息失败' , array( 'status' => 400 ) );
			}
			update_user_meta( $user_id, 'session_key', $session_key );
			update_user_meta( $user_id, 'platform', 'baidu');
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
				"nickName"		=> $user_data["nickname"],
				"openId"		=> $openId,
				"avatarUrl" 	=> $user_data["headimgurl"],
				"gender"		=> $user_data["sex"],
				"role"			=> $roles[0],
				'platform'		=> 'baidu',
				"description"	=> $current_user->description
			),
			"access_token" => base64_encode( $session['session_key'] ),
			"expired_in" => $expire
			
		);

		$response = rest_ensure_response( $user );
		return $response;

	}

	public function wp_toutiao_user_auth_login( $request ) {

		date_default_timezone_set(get_option('timezone_string'));

		$iv = isset($request['iv'])?$request['iv']:'';
		$code = isset($request['code'])?$request['code']:'';
		$encryptedData = isset($request['encryptedData'])?$request['encryptedData']:'';
		if ( empty($iv) || empty($code) || empty($encryptedData) ) {
			return new WP_Error( 'error', '授权登录参数错误', array( 'status' => 403 ) );
		}

		$appid 			= wp_miniprogram_option('tt_appid');
		$secret 		= wp_miniprogram_option('tt_secret');
		$role 			= wp_miniprogram_option('use_role');
		$expire 		= date('Y-m-d H:i:s',time()+7200);
		$user_pass 		= wp_generate_password(16,false);
		$user_id = 0;
		$args = array(
			'appid' => $appid,
			'secret' => $secret,
			'code' => $code
		);
		$url = 'https://developer.toutiao.com/api/apps/jscode2session';
		$urls = add_query_arg( $args, $url );
		$remote = wp_remote_get( $urls );
		$tt_session = wp_remote_retrieve_body($remote);

		$session = json_decode( $tt_session, true );
		$openId = $session['openid'];
		$session_key = $session['session_key'];

		$auth = MP_Auth::decryptData($appid, $session_key, urldecode($encryptedData), urldecode($iv), $data);
		if( $auth != 0 ) {
			return new WP_Error( 'error', '用户信息解密错误', array( 'status' => 403, 'errmsg' => $auth ) );
		}
		
		$user_data = json_decode( $data, true );
		
		if( !username_exists($openId) ) {
			$userdata = array(
                'user_login' 			=> $openId,
				'nickname' 				=> $user_data['nickName'],
				'first_name'			=> $user_data['nickName'],
				'user_nicename' 		=> $openId,
				'display_name' 			=> $user_data['nickName'],
				'user_email' 			=> date('Ymdhms').'@toutiao.com',
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
			add_user_meta( $user_id, 'session_key', $session_key );
			add_user_meta( $user_id, 'platform', 'toutiao');
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
			update_user_meta( $user_id, 'session_key', $session_key );
			update_user_meta( $user_id, 'platform', 'toutiao');
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
				'platform'		=> 'toutiao',
				"description"	=> $current_user->description
			),
			"access_token" => base64_encode( $session_key ),
			"expired_in" => $expire
			
		);
		$response = rest_ensure_response( $user );
		return $response;

	}

}