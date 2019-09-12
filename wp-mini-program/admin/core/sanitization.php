<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Sanitization for text input
 *
 * @link http://developer.wordpress.org/reference/functions/sanitize_text_field/
 */
add_filter( 'setting_sanitize_text', 'sanitize_text_field' );

/**
 * Sanitization for password input
 *
 * @link http://developer.wordpress.org/reference/functions/sanitize_text_field/
 */
add_filter( 'setting_sanitize_password', 'sanitize_text_field' );

/**
 * Validates that the $input is one of the avilable choices
 * for that specific option.
 *
 * @param string $input
 * @returns string $output
 */
function setting_sanitize_enum( $input, $option ) {
	$output = '';
	if ( array_key_exists( $input, $option['options'] ) ) {
		$output = $input;
	}
	return $output;
}

/**
 * Sanitization for select input
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'setting_sanitize_select', 'setting_sanitize_enum', 10, 2 );

/**
 * Sanitization for radio input
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'setting_sanitize_radio', 'setting_sanitize_enum', 10, 2 );

/**
 * Sanitization for image selector
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'setting_sanitize_images', 'setting_sanitize_enum', 10, 2 );

/**
 * Sanitization for textarea field
 *
 * @param $input string
 * @return $output sanitized string
 */
function setting_sanitize_textarea( $input ) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags );
	return $output;
}
add_filter( 'setting_sanitize_textarea', 'setting_sanitize_textarea' );

/**
 * Sanitization for checkbox input
 *
 * @param $input string (1 or empty) checkbox state
 * @return $output '1' or false
 */
function setting_sanitize_checkbox( $input ) {
	if ( $input ) {
		return '1';
	}
	return false;
}
add_filter( 'setting_sanitize_checkbox', 'setting_sanitize_checkbox' );

/**
 * Sanitization for multicheck
 *
 * @param array of checkbox values
 * @return array of sanitized values ('1' or false)
 */
function setting_sanitize_multicheck( $input, $option ) {
	$output = array();
	if ( is_array( $input ) ) {
		foreach( $option['options'] as $key => $value ) {
			$output[$key] = false;
		}
		foreach( $input as $key => $value ) {
			if ( array_key_exists( $key, $option['options'] ) && $value ) {
				$output[$key] = $value;
			}
		}
	}
	return $output;
}
add_filter( 'setting_sanitize_mu-check', 'setting_sanitize_multicheck', 10, 2 );

/**
 * Sanitization for multitext
 *
 * @param array of text values
 * @return array of sanitized values ('1' or false)
 */
function setting_sanitize_multitext( $input, $option ) {
	$output = array();
	if ( is_array( $input ) ) {
		foreach( $input as $value ) {
			$output[] = apply_filters( 'sanitize_text_field', $value );
		}
	}
	return $output;
}
add_filter( 'setting_sanitize_mu-text', 'setting_sanitize_multitext', 10, 2 );

/**
 * File upload sanitization.
 *
 * Returns a sanitized filepath if it has a valid extension.
 *
 * @param string $input filepath
 * @returns string $output filepath
 */
function setting_sanitize_upload( $input ) {
	$output = '';
	$filetype = wp_check_filetype( $input );
	if ( $filetype["ext"] ) {
		$output = esc_url( $input );
	}
	return $output;
}
add_filter( 'setting_sanitize_upload', 'setting_sanitize_upload' );

/**
 * Sanitization of input with allowed tags and wpautotop.
 *
 * Allows allowed tags in html input and ensures tags close properly.
 *
 * @param string $input
 * @returns string $output
 */
function setting_sanitize_allowedtags( $input ) {
	global $allowedtags;
	$output = wpautop( wp_kses( $input, $allowedtags ) );
	return $output;
}

/**
 * Sanitization of input with allowed post tags and wpautotop.
 *
 * Allows allowed post tags in html input and ensures tags close properly.
 *
 * @param string $input
 * @returns string $output
 */
function setting_sanitize_allowedposttags( $input ) {
	global $allowedposttags;
	$output = wpautop( wp_kses( $input, $allowedposttags) );
	return $output;
}