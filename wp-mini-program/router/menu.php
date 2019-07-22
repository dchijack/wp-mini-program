<?php
/**
 * REST API: WP_REST_Message_Controller class
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
class WP_REST_Menu_Router extends WP_REST_Controller {
	
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
        $this->resource_name = 'menu';
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
				'callback'            	=> array( $this, 'get_minapp_menu' ),
				'permission_callback' 	=> array( $this, 'wp_menu_permissions_check' ),
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
	public function wp_menu_permissions_check( $request ) {
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
	
	public function get_minapp_menu( ) {
		
		$data = array();
		
		if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ 'minapp-menu' ] ) ) {
			$menu = wp_get_nav_menu_object( $locations[ 'minapp-menu' ] );
			$navigation = wp_get_nav_menu_items($menu->term_id);
			foreach($navigation as $nav) {
				$_data = array();
				if($nav->type == 'taxonomy' && $nav->object == 'category') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = 'page';
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = '/pages/list/list?id='.$nav->object_id;
				} elseif($nav->type == 'post_type' && $nav->object == 'post') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = 'page';
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = '/pages/detail/detail?id='.$nav->object_id;
				} elseif($nav->type == 'post_type' && $nav->object == 'page') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = 'page';
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = '/pages/page/page?id='.$nav->object_id;
				} elseif($nav->xfn == 'app') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = $nav->xfn;
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['appid'] = str_replace('https://','',str_replace('http://','',$nav->url));
				} elseif($nav->xfn == 'tel') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = $nav->xfn;
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = str_replace('https://','',str_replace('http://','',$nav->url));
				} elseif($nav->xfn == 'page') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = $nav->xfn;
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = str_replace('https://','',str_replace('http://','',$nav->url));
				} elseif($nav->xfn == 'contact') {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = $nav->xfn;
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = str_replace('https://','',str_replace('http://','',$nav->url));
				} else {
					$_data['id'] = $nav->menu_order;
					$_data['name'] = $nav->title;
					$_data['type'] = $nav->xfn;
					$_data['class'] = $nav->classes;
					$_data['icon'] = $nav->description;
					$_data['url'] = '/pages/view/view?url='.$nav->url;
				}
				$data[] = $_data;
			}
		}

		wp_cache_set('miniprogram_menu',$data,'miniprogram_menu_group',3600);

		if($data) {
			$data = wp_cache_get('miniprogram_menu','miniprogram_menu_group');
			if($data === false){
				$data = $_data;
				wp_cache_set('miniprogram_menu',$data,'miniprogram_menu_group',3600);
			}
			$result = array(
				'status'	=> 200,
				'success' 	=> true ,
				'message'	=> 'miniprogram menu setting success',
				'data'		=> $data
			);
		} else {
			$result = array(
				'status'	=> 500,
				'success' 	=> false ,
				'message'	=> 'miniprogram menu setting failure'
			);
		}
		
		$response = rest_ensure_response( $result );
		
		return $response;
		
	}
	
}