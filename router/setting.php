<?php
/**
 * REST API: WP_REST_Setting_Controller class
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
class WP_REST_Setting_Router extends WP_REST_Controller {
	
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
        $this->resource_name = 'setting';
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
		
		register_rest_route( $this->namespace, '/'.$this->resource_name, array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_wp_setting_info' ),
				'permission_callback' 	=> array( $this, 'wp_setting_permissions_check' ),
				'args'                	=> array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) )
				)
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
	public function wp_setting_permissions_check( $request ) {
		return true;
	}
	
	/**
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	
	public function get_wp_setting_info(  ) {
		$data = array( 
			'name' => wp_miniprogram_option('appname'), 
			'description' => wp_miniprogram_option('appdesc'),
			'version' => wp_miniprogram_option('version')?wp_miniprogram_option('version'):get_bloginfo('version')
		);
		$response = rest_ensure_response( $data );
		return $response;
	}
	
}