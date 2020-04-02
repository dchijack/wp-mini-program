<?php
/**
 * REST API: WP_REST_Posts_Controller class
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
class WP_REST_Subscribe_Router extends WP_REST_Controller {
	
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
        $this->resource_name = 'subscribe';
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

		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_insert_subscribe_user' ),
				'permission_callback' 	=> array( $this, 'get_items_permissions_check' ),
				'args'                	=> $this->wp_subscribe_user_params()
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
	public function get_items_permissions_check( $request ) {
		$openid = isset($request['openid'])?$request['openid']:'';
		if( $openid == '' || $openid == null ) {
			return new WP_Error( 'error', '获取用户 OpenID 错误', array( 'status' => 403 ) );
		}
		$template = isset($request['template'])?$request['template']:'';
		if( $template == '' || $template == null ) {
			return new WP_Error( 'error', '获取订阅消息模板 ID 错误', array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
	public function wp_subscribe_user_params() {
		$params = array();
		$params['openid'] = array(
			'required'			 => true,
			'default'			 => '',
			'description'        => __( '用户 OpenID。默认值为空。' ),
			'type'               => 'string',
		);
		$params['template'] = array(
			'required'			 => true,
			'default'			 => '',
			'description'        => __( '消息模板 ID。默认值为空' ),
			'type'               => 'string',
		);
		$params['pages'] = array(
			'default'			 => '',
			'description'        => __( '获取页面。默认值为空' ),
			'type'               => 'string',
		);
		$params['platform'] = array(
			'default'			 => '',
			'description'        => __( '获取平台。默认值为空' ),
			'type'               => 'string',
		);
		$params['program'] = array(
			'default'			 => '',
			'description'        => __( '获取应用。默认值为空' ),
			'type'               => 'string',
		);
		return $params;
	}

	/**
	 * Retrieves a collection of posts.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function wp_insert_subscribe_user( $request ) {
		$args = array();
		$args['openid'] = $request['openid'];
		$args['template'] = $request['template'];
		$rows = MP_Subscribe::mp_user_subscribe_template_count( $request['openid'], $request['template'] );
		$status = isset($request['status']) ? $request['status'] : 'accept';
		$args['pages'] = isset($request['pages']) ? $request['pages'] : '';
		$args['platform'] = isset($request['platform']) ? $request['platform'] : '';
		$args['program'] = isset($request['program']) ? $request['program'] : '';
		$args['date'] = current_time( 'mysql' );
		if( $rows && $status == 'accept' ) {
			$args['count'] = (int)$rows->count + 1;
			$subscribe = MP_Subscribe::mp_update_subscribe_user( $request['openid'], $request['template'], $args );
		} else if( ! $rows && $status == 'accept' ) {
			$args['count'] = 1;
			$subscribe = MP_Subscribe::mp_insert_subscribe_user( $args );
		} else if( ! $rows && $status != 'accept' ) {
			$args['count'] = 0;
			$subscribe = MP_Subscribe::mp_insert_subscribe_user( $args );
		} else {
			$subscribe = '';
		}
		if( $subscribe ) {
			$result = array( 'status' => 200, 'code' => 'success', 'message' => '订阅消息完成' );
		} else {
			$result = array( 'status' => 400, 'code' => 'success', 'message' => '订阅消息失败' );
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

}