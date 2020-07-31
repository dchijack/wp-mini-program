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
class WP_REST_Qrcode_Router extends WP_REST_Controller {

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
    	$this->resource_name = 'qrcode';
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
				'callback'            	=> array( $this, 'wp_post_qrcode' ),
				'permission_callback' 	=> array( $this, 'wp_qrcode_permissions_check' ),
				'args'                	=> $this->qrcode_collection_params(),
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
	public function wp_qrcode_permissions_check($request) {
		return true;
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
	public function qrcode_collection_params() {
		$params = array();
		$params['id'] = array(
			'default'			 => 0,
			'description'        => __( '帖子ID。如果等于0以外的其他内容，则将更新具有该ID的帖子。默认值为0。' ),
			'type'               => 'integer',
		);
		$params['path'] = array(
			'default'			 => '',
			'description'        => __( '小程序详情页路径。默认小程序首页/pages/index/index' ),
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
	public function wp_post_qrcode( $request ) {
		$post_id = $request['id'];
		$qrcode_type = isset($request['type']) ? $request['type'] : '';
		if( $post_id ) {
			$post_type = get_post_type( $post_id );
		} else {
			$post_type = "custom";
		}
		if( $post_type == 'post' ) {
			$post_path = '/pages/detail/detail?id='.$post_id;
		} else {
			$post_path = "/pages/index/index";
		}
		$path = isset($request['path']) && $request['path'] ? $request['path'] : $post_path;
		$wp_upload = wp_upload_dir();
		$upload_path = get_option('upload_path');
		
		if( is_multisite() ) {
			$blog_id = get_current_blog_id();
			$blog_url = get_site_url( 1 );
			if( $blog_id === 1 ) {
				$qrcode_path = $wp_upload['basedir'] .'/qrcode/';
				$upload_url = $upload_path ? trailingslashit($blog_url).$upload_path : trailingslashit($blog_url).'wp-content/uploads';
			} else {
				$qrcode_path = $wp_upload['basedir'] .'/sites/'.$blog_id.'/qrcode/';
				$upload_url = $upload_path ? trailingslashit($blog_url).$upload_path.'/sites/'.$blog_id : trailingslashit($blog_url).'wp-content/uploads/sites/'.$blog_id;
			}
		} else {
			$blog_url = get_bloginfo('url');
			$qrcode_path = $wp_upload['basedir'] .'/qrcode/';
			$upload_url = $upload_path ? trailingslashit($blog_url).$upload_path : trailingslashit($blog_url).'wp-content/uploads';
		}

		if( $qrcode_type && $post_id ) {
			$qrcode 	 = $qrcode_path.$qrcode_type."-qrcode-".$post_id.".png";
			$qrcode_link = str_replace("http://","https://",$upload_url)."/qrcode/".$qrcode_type."-qrcode-".$post_id.".png";
		} else if( $qrcode_type && !$post_id ) {
			$qrcode 	 = $qrcode_path.$qrcode_type."-qrcode-".$post_type.".png";
			$qrcode_link = str_replace("http://","https://",$upload_url)."/qrcode/".$qrcode_type."-qrcode-".$post_type.".png";
		} else {
			$qrcode 	 = $qrcode_path.'qrcode-'.$post_id.'.png';
			$qrcode_link = str_replace("http://","https://",$upload_url)."/qrcode/qrcode-".$post_id.".png";
		}

		$cover_link = apply_filters( 'miniprogram_prefix_thumbnail', $post_id );
		if( empty($cover_link) ) {
			$cover_link = str_replace("http://","https://",wp_miniprogram_option('thumbnail'));
		}

		if (!is_dir($qrcode_path)) {
			mkdir($qrcode_path, 0755);
		}
		
		if(!is_file($qrcode)) {
			$token = MP_Auth::we_miniprogram_access_token();
			if( !isset($token['errcode']) || empty($token['errcode']) ) {
				$access_token = $token['access_token'];
				if( !empty($access_token) ) {
					$api = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
					$color = array(
						"r" => "0",
						"g" => "0",
						"b" => "0",
					);
					$data = array(
						'path' => $path,
						'width' => intval(280),
						'auto_color' => false,
						'line_color' => $color,
						'is_hyaline' => true,
					);
					$args = array(
						'method'  => 'POST',
						'body' 	  => wp_json_encode( $data ),
						'headers' => array(),
						'cookies' => array()
					);
					$remote = wp_remote_post( $api, $args );
					$content = wp_remote_retrieve_body( $remote );
					if( !empty( $content ) ) {
						file_put_contents($qrcode,$content);
						if( is_file($qrcode) ) {
							$result["status"]		= 200; 
							$result["code"]			= "success";
							$result["message"]		= "qrcode creat success";
							$result["qrcode"]		= $qrcode_link;
							$result["cover"] 		= $cover_link;
						} else {
							$result["status"]		= 400; 
							$result["code"]			= "error";
							$result["message"]		= "qrcode creat error";
						}
					} else {
						$result["status"]		= 400; 
						$result["code"]			= "error";
						$result["message"]		= "qrcode creat error"; 
						
					}
				} else {
					$result["status"]		= 400; 
					$result["code"]			= "error";
					$result["message"]		= "access_token is empty"; 
					
				}
			} else {
				$result["status"]		= 400; 
				$result["code"]			= "error";
				$result["message"]		= "access_token code error"; 
			}
		} else {
			$result["status"]		= 200; 
			$result["code"]			= "success";
			$result["message"]		= "qrcode creat success"; 
			$result["qrcode"]		= $qrcode_link;
			$result["cover"] 		= $cover_link;
		}
		$response = rest_ensure_response( $result );
		return $response;
	}
}