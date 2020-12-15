<?php

if ( !defined( 'ABSPATH' ) ) exit;

class WP_REST_Advert_Router extends WP_REST_Controller {

	public function __construct( ) {
		$this->namespace     = 'mp/v1';
        $this->resource_name = 'advert';
	}

	public function register_routes() {

		register_rest_route( $this->namespace, '/'.$this->resource_name.'/wechat', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_wechat_advert_setting' ),
				'permission_callback' 	=> array( $this, 'wp_advert_permissions_check' ),
				'args'                => $this->advert_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/'.$this->resource_name.'/qq', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_qq_advert_setting' ),
				'permission_callback' 	=> array( $this, 'wp_advert_permissions_check' ),
				'args'                => $this->advert_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/'.$this->resource_name.'/baidu', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_baidu_advert_setting' ),
				'permission_callback' 	=> array( $this, 'wp_advert_permissions_check' ),
				'args'                => $this->advert_collection_params()
			)
		));

		register_rest_route( $this->namespace, '/'.$this->resource_name.'/toutiao', array(
			array(
				'methods'             	=> WP_REST_Server::READABLE,
				'callback'            	=> array( $this, 'get_toutiao_advert_setting' ),
				'permission_callback' 	=> array( $this, 'wp_advert_permissions_check' ),
				'args'                => $this->advert_collection_params()
			)
		));
		
	}

	public function wp_advert_permissions_check( $request ) {
		return true;
	}

	public function get_wechat_advert_setting( $request ) {
		$type = isset($request["type"])?$request["type"]:'index';
		if( $type == 'index' ) {
			$adOpen = wp_miniprogram_option('we_i_open');
			$adType = wp_miniprogram_option('we_i_type');
			$adImage = wp_miniprogram_option('we_i_image');
			$adArgs = wp_miniprogram_option('we_i_args');
		} else if( $type == 'list' ) {
			$adOpen = wp_miniprogram_option('we_t_open');
			$adType = wp_miniprogram_option('we_t_type');
			$adImage = wp_miniprogram_option('we_t_image');
			$adArgs = wp_miniprogram_option('we_t_args');
		} else if( $type == 'detail' ) {
			$adOpen = wp_miniprogram_option('we_d_open');
			$adType = wp_miniprogram_option('we_d_type');
			$adImage = wp_miniprogram_option('we_d_image');
			$adArgs = wp_miniprogram_option('we_d_args');
		} else if( $type == 'page' ) {
			$adOpen = wp_miniprogram_option('we_p_open');
			$adType = wp_miniprogram_option('we_p_type');
			$adImage = wp_miniprogram_option('we_p_image');
			$adArgs = wp_miniprogram_option('we_p_args');
		}
		if( $adType == 'unit' ) {
			$point = strpos( $adArgs, '|' );
			$_data = array( "type" => $adType );
			if( $point ) {
				$_data["kind"] = substr( $adArgs, 0, $point );
				$_data["code"] = substr( $adArgs, $point + 1 );
			} else {
				$_data["kind"] = "banner";
				$_data["code"] = $adArgs;
			}
		} else {
			$_data = array( "type" => $adType, "thumbnail" => $adImage, "code" => $adArgs );
		}
		if( $adOpen ) {
			if( !empty($adType) && !empty($adArgs) ) {
				if( empty($adImage) && $adType != 'unit' ) {
					$result["success"] = false;
					$result["message"] = "小程序广告获取失败,广告图片没有设置";
					$result["status"] = 400;
				} else {
					$result["success"] = true;
					$result["message"] = "小程序广告获取成功";
					$result["status"] = 200;
					$result["data"] = $_data;
				}
			} else {
				$result["success"] = false;
				$result["message"] = "小程序广告获取失败,广告没有设置";
				$result["status"] = 400;
			}
		} else {
			$result["success"] = false;
			$result["message"] = "小程序广告获取失败,广告没有开启";
			$result["status"] = 400;
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

	public function get_qq_advert_setting( $request ) {
		$type = isset($request["type"])?$request["type"]:'index';
		if( $type == 'index' ) {
			$adOpen = wp_miniprogram_option('qq_i_open');
			$adType = wp_miniprogram_option('qq_i_type');
			$adImage = wp_miniprogram_option('qq_i_image');
			$adArgs = wp_miniprogram_option('qq_i_args');
		} else if( $type == 'list' ) {
			$adOpen = wp_miniprogram_option('qq_t_open');
			$adType = wp_miniprogram_option('qq_t_type');
			$adImage = wp_miniprogram_option('qq_t_image');
			$adArgs = wp_miniprogram_option('qq_t_args');
		} else if( $type == 'detail' ) {
			$adOpen = wp_miniprogram_option('qq_d_open');
			$adType = wp_miniprogram_option('qq_d_type');
			$adImage = wp_miniprogram_option('qq_d_image');
			$adArgs = wp_miniprogram_option('qq_d_args');
		} else if( $type == 'page' ) {
			$adOpen = wp_miniprogram_option('qq_p_open');
			$adType = wp_miniprogram_option('qq_p_type');
			$adImage = wp_miniprogram_option('qq_p_image');
			$adArgs = wp_miniprogram_option('qq_p_args');
		}
		if( $adType == 'unit' ) {
			$point = strpos( $adArgs, '|' );
			$_data = array( "type" => $adType );
			if( $point ) {
				$_data["kind"] = substr( $adArgs, 0, $point );
				$_data["code"] = substr( $adArgs, $point + 1 );
			} else {
				$_data["kind"] = "banner";
				$_data["code"] = $adArgs;
			}
		} else {
			$_data = array( "type" => $adType, "thumbnail" => $adImage, "code" => $adArgs );
		}
		if( $adOpen ) {
			if( !empty($adType) && !empty($adArgs) ) {
				if( empty($adImage) && $adType != 'unit' ) {
					$result["success"] = false;
					$result["message"] = "小程序广告获取失败,广告图片没有设置";
					$result["status"] = 400;
				} else {
					$result["success"] = true;
					$result["message"] = "小程序广告获取成功";
					$result["status"] = 200;
					$result["data"] = $_data;
				}
			} else {
				$result["success"] = false;
				$result["message"] = "小程序广告获取失败,广告没有设置";
				$result["status"] = 400;
			}
		} else {
			$result["success"] = false;
			$result["message"] = "小程序广告获取失败,广告没有开启";
			$result["status"] = 400;
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

	public function get_baidu_advert_setting( $request ) {
		$type = isset($request["type"])?$request["type"]:'index';
		if( $type == 'index' ) {
			$adOpen = wp_miniprogram_option('bd_i_open');
			$adType = wp_miniprogram_option('bd_i_type');
			$adImage = wp_miniprogram_option('bd_i_image');
			$adArgs = wp_miniprogram_option('bd_i_args');
		} else if( $type == 'list' ) {
			$adOpen = wp_miniprogram_option('bd_t_open');
			$adType = wp_miniprogram_option('bd_t_type');
			$adImage = wp_miniprogram_option('bd_t_image');
			$adArgs = wp_miniprogram_option('bd_t_args');
		} else if( $type == 'detail' ) {
			$adOpen = wp_miniprogram_option('bd_d_open');
			$adType = wp_miniprogram_option('bd_d_type');
			$adImage = wp_miniprogram_option('bd_d_image');
			$adArgs = wp_miniprogram_option('bd_d_args');
		} else if( $type == 'page' ) {
			$adOpen = wp_miniprogram_option('bd_p_open');
			$adType = wp_miniprogram_option('bd_p_type');
			$adImage = wp_miniprogram_option('bd_p_image');
			$adArgs = wp_miniprogram_option('bd_p_args');
		}
		if( $adType == 'unit' ) {
			$point = strpos( $adArgs, '|' );
			$_data = array( "type" => $adType );
			if( $point ) {
				$_data["kind"] = substr( $adArgs, 0, $point );
				$_data["code"] = substr( $adArgs, $point + 1 );
			} else {
				$_data["kind"] = "feed";
				$_data["code"] = $adArgs;
			}
		} else {
			$_data = array( "type" => $adType, "thumbnail" => $adImage, "code" => $adArgs );
		}
		if( $adOpen ) {
			if( !empty($adType) && !empty($adArgs) ) {
				if( empty($adImage) && $adType != 'unit' ) {
					$result["success"] = false;
					$result["message"] = "小程序广告获取失败,广告图片没有设置";
					$result["status"] = 400;
				} else {
					$result["success"] = true;
					$result["message"] = "小程序广告获取成功";
					$result["status"] = 200;
					$result["data"] = $_data;
				}
			} else {
				$result["success"] = false;
				$result["message"] = "小程序广告获取失败,广告没有设置";
				$result["status"] = 400;
			}
		} else {
			$result["success"] = false;
			$result["message"] = "小程序广告获取失败,广告没有开启";
			$result["status"] = 400;
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

	public function get_toutiao_advert_setting( $request ) {
		$type = isset($request["type"])?$request["type"]:'index';
		if( $type == 'index' ) {
			$adOpen = wp_miniprogram_option('tt_i_open');
			$adType = wp_miniprogram_option('tt_i_type');
			$adImage = wp_miniprogram_option('tt_i_image');
			$adArgs = wp_miniprogram_option('tt_i_args');
		} else if( $type == 'list' ) {
			$adOpen = wp_miniprogram_option('tt_t_open');
			$adType = wp_miniprogram_option('tt_t_type');
			$adImage = wp_miniprogram_option('tt_t_image');
			$adArgs = wp_miniprogram_option('tt_t_args');
		} else if( $type == 'detail' ) {
			$adOpen = wp_miniprogram_option('tt_d_open');
			$adType = wp_miniprogram_option('tt_d_type');
			$adImage = wp_miniprogram_option('tt_d_image');
			$adArgs = wp_miniprogram_option('tt_d_args');
		} else if( $type == 'page' ) {
			$adOpen = wp_miniprogram_option('tt_p_open');
			$adType = wp_miniprogram_option('tt_p_type');
			$adImage = wp_miniprogram_option('tt_p_image');
			$adArgs = wp_miniprogram_option('tt_p_args');
		}
		if( $adType == 'unit' ) {
			$point = strpos( $adArgs, '|' );
			$_data = array( "type" => $adType );
			if( $point ) {
				$_data["kind"] = substr( $adArgs, 0, $point );
				$_data["code"] = substr( $adArgs, $point + 1 );
			} else {
				$_data["kind"] = "banner";
				$_data["code"] = $adArgs;
			}
		} else {
			$_data = array( "type" => $adType, "thumbnail" => $adImage, "code" => $adArgs );
		}
		if( $adOpen ) {
			if( !empty($adType) && !empty($adArgs) ) {
				if( empty($adImage) && $adType != 'unit' ) {
					$result["success"] = false;
					$result["message"] = "小程序广告获取失败,广告图片没有设置";
					$result["status"] = 400;
				} else {
					$result["success"] = true;
					$result["message"] = "小程序广告获取成功";
					$result["status"] = 200;
					$result["data"] = $_data;
				}
			} else {
				$result["success"] = false;
				$result["message"] = "小程序广告获取失败,广告没有设置";
				$result["status"] = 400;
			}
		} else {
			$result["success"] = false;
			$result["message"] = "小程序广告获取失败,广告没有开启";
			$result["status"] = 400;
		}
		$response = rest_ensure_response( $result );
		return $response;
	}

	public function advert_collection_params() {
		$params = array();
		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
		$params['type'] = array(
			'default'			 => '',
			'description'        => __( '广告页面类型, 默认为 index (首页)。' ),
			'type'               => 'string',
		);
		return $params;
	}

}