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
class WP_REST_Vote_Router extends WP_REST_Controller {
	
	/**
	 * Post type.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var string
	 */
	protected $post_type;

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
        $this->resource_name = 'vote';
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
				'callback'            	=> array( $this, 'wp_posts_vote' ),
				'permission_callback' 	=> array( $this, 'wp_posts_vote_permissions_check' ),
				'args'                	=> $this->vote_posts_collection_params()
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
	public function wp_posts_vote_permissions_check( $request ) {
		$post_id = isset($request['id'])?$request['id']:0;
		if( $post_id == '' || $post_id == null || $post_id == 0 ) {
			return new WP_Error( 'error', '文章 ID 错误', array( 'status' => 400 ) );
		}
		$access_token = isset($request['access_token'])?$request['access_token']:'';
		if( $access_token == '' || $access_token == null ) {
			return new WP_Error( 'error', 'Token 认证错误,未授权用户', array( 'status' => 400 ) );
		}
		$option_id = isset($request['option'])?$request['option']:'';
		if( $option_id == '' || $option_id == null ) {
			return new WP_Error( 'error', '选项错误, 不能为空', array( 'status' => 400 ) );
		}
		return true;
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
	public function wp_posts_vote( $request ) {
		$post_id = $request['id'];
		$access_token = $request['access_token'];
		$option_id = $request['option'];
		$vote_option = get_post_meta($post_id, 'vote', true);
		$options = is_serialized( $vote_option ) ? maybe_unserialize( $vote_option ) : $vote_option;
		$session = base64_decode( $access_token );
		$users = MP_Auth::login( $session );
		if ( !$users ) {
			return new WP_Error( 'error', '授权信息有误' , array( 'status' => 400 ) );
		}
		$user_id = $users->ID;
		$user = get_user_by( 'ID', $user_id );
		$openid = $user->openid;
		if( is_array($options) ) {
			if( array_key_exists( $openid, $options) ) {
				$result = array( 'status' => 201, 'code'=>'success', 'message'=>'你已经投过票' );
			} else {
				$options[$openid] = $option_id;
				if( !update_post_meta( $post_id, 'vote', serialize( $options ) ) ) {
					add_post_meta($post_id, 'vote', serialize( $options ), true);  
				}
				$result = array( 'status' => 200, 'code'=>'success', 'message'=>'恭喜,投票成功' );
			}
		} else {
			if( !empty($options) ) {
				$options = explode( ",", $options );
				if( array_key_exists( $openid, $options) ) {
					$result = array( 'status' => 201, 'code'=>'success', 'message'=>'你已经投过票' );
				} else {
					$options[$openid] = $option_id;
					if( !update_post_meta( $post_id, 'vote', serialize( $options ) ) ) {
						add_post_meta($post_id, 'vote', serialize( $options ), true);  
					}
					$result = array( 'status' => 200, 'code'=>'success', 'message'=>'恭喜,投票成功' );
				}
			} else {
				$options = array();
				$options[$openid] = $option_id;
				if( !update_post_meta( $post_id, 'vote', serialize( $options ) ) ) {
					add_post_meta($post_id, 'vote', serialize( $options ), true);  
				}
				$result = array( 'status' => 200, 'code'=>'success', 'message'=>'恭喜,投票成功' );
			}
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
	public function vote_posts_collection_params() {
		$params = array();
		$params['id'] = array(
			'required' => true,
			'default'			 => 0,
			'description'        => __( '对象的唯一标识符' ),
			'type'               => 'integer',
		);
		$params['access_token'] = array(
			'required' => true,
			'default'			 => '',
			'description'        => __( '授权用户 Token ' ),
			'type'               => 'string',
		);
		$params['option'] = array(
			'required' => true,
			'default'			 => '',
			'description'        => __( '选项参数' ),
			'type'               => 'string',
		);
		return $params;
	}

}