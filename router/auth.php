<?php
/**
 * REST API: WP_REST_Users_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Core class to access posts via the REST API.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */
class WP_REST_Auth_Router extends WP_REST_Controller {
	
	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = '/';
	}
	
	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, $this->resource_name.'tencent/login', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_qq_user_auth_login' ),
				'permission_callback' 	=> array( $this, 'wp_login_permissions_check' ),
				'args'                	=> $this->wp_user_login_collection_params(),
			)
		));

	}
	
	/**
	 * Checks if a given request has access to read posts.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function wp_login_permissions_check( $request ) {

		return true;
		
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
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
	
	/**
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function wp_qq_user_auth_login( $request ) {

		date_default_timezone_set(get_option('timezone_string'));

		$iv = isset($request['iv'])?$request['iv']:'';
		$code = isset($request['code'])?$request['code']:'';
		$encryptedData = isset($request['encryptedData'])?$request['encryptedData']:'';
		if ( empty($iv) || empty($code) || empty($encryptedData) ) {
			return new WP_Error( 'error', '授权登录参数错误', array( 'status' => 500 ) );
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
		
		$urls = add_query_arg($args,$url);
		
		$remote = wp_remote_get($urls);

		if( !is_array( $remote ) || is_wp_error($remote) || $remote['response']['code'] != '200' ) {
			return new WP_Error( 'error', '获取授权 OpenID 和 Session 错误', array( 'status' => 500 ) );
		}
		
		$session = json_decode( $remote['body'], true );
		if( $session['errcode'] != 0 ) {
			return new WP_Error( 'error', $session['errmsg'], array( 'status' => 500 ) );
		}

		$auth_code = MP_Auth::decryptData($appid, $session['session_key'], urldecode($encryptedData), urldecode($iv), $data );
		if( $auth_code != 0 ) {
			return new WP_Error( 'error', '授权获取失败：' .$auth_code, array( 'status' => 400 ) );
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
				return new WP_Error( 'error', '创建用户失败', array( 'status' => 404 ) );				
			}
			add_user_meta( $user_id, 'session_key', $session['session_key']);
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
				return new WP_Error( 'error', '更新用户信息失败' , array( 'status' => 404 ) );
			}
			update_user_meta( $user_id, 'session_key', $session['session_key'] );
			update_user_meta( $user_id, 'platform', 'tencent');
		}
		
		wp_set_current_user( $user_id, $openId );
		wp_set_auth_cookie( $user_id, true );
		
		$current_user = get_user_by( 'ID', $user_id );
		$roles = ( array )$current_user->roles;
		
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

}