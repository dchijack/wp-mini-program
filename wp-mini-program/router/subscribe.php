<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Subscribe_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = 'subscribe';
	}

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

	public function wp_insert_subscribe_user( $request ) {
		$args = array();
		$args['openid'] = $request['openid'];
		$args['template'] = $request['template'];
		$rows = get_miniprogram_subscribe_by_utplid( $request['openid'], $request['template'] );
		$status = isset($request['status']) ? $request['status'] : 'reject';
		$args['pages'] = isset($request['pages']) ? $request['pages'] : '';
		$args['platform'] = isset($request['platform']) ? $request['platform'] : '';
		$args['program'] = isset($request['program']) ? $request['program'] : '';
		$args['date'] = current_time( 'mysql' );
		if( $rows && $status == 'accept' ) {
			$args['count'] = (int)$rows->count + 1;
			$subscribe = wp_update_miniprogram_subscribe( array( "openid" => $request['openid'], "template" => $request['template'] ), $args );
		} else if( ! $rows && $status == 'accept' ) {
			$args['count'] = 1;
			$subscribe = wp_insert_miniprogram_subscribe( $args );
		} else if( ! $rows && $status != 'accept' ) {
			$args['count'] = 0;
			$subscribe = wp_insert_miniprogram_subscribe( $args );
		} else {
			$subscribe = '';
		}
		if( $subscribe ) {
			$result = array( 'status' => 200, 'code' => 'success', 'message' => '订阅完成' );
		} else {
			$result = array( 'status' => 400, 'code' => 'success', 'message' => '订阅失败' );
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

}