<?php
/*
 * router files
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

include( MINI_PROGRAM_REST_API.'router/setting.php' );
include( MINI_PROGRAM_REST_API.'router/users.php' );
include( MINI_PROGRAM_REST_API.'router/posts.php' );
include( MINI_PROGRAM_REST_API.'router/comments.php' );
include( MINI_PROGRAM_REST_API.'router/qrcode.php' );
include( MINI_PROGRAM_REST_API.'router/auth.php' );
include( MINI_PROGRAM_REST_API.'router/subscribe.php' );
include( MINI_PROGRAM_REST_API.'router/advert.php' );
include( MINI_PROGRAM_REST_API.'router/menu.php' );

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
} );