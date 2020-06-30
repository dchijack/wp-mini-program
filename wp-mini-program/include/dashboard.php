<?php
/*
 * WordPress Custom Dashboard
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup',function() {
	wp_add_dashboard_widget(
		'applets_dashboard_widget',        // Widget slug.
		'WordPress 小程序',                // Title.
		'imahui_applets_dashboard_widget' // Display function.
	);	
});

function update_standard_datetime($the_time) {
	$datetime = explode(" ",$the_time);
	return $datetime[0];
}

function imahui_applets_dashboard_widget() {
	// Display whatever it is you want to show.
	$minprogam = array(
		"categories" => 3
	);
	$url = 'https://mp.weitimes.com';
	$miniprograms = wp_remote_get( $url.'/wp-json/wp/v2/products' );
	if( is_array( $miniprograms ) || !is_wp_error($miniprograms) ) {
		$miniprogram = json_decode( $miniprograms['body'], true );
	} else {
		$miniprogram = array( );
	}
	$plugins = wp_remote_get( $url.'/wp-json/wp/v2/plugins/269');
	if( is_array( $plugins ) || !is_wp_error($plugins) ) {
		$plugin = json_decode( $plugins['body'], true );
		$plugin_version = isset($plugin['plugins']['version'])?$plugin['plugins']['version']:'1.0.0';
	} else {
		$plugin_version = '1.0.0';
	}
	$plugin_ver = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
	esc_url( 'https://www.imahui.com/minapp/1044.html' ),
	esc_attr( "_blank" ),
	esc_attr( '小程序 API' ),
	esc_html( '高级版插件：Version '.$plugin_version.'' )
	);
	$update_ver = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
	esc_url( 'https://www.imahui.com/minapp/2185.html' ),
	esc_attr( "_blank" ),
	esc_attr( ' Mini Program API ' ),
	esc_html( '查看 WordPress 免费小程序' )
	);
	$html = '';
	$html .= '<div class="main">
	<ul>
	<li class="post-count">'.$plugin_ver.'</li>
	<li class="page-count">'.$update_ver.'</li>
	</ul>';
	foreach($miniprogram as $post) {
		$version = isset($post["version"])?$post["version"]:"1.0.0";
		$title = isset($post["title"])?$post["title"]:"丸子小程序";
		$date = isset($post["date"])?$post["date"]:date('Y-m-d h:i:s');
		$html .= '<p id="applets-version"><a href="https://www.weitimes.com/" class="button">查看 Version '.$version.'</a> <span id="wp-version">'.$title.'   更新:'.update_standard_datetime($date).'</span></p>';
	}
	$html .= '<p class="community-events-footer">
	<a href="https://www.imahui.com/" target="_blank">艾码汇 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="http://www.wpstorm.cn/" target="_blank">小程序 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://www.weitimes.com/" target="_blank">丸子小程序 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://static.weitimes.com/go/aliyun.html" target="_blank">阿里云 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://static.weitimes.com/go/tencent.html" target="_blank">腾讯云 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://static.weitimes.com/go/huawei.html" target="_blank">华为云 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
	</p>';
	$html .= '</div>';
	echo $html;
}