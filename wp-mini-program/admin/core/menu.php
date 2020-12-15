<?php

if ( !defined( 'ABSPATH' ) ) exit;

function register_miniprogram_manage_menu() {
	$admin_menu = apply_filters( 'miniprogram_manage_menus', $admin_menu = array() );
	if(is_admin() && !empty($admin_menu)) {
		foreach ( $admin_menu as $menus ) {
			foreach ( $menus as $key => $menu ) {
				switch ( $key ) {
					case 'menu':
						add_menu_page( $menu['page_title'], $menu['menu_title'], isset($menu['capability'])?$menu['capability']:'manage_options', $menu['option_name'], $menu['function'], $menu['icon'], $menu['position'] );
						break;
					case 'submenu':
						foreach ( $menu as $submenu ) {
							add_submenu_page( $submenu['option_name'], $submenu['page_title'], $submenu['menu_title'], isset($submenu['capability'])?$submenu['capability']:'manage_options', $submenu['slug'], $submenu['function'] );
						}
						break;
				}
			}
		}
	}
}