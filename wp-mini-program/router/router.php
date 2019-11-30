<?php
/*
 * router files
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

include( IMAHUI_REST_API.'router/setting.php' );
include( IMAHUI_REST_API.'router/users.php' );
include( IMAHUI_REST_API.'router/posts.php' );
include( IMAHUI_REST_API.'router/comments.php' );
include( IMAHUI_REST_API.'router/qrcode.php' );
include( IMAHUI_REST_API.'router/auth.php' );
include( IMAHUI_REST_API.'router/subscribe.php' );
include( IMAHUI_REST_API.'router/advert.php' );
include( IMAHUI_REST_API.'router/menu.php' );

add_action( 'rest_api_init', function () {
	$controller = array();
	$controller[] = new WP_REST_Setting_Router();
	$controller[] = new WP_REST_Posts_Router();
	$controller[] = new WP_REST_Comments_Router();
	$controller[] = new WP_REST_Qrcode_Router();
	$controller[] = new WP_REST_Users_Router();
	$controller[] = new WP_REST_Auth_Router();
	$controller[] = new WP_REST_Subscribe_Router();
	$controller[] = new WP_REST_Advert_Router();
	$controller[] = new WP_REST_Menu_Router();
	foreach ( $controller as $control ) {
		$control->register_routes();
	}
});