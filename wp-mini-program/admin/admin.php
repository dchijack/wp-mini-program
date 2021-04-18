<?php
/**
 * @package   Admin Settings
 */
if ( !defined( 'ABSPATH' ) ) exit;
include( MINI_PROGRAM_REST_API. 'admin/about.php' );
include( MINI_PROGRAM_REST_API. 'admin/options.php' );
include( MINI_PROGRAM_REST_API. 'admin/core/menu.php');
include( MINI_PROGRAM_REST_API. 'admin/core/meta.php');
include( MINI_PROGRAM_REST_API. 'admin/core/terms.php' );
include( MINI_PROGRAM_REST_API. 'admin/core/interface.php' );
include( MINI_PROGRAM_REST_API. 'admin/core/sanitization.php' );
include( MINI_PROGRAM_REST_API. 'admin/page/subscribe.php' );
add_action( 'init', 'creat_miniprogram_terms_meta_box' );
add_action( 'admin_menu', function() {
	register_miniprogram_manage_menu();
	mp_install_subscribe_message_table();
});
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style('miniprogram', MINI_PROGRAM_API_URL.'static/style.css', array(), get_bloginfo('version') );
} );
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'miniprogram', MINI_PROGRAM_API_URL.'static/script.js', array( 'jquery' ), get_bloginfo('version') );
	wp_enqueue_script( 'mini-adv', MINI_PROGRAM_API_URL.'static/mini.adv.js', array( 'jquery' ), get_bloginfo('version') );
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}
} );

add_action( 'admin_init', function() {
	register_setting( "minapp-group", "minapp", array( 'sanitize_callback' => 'validate_sanitize_miniprogram_options' ) );
});

// Menu
if(is_admin()) {
	add_filter( 'miniprogram_manage_menus', function( $admin_menu ) {
		$submenu = array();
		$submenu[] = ['page_title' => '小程序设置','menu_title' => '基本设置', 'option_name' => 'miniprogram','slug' => 'miniprogram', 'function' => 'miniprogram_options_manage_page'];
		$submenu[] = ['page_title' => '小程序订阅消息统计','menu_title' => '订阅统计', 'option_name' => 'miniprogram','slug' => 'subscribe', 'function' => 'miniprogram_subscribe_message_count'];
		$submenu[] = ['page_title' => '小程序历史推送任务','menu_title' => '任务列表', 'option_name' => 'miniprogram','slug' => 'task', 'function' => 'miniprogram_subscribe_message_task_table'];
		$submenu[] = ['page_title' => 'Mini Program API 使用指南','menu_title' => '使用指南', 'option_name' => 'miniprogram','slug' => 'guide', 'function' => 'miniprogram_api_guide'];
		$admin_menu[] = array(
			'menu' => [
				'page_title' => '小程序设置','menu_title' => '小程序', 'option_name' => 'miniprogram', 'function' => 'miniprogram_options_manage_page', 'icon' => 'dashicons-editor-code', 'position' => 2
			],
			'submenu'	=> $submenu
		);
		return $admin_menu;
	} );
}
// Pages
function miniprogram_options_manage_page( ) {
	$option = array(
		'id' 		=> 'minapp-form',
		'options'	=> 'minapp',
		"group"		=> "minapp-group"
	);
	$options = apply_filters( 'miniprogram_setting_options', $options = array() );
	require_once( MINI_PROGRAM_REST_API. 'admin/core/settings.php' );
}

add_action( 'admin_notices', function () {
	if( isset($_GET['page']) && trim($_GET['page']) == 'miniprogram' && isset($_REQUEST['settings-updated']) ) {
		wp_cache_flush( );
		$class = 'notice notice-success is-dismissible';
		$message = __( '设置已更新保存!', 'imahui' );
		printf( '<div class="%1$s"><p><strong>%2$s</strong></p></div>', esc_attr( $class ), esc_html( $message ) );
	}
} );

add_action('admin_footer', function () {
	echo '<script type="text/html" id="tmpl-mp-del-item">
	<a href="javascript:;" class="button del-item">删除</a> <span class="dashicons dashicons-menu"></span>
</script>';
});