<?php
/*
 * WordPress REST API Auto Updater
 */

if ( !defined( 'ABSPATH' ) ) exit;
if( !isset( $GLOBALS['plugin_update_url'] ) ) {
	$GLOBALS['plugin_update_url'] = 'https://mp.weitimes.com';
}

if( !isset( $GLOBALS['wp_mini_program'] ) ) {
	$GLOBALS['wp_mini_program'] = 'wp-mini-program';
}
// Take over the update check
add_filter('pre_set_site_transient_update_plugins', function ($checked_data) {
	global $plugin_update_url, $wp_mini_program, $wp_version;
	//Comment out these two lines during testing.
	if (empty($checked_data->checked))
		return $checked_data;
	$args = array(
		'slug' => $wp_mini_program,
		'version' => $checked_data->checked[$wp_mini_program .'/'. $wp_mini_program .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	// Start checking for an update
	$raw_response = wp_remote_post($plugin_update_url.'/update/', $request_string);
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$wp_mini_program .'/'. $wp_mini_program .'.php'] = $response;
	return $checked_data;
});


// Take over the Plugin info screen
add_filter('plugins_api', function ($def, $action, $args) {
	global $wp_mini_program, $plugin_update_url, $wp_version;
	if (!isset($args->slug) || ($args->slug != $wp_mini_program))
		return false;
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$wp_mini_program .'/'. $wp_mini_program .'.php'];
	$args->version = $current_version;
	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	$request = wp_remote_post($plugin_update_url.'/update/', $request_string);
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	return $res;
}, 10, 3);