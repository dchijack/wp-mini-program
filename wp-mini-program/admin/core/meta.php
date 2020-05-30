<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Calls the class on the post edit screen.
 */
add_action('add_meta_boxes', array('WP_Custom_Meta_Box', 'add_custom_meta_box'));
add_action('save_post', array('WP_Custom_Meta_Box', 'save_meta_methods'));

/**
 * WordPress Creat Custom Meta Box Class.
 */
abstract class WP_Custom_Meta_Box {

    /**
     * Adds the meta box container.
     */
    public static function add_custom_meta_box( ) {
        // Limit meta box to certain post types.

		$metas = apply_filters( 'meta_options', $options = array() );
		
		if($metas) {
			foreach($metas as $key => $meta) {
				add_meta_box( $key, $meta['title'], array( self::class, 'creat_meta_container' ), $meta['type'], 'normal','default' );
			}
		}
    }
 
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public static function save_meta_methods( $post_id ) {
 
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
 
        // Check if our nonce is set.
        if ( ! isset( $_POST['add_meta_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['add_meta_custom_box_nonce'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'add_meta_inner_custom_box' ) ) {
            return $post_id;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
		
		$metas = apply_filters( 'meta_options', $options = array() );
		
		if($metas) {
			foreach($metas as $keys => $meta) {
				$fields = $meta['fields'];
				foreach($fields as $key => $field) {
					if($field['type'] === 'mu-text') {
						$data = apply_filters( 'setting_sanitize_mu-text', $_POST[$key], '' );
					} else {
						$data = sanitize_text_field( $_POST[$key] );
					}
					if ( get_post_meta( $post_id, $key, false ) ) {
						update_post_meta( $post_id, $key, $data );
					} else {
						add_post_meta( $post_id, $key, $data, true );
					}
					if ( !$data ) {
						delete_post_meta( $post_id, $key );
					}
				}
			}
		}
 
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public static function creat_meta_container( $post ) {
 
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'add_meta_inner_custom_box', 'add_meta_custom_box_nonce' );
		
		$output = '';
		
		$metas = apply_filters( 'meta_options', $options = array() );
		
		if($metas) {
			$output .= '<table class="form-table" cellspacing="0"></tbody>';
			foreach($metas as $keys => $meta) {
				$fields = $meta['fields'];
				if($post->post_type == $meta['type']) {
					foreach($fields as $key => $field) {
						$value = get_post_meta( $post->ID, $key, true );
						switch ( $field['type'] ) {
							case 'textarea':
								$rows = isset($field["rows"])?$field["rows"]:5;
								$cols = isset($field["cols"])?$field["cols"]:4;
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$output .= '<tr id="'.$key.'_textarea">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><textarea id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' rows="'.$rows.'" cols="'.$cols.'">' . esc_textarea( $value ) . '</textarea>';
											if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
								$output .= '</td></tr>';
								break;
							case 'select':
								$output .= '<tr id="'.$key.'_select">
									<th><label for="'.$key.'">'.$field["title"].'</label></th>
									<td>
									<select name="' .esc_attr( $key ). '" id="' . esc_attr( $key ) . '">';
									foreach ($field['options'] as $id => $option ) {
										$output .= '<option'. selected( $value, $id, false ) .' value="' . esc_attr( $id ) . '">' . esc_html( $option ) . '</option>';
									}
									$output .= '</select>';
									if(isset($field['description']) && !empty($field['description'])) { $output .= '<span class="description">'.$field['description'].'</span>'; }
									$output .= '</td></tr>';
								break;
								
							case "checkbox":
								$output .= '</div>'."\n".'</div>';
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_checkbox">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><input type="checkbox" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' '. checked( $value, 1, false) .' value="1">';
											if(isset($field['description']) && !empty($field['description'])) { $output .= '<span class="regular-color description">'.$field['description'].'</span>'; }
								$output .= '</td></tr>';
								break;
								
							case "upload":
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_upload">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' '.$val.'>
											<input type="button" id="' . esc_attr( $key ) . '-btn" class="button upload-button" value="选择媒体">';
											if(isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
								$output .= '</td></tr>';
								break;

							case "image":
								$output .= '<tr id="'.$key.'_image">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>';
											if( $value ) {
												$output .= '<td><div class="image_field">
												<img src="'.esc_attr( $value ).'" width="360" height="180" />
												</div>
												<input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" class="regular-text" value="'.esc_attr( $value ).'" >
												<input type="button" id="' . esc_attr( $key ) . '-btn" class="button upload-button" value="选择媒体">
												</td>';
											} else {
												$output .= '<td><input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" class="regular-text" value="" >
													<input type="button" id="' . esc_attr( $key ) . '-btn" class="button upload-button" value="选择媒体">
													</td>';
											}
								$output .= '</tr>';
								break;

							case "mu-text":
								$multexts = $value?$value:'';
								$output .= '<tr id="'.$key.'_mu_text">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td>
											<div class="mu-texts sortable ui-sortable">';
											if($multexts) {
												foreach ($multexts as $option) {
													if($option) {
														$output .= '<div class="mu-item">
																	<input id="' . esc_attr( $key ) . '" type="text" name="' .esc_attr( $key.'[]' ). '" class="regular-text" value="' . esc_html( $option ) . '" />
																	<a href="javascript:;" class="button del-item">删除</a>
																	<span class="dashicons dashicons-menu ui-sortable-handle"></span>
																	</div>';
													}
												}
											}
											$output .= '<div class="mu-item">
														<input id="' . esc_attr( $key ) . '" type="text" name="' .esc_attr( $key.'[]' ). '" class="regular-text" value="" />
														<a class="mp-mu-text button">添加</a>
														</div>';		
								$output .= '</div></td></tr>';
								break;
						
							default:
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_text">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td>
											<input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ).'" '.$class.' '.$val.' />';
											if(!isset($field["class"]) && isset($field['description']) && !empty($field['description'])) { $output .= '<span class="description">'.$field['description'].'</span>'; }
											if(isset($field["class"]) && isset($field['description']) && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
								$output .= '</td></tr>';
								break;
						}
					}
				}
			}
			$output .= '</tbody></table>';
			echo $output;
		}
    }
}