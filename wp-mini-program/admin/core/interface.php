<?php
if ( !defined( 'ABSPATH' ) ) exit;

function miniprogram_options_nav_menu( $options ) {
	$menu = '';
	if($options) {
		foreach ( $options as $key => $option ) {
			$menu .= '<a id="'.$key. '-tab" class="mp-nav-tab ' .$key.'-tab" title="' . esc_attr( $option['title'] ) . '" href="#'.$key.'">' . esc_html( $option['title'] ) . '</a>';
		}
		echo $menu;
	}
}

function miniprogram_options_container( $option_name, $options ) {
	
	$output = '';
	if($options) {
		foreach ( $options as $key => $option ) {
			$output .= '<div id="'.$key.'" class="miniprogram-group">'. "\n" .'<h3>'.$option["summary"].'</h3>'. "\n";
			$output .= miniprogram_table_options_container( $option_name, $option["fields"] );
			$output .= '</div>';
		}
	} else {
		$output = '<div class="wrap">未定义设置选项</div><!-- / .wrap -->';
	}
	echo $output;
	
}

function miniprogram_table_options_container( $option_name, $fields ) {

	$output = '';
	$settings = get_option($option_name);
	if( $fields ) {
		$output .= '<table class="form-table" cellspacing="0"></tbody>';
		foreach ( $fields as $var => $field ) {

			switch ( $field['type'] ) {
					
				case 'password':
					$rows = isset($field["rows"])?$field["rows"]:4;
					$class = isset($field["class"])?'class="'.$field["class"].'"':'';
					$placeholder = isset($field["placeholder"])?'placeholder="'.$field["placeholder"].'"':'';
					$value = isset($settings[$var])?'value="'. esc_attr( $settings[$var] ).'"':'value=""';
					$output .= '<tr id="'.$var.'_text">
								<th><label for="'.$var.'">'.$field["title"].'</label></th>
								<td>
								<input type="password" id="' . esc_attr( $var ) . '" name="' .esc_attr( $option_name . '[' . $var. ']' ). '" '.$class.' rows="'.$rows.'" '.$placeholder.' '.$value.' />';
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
					$value = isset($settings[$var])?$settings[$var]:'';
					$output .= '<tr id="'.$var.'_select">
								<th><label for="'.$var.'">'.$field["title"].'</label></th>
								<td>
								<select name="' .esc_attr( $option_name . '[' . $var. ']' ). '" id="' . esc_attr( $var ) . '">';
								foreach ($field['options'] as $key => $option ) {
									$output .= '<option'. selected( $value, $key, false ) .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
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

				case "mu-text":
					$multexts = isset($settings[$var])?$settings[$var]:'';
					$class = isset($field["class"])?'class="'.$field["class"].'"':'';
					$placeholder = isset($field["placeholder"])?'placeholder="'.$field["placeholder"].'"':'';
					$output .= '<tr id="'.$var.'_mu_text">
								<th><label for="'.$var.'">'.$field["title"].'</label></th>
								<td>
								<div class="mu-texts sortable ui-sortable">';
								if($multexts) {
									foreach ($multexts as $option) {
										if($option) {
											$output .= '<div class="mu-item">
														<input '.$class.' id="' . esc_attr( $var ) . '" type="text" name="' .esc_attr( $option_name.'['.$var.'][]' ). '" '.$placeholder.' value="' . esc_html( $option ) . '" />
														<a href="javascript:;" class="button del-item">删除</a>
														<span class="dashicons dashicons-menu ui-sortable-handle"></span>
														</div>';
										}
									}
								}
								$output .= '<div class="mu-item">
											<input '.$class.' id="' . esc_attr( $var ) . '" type="text" name="' .esc_attr( $option_name.'['.$var.'][]' ). '" '.$placeholder.' value="" />
											<a class="mp-mu-text button">添加</a>
											</div>';
												
								$output .= '</div></td></tr>';
					break;

				default:
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
	
			}
				
		}

		$output .= '</tbody></table>';

	}

	return $output;

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
				if ( 'mu-text' == $field['type'] && ! isset( $input[$id] ) ) {
					$input[$id] = false;
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