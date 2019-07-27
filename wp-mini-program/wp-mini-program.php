<?php
/*
Plugin Name: WP Mini Program
Plugin URI: https://www.imahui.com/minapp/1044.html
Description: 由 丸子小程序团队 基于 WordPress REST 创建小程序应用 API 数据接口。免费开源，实现 WordPress 连接小程序应用数据。<a href="https://developer.wordpress.org/rest-api/" taraget="_blank">WP REST API 使用帮助</a>。
Version: 1.0.7
Author:  艾码汇
Author URI: https://www.imahui.com/
requires at least: 4.9.5
tested up to: 5.2.1
*/
// DEFINE PLUGIN PATH
define('IMAHUI_REST_API', plugin_dir_path(__FILE__));
define('IMAHUI_REST_URL', plugin_dir_url(__FILE__ ));
// 所有插件加载完成
add_action( 'plugins_loaded', 'minprogam_plugins_loaded' );
function minprogam_plugins_loaded() {
	include( IMAHUI_REST_API.'include/include.php' );
	include( IMAHUI_REST_API.'router/router.php' );
}
add_filter( 'plugin_action_links', function( $links, $file ) {
	if ( plugin_basename( __FILE__ ) !== $file ) {
		return $links;
	}
	$settings_link = '<a href="admin.php?page=miniprogram">' . esc_html__( '设置', 'imahui' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}, 10, 2 );

register_activation_hook(__FILE__, function () {
	add_role( 'wechat', '小程序', array( 'read' => true, 'level_0' => true ) );
});
if(function_exists('register_nav_menus')) {
	register_nav_menus( array(
		'minapp-menu' => __( '小程序导航' )
	) );
}
// 获取设置选项返回数据
function wp_miniprogram_option($option_name) {
	$options = get_option('minapp');
	if($options) {
		if (array_key_exists($option_name,$options)) {
			return $options[$option_name];
		} else {
			return false;
		}	
	} else {
		return false;
	}
}