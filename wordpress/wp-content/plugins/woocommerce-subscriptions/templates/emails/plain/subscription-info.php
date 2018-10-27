<?php
/**
 * Subscription information template
 *
 * @author  Brent Shepherd / Chuck Mac
 * @package WooCommerce_Subscriptions/Templates/Emails
 * @version 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! empty( $subscriptions ) ) {

	echo "\n\n" .  __( 'Subscription Information:', 'woocommerce-subscriptions' ) . "\n\n";
	foreach ( $subscriptions as $subscription ) {
		// translators: placeholder is subscription's number
		echo sprintf( _x( 'Subscription: %s', 'in plain emails for subscription information', 'woocommerce-subscriptions' ), $subscription->get_order_number() ) . "\n";
		// translators: placeholder is either view or edit url for the subscription
		echo sprintf( _x( 'View Subscription: %s', 'in plain emails for subscription information', 'woocommerce-subscriptions' ), $is_admin_email ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ) . "\n";
		// translators: placeholder is localised start date
		echo sprintf( _x( 'Start Date: %s', 'in plain emails for subscription information', 'woocommerce-subscriptions' ), date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ) . "\n";

		$end_date = ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When Cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions' );
		// translators: placeholder is localised end date, or "when cancelled"
		echo sprintf( _x( 'End Date: %s', 'in plain emails for subscription information', 'woocommerce-subscriptions' ), $end_date ) . "\n";
		// translators: placeholder is the formatted order total for the subscription
		echo sprintf( _x( 'Price: %s', 'in plain emails for subscription information', 'woocommerce-subscriptions' ), $subscription->get_formatted_order_total() );
		echo "\n\n";
	}
}
