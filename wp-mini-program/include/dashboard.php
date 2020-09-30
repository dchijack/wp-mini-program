<?php
/*
 * WordPress Custom Dashboard
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup',function() {
	wp_add_dashboard_widget(
		'applets_dashboard_widget',
		'WordPress 小程序',
		'imahui_applets_dashboard_widget'
	);	
});

function update_standard_datetime($the_time) {
	$datetime = explode(" ",$the_time);
	return $datetime[0];
}

function imahui_applets_dashboard_widget() {
	$dashboard = get_transient( 'wp_applets_dashboard_cache' );
	if( $dashboard === false ) {
		$url = 'https://mp.weitimes.com/wp-json/wp/v2/miniprogram/dashboard';
		$request = wp_remote_get( $url );
		if( !is_wp_error( $request ) ) {
			$dashboard = json_decode( $request['body'], true );
			set_transient( 'wp_applets_dashboard_cache', $dashboard, 6*HOUR_IN_SECONDS );
		}
	}
	$html = '';
	$html .= '<div class="main">';
	if( !$dashboard ) {
		$html .= '<ul><li class="post-count"><a href="https://www.weitimes.com/">获取小程序信息错误</a></li><ul>';
	} else {
		$plugin = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
			esc_url( 'https://www.imahui.com/minapp/1044.html' ),
			esc_attr( "_blank" ),
			esc_attr( '小程序 API' ),
			esc_html( '高级版插件：Version '.$dashboard["plugin"]["version"].'' )
		);
		$miniprogram = sprintf( ' <a href="%s" target="%s" data-title="%s">%s</a>',
			esc_url( 'https://www.wpstorm.cn' ),
			esc_attr( "_blank" ),
			esc_attr( 'WordPress 小程序主题下载' ),
			esc_html( '查看 WordPress 小程序主题下载' )
		);
		$html .= '<ul><li class="post-count">'.$plugin.'</li>';
		$html .= '<li class="page-count">'.$miniprogram.'</li>';
		$html .= '<ul>';
		foreach($dashboard["theme"]["products"] as $i => $post) {
			$version = $post["version"];
			$title = $post["title"];
			$date = $post["date"];
			$html .= '<p id="applets-version"><a href="https://www.weitimes.com/" class="button">查看 Version '.$version.'</a> <span id="wp-version">'.$title.'   更新:'.update_standard_datetime($date).'</span></p>';
		}
	}
	
	$html .= '<p class="community-events-footer">
	<a href="https://www.imahui.com/" target="_blank">艾码汇 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://www.weitimes.com/" target="_blank">小程序 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="http://static.weitimes.com/go/aliyun.html" target="_blank">阿里云 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://static.weitimes.com/go/tencent.html" target="_blank">腾讯云 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> | 
	<a href="https://static.weitimes.com/go/huawei.html" target="_blank">云主机 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
	</p>';
	$html .= '</div>';
	echo $html;
}