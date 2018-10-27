<?php
/**
 * WooCommerce Subscriptions Functions
 *
 * @author 		Prospress
 * @category 	Core
 * @package 	WooCommerce Subscriptions/Functions
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once( 'includes/class-wcs-select2.php' );
require_once( 'includes/wcs-deprecated-functions.php' );
require_once( 'includes/wcs-compatibility-functions.php' );
require_once( 'includes/wcs-conditional-functions.php' );
require_once( 'includes/wcs-formatting-functions.php' );
require_once( 'includes/wcs-product-functions.php' );
require_once( 'includes/wcs-cart-functions.php' );
require_once( 'includes/wcs-order-functions.php' );
require_once( 'includes/wcs-time-functions.php' );
require_once( 'includes/wcs-user-functions.php' );
require_once( 'includes/wcs-helper-functions.php' );
require_once( 'includes/wcs-renewal-functions.php' );
require_once( 'includes/wcs-resubscribe-functions.php' );
require_once( 'includes/wcs-switch-functions.php' );
require_once( 'includes/wcs-limit-functions.php' );

if ( is_admin() ) {
	require_once( 'includes/admin/wcs-admin-functions.php' );
}

/**
 * Check if a given object is a WC_Subscription (or child class of WC_Subscription), or if a given ID
 * belongs to a post with the subscription post type ('shop_subscription')
 *
 * @since  2.0
 * @return boolean true if anything is found
 */
function wcs_is_subscription( $subscription ) {

	if ( is_object( $subscription ) && is_a( $subscription, 'WC_Subscription' ) ) {
		$is_subscription = true;
	} elseif ( is_numeric( $subscription ) && 'shop_subscription' == get_post_type( $subscription ) ) {
		$is_subscription = true;
	} else {
		$is_subscription = false;
	}

	return apply_filters( 'wcs_is_subscription', $is_subscription, $subscription );
}

/**
 * A very simple check. Basically if we have ANY subscriptions in the database, then the user has probably set at
 * least one up, so we can give them the standard message. Otherwise
 *
 * @since  2.0
 * @return boolean 							true if anything is found
 */
function wcs_do_subscriptions_exist() {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1;", 'shop_subscription' );

	// query is the fastest, every other built in method uses this. Plus, the return value is the number of rows found
	$num_rows_found = $wpdb->query( $sql );

	return ( 0 !== $num_rows_found ) ? true: false;
}

/**
 * Main function for returning subscriptions. Wrapper for the wc_get_order() method.
 *
 * @since  2.0
 * @param  mixed $the_subscription Post object or post ID of the order.
 * @return WC_Subscription
 */
function wcs_get_subscription( $the_subscription ) {

	if ( is_object( $the_subscription ) && wcs_is_subscription( $the_subscription ) ) {
		$the_subscription = $the_subscription->get_id();
	}

	$subscription = WC()->order_factory->get_order( $the_subscription );

	if ( ! wcs_is_subscription( $subscription ) ) {
		$subscription = false;
	}

	return apply_filters( 'wcs_get_subscription', $subscription );
}

/**
 * Create a new subscription
 *
 * Returns a new WC_Subscription object on success which can then be used to add additional data.
 *
 * @return WC_Subscription | WP_Error A WC_Subscription on success or WP_Error object on failure
 * @since  2.0
 */
