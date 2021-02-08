<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Qrcode_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
    	$this->resource_name = 'qrcode';
	}

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

	public function wp_qrcode_permissions_check($request) {
		return true;
	}

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
		$blog_url = get_bloginfo('url');
		$qrcode_path = $wp_upload['basedir'] .'/qrcode/';
		$qrcode_urls = $wp_upload['baseurl'] .'/qrcode/';
		$parse_blog_url = wp_parse_url( $blog_url );
		$parse_qrcode_url = wp_parse_url( $qrcode_urls );
		if( $parse_blog_url['host'] != $parse_qrcode_url['host'] ) {
			$qrcode_urls = str_replace( $parse_qrcode_url['host'], $parse_blog_url['host'], $qrcode_urls );
		}

		if( $qrcode_type && $post_id ) {
			$qrcode 	 = $qrcode_path.$qrcode_type."-qrcode-".$post_id.".png";
			$qrcode_link = trailingslashit( str_replace( "http://", "https://", $qrcode_urls ) ).$qrcode_type."-qrcode-".$post_id.".png";
		} else if( $qrcode_type && !$post_id ) {
			$qrcode 	 = $qrcode_path.$qrcode_type."-qrcode-".$post_type.".png";
			$qrcode_link = trailingslashit( str_replace( "http://", "https://", $qrcode_urls ) ).$qrcode_type."-qrcode-".$post_type.".png";
		} else {
			$qrcode 	 = $qrcode_path.'qrcode-'.$post_id.'.png';
			$qrcode_link = trailingslashit( str_replace( "http://", "https://", $qrcode_urls ) )."qrcode-".$post_id.".png";
		}

		$cover_link = apply_filters( 'post_thumbnail', $post_id );
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
							$result["cover"] 		= apply_filters( 'mp_cover_url', $cover_link );
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
			$result["cover"] 		= apply_filters( 'mp_cover_url', $cover_link );
		}
		$response = rest_ensure_response( $result );
		return $response;
	}
}