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
		$post_type = get_post_type( $post_id );
		if( $post_type == 'post' ) {
			$post_path = '/pages/detail/detail?id='.$post_id;
		} else {
			$post_path = "/pages/index/index";
		}
		$path = isset($request['path'])?$request['path']:$post_path;
		$uploads = wp_upload_dir();
		$qrcode_path = $uploads['basedir'] .'/qrcode/';
		$qrcode_link = str_replace("http://","https://",$uploads["baseurl"])."/qrcode/qrcode-".$post_id.".png";
		
		if (!is_dir($qrcode_path)) {
			mkdir($qrcode_path, 0755);
		}
		if( $qrcode_type ) {
			$qrcode 	= $qrcode_path.'qrcode-'.$qrcode_type.'-'.$post_id.'.png';
		} else {
			$qrcode 	= $qrcode_path.'qrcode-'.$post_id.'.png';
		}
		$appid 			= wp_miniprogram_option('appid');
		$appsecret 		= wp_miniprogram_option('secretkey');
		
		$thumbnail = apply_filters( 'post_thumbnail', $post_id );
		if( $thumbnail ) {
			$prefix = parse_url($thumbnail);
			$domain = $prefix["host"];
			$trust_domain = wp_miniprogram_option('trust_domain');
			$trust_domain = str_replace( "http://", "", str_replace( "https://", "", $trust_domain ) );
			$trust = explode(PHP_EOL, $trust_domain);
			if( in_array($domain,$trust) ) { 
				$cover = $thumbnail;
			} else {  
				$cover = wp_miniprogram_option('thumbnail');
			}
		} else {
			$cover = wp_miniprogram_option('thumbnail');
		}
		
		if(!is_file($qrcode)) {
			
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
			
			$token = wp_remote_get($url);
			
			if( !is_array( $token ) || is_wp_error($token) || $token['response']['code'] != '200' ) {
				
				return new WP_Error( 'error', '获取 Token 错误：' .json_encode( $token ), array( 'status' => 500 ) );
				
			}
			
			$token_data = json_decode( $token['body'], true );
			
			if(empty($token_data['errcode'])) {
				
				$access_token =$token_data['access_token'];
				
				if( !empty($access_token) ) {
					
					  //接口A小程序码,总数10万个（永久有效，扫码进入path对应的动态页面）
					$api = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
					//接口B小程序码,不限制数量（永久有效，将统一打开首页，可根据scene跟踪推广人员或场景）
					//$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
					//接口C小程序二维码,总数10万个（永久有效，扫码进入path对应的动态页面）
					//$url = 'http://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN;
					//header('content-type:image/png');
					
					$color = array(
						"r" => "0",  //这个颜色码自己到Photoshop里设
						"g" => "0",  //这个颜色码自己到Photoshop里设
						"b" => "0",  //这个颜色码自己到Photoshop里设
					);
					
					$data = array(
						//$data['scene'] = "scene"; //自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
						//$data['page'] = "pages/index/index"; //扫码后对应的path，只能是固定页面
						'path' => $path, // 前端传过来的页面path,不能为空，最大长度 128 字节
						'width' => intval(100), // 设置二维码尺寸,二维码的宽度
						'auto_color' => false, // 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
						'line_color' => $color, // auth_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"},十进制表示
						'is_hyaline' => true, // 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
					);
					$args = json_encode($data);
					
					$content = get_content_by_curl($api,$args);
					
					if($content !='error') {
						
						//输出二维码
						file_put_contents($qrcode,$content);
						
						$result["status"]		= 200; 
						$result["code"]			= "success";
						$result["message"]		= "qrcode creat success"; 
						$result["qrcode"]		= $qrcode_link;
						$result["cover"] 		= $cover;
						
					} else {
						
						$result["status"]		= 500; 
						$result["code"]			= "error";
						$result["message"]	= "qrcode creat error"; 
						
					}
					
				} else {
					
					$result["status"]		= 500; 
					$result["code"]			= "error";
					$result["message"]		= "access_token is empty"; 
					
				}
				
			} else {
				
				$result["status"]		= 500; 
				$result["code"]			= "error";
				$result["message"]		= "access_token code error"; 
				
			}
			
		} else {
			
			$result["status"]		= 200; 
			$result["code"]			= "success";
			$result["message"]		= "qrcode creat success"; 
			$result["qrcode"]		= $qrcode_link;
			$result["cover"] 		= $cover;
			
		}
		
		$response = rest_ensure_response( $result );
		return $response;
		
	}
	
}