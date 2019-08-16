<?php
/*
 * WordPress Custom API Data Hooks
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
	$url = 'https://mp.weitimes.com/wp-json/wp/v2/posts';
	$minprogams = add_query_arg($minprogam,$url);
	$notice = wp_remote_get($minprogams);
	$notices = json_decode( $notice['body'], true );
	$count = count($notices);
	$plugin = array(
		"categories" => 1
	);
	$plugins = add_query_arg($plugin,$url);
	$update = wp_remote_get($plugins);
	$updates = json_decode( $update['body'], true );
	$plugin_ver = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
	esc_url( 'https://www.imahui.com/minapp/1044.html' ),
	"_blank",
	esc_attr( '小程序 API' ),
	'高级版插件：Version '.$updates[0]["meta"]["version"].''
	);
	$update_ver = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
	esc_url( 'https://www.imahui.com/minapp/1747.html' ),
	"_blank",
	esc_attr( ' Mini Program API ' ),
	'查看免费 WordPress 小程序详情'
	);
	$html = '';
	$html .= '<div class="main">
	<ul>
	<li class="post-count">'.$plugin_ver.'</li>
	<li class="page-count">'.$update_ver.'</li>
	</ul>';
	foreach($notices as $post) {
		$version = $post["meta"]["version"];
		$title = $post["title"]["rendered"];
		//$down = isset($post["meta"]["views"])?' 下载:'.$post["meta"]["views"].'次':'';
		$html .= '<p id="applets-version"><a href="https://www.imahui.com/" class="button">获取 Version '.$version.'</a> <span id="wp-version">'.$title.'   更新:'.update_standard_datetime($post["date"]).'</span></p>';
	}
	$html .= '<p class="community-events-footer">
	<a href="https://www.imahui.com/" target="_blank">艾码汇 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://www.imahui.com/minapp" target="_blank">小程序 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="http://mzhuti.com/" target="_blank">M主题小程序 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://get-vpush2.mssnn.cn/?a=imahui" target="_blank">小程序推送 <span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>';
	$html .= '</div>';
	echo $html;
}