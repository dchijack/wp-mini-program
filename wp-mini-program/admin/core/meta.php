<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Calls the class on the post edit screen.
 */
function creat_meta_box() {
    new WP_Custom_Meta_Box();
}
 
/**
 * WordPress Creat Custom Meta Box Class.
 */
class WP_Custom_Meta_Box {
 
    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_container' ) );
        add_action( 'save_post',      array( $this, 'save_meta_methods'   ) );
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_container( ) {
        // Limit meta box to certain post types.

		$metas = apply_filters( 'meta_options', $options = array() );
		
		if($metas) {
			foreach($metas as $key => $meta) {
				add_meta_box( $key, $meta['title'], array( $this, 'creat_meta_container' ), $meta['type'], 'normal','default' );
			}
		}
    }
 
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_methods( $post_id ) {
 
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
					$data = sanitize_text_field( $_POST[$key] );
					if ( get_post_meta($post_id, $key, FALSE) ) {
						update_post_meta($post_id, $key, $data);
					} else {
						add_post_meta($post_id, $key, $data, true);
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
    public function creat_meta_container( $post ) {
 
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
								$rows = isset($field["rows"])?$field["rows"]:4;
								$rows = isset($field["cols"])?$field["cols"]:20;
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$output .= '<tr id="'.$key.'_textarea">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><textarea id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' rows="'.$rows.'" cols="'.$cols.'">' . esc_textarea( $value ) . '</textarea>';
											if($field['description'] && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
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
									if($field['description'] && !empty($field['description'])) { $output .= '<span class="description">'.$field['description'].'</span>'; }
									$output .= '</td></tr>';
								break;
								
							case "checkbox":
								$output .= '</div>'."\n".'</div>';
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_checkbox">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><input type="checkbox" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' '. checked( $value, 1, false) .' value="1">';
											if($field['description'] && !empty($field['description'])) { $output .= '<span class="regular-color description">'.$field['description'].'</span>'; }
								$output .= '</td></tr>';
								break;
								
							case "upload":
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_upload">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td><input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ). '" '.$class.' '.$val.'>
											<input type="button" id="' . esc_attr( $key ) . '-btn" class="button upload-button" value="选择媒体">';
											if($field['description'] && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
								$output .= '</td></tr>';
								break;
						
							default:
								$rows = isset($field["rows"])?$field["rows"]:4;
								$class = isset($field["class"])?'class="'.$field["class"].'"':'';
								$val = $value?'value="'. esc_attr( $value ).'"':'value=""';
								$output .= '<tr id="'.$key.'_text">
											<th><label for="'.$key.'">'.$field["title"].'</label></th>
											<td>
											<input type="text" id="' . esc_attr( $key ) . '" name="' .esc_attr( $key ).'" '.$class.' rows="'.$rows.'" '.$val.' />';
											if(!isset($field["class"]) && $field['description'] && !empty($field['description'])) { $output .= '<span class="description">'.$field['description'].'</span>'; }
											if(isset($field["class"]) && $field['description'] && !empty($field['description'])) { $output .= '<p class="description">'.$field['description'].'</p>'; }
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