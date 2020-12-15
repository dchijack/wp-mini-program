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