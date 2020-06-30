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
class WP_REST_Posts_Router extends WP_REST_Controller {

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
        $this->resource_name = 'posts';
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

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/sticky', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_sticky_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->default_posts_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/rand', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_rand_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->default_posts_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/most', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_most_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->most_posts_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/relate', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_relate_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->relate_posts_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/comment', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_comment_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->comment_posts_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/' . $this->resource_name.'/meta', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_meta_posts' ),
				'permission_callback' 	=> array( $this, 'get_wp_posts_permissions_check' ),
				'args'                	=> $this->default_posts_collection_params()
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
	public function get_wp_posts_permissions_check( $request ) {
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
	public function get_sticky_posts( $request ) {
		$data = array();
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$access_token = isset($request['access_token'])?$request['access_token']:'';
		$offset = ($page * $per_page) - $per_page;
		$is_sticky = wp_miniprogram_option('sticky');
		if($is_sticky) {
			$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'orderby' => 'date', 'meta_key' => 'focus' );
		} else {
			$sticky = get_option( 'sticky_posts' );
			$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'orderby' => 'date', 'post__in'  => $sticky );
		}
		$args = apply_filters( "rest_post_query", $args, $request );
		$query  = new WP_Query();
		$posts = $query->query( $args );

		if($posts) {
			$data = apply_filters( 'rest_posts', $posts, $request );
		}
		
		$response  = rest_ensure_response( $data );

