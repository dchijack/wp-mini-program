<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Security_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = 'security';
	}

	public function register_routes() {
		
		register_rest_route( $this->namespace, '/'.$this->resource_name .'/text', array(
			array(
				'methods'             	=> WP_REST_Server::CREATABLE,
				'callback'            	=> array( $this, 'wp_post_content_security' ),
				'permission_callback' 	=> array( $this, 'get_item_permissions_check' ),
				'args'                	=> array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) )
				)
			)
		) );

	}

	public function get_item_permissions_check( $request ) {
		return true;
	}

	public function wp_post_content_security( $request ) {
		$content = isset($request['content'])?$request['content']:'';
		if( !$content ) {
			return new WP_Error( 'error', '检测文本内容不能为空', array( 'status' => 403 ) );
		}
		$msgCheck = apply_filters( 'security_msgSecCheck', $content );
		$response = rest_ensure_response( $msgCheck );
		return $response;
	}
	
}