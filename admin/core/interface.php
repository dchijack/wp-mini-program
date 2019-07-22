<?php
if ( !defined( 'ABSPATH' ) ) exit;

function register_miniprogram_manage_menu() {
	$menus = apply_filters( 'miniprogram_manage_menus', $admin_menu = array() );
	if(is_admin() && $menus) {
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

function miniprogram_options_nav_menu() {
	$options = apply_filters( 'miniprogram_setting_options', $options = array() );
	$menu = '';
	if($options) {
		foreach ( $options as $key => $option ) {
			$menu .= '<a id="'.$key. '-tab" class="nav-tab ' .$key.'-tab" title="' . esc_attr( $option['title'] ) . '" href="#'.$key.'">' . esc_html( $option['title'] ) . '</a>';
		}
		echo $menu;
	}
}

function miniprogram_options_container( $option_name ) {
	$settings = get_option($option_name);
	$options = apply_filters( 'miniprogram_setting_options', $options = array() );
	$output = '';
	if($options) {
		foreach ( $options as $key => $option ) {
			$output .= '<div id="'.$key.'" class="miniprogram-group">'. "\n" .'<h3>'.$option["summary"].'</h3>'. "\n";
			$output .= '<table class="form-table" cellspacing="0"></tbody>';
			$fields = $option["fields"];
			foreach ( $fields as $var => $field ) {
				
				switch ( $field['type'] ) {
					
					case 'text':
						$rows = isset($field["rows"])?$field["rows"]:4;
						$class = isset($field["class"])?'class="'.$field["class"].'"':'';
						$placeholder = isset($field["placeholder"])?'placeholder="'.$field["placeholder"].'"':'';
						$value = isset($settings[$var])?'value="'. esc_attr( $settings[$var] ).'"':'value=""';
						$output .= '<tr id="'.$var.'_text">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td>
									<input type="text" id="' . esc_attr( $var ) . '" name="' .esc_attr( $option_name . '[' . $var. ']' ). '" '.$class.' rows="'.$rows.'" '.$placeholder.' '.$value.' />';
									if(!isset($field["class"]) && isset($field['description']) && !empty($field['description'])) { $output .= '<span class="desc description">'.$field['description'].'</span>'; }
									if(isset($field["class"]) && isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
						$output .= '</td></tr>';
						break;
						
					case 'textarea':
						$rows = isset($field["rows"])?$field["rows"]:4;
						$cols = isset($field["cols"])?$field["cols"]:20;
						$class = isset($field["class"])?'class="'.$field["class"].'"':'';
						$placeholder = isset($field["placeholder"])?'placeholder="'.$field["placeholder"].'"':'';
						$output .= '<tr id="'.$var.'_textarea">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td><textarea id="' . esc_attr( $var ) . '" name="' .esc_attr( $option_name . '[' .$var. ']' ). '" '.$class.' rows="'.$rows.'" cols="'.$cols.'" '.$placeholder.'>' . esc_textarea( $settings[$var] ) . '</textarea>';
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
						$output .= '</td></tr>';
						break;
						
					case 'select':
						$output .= '<tr id="'.$var.'_select">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td>
									<select name="' .esc_attr( $option_name . '[' . $var. ']' ). '" id="' . esc_attr( $var ) . '">';
									foreach ($field['options'] as $key => $option ) {
										$output .= '<option'. selected( $settings[$var], $key, false ) .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
									}
									$output .= '</select>';
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<span class="desc description">'.$field['description'].'</span>'; }
						$output .= '</td></tr>';
						break;

					case "radio":
						$value = isset($settings[$var])?$settings[$var]:'';
						$output .= '<tr id="'.$var.'_radio">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td>';
									foreach ($field['options'] as $key => $option ) {
										$output .= '<input type="radio" name="' .esc_attr( $option_name . '[' . $var. ']' ). '" id="' . esc_attr( $var ) . '" value="'. esc_attr( $key ) . '" '. checked( $value, $key, false) .' /><label for="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</label>';
									}
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
						$output .= '</td></tr>';		
						break;
						
					case "checkbox":
						$class = isset($field["class"])?'class="'.$field["class"].'"':'';
						$value = isset($settings[$var])?$settings[$var]:'';
						$output .= '<tr id="'.$var.'_checkbox">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td><input type="checkbox" id="' . esc_attr( $var ) . '" name="' .esc_attr( $option_name . '[' . $var. ']' ). '" '.$class.' '. checked( $value, 1, false) .' value="1">';
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<span class="description">'.$field['description'].'</span>'; }
						$output .= '</td></tr>';
						break;
						
					case "upload":
						$class = isset($field["class"])?'class="'.$field["class"].'"':'';
						$placeholder = isset($field["placeholder"])?'placeholder="'.$field["placeholder"].'"':'';
						$value = isset($settings[$var])?'value="'. esc_attr( $settings[$var] ).'"':'value=""';
						$output .= '<tr id="'.$var.'_upload">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td><input type="text" id="' . esc_attr( $var ) . '" name="' .esc_attr( $option_name . '[' . $var. ']' ). '" '.$class.' '.$placeholder.' '.$value.'>
									<input type="button" id="' . esc_attr( $var ) . '-btn" class="button upload-button" value="选择媒体">';
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
						$output .= '</td></tr>';
						break;

					case "mu-check":
						$multicheck = $settings[$var];
						$output .= '<tr id="'.$var.'_mu_check">
									<th><label for="'.$var.'">'.$field["title"].'</label></th>
									<td>';
									foreach ($field['options'] as $key => $option) {
										$checked = '';
										if( isset($multicheck[$key]) ) {
											$checked = checked($multicheck[$key], 1, false);
										}
										$output .= '<input id="' . esc_attr( $key ) . '" type="checkbox" name="' .esc_attr( $option_name.'['.$var.']['.$key.']' ). '" ' .$checked. ' value="1" /><span class="' . esc_attr( $key ) . ' mu-mar">' . esc_html( $option ) . '</span>';
									}
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
						$output .= '</td></tr>';
						break;
	
				}
				
			}

			$output .= '</tbody></table></div>';
			
		}

	} else {

		$output = '<div class="wrap">未定义设置选项</div><!-- / .wrap -->';

	}
	
	echo $output;
	
}

/**
* Validate Options.
*
* This runs after the submit/reset button has been clicked and
* validates the inputs.
*
* @uses $_POST['reset'] to restore default options
*/
function validate_sanitize_miniprogram_options( $input ) {

	/*
	* Update Settings
	*
	* This used to check for $_POST['update'], but has been updated
	* to be compatible with the theme customizer introduced in WordPress 3.4
	*/

	$clean = array();
	$options = apply_filters( 'miniprogram_setting_options', $options = array() );
	if($options) {
		foreach ( $options as $key => $option ) {
			$fields = $option["fields"];
			foreach ( $fields as $var => $field ) {
				if ( ! isset( $var ) ) {
					continue;
				}
				if ( ! isset( $field['type'] ) ) {
					continue;
				}
				$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $var ) );
				// Set checkbox to false if it wasn't sent in the $_POST
				if ( 'checkbox' == $field['type'] && ! isset( $input[$id] ) ) {
					$input[$id] = false;
				}
				// Set each item in the multicheck to false if it wasn't sent in the $_POST
				if ( 'mu-check' == $field['type'] && ! isset( $input[$id] ) ) {
					foreach ( $field['options'] as $key => $value ) {
						$input[$id][$key] = false;
					}
				}
				// For a value to be submitted to database it must pass through a sanitization filter
				if ( has_filter( 'setting_sanitize_' . $field['type'] ) ) {
					$clean[$id] = apply_filters( 'setting_sanitize_' . $field['type'], $input[$id], $field );
				}
			}
		}
	}
	// Hook to run after validation
	do_action( 'update_setting_validate', $clean );
	return $clean;
}