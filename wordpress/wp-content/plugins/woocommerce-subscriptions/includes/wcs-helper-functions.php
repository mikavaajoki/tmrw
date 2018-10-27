<?php
/**
 * WooCommerce Subscriptions Helper Functions
 *
 * @author 		Prospress
 * @category 	Core
 * @package 	WooCommerce Subscriptions/Functions
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display date/time input fields
 *
 * @param int (optional) A timestamp for a certain date in the site's timezome. If left empty, or 0, it will be set to today's date.
 * @param array $args A set of name => value pairs to customise the input fields
 *		'id_attr': (string) the date to display in the selector in MySQL format ('Y-m-d H:i:s'). Required.
 *		'date': (string) the date to display in the selector in MySQL format ('Y-m-d H:i:s'). Required.
 *		'tab_index': (int) the tab index for the element. Optional. Default 0.
 *		'include_time': (bool) whether to include a specific time for the selector. Default true.
 *		'include_year': (bool) whether to include a the year field. Default true.
 *		'include_buttons': (bool) whether to include submit buttons on the selector. Default true.
 * @since 2.0
 */
function wcs_date_input( $timestamp = 0, $args = array() ) {

	$args = wp_parse_args( $args, array(
			'name_attr'         => '',
			'include_time'      => true,
		)
	);

	$date       = ( 0 !== $timestamp ) ? date_i18n( 'Y-m-d', $timestamp ) : '';
	// translators: date placeholder for input, javascript format
	$date_input = '<input type="text" class="date-picker woocommerce-subscriptions" placeholder="' . esc_attr__( 'YYYY-MM-DD', 'woocommerce-subscriptions' ) . '" name="' . esc_attr( $args['name_attr'] ) . '" id="' . esc_attr( $args['name_attr'] ) . '" maxlength="10" value="' . esc_attr( $date ) . '" pattern="([0-9]{4})-(0[1-9]|1[012])-(##|0[1-9#]|1[0-9]|2[0-9]|3[01])"/>';

	if ( true === $args['include_time'] ) {
		$hours        = ( 0 !== $timestamp ) ? date_i18n( 'H', $timestamp ) : '';
		// translators: hour placeholder for time input, javascript format
		$hour_input   = '<input type="text" class="hour" placeholder="' . esc_attr__( 'HH', 'woocommerce-subscriptions' ) . '" name="' . esc_attr( $args['name_attr'] ) . '_hour" id="' . esc_attr( $args['name_attr'] ) . '_hour" value="' . esc_attr( $hours ) . '" maxlength="2" size="2" pattern="([01]?[0-9]{1}|2[0-3]{1})" />';
		$minutes      = ( 0 !== $timestamp ) ? date_i18n( 'i', $timestamp ) : '';
		// translators: minute placeholder for time input, javascript format
		$minute_input = '<input type="text" class="minute" placeholder="' . esc_attr__( 'MM', 'woocommerce-subscriptions' ) . '" name="' . esc_attr( $args['name_attr'] ) . '_minute" id="' . esc_attr( $args['name_attr'] ) . '_minute" value="' . esc_attr( $minutes ) . '" maxlength="2" size="2" pattern="[0-5]{1}[0-9]{1}" />';
		$date_input   = sprintf( '%s@%s:%s', $date_input, $hour_input, $minute_input );
	}

	$timestamp_utc = ( 0 !== $timestamp ) ? $timestamp - get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS : $timestamp;
	$date_input    = '<div class="wcs-date-input">' . $date_input . '</div>';

	return apply_filters( 'woocommerce_subscriptions_date_input', $date_input, $timestamp, $args );
}

/**
 * Get the edit post link without checking if the user can edit that post or not.
 *
 * @param int $post_id
 * @since 2.0
 */
