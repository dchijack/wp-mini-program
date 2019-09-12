<?php
/**
 * @package   Admin Settings
 */
if ( !defined( 'ABSPATH' ) ) exit;
include( IMAHUI_REST_API. 'admin/about.php' );
include( IMAHUI_REST_API. 'admin/options.php' );
include( IMAHUI_REST_API. 'admin/core/meta.php');
include( IMAHUI_REST_API. 'admin/core/terms.php' );
include( IMAHUI_REST_API. 'admin/core/interface.php' );
include( IMAHUI_REST_API. 'admin/core/sanitization.php' );
add_action( 'load-post.php',     'creat_meta_box' );
add_action( 'load-post-new.php', 'creat_meta_box' );
add_action( 'init', 'creat_miniprogram_terms_meta_box' );
add_action( 'admin_menu', function() {
	register_miniprogram_manage_menu();
});
add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );
add_action( 'admin_enqueue_scripts', 'enqueue_admin_scripts' );
function enqueue_admin_styles() {
	wp_enqueue_style('miniprogram', IMAHUI_REST_URL.'static/style.css', array(), '1.0' );
}
function enqueue_admin_scripts() {
	wp_enqueue_script( 'miniprogram', IMAHUI_REST_URL.'static/script.js', array( 'jquery' ), '1.0' );
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}
}

add_action( 'admin_init', function() {
	register_setting( "minapp-group", "minapp", array( 'sanitize_callback' => 'validate_sanitize_miniprogram_options' ) );
});

// Menu
if(is_admin()) {
	add_filter( 'miniprogram_manage_menus', function( $admin_menu ) {
		$submenu = array();
		$submenu[] = ['page_title' => '小程序设置','menu_title' => '基本设置', 'option_name' => 'miniprogram','slug' => 'miniprogram', 'function' => 'miniprogram_options_manage_page'];
		$submenu[] = ['page_title' => 'Mini Program API 使用指南','menu_title' => '使用指南', 'option_name' => 'miniprogram','slug' => 'guide', 'function' => 'guide'];
		$admin_menu = array(
			'menu' => [
				'page_title' => '小程序设置','menu_title' => '小程序', 'option_name' => 'miniprogram', 'function' => 'miniprogram_options_manage_page', 'icon' => 'dashicons-editor-code', 'position' => 2
			],
			'submenu'	=> $submenu
		);
		return $admin_menu;
	});
}
// Pages
function miniprogram_options_manage_page( ) {
	$option = array(
		'id' 		=> 'minapp-form',
		'options'	=> 'minapp',
		"group"		=> "minapp-group"
	);
	require_once( IMAHUI_REST_API. 'admin/core/settings.php' );
}
add_action('admin_footer', function () {
	echo '<script type="text/html" id="tmpl-mp-del-item">
	<a href="javascript:;" class="button del-item">删除</a> <span class="dashicons dashicons-menu"></span>
</script>';
});