		return $response;

	}
	public function get_rand_posts( $request ) {
		$data = array();
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$offset = ($page * $per_page) - $per_page;
		$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'orderby' => 'rand', 'date_query' => array( array( 'after' => '1 year ago' ) ) );
		$args = apply_filters( "rest_post_query", $args, $request );
		$query  = new WP_Query();
		$posts = $query->query( $args );
		if($posts) {
			$data = apply_filters( 'rest_posts', $posts, $request );
		}
		$response  = rest_ensure_response( $data );
		return $response;
	}

	public function get_most_posts( $request ) {
		$data = array();
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$offset = ($page * $per_page) - $per_page;
		$meta = isset($request["meta"])?$request["meta"]:'views';
		$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'meta_key' => $meta, 'orderby' => 'meta_value_num', 'order' => 'DESC', 'date_query' => array( array( 'after' => '1 year ago' )), 'update_post_meta_cache' => false, 'cache_results' => false );
		$args = apply_filters( "rest_post_query", $args, $request );
		$query  = new WP_Query();
		$posts = $query->query( $args );
		if($posts) {
			$data = apply_filters( 'rest_posts', $posts, $request );
		}
		$response  = rest_ensure_response( $data );
		return $response;
	}

	public function get_relate_posts( $request ) {
		$data = array();
		$post_id = isset($request["id"])?(int)$request["id"]:0;
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$offset = ($page * $per_page) - $per_page;
		if( $post_id ) {
			$tags = get_the_tags($post_id);
			$post_tag = array();
			foreach($tags as $tag) {
				$post_tag[] = $tag->term_id;
			}
			$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'orderby' => 'date', 'order' => 'DESC', 'post__not_in' => array( $post_id ), 'tag__in' => $post_tag, 'date_query' => array( array( 'after' 	=> '1 year ago' ) ) );
		}
		$args = apply_filters( "rest_post_query", $args, $request );
		$query  = new WP_Query();
		$posts = $query->query( $args );
		if($posts) {
			$data = apply_filters( 'rest_posts', $posts, $request );
		}
		$response  = rest_ensure_response( $data );
		return $response;
	}

	public function get_comment_posts( $request ) {
		$data = array();
		$type = isset($request["type"])?$request["type"]:"comment";
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$offset = ($page * $per_page) - $per_page;
		$access_token = isset($request['access_token'])?$request['access_token']:"";
		if( $access_token ) {
			$session = base64_decode( $access_token );
			$users = MP_Auth::login( $session );
			if ( !$users ) {
				return new WP_Error( 'error', '授权信息有误' , array( 'status' => 403 ) );
			}
			$user_id = $users->ID;
			$user_comments_arr = array( 
				'type__in' => array( $type ), 
				'status' => 'approve', 
				'user_id' => $user_id, 
				'number' => $per_page, 
				'offset' => $offset 
			);
			$comments = get_comments($user_comments_arr);
			if($comments) {
				$posts = array();
				foreach ( $comments as $comment ) {
					$posts[] = $comment->comment_post_ID;
				}
				$posts = array_values(array_flip(array_flip($posts)));
				foreach ( $posts as $post_id ) {
					$post = get_post( $post_id );
					$data[] = $post;
				}
			}
		}
		$result = apply_filters( 'rest_posts', $data, $access_token );
		$response  = rest_ensure_response( $result );
		return $response;
	}

	public function get_meta_posts( $request ) {
		$data = array();
		$page = isset($request["page"])?$request["page"]:1;
		$per_page = isset($request["per_page"])?$request["per_page"]:10;
		$meta_key = isset($request['key'])?$request['key']:'';
		$meta_value = isset($request['value'])?$request['value']:'';
		if( !$meta_key || !$meta_value ) {
			return new WP_Error( 'error', '自定义字段 Key 和 Value 不能为空' , array( 'status' => 403 ) );
		}
		$offset = ($page * $per_page) - $per_page;
		$args = array( 'posts_per_page' => $per_page, 'offset' => $offset, 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'update_post_meta_cache' => false, 'cache_results' => false );
		$args = apply_filters( "rest_post_query", $args, $request );
		$query  = new WP_Query();
		$posts = $query->query( $args );
		if($posts) {
			$data = apply_filters( 'rest_posts', $posts, $request );
		}
		$response  = rest_ensure_response( $data );
		return $response;
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 *
	 * @return array Collection parameters.
	 */
	public function default_posts_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['page'] = array(
			'default'			 => 1,
			'description'        => __( '集合的当前页。' ),
			'type'               => 'integer',
		);
		$params['per_page'] = array(
			'default'		 => 10,
			'description'    => __( '结果集包含的最大项目数量。' ),
			'type'           => 'integer',
		);
		return $params;
	}
	public function most_posts_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['page'] = array(
			'default'			 => 1,
			'description'        => __( '集合的当前页。' ),
			'type'               => 'integer',
		);
		$params['per_page'] = array(
			'default'		 => 10,
			'description'    => __( '结果集包含的最大项目数量。' ),
			'type'           => 'integer',
		);
		$params['meta'] = array(
			'default'			 => 'views',
			'description'        => __( '自定义查询类型：views(阅读), commnets(评论), favs(收藏), likes(喜欢)' ),
			'type'               => 'string',
		);
		return $params;
	}
	public function relate_posts_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['id'] = array(
			'default'			 => 0,
			'description'        => __( '对象的唯一标识符' ),
			'type'               => 'integer',
		);
		$params['page'] = array(
			'default'			 => 1,
			'description'        => __( '集合的当前页。' ),
			'type'               => 'integer',
		);
		$params['per_page'] = array(
			'default'		 => 10,
			'description'    => __( '结果集包含的最大项目数量。' ),
			'type'           => 'integer',
		);
		return $params;
	}
	public function comment_posts_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['page'] = array(
			'default'			 => 1,
			'description'        => __( '集合的当前页。' ),
			'type'               => 'integer',
		);
		$params['per_page'] = array(
			'default'		 => 10,
			'description'    => __( '结果集包含的最大项目数量。' ),
			'type'           => 'integer',
		);
		$params['type'] = array(
			'default'			 => '',
			'description'        => __( '自定义查询类型：默认为空,近期评论文章, comment(评论), fav(收藏), like(喜欢)' ),
			'type'               => 'string',
		);
		$params['access_token'] = array(
			'default'			 => '',
			'description'        => __( '授权用户 Token ' ),
			'type'               => 'string',
		);
		return $params;
	}
}