function wcs_get_edit_post_link( $post_id ) {
	$post_type_object = get_post_type_object( get_post_type( $post_id ) );

	if ( ! $post_type_object || ! in_array( $post_type_object->name, array( 'shop_order', 'shop_subscription' ) ) ) {
		return;
	}

	return apply_filters( 'get_edit_post_link', admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', $post_id ) ),$post_id, '' );
}

/**
 * Returns a string with all non-ASCII characters removed. This is useful for any string functions that expect only
 * ASCII chars and can't safely handle UTF-8
 *
 * Based on the SV_WC_Helper::str_to_ascii() method developed by the masterful SkyVerge team
 *
 * Note: We must do a strict false check on the iconv() output due to a bug in PHP/glibc {@link https://bugs.php.net/bug.php?id=63450}
 *
 * @param string $string string to make ASCII
 * @return string|null ASCII string or null if error occurred
 * @since 2.0
 */
function wcs_str_to_ascii( $string ) {

	$ascii = false;

	if ( function_exists( 'iconv' ) ) {
		$ascii = iconv( 'UTF-8', 'ASCII//IGNORE', $string );
	}

	return false === $ascii ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $string ) : $ascii;
}

/**
 * wp_json_encode exists since WP 4.1, but because we can't be sure that stores will actually use at least 4.1, we need
 * to have this wrapper.
 *
 * @param array $data Data to be encoded
 *
 * @return string
 */
function wcs_json_encode( $data ) {
	if ( function_exists( 'wp_json_encode' ) ) {
		return wp_json_encode( $data );
	}
	return json_encode( $data );
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @param $needle The array key to insert the element after
 * @param $haystack An array to insert the element into
 * @param $new_key The key to insert
 * @param $new_value An value to insert
 * @return The new array if the $needle key exists, otherwise an unmodified $haystack
 */
function wcs_array_insert_after( $needle, $haystack, $new_key, $new_value ) {

	if ( array_key_exists( $needle, $haystack ) ) {

		$new_array = array();

		foreach ( $haystack as $key => $value ) {

			$new_array[ $key ] = $value;

			if ( $key === $needle ) {
				$new_array[ $new_key ] = $new_value;
			}
		}

		return $new_array;
	}

	return $haystack;
}

/**
 * Helper function to get around WooCommerce version 2.6.3 which removed the constant WC_ROUNDING_PRECISION and
 * introduced the function wc_get_rounding_precision. Every version 2.6.2 and earlier has the constant. Every version
 * 2.6.4 and later (hopefully) will also have the constant AND the wc_get_rounding_precision function. 2.6.3 only has
 * the function however.
 *
 * @see https://github.com/Prospress/woocommerce-subscriptions/issues/1545
 *
 * @return int rounding precision
 */
function wcs_get_rounding_precision() {
	if ( function_exists( 'wc_get_rounding_precision' ) ) {
		$precision = wc_get_rounding_precision();
	} elseif ( defined( 'WC_ROUNDING_PRECISION' ) ) {
		$precision = WC_ROUNDING_PRECISION;
	} else {
		$precision = wc_get_price_decimals() + 2;
	}

	return $precision;
}

/**
 * Add a prefix to a string if it doesn't already have it
 *
 * @param string
 * @param string
 * @since 2.2.0
 * @return string
 */
function wcs_maybe_prefix_key( $key, $prefix = '_' ) {
	return ( substr( $key, 0, strlen( $prefix ) ) != $prefix ) ? $prefix . $key : $key;
}

/**
 * Find the name of the function which called the function which called this function.
 *
 * @since 2.2.0
 * @return string
 */
function wcs_get_calling_function_name() {

	$backtrace         = version_compare( phpversion(), '5.4.0', '>=' ) ? debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 ) : debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // the 2nd param for debug_backtrace() was added in PHP 5.4
	$calling_function  = isset( $backtrace[2]['class'] ) ? $backtrace[2]['class'] : '';
	$calling_function .= isset( $backtrace[2]['type'] ) ? ( ( '->' == $backtrace[2]['type'] ) ? '::' : $backtrace[2]['type'] ) : ''; // Ternary abuses
	$calling_function .= isset( $backtrace[2]['function'] ) ? $backtrace[2]['function'] : '';

	return $calling_function;
}
