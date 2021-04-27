<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

function creat_miniprogram_terms_fields_box($taxonomy) {
	$options = apply_filters( 'mp_'.$taxonomy.'_term_options', $options = array() );
	if( !empty($options) ) {
		foreach($options as $key => $option) {
			switch ( $option['type'] ) {
				case 'checkbox': ?>
					<div class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label>
						<input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="checkbox" value="1" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<span>'.esc_html($option['description']).'</span>'; } ?>
					</div>
					<?php break;
				case "upload": ?>
					<div class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label>
						<input id="<?php echo esc_attr( $key ); ?>" class="term-upload-field" name="<?php echo esc_attr( $key ); ?>" type="text" value="" />
						<input id="<?php echo esc_attr( $key ); ?>-btn" class="button upload-button" type="button" value="选择媒体" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<p>'.esc_html($option['description']).'</p>'; } ?>
					</div>
					<?php break;
				default: ?>
					<div class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label>
						<input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" size="40" value="" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<p>'.esc_html($option['description']).'</p>'; } ?>
					</div>
					<?php break;
			}
		} 
	} 
}

function save_miniprogram_term_field_action( $term_id, $tt_id ) {
	if(isset($_POST['taxonomy']) && isset($_POST['action']) && trim($_POST['action']) == 'add-tag' ) {
		$taxonomy = trim( $_POST['taxonomy'] );
		$options = apply_filters( 'mp_'.$taxonomy.'_term_options', $options = array() );
		if( !empty($options) ) {
			foreach($options as $key => $option) {
				if( isset($_POST[$key]) ) {
					$data = sanitize_text_field( $_POST[$key] );
					add_term_meta( $term_id, $key, $data, true );
				}
			}
		}
	}
}

function edit_miniprogram_terms_fields_box( $term, $taxonomy ) {
	$options = apply_filters( 'mp_'.$taxonomy.'_term_options', $options = array() );
	if( !empty($options) ) {
		foreach($options as $key => $option) {
			$value = get_term_meta( $term->term_id, $key, true );
			if( !$value ) { $value = ""; }
			switch ( $option['type'] ) {
				case 'checkbox': ?>
					<tr class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label></th>
						<td>
						<input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="checkbox" <?php checked( $value, 1 ); ?> value="1" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<span>'.esc_html($option['description']).'</span>'; } ?>
						</td>
					</tr>
					<?php break;
				case "upload": ?>
					<tr class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label></th>
						<td>
						<input id="<?php echo esc_attr( $key ); ?>" class="term-upload-field" name="<?php echo esc_attr( $key ); ?>" type="text" value="<?php echo esc_html($value); ?>" />
						<input id="<?php echo esc_attr( $key ); ?>-btn" class="button upload-button" type="button" value="选择媒体" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<p>'.esc_html($option['description']).'</p>'; } ?>
						</td>
					</tr>
					<?php break;
				default: ?>
					<tr class="form-field term-<?php echo esc_attr( $key ); ?>-wrap">
						<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option["title"] ); ?></label></th>
						<td>
						<input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" size="40" value="<?php echo esc_html($value); ?>" />
						<?php if(isset($option['description']) && !empty($option['description'])) { echo '<p>'.esc_html($option['description']).'</p>'; } ?>
						</td>
					</tr>
					<?php break;
			}
		}
	}
}

function update_miniprogram_term_field_action( $term_id, $tt_id ){
	if(isset($_POST['taxonomy']) && isset($_POST['action']) && trim($_POST['action']) == 'editedtag' ) {
		$taxonomy = trim( $_POST['taxonomy'] );
		$options = apply_filters( 'mp_'.$taxonomy.'_term_options', $options = array() );
		if( !empty($options) ) {
			foreach($options as $key => $option) {
				if( isset($_POST[$key]) ) {
					$data = sanitize_text_field( $_POST[$key] );
					update_term_meta( $term_id, $key, $data );
				} else {
					delete_term_meta( $term_id, $key );
				}
			}
		}
	}
}

function creat_miniprogram_terms_meta_box( ) {
	global $pagenow;
	if( ( $pagenow == 'term.php' || $pagenow == 'edit-tags.php' ) && isset($_GET['taxonomy']) ) {
		$term = trim( $_GET['taxonomy'] );
		add_action( $term.'_add_form_fields', 'creat_miniprogram_terms_fields_box' , 10, 2 );
		add_action( $term.'_edit_form_fields', 'edit_miniprogram_terms_fields_box' , 10, 2 );
	}
	if( ( isset($_POST['action']) && trim($_POST['action']) == 'add-tag' && isset($_POST['taxonomy']) ) || ( $pagenow == 'edit-tags.php' && isset($_POST['taxonomy']) ) ) {
		$taxonomy = trim( $_POST['taxonomy'] );
		add_action( 'created_'.$taxonomy, 'save_miniprogram_term_field_action' , 10, 2 );
		add_action( 'edited_'.$taxonomy,   'update_miniprogram_term_field_action' , 10, 2 );
	}
}