function wcs_create_subscription( $args = array() ) {

	$order = ( isset( $args['order_id'] ) ) ? wc_get_order( $args['order_id'] ) : null;

	if ( ! empty( $order ) ) {
		$default_start_date  = wcs_get_datetime_utc_string( wcs_get_objects_property( $order, 'date_created' ) );
	} else {
		$default_start_date = gmdate( 'Y-m-d H:i:s' );
	}

	$default_args = array(
		'status'             => '',
		'order_id'           => 0,
		'customer_note'      => null,
		'customer_id'        => ( ! empty( $order ) ) ? $order->get_user_id() : null,
		'start_date'         => $default_start_date,
		'created_via'        => ( ! empty( $order ) ) ? wcs_get_objects_property( $order, 'created_via' ) : '',
		'order_version'      => ( ! empty( $order ) ) ? wcs_get_objects_property( $order, 'version' ) : WC_VERSION,
		'currency'           => ( ! empty( $order ) ) ? wcs_get_objects_property( $order, 'currency' ) : get_woocommerce_currency(),
		'prices_include_tax' => ( ! empty( $order ) ) ? ( ( wcs_get_objects_property( $order, 'prices_include_tax' ) ) ? 'yes' : 'no' ) : get_option( 'woocommerce_prices_include_tax' ), // we don't use wc_prices_include_tax() here because WC doesn't use it in wc_create_order(), not 100% sure why it doesn't also check the taxes are enabled, but there could forseeably be a reason
	);

	$args              = wp_parse_args( $args, $default_args );
	$subscription_data = array();

	// validate the start_date field
	if ( ! is_string( $args['start_date'] ) || false === wcs_is_datetime_mysql_format( $args['start_date'] ) ) {
		return new WP_Error( 'woocommerce_subscription_invalid_start_date_format', _x( 'Invalid date. The date must be a string and of the format: "Y-m-d H:i:s".', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
	} else if ( wcs_date_to_time( $args['start_date'] ) > current_time( 'timestamp', true ) ) {
		return new WP_Error( 'woocommerce_subscription_invalid_start_date', _x( 'Subscription start date must be before current day.', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
	}

	// check customer id is set
	if ( empty( $args['customer_id'] ) || ! is_numeric( $args['customer_id'] ) || $args['customer_id'] <= 0 ) {
		return new WP_Error( 'woocommerce_subscription_invalid_customer_id', _x( 'Invalid subscription customer_id.', 'Error message while creating a subscription', 'woocommerce-subscriptions' ) );
	}

	// check the billing period
	if ( empty( $args['billing_period'] ) || ! in_array( strtolower( $args['billing_period'] ), array_keys( wcs_get_subscription_period_strings() ) ) ) {
		return new WP_Error( 'woocommerce_subscription_invalid_billing_period', __( 'Invalid subscription billing period given.', 'woocommerce-subscriptions' ) );
	}

	// check the billing interval
	if ( empty( $args['billing_interval'] ) || ! is_numeric( $args['billing_interval'] ) || absint( $args['billing_interval'] ) <= 0 ) {
		return new WP_Error( 'woocommerce_subscription_invalid_billing_interval', __( 'Invalid subscription billing interval given. Must be an integer greater than 0.', 'woocommerce-subscriptions' ) );
	}

	$subscription_data['post_type']     = 'shop_subscription';
	$subscription_data['post_status']   = 'wc-' . apply_filters( 'woocommerce_default_subscription_status', 'pending' );
	$subscription_data['ping_status']   = 'closed';
	$subscription_data['post_author']   = 1;
	$subscription_data['post_password'] = uniqid( 'order_' );
	// translators: Order date parsed by strftime
	$post_title_date = strftime( _x( '%b %d, %Y @ %I:%M %p', 'Used in subscription post title. "Subscription renewal order - <this>"', 'woocommerce-subscriptions' ) );
	// translators: placeholder is order date parsed by strftime
	$subscription_data['post_title']    = sprintf( _x( 'Subscription &ndash; %s', 'The post title for the new subscription', 'woocommerce-subscriptions' ), $post_title_date );
	$subscription_data['post_date_gmt'] = $args['start_date'];
	$subscription_data['post_date']     = get_date_from_gmt( $args['start_date'] );

	if ( $args['order_id'] > 0 ) {
		$subscription_data['post_parent'] = absint( $args['order_id'] );
	}

	if ( ! is_null( $args['customer_note'] ) && ! empty( $args['customer_note'] ) ) {
		$subscription_data['post_excerpt'] = $args['customer_note'];
	}

	// Only set the status if creating a new subscription, use wcs_update_subscription to update the status
	if ( $args['status'] ) {
		if ( ! in_array( 'wc-' . $args['status'], array_keys( wcs_get_subscription_statuses() ) ) ) {
			return new WP_Error( 'woocommerce_invalid_subscription_status', __( 'Invalid subscription status given.', 'woocommerce-subscriptions' ) );
		}
		$subscription_data['post_status']  = 'wc-' . $args['status'];
	}

	$subscription_id = wp_insert_post( apply_filters( 'woocommerce_new_subscription_data', $subscription_data, $args ), true );

	if ( is_wp_error( $subscription_id ) ) {
		return $subscription_id;
	}

	// Default order meta data.
	update_post_meta( $subscription_id, '_order_key', 'wc_' . apply_filters( 'woocommerce_generate_order_key', uniqid( 'order_' ) ) );
	update_post_meta( $subscription_id, '_order_currency', $args['currency'] );
	update_post_meta( $subscription_id, '_prices_include_tax', $args['prices_include_tax'] );
	update_post_meta( $subscription_id, '_created_via', sanitize_text_field( $args['created_via'] ) );

	// add/update the billing
	update_post_meta( $subscription_id, '_billing_period', $args['billing_period'] );
	update_post_meta( $subscription_id, '_billing_interval', absint( $args['billing_interval'] ) );

	update_post_meta( $subscription_id, '_customer_user', $args['customer_id'] );
	update_post_meta( $subscription_id, '_order_version', $args['order_version'] );

	return wcs_get_subscription( $subscription_id );
}

/**
 * Return an array of subscription status types, similar to @see wc_get_order_statuses()
 *
 * @since  2.0
 * @return array
 */
function wcs_get_subscription_statuses() {

	$subscription_statuses = array(
		'wc-pending'        => _x( 'Pending', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-active'         => _x( 'Active', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-on-hold'        => _x( 'On hold', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-cancelled'      => _x( 'Cancelled', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-switched'       => _x( 'Switched', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-expired'        => _x( 'Expired', 'Subscription status', 'woocommerce-subscriptions' ),
		'wc-pending-cancel' => _x( 'Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions' ),
	);

	return apply_filters( 'wcs_subscription_statuses', $subscription_statuses );
}

/**
 * Get the nice name for a subscription's status
 *
 * @since  2.0
 * @param  string $status
 * @return string
 */
function wcs_get_subscription_status_name( $status ) {

	if ( ! is_string( $status ) ) {
		return new WP_Error( 'woocommerce_subscription_wrong_status_format', __( 'Can not get status name. Status is not a string.', 'woocommerce-subscriptions' ) );
	}

	$statuses = wcs_get_subscription_statuses();

	$sanitized_status_key = wcs_sanitize_subscription_status_key( $status );

	// if the sanitized status key is not in the list of filtered subscription names, return the
	// original key, without the wc-
	$status_name   = isset( $statuses[ $sanitized_status_key ] ) ? $statuses[ $sanitized_status_key ] : $status;

	return apply_filters( 'woocommerce_subscription_status_name', $status_name, $status );
}

/**
 * Helper function to return a localised display name for an address type
 *
 * @param string $address_type the type of address (shipping / billing)
 *
 * @return string
 */
function wcs_get_address_type_to_display( $address_type ) {
	if ( ! is_string( $address_type ) ) {
		return new WP_Error( 'woocommerce_subscription_wrong_address_type_format', __( 'Can not get address type display name. Address type is not a string.', 'woocommerce-subscriptions' ) );
	}

	$address_types = apply_filters( 'woocommerce_subscription_address_types', array(
		'shipping' => __( 'Shipping Address', 'woocommerce-subscriptions' ),
		'billing' => __( 'Billing Address', 'woocommerce-subscriptions' ),
	) );

	// if we can't find the address type, return the raw key
	$address_type_display = isset( $address_types[ $address_type ] ) ? $address_types[ $address_type ] : $address_type;

	return apply_filters( 'woocommerce_subscription_address_type_display', $address_type_display, $address_type );
}

/**
 * Returns an array of subscription dates
 *
 * @since  2.0
 * @return array
 */
function wcs_get_subscription_date_types() {

	$dates = array(
		'start'        => _x( 'Start Date', 'table heading', 'woocommerce-subscriptions' ),
		'trial_end'    => _x( 'Trial End', 'table heading', 'woocommerce-subscriptions' ),
		'next_payment' => _x( 'Next Payment', 'table heading', 'woocommerce-subscriptions' ),
		'last_payment' => _x( 'Last Order Date', 'table heading', 'woocommerce-subscriptions' ),
		'cancelled'    => _x( 'Cancelled Date', 'table heading', 'woocommerce-subscriptions' ),
		'end'          => _x( 'End Date', 'table heading', 'woocommerce-subscriptions' ),
	);

	return apply_filters( 'woocommerce_subscription_dates', $dates );
}

/**
 * Find whether to display a specific date type in the admin area
 *
 * @param string A subscription date type key. One of the array key values returned by @see wcs_get_subscription_date_types().
 * @param WC_Subscription
 * @since 2.1
 * @return bool
 */
function wcs_display_date_type( $date_type, $subscription ) {

	if ( 'last_payment' === $date_type ) {
		$display_date_type = false;
	} elseif ( 'cancelled' === $date_type && 0 == $subscription->get_date( $date_type ) ) {
		$display_date_type = false;
	} else {
		$display_date_type = true;
	}

	return apply_filters( 'wcs_display_date_type', $display_date_type, $date_type, $subscription );
}

/**
 * Get the meta key value for storing a date in the subscription's post meta table.
 *
 * @param string $date_type Internally, 'trial_end', 'next_payment' or 'end', but can be any string
 * @since 2.0
 */
function wcs_get_date_meta_key( $date_type ) {
	if ( ! is_string( $date_type ) ) {
		return new WP_Error( 'woocommerce_subscription_wrong_date_type_format', __( 'Date type is not a string.', 'woocommerce-subscriptions' ) );
	} elseif ( empty( $date_type ) ) {
		return new WP_Error( 'woocommerce_subscription_wrong_date_type_format', __( 'Date type can not be an empty string.', 'woocommerce-subscriptions' ) );
	}
	return apply_filters( 'woocommerce_subscription_date_meta_key_prefix', sprintf( '_schedule_%s', $date_type ), $date_type );
}

/**
 * Accept a variety of date type keys and normalise them to current canonical key.
 *
 * This method saves code calling the WC_Subscription date functions, e.g. self::get_date(), needing
 * to make sure they pass the correct date type key, which can involve transforming a prop key or
 * deprecated date type key.
 *
 * @since 2.2.0
 * @param string $date_type_key String referring to a valid date type, can be: 'date_created', 'trial_end', 'next_payment', 'last_order_date_created' or 'end', or any other value returned by @see this->get_valid_date_types()
 * @return string
 */
function wcs_normalise_date_type_key( $date_type_key, $display_deprecated_notice = false ) {

	// Accept date types with a 'schedule_' prefix, like 'schedule_next_payment' because that's the key used for props
	$prefix_length = strlen( 'schedule_' );
	if ( 'schedule_' === substr( $date_type_key, 0, $prefix_length ) ) {
		$date_type_key = substr( $date_type_key, $prefix_length );
	}

	// Accept dates with a '_date' suffix, like 'next_payment_date' or 'start_date'
	$suffix_length = strlen( '_date' );
	if ( '_date' === substr( $date_type_key, -$suffix_length ) ) {
		$date_type_key = substr( $date_type_key, 0, -$suffix_length );
	}

	$deprecated_notice = '';

	if ( 'start' === $date_type_key ) {
		$deprecated_notice = 'The "start" date type parameter has been deprecated to align date types with improvements to date APIs in WooCommerce 3.0, specifically the introduction of a new "date_created" API. Use "date_created"';
		$date_type_key     = 'date_created';
	} elseif ( 'last_payment' === $date_type_key ) {
		$deprecated_notice = 'The "last_payment" date type parameter has been deprecated due to ambiguity (it actually returns the date created for the last order) and to align date types with improvements to date APIs in WooCommerce 3.0, specifically the introduction of a new "date_paid" API. Use "last_order_date_created" or "last_order_date_paid"';
		// For backward compatibility we have to use the date created here not the 'date_paid', see: https://github.com/Prospress/woocommerce-subscriptions/issues/1943
		$date_type_key = 'last_order_date_created';
	}

	if ( true === $display_deprecated_notice && ! empty( $deprecated_notice ) ) {
		wcs_deprecated_argument( esc_attr( wcs_get_calling_function_name() ), '2.2.0', $deprecated_notice );
	}

	return $date_type_key;
}

/**
 * Utility function to standardise status keys:
 * - turns 'pending' into 'wc-pending'.
 * - turns 'wc-pending' into 'wc-pending'
 *
 * @param  string $status_key The status key going in
 * @return string             Status key guaranteed to have 'wc-' at the beginning
 */
function wcs_sanitize_subscription_status_key( $status_key ) {
	if ( ! is_string( $status_key ) || empty( $status_key ) ) {
		return '';
	}
	$status_key = ( 'wc-' === substr( $status_key, 0, 3 ) ) ? $status_key : sprintf( 'wc-%s', $status_key );
	return $status_key;
}

/**
 * A general purpose function for grabbing an array of subscriptions in form of post_id => WC_Subscription
 *
 * The $args parameter is based on the parameter of the same name used by the core WordPress @see get_posts() function.
 * It can be used to choose which subscriptions should be returned by the function, how many subscriptions should be returned
 * and in what order those subscriptions should be returned.
 *
 * @param array $args A set of name value pairs to determine the return value.
 *		'subscriptions_per_page' The number of subscriptions to return. Set to -1 for unlimited. Default 10.
 *		'offset' An optional number of subscription to displace or pass over. Default 0.
 *		'orderby' The field which the subscriptions should be ordered by. Can be 'start_date', 'trial_end_date', 'end_date', 'status' or 'order_id'. Defaults to 'start_date'.
 *		'order' The order of the values returned. Can be 'ASC' or 'DESC'. Defaults to 'DESC'
 *		'customer_id' The user ID of a customer on the site.
 *		'product_id' The post ID of a WC_Product_Subscription, WC_Product_Variable_Subscription or WC_Product_Subscription_Variation object
 *		'order_id' The post ID of a shop_order post/WC_Order object which was used to create the subscription
 *		'subscription_status' Any valid subscription status. Can be 'any', 'active', 'cancelled', 'suspended', 'expired', 'pending' or 'trash'. Defaults to 'any'.
 * @return array Subscription details in post_id => WC_Subscription form.
 * @since  2.0
 */
function wcs_get_subscriptions( $args ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
			'subscriptions_per_page' => 10,
			'paged'                  => 1,
			'offset'                 => 0,
			'orderby'                => 'start_date',
			'order'                  => 'DESC',
			'customer_id'            => 0,
			'product_id'             => 0,
			'variation_id'           => 0,
			'order_id'               => 0,
			'subscription_status'    => array( 'any' ),
			'meta_query_relation'    => 'AND',
		)
	);

	// if order_id is not a shop_order
	if ( 0 !== $args['order_id'] && 'shop_order' !== get_post_type( $args['order_id'] ) ) {
		return array();
	}

	// Ensure subscription_status is an array.
	$args['subscription_status'] = $args['subscription_status'] ? (array) $args['subscription_status'] : array();

	// Grab the native post stati, removing pending and adding any.
	$builtin = get_post_stati( array( '_builtin' => true ) );
	unset( $builtin['pending'] );
	$builtin['any'] = 'any';

	// Make sure status starts with 'wc-'
	foreach ( $args['subscription_status'] as &$status ) {
		if ( isset( $builtin[ $status ] ) ) {
			continue;
		}

		$status = wcs_sanitize_subscription_status_key( $status );
	}

	// Prepare the args for WP_Query
	$query_args = array(
		'post_type'      => 'shop_subscription',
		'post_status'    => $args['subscription_status'],
		'posts_per_page' => $args['subscriptions_per_page'],
		'paged'          => $args['paged'],
		'offset'         => $args['offset'],
		'order'          => $args['order'],
		'fields'         => 'ids',
		'meta_query'     => isset( $args['meta_query'] ) ? $args['meta_query'] : array(), // just in case we need to filter or order by meta values later
	);

	// Maybe only get subscriptions created by a certain order
	if ( 0 != $args['order_id'] && is_numeric( $args['order_id'] ) ) {
		$query_args['post_parent'] = $args['order_id'];
	}

	// Map subscription specific orderby values to internal/WordPress keys
	switch ( $args['orderby'] ) {
		case 'status' :
			$query_args['orderby'] = 'post_status';
			break;
		case 'start_date' :
			$query_args['orderby'] = 'date';
			break;
		case 'trial_end_date' :
		case 'end_date' :
			// We need to orderby post meta value: http://www.paulund.co.uk/order-meta-query
			$date_type  = str_replace( '_date', '', $args['orderby'] );
			$query_args = array_merge( $query_args, array(
				'orderby'   => 'meta_value',
				'meta_key'  => wcs_get_date_meta_key( $date_type ),
				'meta_type' => 'DATETIME',
			) );
			$query_args['meta_query'][] = array(
				'key'     => wcs_get_date_meta_key( $date_type ),
				'compare' => 'EXISTS',
				'type'    => 'DATETIME',
			);
			break;
		default :
			$query_args['orderby'] = $args['orderby'];
			break;
	}

	// Maybe filter to a specific user
	if ( 0 != $args['customer_id'] && is_numeric( $args['customer_id'] ) ) {
		$query_args['meta_query'][] = array(
			'key'     => '_customer_user',
			'value'   => $args['customer_id'],
			'type'    => 'numeric',
			'compare' => ( is_array( $args['customer_id'] ) ) ? 'IN' : '=',
		);
	};

	// We need to restrict subscriptions to those which contain a certain product/variation
	if ( ( 0 != $args['product_id'] && is_numeric( $args['product_id'] ) ) || ( 0 != $args['variation_id'] && is_numeric( $args['variation_id'] ) ) ) {
		$query_args['post__in'] = wcs_get_subscriptions_for_product( array( $args['product_id'], $args['variation_id'] ) );
	}

	if ( ! empty( $query_args['meta_query'] ) ) {
		$query_args['meta_query']['relation'] = $args['meta_query_relation'];
	}

	$query_args = apply_filters( 'woocommerce_get_subscriptions_query_args', $query_args, $args );

	$subscription_post_ids = get_posts( $query_args );

	$subscriptions = array();

	foreach ( $subscription_post_ids as $post_id ) {
		$subscriptions[ $post_id ] = wcs_get_subscription( $post_id );
	}

	return apply_filters( 'woocommerce_got_subscriptions', $subscriptions, $args );
}

/**
 * Get subscriptions that contain a certain product, specified by ID.
 *
 * @param  int | array $product_ids Either the post ID of a product or variation or an array of product or variation IDs
 * @param  string $fields The fields to return, either "ids" to receive only post ID's for the match subscriptions, or "subscription" to receive WC_Subscription objects
 * @return array
 * @since  2.0
 */
function wcs_get_subscriptions_for_product( $product_ids, $fields = 'ids' ) {
	global $wpdb;

	// If we have an array of IDs, convert them to a comma separated list and sanatise them to make sure they're all integers
	if ( is_array( $product_ids ) ) {
		$ids_for_query = implode( "', '", array_map( 'absint', array_unique( array_filter( $product_ids ) ) ) );
	} else {
		$ids_for_query = absint( $product_ids );
	}

	$subscription_ids = $wpdb->get_col( "
		SELECT DISTINCT order_items.order_id FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON order_items.order_item_id = itemmeta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
		WHERE posts.post_type = 'shop_subscription'
			AND itemmeta.meta_value IN ( '" . $ids_for_query . "' )
			AND itemmeta.meta_key   IN ( '_variation_id', '_product_id' )"
	);

	$subscriptions = array();

	foreach ( $subscription_ids as $post_id ) {
		$subscriptions[ $post_id ] = ( 'ids' !== $fields ) ? wcs_get_subscription( $post_id ) : $post_id;
	}

	return apply_filters( 'woocommerce_subscriptions_for_product', $subscriptions, $product_ids, $fields );
}

/**
 * Get all subscription items which have a trial.
 *
 * @param mixed WC_Subscription|post_id
 * @return array
 * @since 2.0
 */
function wcs_get_line_items_with_a_trial( $subscription_id ) {

	$subscription = ( is_object( $subscription_id ) ) ? $subscription_id : wcs_get_subscription( $subscription_id );
	$trial_items  = array();

	foreach ( $subscription->get_items() as $line_item_id => $line_item ) {

		if ( isset( $line_item['has_trial'] ) ) {
			$trial_items[ $line_item_id ] = $line_item;
		}
	}

	return apply_filters( 'woocommerce_subscription_trial_line_items', $trial_items, $subscription_id );
}

/**
 * Checks if the user can be granted the permission to remove a line item from the subscription.
 *
 * @param WC_Subscription $subscription An instance of a WC_Subscription object
 * @since 2.0
 */
function wcs_can_items_be_removed( $subscription ) {
	$allow_remove = false;

	if ( sizeof( $subscription->get_items() ) > 1 && $subscription->payment_method_supports( 'subscription_amount_changes' ) && $subscription->has_status( array( 'active', 'on-hold', 'pending' ) ) ) {
		$allow_remove = true;
	}

	return apply_filters( 'wcs_can_items_be_removed', $allow_remove, $subscription );
}

/**
 * Checks if the user can be granted the permission to remove a particular line item from the subscription.
 *
 * @param WC_Order_item $item An instance of a WC_Order_item object
 * @param WC_Subscription $subscription An instance of a WC_Subscription object
 * @since 2.2.15
 */
function wcs_can_item_be_removed( $item, $subscription ) {
	return apply_filters( 'wcs_can_item_be_removed', true, $item, $subscription );
}

/**
 * Get the Product ID for an order's line item (only the product ID, not the variation ID, even if the order item
 * is for a variation).
 *
 * @param int An order item ID
 * @since 2.0
 */
function wcs_get_order_items_product_id( $item_id ) {
	global $wpdb;

	$product_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta
		 WHERE order_item_id = %d
		 AND meta_key = '_product_id'",
		$item_id
	) );

	return $product_id;
}

/**
 * Get the variation ID for variation items or the product ID for non-variation items.
 *
 * When acting on cart items or order items, Subscriptions often needs to use an item's canonical product ID. For
 * items representing a variation, that means the 'variation_id' value, if the item is not a variation, that means
 * the 'product_id value. This function helps save keystrokes on the idiom to check if an item is to a variation or not.
 *
 * @param array or object $item Either a cart item, order/subscription line item, or a product.
 */
function wcs_get_canonical_product_id( $item_or_product ) {

	if ( is_a( $item_or_product, 'WC_Product' ) ) {
		$product_id = $item_or_product->get_id(); // WC_Product::get_id(), introduced in WC 2.5+, will return the variation ID by default
	} elseif ( is_a( $item_or_product, 'WC_Order_Item' ) ) { // order line item in WC 3.0+
		$product_id = ( $item_or_product->get_variation_id() ) ? $item_or_product->get_variation_id() : $item_or_product->get_product_id();
	} else { // order line item in WC < 3.0
		$product_id = ( ! empty( $item_or_product['variation_id'] ) ) ? $item_or_product['variation_id'] : $item_or_product['product_id'];
	}

	return $product_id;
}

/**
 * Return an array statuses used to describe when a subscriptions has been marked as ending or has ended.
 *
 * @return array
 * @since 2.0
 */
function wcs_get_subscription_ended_statuses() {
	return apply_filters( 'wcs_subscription_ended_statuses', array( 'cancelled', 'trash', 'expired', 'switched', 'pending-cancel' ) );
}

/**
 * Returns true when on the My Account > View Subscription front end page.
 *
 * @return bool
 * @since 2.0
 */
function wcs_is_view_subscription_page() {
	global $wp;

	return ( is_page( wc_get_page_id( 'myaccount' ) ) && isset( $wp->query_vars['view-subscription'] ) ) ? true : false;
}

/**
 * Get a WooCommerce Subscription's image asset url.
 *
 * @param string $file_name The image file name.
 * @return string The image asset url.
 * @since 2.2.20
 */
function wcs_get_image_asset_url( $file_name ) {
	return plugins_url( "/assets/images/{$file_name}", WC_Subscriptions::$plugin_file );
}
