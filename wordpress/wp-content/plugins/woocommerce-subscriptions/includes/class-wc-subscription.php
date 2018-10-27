<?php
/**
 * Subscription Object
 *
 * Extends WC_Order because the Edit Order/Subscription interface requires some of the refund related methods
 * from WC_Order that don't exist in WC_Abstract_Order (which would seem the more appropriate choice)
 *
 * @class    WC_Subscription
 * @version  2.0
 * @package  WooCommerce Subscriptions/Classes
 * @category Class
 * @author   Brent Shepherd
 */

class WC_Subscription extends WC_Order {

	/** @public WC_Order Stores order data for the order in which the subscription was purchased (if any) */
	protected $order = null;

	/** @public string Order type */
	public $order_type = 'shop_subscription';

	/** @private int Stores get_completed_payment_count when used multiple times in payment_complete() */
	private $cached_completed_payment_count = false;

	/**
	 * Which data store to load. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $data_store_name = 'subscription';

	/**
	 * This is the name of this object type. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $object_type = 'subscription';

	/**
	 * Stores the $this->is_editable() returned value in memory
	 *
	 * @var bool
	 */
	private $editable;

	/**
	 * Extra data for this object. Name value pairs (name + default value). Used to add additional information to parent.
	 *
	 * WC 3.0+ property.
	 *
	 * @var array
	 */
	protected $extra_data = array(

		// Extra data with getters/setters
		'billing_period'          => '',
		'billing_interval'        => 1,
		'suspension_count'        => 0,
		'requires_manual_renewal' => 'true',
		'cancelled_email_sent'    => false,
		'trial_period'            => '',

		// Extra data that requires manual getting/setting because we don't define getters/setters for it
		'schedule_trial_end'      => null,
		'schedule_next_payment'   => null,
		'schedule_cancelled'      => null,
		'schedule_end'            => null,
		'schedule_payment_retry'  => null,

		'switch_data'             => array(),
	);

	/** @private array The set of valid date types that can be set on the subscription */
	protected $valid_date_types = array();

	/**
	 * List of properties deprecated for direct access due to WC 3.0+ & CRUD.
	 *
	 * @var array
	 */
	private $deprecated_properties = array(
		'start_date',
		'trial_end_date',
		'next_payment_date',
		'end_date',
		'last_payment_date',
		'order',
		'payment_gateway',
		'requires_manual_renewal',
		'suspension_count',
	);

	/**
	 * Initialize the subscription object.
	 *
	 * @param int|WC_Subscription $order
	 */
	public function __construct( $subscription ) {

		parent::__construct( $subscription );

		$this->order_type = 'shop_subscription';
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'shop_subscription';
	}

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __isset( $key ) {

		if ( ! WC_Subscriptions::is_woocommerce_pre( '3.0' ) && in_array( $key, $this->deprecated_properties ) ) {

			$is_set = true;

		} else {

			$is_set = parent::__isset( $key );

		}

		return $is_set;
	}

	/**
	 * Set deprecated properties via new methods.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set( $key, $value ) {

		if ( in_array( $key, $this->deprecated_properties ) ) {

			switch ( $key ) {

				case 'order' :
					$function = 'WC_Subscription::set_parent_id( $order_id )';
					$this->set_parent_id( wcs_get_objects_property( $value, 'id' ) );
					$this->order = $value;
					break;

				case 'requires_manual_renewal' :
					$function = 'WC_Subscription::set_requires_manual_renewal()';
					$this->set_requires_manual_renewal( $value );
					break;

				case 'payment_gateway' :
					$function = 'WC_Subscription::set_payment_method()';
					$this->set_payment_method( $value );
					break;

				case 'suspension_count' :
					$function = 'WC_Subscription::set_suspension_count()';
					$this->set_suspension_count( $value );
					break;

				default :
					$function = 'WC_Subscription::update_dates()';
					$this->update_dates( array( $key => $value ) );
					break;
			}

			if ( ! WC_Subscriptions::is_woocommerce_pre( '3.0' ) ) {
				wcs_doing_it_wrong( $key, sprintf( 'Subscription properties should not be set directly as WooCommerce 3.0 no longer supports direct property access. Use %s instead.', $function ), '2.2.0' );
			}
		} else {

			$this->$key = $value;
		}
	}

	/**
	 * __get function.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( in_array( $key, $this->deprecated_properties ) ) {

			switch ( $key ) {

				case 'order' :
					$function = 'WC_Subscription::get_parent()';
					$value    = $this->get_parent();
					break;

				case 'requires_manual_renewal' :
					$function = 'WC_Subscription::get_requires_manual_renewal()';
					$value    = $this->get_requires_manual_renewal() ? 'true' : 'false'; // We now use booleans for getter return values, so we need to convert it when being accessed via the old property approach to the string value returned
					break;

				case 'payment_gateway' :
					$function = 'wc_get_payment_gateway_by_order( $subscription )';
					$value    = wc_get_payment_gateway_by_order( $this );
					break;

				case 'suspension_count' :
					$function = 'WC_Subscription::get_suspension_count()';
					$value    = $this->get_suspension_count();
					break;

				default :
					$function = 'WC_Subscription::get_date( ' . $key . ' )';
					$value    = $this->get_date( $key );
					break;
			}

			if ( ! WC_Subscriptions::is_woocommerce_pre( '3.0' ) ) {
				wcs_doing_it_wrong( $key, sprintf( 'Subscription properties should not be accessed directly as WooCommerce 3.0 no longer supports direct property access. Use %s instead.', $function ), '2.2.0' );
			}
		} else {

			$value = parent::__get( $key );

		}

		return $value;
	}

	/**
	 * Checks if the subscription has an unpaid order or renewal order (and therefore, needs payment).
	 *
	 * @param string $subscription_key A subscription key of the form created by @see self::get_subscription_key()
	 * @param int $user_id The ID of the user who owns the subscriptions. Although this parameter is optional, if you have the User ID you should pass it to improve performance.
	 * @return bool True if the subscription has an unpaid renewal order, false if the subscription has no unpaid renewal orders.
	 * @since 2.0
	 */
	public function needs_payment() {

		$needs_payment = false;

		// First check if the subscription is pending or failed or is for $0
		if ( parent::needs_payment() ) {

			$needs_payment = true;

		// Now make sure the parent order doesn't need payment
		} elseif ( ( $parent_order = $this->get_parent() ) && ( $parent_order->needs_payment() || $parent_order->has_status( 'on-hold' ) ) ) {

			$needs_payment = true;

		// And finally, check that the latest order (switch or renewal) doesn't need payment
		} else {

			$last_order_id = get_posts( array(
				'posts_per_page' => 1,
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_subscription_renewal',
						'compare' => '=',
						'value'   => $this->get_id(),
						'type'    => 'numeric',
					),
				),
			) );

			if ( ! empty( $last_order_id ) ) {

				$order = wc_get_order( $last_order_id[0] );

				if ( $order->needs_payment() || $order->has_status( array( 'on-hold', 'failed', 'cancelled' ) ) ) {
					$needs_payment = true;
				}
			}
		}

		return apply_filters( 'woocommerce_subscription_needs_payment', $needs_payment, $this );
	}

	/**
	 * Check if the subscription's payment method supports a certain feature, like date changes.
	 *
	 * If the subscription uses manual renewals as the payment method, it supports all features.
	 * Otherwise, the feature will only be supported if the payment gateway set as the payment
	 * method supports for the feature.
	 *
	 * @param string $payment_gateway_feature one of:
	 *		'subscription_suspension'
	 *		'subscription_reactivation'
	 *		'subscription_cancellation'
	 *		'subscription_date_changes'
	 *		'subscription_amount_changes'
	 * @since 2.0
	 */
	public function payment_method_supports( $payment_gateway_feature ) {

		if ( $this->is_manual() || ( false !== ( $payment_gateway = wc_get_payment_gateway_by_order( $this ) ) && $payment_gateway->supports( $payment_gateway_feature ) ) ) {
			$payment_gateway_supports = true;
		} else {
			$payment_gateway_supports = false;
		}

		return apply_filters( 'woocommerce_subscription_payment_gateway_supports', $payment_gateway_supports, $payment_gateway_feature, $this );
	}

	/**
	 * Check if a the subscription can be changed to a new status or date
	 */
	public function can_be_updated_to( $new_status ) {

		$new_status = ( 'wc-' === substr( $new_status, 0, 3 ) ) ? substr( $new_status, 3 ) : $new_status;

		switch ( $new_status ) {
			case 'pending' :
				if ( $this->has_status( array( 'auto-draft', 'draft' ) ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'completed' : // core WC order status mapped internally to avoid exceptions
			case 'active' :
				if ( $this->payment_method_supports( 'subscription_reactivation' ) && $this->has_status( 'on-hold' ) ) {
					$can_be_updated = true;
				} elseif ( $this->has_status( 'pending' ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'failed' : // core WC order status mapped internally to avoid exceptions
			case 'on-hold' :
				if ( $this->payment_method_supports( 'subscription_suspension' ) && $this->has_status( array( 'active', 'pending' ) ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'cancelled' :
				if ( $this->payment_method_supports( 'subscription_cancellation' ) && ( $this->has_status( 'pending-cancel' ) || ! $this->has_status( wcs_get_subscription_ended_statuses() ) ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'pending-cancel' :
				// Only active subscriptions can be given the "pending cancellation" status, becuase it is used to account for a prepaid term
				if ( $this->payment_method_supports( 'subscription_cancellation' ) && $this->has_status( 'active' ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'expired' :
				if ( ! $this->has_status( array( 'cancelled', 'trash', 'switched' ) ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'trash' :
				if ( $this->has_status( wcs_get_subscription_ended_statuses() ) || $this->can_be_updated_to( 'cancelled' ) ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			case 'deleted' :
				if ( 'trash' == $this->get_status()  ) {
					$can_be_updated = true;
				} else {
					$can_be_updated = false;
				}
				break;
			default :
				$can_be_updated = apply_filters( 'woocommerce_can_subscription_be_updated_to', false, $new_status, $this );
				break;
		}

		return apply_filters( 'woocommerce_can_subscription_be_updated_to_' . $new_status, $can_be_updated, $this );
	}

	/**
	 * Updates status of the subscription
	 *
	 * @param string $new_status Status to change the order to. No internal wc- prefix is required.
	 * @param string $note (default: '') Optional note to add
	 */
	public function update_status( $new_status, $note = '', $manual = false ) {

		if ( ! $this->get_id() ) {
			return;
		}

		// Standardise status names.
		$new_status     = ( 'wc-' === substr( $new_status, 0, 3 ) ) ? substr( $new_status, 3 ) : $new_status;
		$new_status_key = 'wc-' . $new_status;
		$old_status     = ( 'wc-' === substr( $this->get_status(), 0, 3 ) ) ? substr( $this->get_status(), 3 ) : $this->get_status();
		$old_status_key = 'wc-' . $old_status;

		if ( $new_status !== $old_status || ! in_array( $old_status_key, array_keys( wcs_get_subscription_statuses() ) ) ) {

			do_action( 'woocommerce_subscription_pre_update_status', $old_status, $new_status, $this );

			// Only update if possible
			if ( ! $this->can_be_updated_to( $new_status ) ) {

				$message = sprintf( __( 'Unable to change subscription status to "%s".', 'woocommerce-subscriptions' ), $new_status );

				$this->add_order_note( $message );

				do_action( 'woocommerce_subscription_unable_to_update_status', $this, $new_status, $old_status );

				// Let plugins handle it if they tried to change to an invalid status
				throw new Exception( $message );

			}

			try {

				$this->set_status( $new_status, $note, $manual );

				switch ( $new_status ) {

					case 'pending' :
						// Nothing to do here
					break;

					case 'pending-cancel' :

						$end_date = $this->calculate_date( 'end_of_prepaid_term' );

						// If there is no future payment and no expiration date set, or the end date is before now, the customer has no prepaid term (this shouldn't be possible as only active subscriptions can be set to pending cancellation and an active subscription always has either an end date or next payment), so set the end date and cancellation date to now
						if ( 0 == $end_date || wcs_date_to_time( $end_date ) < current_time( 'timestamp', true ) ) {
							$cancelled_date = $end_date = current_time( 'mysql', true );
						} else {
							// the cancellation date is now, and the end date is the end of prepaid term date
							$cancelled_date = current_time( 'mysql', true );
						}

						$this->delete_date( 'trial_end' );
						$this->delete_date( 'next_payment' );
						$this->update_dates( array( 'cancelled' => $cancelled_date, 'end' => $end_date ) );
					break;

					case 'completed' : // core WC order status mapped internally to avoid exceptions
					case 'active' :
						// Recalculate and set next payment date
						$stored_next_payment = $this->get_time( 'next_payment' );

						// Make sure the next payment date is more than 2 hours in the future by default
						if ( $stored_next_payment < ( gmdate( 'U' ) + apply_filters( 'woocommerce_subscription_activation_next_payment_date_threshold', 2 * HOUR_IN_SECONDS, $stored_next_payment, $old_status, $this ) ) ) { // also accounts for a $stored_next_payment of 0, meaning it's not set

							$calculated_next_payment = $this->calculate_date( 'next_payment' );

							if ( $calculated_next_payment > 0 ) {
								$this->update_dates( array( 'next_payment' => $calculated_next_payment ) );
							} elseif ( $stored_next_payment < gmdate( 'U' ) ) { // delete the stored date if it's in the past as we're not updating it (the calculated next payment date is 0 or none)
								$this->delete_date( 'next_payment' );
							}
						} else {
							// In case plugins want to run some code when the subscription was reactivated, but the next payment date was not recalculated.
							do_action( 'woocommerce_subscription_activation_next_payment_not_recalculated', $stored_next_payment, $old_status, $this );
						}
						// Trial end date and end/expiration date don't change at all - they should be set when the subscription is first created
						wcs_make_user_active( $this->get_user_id() );
					break;

					case 'failed' : // core WC order status mapped internally to avoid exceptions
					case 'on-hold' :
						// Record date of suspension - 'post_modified' column?
						$this->set_suspension_count( $this->get_suspension_count() + 1 );
					break;
					case 'cancelled' :
					case 'switched' :
					case 'expired' :
						$this->delete_date( 'trial_end' );
						$this->delete_date( 'next_payment' );

						$dates_to_update = array(
							'end' => current_time( 'mysql', true ),
						);

						// Also set the cancelled date to now if it wasn't set previously (when the status was changed to pending-cancellation)
						if ( 'cancelled' === $new_status && 0 == $this->get_date( 'cancelled' ) ) {
							$dates_to_update['cancelled'] = $dates_to_update['end'];
						}

						$this->update_dates( $dates_to_update );
					break;
				}

				// Make sure status is saved when WC 3.0+ is active, similar to WC_Order::update_status() with WC 3.0+ - set_status() can be used to avoid saving.
				$this->save();

			} catch ( Exception $e ) {
				// Log any exceptions to a WC logger
				$log        = new WC_Logger();
				$log_entry  = print_r( $e, true );
				$log_entry .= 'Exception Trace: ' . print_r( $e->getTraceAsString(), true );

				$log->add( 'wcs-update-status-failures', $log_entry );

				// Make sure the old status is restored
				$this->set_status( $old_status, $note, $manual );

				// There is no status transition
				$this->status_transition = false;

				$this->add_order_note( sprintf( __( 'Unable to change subscription status to "%s". Exception: %s', 'woocommerce-subscriptions' ), $new_status, $e->getMessage() ) );

				// Make sure status is saved when WC 3.0+ is active, similar to WC_Order::update_status() with WC 3.0+ - set_status() can be used to avoid saving.
				$this->save();

				do_action( 'woocommerce_subscription_unable_to_update_status', $this, $new_status, $old_status );

				throw $e;
			}
		}
	}

	/**
	 * Handle the status transition.
	 */
	protected function status_transition() {

		if ( $this->status_transition ) {
			do_action( 'woocommerce_subscription_status_' . $this->status_transition['to'], $this );

			if ( ! empty( $this->status_transition['from'] ) ) {
				/* translators: 1: old subscription status 2: new subscription status */
				$transition_note = sprintf( __( 'Status changed from %1$s to %2$s.', 'woocommerce-subscriptions' ), wcs_get_subscription_status_name( $this->status_transition['from'] ), wcs_get_subscription_status_name( $this->status_transition['to'] ) );

				do_action( 'woocommerce_subscription_status_' . $this->status_transition['from'] . '_to_' . $this->status_transition['to'], $this );

				// Trigger a hook with params we want
				do_action( 'woocommerce_subscription_status_updated', $this, $this->status_transition['to'], $this->status_transition['from'] );

				// Trigger a hook with params matching WooCommerce's 'woocommerce_order_status_changed' hook so functions attached to it can be attached easily to subscription status changes
				do_action( 'woocommerce_subscription_status_changed', $this->get_id(), $this->status_transition['from'], $this->status_transition['to'], $this );

			} else {
				/* translators: %s: new order status */
				$transition_note = sprintf( __( 'Status set to %s.', 'woocommerce-subscriptions' ), wcs_get_subscription_status_name( $this->status_transition['to'] ) );
			}

			// Note the transition occured
			$this->add_order_note( trim( $this->status_transition['note'] . ' ' . $transition_note ), 0, $this->status_transition['manual'] );

			// This has ran, so reset status transition variable
			$this->status_transition = false;
		}
	}

	/**
	 * Checks if the subscription requires manual renewal payments.
	 *
	 * This differs to the @see self::get_requires_manual_renewal() method in that it also conditions outside
	 * of the 'requires_manual_renewal' property which would force a subscription to require manual renewal
	 * payments, like an inactive payment gateway or a site in staging mode.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_manual() {

		if ( WC_Subscriptions::is_duplicate_site() || true === $this->get_requires_manual_renewal() || false === wc_get_payment_gateway_by_order( $this ) ) {
			$is_manual = true;
		} else {
			$is_manual = false;
		}

		return $is_manual;
	}

	/**
	 * Overrides the WC Order get_status function for draft and auto-draft statuses for a subscription
	 * so that it will return a pending status instead of draft / auto-draft.
	 *
	 * @since 2.0
	 * @return string Status
	 */
	public function get_status( $context = 'view' ) {

		if ( in_array( get_post_status( $this->get_id() ), array( 'draft', 'auto-draft' ) ) ) {
			$this->post_status = 'wc-pending';
			$status = apply_filters( 'woocommerce_order_get_status', 'pending', $this );
		} else {
			$status = parent::get_status();
		}

		return $status;
	}

	/**
	 * Get valid order status keys
	 *
	 * @since 2.2.0
	 * @return array details of change
	 */
	public function get_valid_statuses() {
		return array_keys( wcs_get_subscription_statuses() );
	}

	/**
	 * WooCommerce handles statuses without the wc- prefix in has_status, get_status and update_status, however in the database
	 * it stores it with the prefix. This makes it hard to use the same filters / status names in both WC's methods AND WP's
	 * get_posts functions. This function bridges that gap and returns the prefixed versions of completed statuses.
	 *
	 * @since 2.0
	 * @return array By default: wc-processing and wc-completed
	 */
	public function get_paid_order_statuses() {
		$paid_statuses = array(
			'processing',
			'completed',
			'wc-processing',
			'wc-completed',
		);

		$custom_status = apply_filters( 'woocommerce_payment_complete_order_status', 'completed', $this->get_id(), $this );

		if ( '' !== $custom_status && ! in_array( $custom_status, $paid_statuses ) && ! in_array( 'wc-' . $custom_status, $paid_statuses ) ) {
			$paid_statuses[] = $custom_status;
			$paid_statuses[] = 'wc-' . $custom_status;
		}

		return apply_filters( 'woocommerce_subscriptions_paid_order_statuses', $paid_statuses, $this );
	}

	/**
	 * Get the number of payments completed for a subscription
	 *
	 * Completed payment include all renewal orders and potentially an initial order (if the
	 * subscription was created as a result of a purchase from the front end rather than
	 * manually by the store manager).
	 *
	 * @since 2.0
	 */
	public function get_completed_payment_count() {

		// If not cached, calculate the completed payment count otherwise return the cached version
		if ( false === $this->cached_completed_payment_count ) {

			$completed_payment_count = ( ( $parent_order = $this->get_parent() ) && ( null !== wcs_get_objects_property( $parent_order, 'date_paid' ) || $parent_order->has_status( $this->get_paid_order_statuses() ) ) ) ? 1 : 0;

			// Get all renewal orders - for large sites its more efficient to find the two different sets of renewal orders below using post__in than complicated meta queries
			$renewal_orders = get_posts( array(
				'posts_per_page'         => -1,
				'post_status'            => 'any',
				'post_type'              => 'shop_order',
				'fields'                 => 'ids',
				'orderby'                => 'date',
				'order'                  => 'desc',
				'meta_key'               => '_subscription_renewal',
				'meta_compare'           => '=',
				'meta_type'              => 'numeric',
				'meta_value'             => $this->get_id(),
				'update_post_term_cache' => false,
			) );

			if ( ! empty( $renewal_orders ) ) {

				// Not all gateways will call $order->payment_complete() so we need to find renewal orders with a paid status rather than just a _paid_date
				$paid_status_renewal_orders = get_posts( array(
					'posts_per_page' => -1,
					'post_status'    => $this->get_paid_order_statuses(),
					'post_type'      => 'shop_order',
					'fields'         => 'ids',
					'orderby'        => 'date',
					'order'          => 'desc',
					'post__in'       => $renewal_orders,
				) );

				// Some stores may be using custom order status plugins, we also can't rely on order status to find paid orders, so also check for a _paid_date
				$paid_date_renewal_orders = get_posts( array(
					'posts_per_page'         => -1,
					'post_status'            => 'any',
					'post_type'              => 'shop_order',
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'desc',
					'post__in'               => $renewal_orders,
					'meta_key'               => '_paid_date',
					'meta_compare'           => 'EXISTS',
					'update_post_term_cache' => false,
				) );

				$paid_renewal_orders = array_unique( array_merge( $paid_date_renewal_orders, $paid_status_renewal_orders ) );

				if ( ! empty( $paid_renewal_orders ) ) {
					$completed_payment_count += count( $paid_renewal_orders );
				}
			}
		} else {
			$completed_payment_count = $this->cached_completed_payment_count;
		}

		// Store the completed payment count to avoid hitting the database again
		$this->cached_completed_payment_count = apply_filters( 'woocommerce_subscription_payment_completed_count', $completed_payment_count, $this );

		return $this->cached_completed_payment_count;
	}

	/**
	 * Get the number of payments failed
	 *
	 * Failed orders are the number of orders that have wc-failed as the status
	 *
	 * @since 2.0
	 */
	public function get_failed_payment_count() {

		$failed_payment_count = ( ( $parent_order = $this->get_parent() ) && $parent_order->has_status( 'failed' ) ) ? 1 : 0;

		$failed_renewal_orders = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'wc-failed',
			'post_type'      => 'shop_order',
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'desc',
			'meta_query'     => array(
				array(
					'key'     => '_subscription_renewal',
					'compare' => '=',
					'value'   => $this->get_id(),
					'type'    => 'numeric',
				),
			),
		) );

		if ( ! empty( $failed_renewal_orders ) ) {
			$failed_payment_count += count( $failed_renewal_orders );
		}

		return apply_filters( 'woocommerce_subscription_payment_failed_count', $failed_payment_count, $this );
	}

	/**
	 * Returns the total amount charged at the outset of the Subscription.
	 *
	 * This may return 0 if there is a free trial period or the subscription was synchronised, and no sign up fee,
	 * otherwise it will be the sum of the sign up fee and price per period.
	 *
	 * @return float The total initial amount charged when the subscription product in the order was first purchased, if any.
	 * @since 2.0
	 */
	public function get_total_initial_payment() {
		$initial_total = ( $parent_order = $this->get_parent() ) ? $parent_order->get_total() : 0;
		return apply_filters( 'woocommerce_subscription_total_initial_payment', $initial_total, $this );
	}

	/**
	 * Get billing period.
	 *
	 * @return string
	 * @since 2.2.0
	 */
	public function get_billing_period( $context = 'view' ) {
		return $this->get_prop( 'billing_period', $context );
	}

	/**
	 * Get billing interval.
	 *
	 * @return string
	 * @since 2.2.0
	 */
	public function get_billing_interval( $context = 'view' ) {
		return $this->get_prop( 'billing_interval', $context );
	}

	/**
	 * Get trial period.
	 *
	 * @return string
	 * @since 2.2.0
	 */
	public function get_trial_period( $context = 'view' ) {
		return $this->get_prop( 'trial_period', $context );
	}

	/**
	 * Get suspension count.
	 *
	 * @return string
	 * @since 2.2.0
	 */
	public function get_suspension_count( $context = 'view' ) {
		return $this->get_prop( 'suspension_count', $context );
	}

	/**
	 * Checks if the subscription requires manual renewal payments.
	 *
	 * @access public
	 * @return bool
	 * @since 2.2.0
	 */
	public function get_requires_manual_renewal( $context = 'view' ) {
		return $this->get_prop( 'requires_manual_renewal', $context );
	}

	/**
	 * Get the switch data.
	 *
	 * @since 2.2.0
	 * @return string
	 */
	public function get_switch_data( $context = 'view' ) {
		return $this->get_prop( 'switch_data', $context );
	}

	/**
	 * Get the flag about whether the cancelled email has been sent or not.
	 *
	 * @return string
	 */
	public function get_cancelled_email_sent( $context = 'view' ) {
		return $this->get_prop( 'cancelled_email_sent', $context );
	}

	/*** Setters *****************************************************/

	/**
	 * Set billing period.
	 *
	 * @since 2.2.0
	 * @param string $value
	 */
	public function set_billing_period( $value ) {
		$this->set_prop( 'billing_period', $value );
	}

	/**
	 * Set billing interval.
	 *
	 * @since 2.2.0
	 * @param int $value
	 */
	public function set_billing_interval( $value ) {
		$this->set_prop( 'billing_interval', (string) absint( $value ) );
	}

	/**
	 * Set trial period.
	 *
	 * @param string $value
	 * @since 2.2.0
	 */
	public function set_trial_period( $value ) {
		$this->set_prop( 'trial_period', $value );
	}

	/**
	 * Set suspension count.
	 *
	 * @since 2.2.0
	 * @param int $value
	 */
	public function set_suspension_count( $value ) {
		$this->set_prop( 'suspension_count', absint( $value ) );
	}

	/**
	 * Set parent order ID. We don't use WC_Abstract_Order::set_parent_id() because we want to allow false
	 * parent IDs, like 0.
	 *
	 * @since 2.2.0
	 * @param int $value
	 */
	public function set_parent_id( $value ) {
		$this->set_prop( 'parent_id', absint( $value ) );
		$this->order = null;
	}

	/**
	 * Set the manual renewal flag on the subscription.
	 *
	 * The manual renewal flag is stored in database as string 'true' or 'false' when set, and empty string when not set
	 * (which means it doesn't require manual renewal), but we want to consistently use it via get/set as a boolean,
	 * for sanity's sake.
	 *
	 * @since 2.2.0
	 * @param bool $value
	 */
	public function set_requires_manual_renewal( $value ) {

		if ( ! is_bool( $value ) ) {
			if ( 'false' === $value || '' === $value ) {
				$value = false;
			} else { // default to require manual renewal for all other values, which may often includes string 'true' or some invalid value
				$value = true;
			}
		}

		$this->set_prop( 'requires_manual_renewal', $value );
	}

	/**
	 * Set the switch data on the subscription.
	 *
	 * @since 2.2.0
	 */
	public function set_switch_data( $value ) {
		$this->set_prop( 'switch_data', $value );
	}

	/**
	 * Set the flag about whether the cancelled email has been sent or not.
	 *
	 * @since 2.2.0
	 */
	public function set_cancelled_email_sent( $value ) {
		$this->set_prop( 'cancelled_email_sent', $value );
	}

	/*** Date methods *****************************************************/

	/**
	 * Get the MySQL formatted date for a specific piece of the subscriptions schedule
	 *
	 * @param string $date_type 'date_created', 'trial_end', 'next_payment', 'last_order_date_created' or 'end'
	 * @param string $timezone The timezone of the $datetime param, either 'gmt' or 'site'. Default 'gmt'.
	 */
	public function get_date( $date_type, $timezone = 'gmt' ) {

		$date_type = wcs_normalise_date_type_key( $date_type, true );

		if ( empty( $date_type ) ) {
			$date = 0;
		} else {
			switch ( $date_type ) {
				case 'date_created' :
					$date = $this->get_date_created();
					$date = is_null( $date ) ? wcs_get_datetime_from( get_the_date( 'Y-m-d H:i:s', $this->get_id() ) ) : $date; // When a post is first created via the Add Subscription screen, it has a post_date but not a date_created value yet
					break;
				case 'date_modified' :
					$date = $this->get_date_modified();
					break;
				case 'date_paid' :
					$date = $this->get_date_paid();
					break;
				case 'date_completed' :
					$date = $this->get_date_completed();
					break;
				case 'last_order_date_created' :
					$date = $this->get_related_orders_date( 'date_created', 'last' );
					break;
				case 'last_order_date_paid' :
					$date = $this->get_related_orders_date( 'date_paid', 'last' );
					break;
				case 'last_order_date_completed' :
					$date = $this->get_related_orders_date( 'date_completed', 'last' );
					break;
				default :
					$date = $this->get_date_prop( $date_type );
					break;
			}

			if ( is_null( $date ) ) {
				$date = 0;
			}
		}

		if ( is_a( $date, 'DateTime' ) ) {
			// Don't change the original date object's timezone as this may affect the prop stored on the subscription
			$date = clone $date;

			// WC's return values use site timezone by default
			if ( 'gmt' === strtolower( $timezone ) ) {
				$date->setTimezone( new DateTimeZone( 'UTC' ) );
			}

			$date = $date->format( 'Y-m-d H:i:s' );
		}

		return apply_filters( 'woocommerce_subscription_get_' . $date_type . '_date', $date, $this, $timezone );
	}

	/**
	 * Get the stored date.
	 *
	 * Used for WC 3.0 compatibility and for WC_Subscription_Legacy to override.
	 *
	 * @param string $date_type 'trial_end', 'next_payment', 'last_order_date_created', 'cancelled', 'payment_retry' or 'end'
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	protected function get_date_prop( $date_type ) {
		return $this->get_prop( $this->get_date_prop_key( $date_type ) );
	}

	/**
	 * Set the stored date.
	 *
	 * Used for WC 3.0 compatibility and for WC_Subscription_Legacy to override.
	 *
	 * @param string $date_type 'trial_end', 'next_payment', 'cancelled', 'payment_retry' or 'end'
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
	 */
	protected function set_date_prop( $date_type, $value ) {
		parent::set_date_prop( $this->get_date_prop_key( $date_type ), $value );
	}

	/**
	 * Get the key used to refer to the date type in the set of props
	 *
	 * @param string $date_type 'trial_end', 'next_payment', 'last_order_date_created', 'cancelled', 'payment_retry' or 'end'
	 * @return string The key used to refer to the date in props
	 */
	protected function get_date_prop_key( $date_type ) {
		$prefixed_date_type = wcs_maybe_prefix_key( $date_type, 'schedule_' );
		return array_key_exists( $prefixed_date_type, $this->extra_data ) ? $prefixed_date_type : $date_type;
	}

	/**
	 * Get date_paid prop of most recent related order that has been paid.
	 *
	 * A subscription's paid date is actually determined by the most recent related order,
	 * with a paid date set, not a prop on the subscription itself.
	 *
	 * @param  string $context
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_paid( $context = 'view' ) {
		return $this->get_related_orders_date( 'date_paid' );
	}

	/**
	 * Set date_paid.
	 *
	 * A subscription's paid date is actually determined by the last order, not a prop on WC_Subscription.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
	 * @throws WC_Data_Exception
	 */
	public function set_date_paid( $date = null ) {
		$this->set_last_order_date( 'date_paid', $date );
	}

	/**
	 * Get date_completed.
	 *
	 * A subscription's completed date is actually determined by the last order, not a prop.
	 *
	 * @param string $context
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_completed( $context = 'view' ) {
		return $this->get_related_orders_date( 'date_completed' );
	}

	/**
	 * Set date_completed.
	 *
	 * A subscription's completed date is actually determined by the last order, not a prop.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
	 * @throws WC_Data_Exception
	 */
	public function set_date_completed( $date = null ) {
		$this->set_last_order_date( 'date_completed', $date );
	}

	/**
	 * Get a certain date type for the most recent order on the subscription with that date type,
	 * or the last order, if the order type is specified as 'last'.
	 *
	 * @since 2.2.0
	 * @param string $date_type Any valid WC 3.0 date property, including 'date_paid', 'date_completed', 'date_created', or 'date_modified'
	 * @param string $order_type The type of orders to return, can be 'last', 'parent', 'switch', 'renewal' or 'all'. Default 'all'. Use 'last' to only check the last order.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	protected function get_related_orders_date( $date_type, $order_type = 'all' ) {

		$date = null;

		if ( 'last' === $order_type ) {
			$last_order = $this->get_last_order( 'all' );
			$date       = ( ! $last_order ) ? null : wcs_get_objects_property( $last_order, $date_type );
		} else {
			// Loop over orders until we find a valid date of this type or run out of related orders
			foreach ( $this->get_related_orders( 'ids', $order_type ) as $related_order_id ) {
				$related_order = wc_get_order( $related_order_id );
				$date          = ( ! $related_order ) ? null : wcs_get_objects_property( $related_order, $date_type );
				if ( is_a( $date, 'WC_Datetime' ) ) {
					break;
				}
			}
		}

		return $date;
	}

	/**
	 * Set a certain date type for the last order on the subscription.
	 *
	 * @since 2.2.0
	 * @param string $date_type One of 'date_paid', 'date_completed', 'date_modified', or 'date_created'.
	 */
	protected function set_last_order_date( $date_type, $date = null ) {

		if ( $this->object_read ) {

			$setter     = 'set_' . $date_type;
			$last_order = $this->get_last_order( 'all' );

			if ( $last_order && is_callable( array( $last_order, $setter ) ) ) {
				$last_order->{$setter}( $date );
				$last_order->save();
			}
		}
	}

	/**
	 * Returns a string representation of a subscription date in the site's time (i.e. not GMT/UTC timezone).
	 *
	 * @param string $date_type 'date_created', 'trial_end', 'next_payment', 'last_order_date_created', 'end' or 'end_of_prepaid_term'
	 */
	public function get_date_to_display( $date_type = 'next_payment' ) {

		$date_type = wcs_normalise_date_type_key( $date_type, true );

		$timestamp_gmt = $this->get_time( $date_type, 'gmt' );

		// Don't display next payment date when the subscription is inactive
		if ( 'next_payment' == $date_type && ! $this->has_status( 'active' ) ) {
			$timestamp_gmt = 0;
		}

		if ( $timestamp_gmt > 0 ) {

			$time_diff = $timestamp_gmt - current_time( 'timestamp', true );

			if ( $time_diff > 0 && $time_diff < WEEK_IN_SECONDS ) {
				// translators: placeholder is human time diff (e.g. "3 weeks")
				$date_to_display = sprintf( __( 'In %s', 'woocommerce-subscriptions' ), human_time_diff( current_time( 'timestamp', true ), $timestamp_gmt ) );
			} elseif ( $time_diff < 0 && absint( $time_diff ) < WEEK_IN_SECONDS ) {
				// translators: placeholder is human time diff (e.g. "3 weeks")
				$date_to_display = sprintf( __( '%s ago', 'woocommerce-subscriptions' ), human_time_diff( current_time( 'timestamp', true ), $timestamp_gmt ) );
			} else {
				$date_to_display = date_i18n( wc_date_format(), $this->get_time( $date_type, 'site' ) );
			}
		} else {
			switch ( $date_type ) {
				case 'end' :
					$date_to_display = __( 'Not yet ended', 'woocommerce-subscriptions' );
					break;
				case 'cancelled' :
					$date_to_display = __( 'Not cancelled', 'woocommerce-subscriptions' );
					break;
				case 'next_payment' :
				case 'trial_end' :
				default :
					$date_to_display = _x( '-', 'original denotes there is no date to display', 'woocommerce-subscriptions' );
					break;
			}
		}

		return apply_filters( 'woocommerce_subscription_date_to_display', $date_to_display, $date_type, $this );
	}

	/**
	 * Get the timestamp for a specific piece of the subscriptions schedule
	 *
	 * @param string $date_type 'date_created', 'trial_end', 'next_payment', 'last_order_date_created', 'end' or 'end_of_prepaid_term'
	 * @param string $timezone The timezone of the $datetime param. Default 'gmt'.
	 */
	public function get_time( $date_type, $timezone = 'gmt' ) {

		$datetime = $this->get_date( $date_type, $timezone );
		$datetime = wcs_date_to_time( $datetime );

		return $datetime;
	}

	/**
	 * Set the dates on the subscription.
	 *
	 * Because dates are interdependent on each other, this function will take an array of dates, make sure that all
	 * dates are in the right order in the right format, that there is at least something to update.
	 *
	 * @param array $dates array containing dates with keys: 'date_created', 'trial_end', 'next_payment', 'last_order_date_created' or 'end'. Values are MySQL formatted date/time strings in UTC timezone.
	 * @param string $timezone The timezone of the $datetime param. Default 'gmt'.
	 */
	public function update_dates( $dates, $timezone = 'gmt' ) {
		global $wpdb;

		$dates = $this->validate_date_updates( $dates, $timezone );

		// If an exception hasn't been thrown by this point, we can safely update the dates
		$is_updated = false;

		foreach ( $dates as $date_type => $datetime ) {

			if ( $datetime == $this->get_date( $date_type ) ) {
				continue;
			}

			// Delete dates with a 0 date time
			if ( 0 == $datetime ) {
				if ( ! in_array( $date_type, array( 'date_created', 'last_order_date_created', 'last_order_date_modified' ) ) ) {
					$this->delete_date( $date_type );
				}
				continue;
			}

			// WC_Data::set_date_prop() uses site timezone for MySQL date/time strings, but we have a string in UTC, so convert it to a timestamp, which WC_Data will treat as being in UTC. Or if we don't have a date, set it to null so WC_Data deletes it.
			$utc_timestamp = ( 0 === $datetime ) ? null : wcs_date_to_time( $datetime );

			switch ( $date_type ) {
				case 'date_created' :
					$this->set_date_created( $utc_timestamp );
					$is_updated = true;
					break;
				case 'date_modified' :
					$this->set_date_modified( $utc_timestamp );
					$is_updated = true;
					break;
				case 'date_paid' :
					$this->set_date_paid( $utc_timestamp );
					$is_updated = true;
					break;
				case 'date_completed' :
					$this->set_date_completed( $utc_timestamp );
					$is_updated = true;
					break;
				case 'last_order_date_created' :
					$this->set_last_order_date( 'date_created', $utc_timestamp );
					$is_updated = true;
					break;
				case 'last_order_date_paid' :
					$this->set_last_order_date( 'date_paid', $utc_timestamp );
					$is_updated = true;
					break;
				case 'last_order_date_completed' :
					$this->set_last_order_date( 'date_completed', $utc_timestamp );
					$is_updated = true;
					break;
				default :
					$this->set_date_prop( $date_type, $utc_timestamp );
					$is_updated = true;
					break;
			}

			if ( $is_updated && true === $this->object_read ) {
				$this->save_dates();
				do_action( 'woocommerce_subscription_date_updated', $this, $date_type, $datetime );
			}
		}
	}

	/**
	 * Remove a date from a subscription.
	 *
	 * @param string $date_type 'trial_end', 'next_payment' or 'end'. The 'date_created' and 'last_order_date_created' date types will throw an exception.
	 */
	public function delete_date( $date_type ) {

		$date_type = wcs_normalise_date_type_key( $date_type, true );

		// Make sure some dates are before next payment date
		switch ( $date_type ) {
			case 'date_created' :
				$message = __( 'The start date of a subscription can not be deleted, only updated.', 'woocommerce-subscriptions' );
			break;
			case 'last_order_date_created' :
			case 'last_order_date_modified' :
				$message = sprintf( __( 'The %s date of a subscription can not be deleted. You must delete the order.', 'woocommerce-subscriptions' ), $date_type );
			break;
			default :
				$message = '';
			break;
		}

		if ( ! empty( $message ) ) {
			throw new Exception( sprintf( __( 'Subscription #%d: ', 'woocommerce-subscriptions' ), $this->get_id() ) . $message );
		}

		$this->set_date_prop( $date_type, 0 );

		if ( true === $this->object_read ) {
			$this->save_dates();
			do_action( 'woocommerce_subscription_date_deleted', $this, $date_type );
		}
	}

	/**
	 * Check if a given date type can be updated for this subscription.
	 *
	 * @param string $date_type 'date_created', 'trial_end', 'next_payment', 'last_order_date_created' or 'end'
	 */
	public function can_date_be_updated( $date_type ) {

		switch ( $date_type ) {
			case 'date_created' :
				if ( $this->has_status( array( 'auto-draft', 'pending' ) ) ) {
					$can_date_be_updated = true;
				} else {
					$can_date_be_updated = false;
				}
				break;
			case 'trial_end' :
				$this->cached_completed_payment_count = false;
				if ( $this->get_completed_payment_count() < 2 && ! $this->has_status( wcs_get_subscription_ended_statuses() ) && ( $this->has_status( 'pending' ) || $this->payment_method_supports( 'subscription_date_changes' ) ) ) {
					$can_date_be_updated = true;
				} else {
					$can_date_be_updated = false;
				}
				break;
			case 'next_payment' :
			case 'end' :
				if ( ! $this->has_status( wcs_get_subscription_ended_statuses() ) && ( $this->has_status( 'pending' ) || $this->payment_method_supports( 'subscription_date_changes' ) ) ) {
					$can_date_be_updated = true;
				} else {
					$can_date_be_updated = false;
				}
				break;
			case 'last_order_date_created' :
				$can_date_be_updated = true;
				break;
			default :
				$can_date_be_updated = false;
				break;
		}

		return apply_filters( 'woocommerce_subscription_can_date_be_updated', $can_date_be_updated, $date_type, $this );
	}

	/**
	 * Calculate a given date for the subscription in GMT/UTC.
	 *
	 * @param string $date_type 'trial_end', 'next_payment', 'end_of_prepaid_term' or 'end'
	 */
	public function calculate_date( $date_type ) {

		switch ( $date_type ) {
			case 'next_payment' :
				$date = $this->calculate_next_payment_date();
				break;
			case 'trial_end' :
				if ( $this->get_completed_payment_count() >= 2 ) {
					$date = 0;
				} else {
					// By default, trial end is the same as the next payment date
					$date = $this->calculate_next_payment_date();
				}
				break;
			case 'end_of_prepaid_term' :

				$next_payment_time = $this->get_time( 'next_payment' );
				$end_time          = $this->get_time( 'end' );

				// If there was a future payment, the customer has paid up until that payment date
				if ( $this->get_time( 'next_payment' ) >= current_time( 'timestamp', true ) ) {
					$date = $this->get_date( 'next_payment' );
				// If there is no future payment and no expiration date set, the customer has no prepaid term (this shouldn't be possible as only active subscriptions can be set to pending cancellation and an active subscription always has either an end date or next payment)
				} elseif ( 0 == $next_payment_time || $end_time <= current_time( 'timestamp', true ) ) {
					$date = current_time( 'mysql', true );
				} else {
					$date = $this->get_date( 'end' );
				}
				break;
			default :
				$date = 0;
				break;
		}

		return apply_filters( 'woocommerce_subscription_calculated_' . $date_type . '_date', $date, $this );
	}

	/**
	 * Calculates the next payment date for a subscription.
	 *
	 * Although an inactive subscription does not have a next payment date, this function will still calculate the date
	 * so that it can be used to determine the date the next payment should be charged for inactive subscriptions.
	 *
	 * @return int | string Zero if the subscription has no next payment date, or a MySQL formatted date time if there is a next payment date
	 */
	protected function calculate_next_payment_date() {

		$next_payment_date = 0;

		// If the subscription is not active, there is no next payment date
		$start_time        = $this->get_time( 'date_created' );
		$next_payment_time = $this->get_time( 'next_payment' );
		$trial_end_time    = $this->get_time( 'trial_end' );
		$last_payment_time = max( $this->get_time( 'last_order_date_created' ), $this->get_time( 'last_order_date_paid' ) );
		$end_time          = $this->get_time( 'end' );

		// If the subscription has a free trial period, and we're still in the free trial period, the next payment is due at the end of the free trial
		if ( $trial_end_time > current_time( 'timestamp', true ) ) {

			$next_payment_timestamp = $trial_end_time;

		} else {

			// The next payment date is {interval} billing periods from the start date, trial end date or last payment date
			if ( 0 !== $next_payment_time && $next_payment_time < gmdate( 'U' ) && ( ( 0 !== $trial_end_time && 1 >= $this->get_completed_payment_count() ) || WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $this ) ) ) {
				$from_timestamp = $next_payment_time;
			} elseif ( $last_payment_time > $start_time && apply_filters( 'wcs_calculate_next_payment_from_last_payment', true, $this ) ) {
				$from_timestamp = $last_payment_time;
			} elseif ( $next_payment_time > $start_time ) { // Use the currently scheduled next payment to preserve synchronisation
				$from_timestamp = $next_payment_time;
			} else {
				$from_timestamp = $start_time;
			}

			$next_payment_timestamp = wcs_add_time( $this->get_billing_interval(), $this->get_billing_period(), $from_timestamp );

			// Make sure the next payment is more than 2 hours in the future, this ensures changes to the site's timezone because of daylight savings will never cause a 2nd renewal payment to be processed on the same day
			$i = 1;
			while ( $next_payment_timestamp < ( current_time( 'timestamp', true ) + 2 * HOUR_IN_SECONDS ) && $i < 3000 ) {
				$next_payment_timestamp = wcs_add_time( $this->get_billing_interval(), $this->get_billing_period(), $next_payment_timestamp );
				$i += 1;
			}
		}

		// If the subscription has an end date and the next billing period comes after that, return 0
		if ( 0 != $end_time && ( $next_payment_timestamp + 23 * HOUR_IN_SECONDS ) > $end_time ) {
			$next_payment_timestamp = 0;
		}

		if ( $next_payment_timestamp > 0 ) {
			$next_payment_date = gmdate( 'Y-m-d H:i:s', $next_payment_timestamp );
		}

		return $next_payment_date;
	}

	/**
	 * Complete a partial save, saving subscription date changes to the database.
	 *
	 * Sometimes it's necessary to only save changes to date properties, for example, when you
	 * don't want status transitions to be triggered by a full object @see $this->save().
	 *
	 * @since 2.2.6
	 */
	public function save_dates() {
		if ( $this->data_store && $this->get_id() ) {
			$saved_dates = $this->data_store->save_dates( $this );

			// Apply the saved date changes
			$this->data    = array_replace_recursive( $this->data, $saved_dates );
			$this->changes = array_diff_key( $this->changes, $saved_dates );
		}
	}

	/** Formatted Totals Methods *******************************************************/

	/**
	 * Gets line subtotal - formatted for display.
	 *
	 * @param array  $item
	 * @param string $tax_display
	 * @return string
	 */
	public function get_formatted_line_subtotal( $item, $tax_display = '' ) {

		if ( ! $tax_display ) {
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
		}

		if ( ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal_tax'] ) ) {
			return '';
		}

		if ( $this->is_one_payment() ) {

			$subtotal = parent::get_formatted_line_subtotal( $item, $tax_display );

		} else {

			if ( 'excl' == $tax_display ) {
				$line_subtotal = $this->get_line_subtotal( $item );
			} else {
				$line_subtotal = $this->get_line_subtotal( $item, true );
			}
			$subtotal = wcs_price_string( $this->get_price_string_details( $line_subtotal ) );
			$subtotal = apply_filters( 'woocommerce_order_formatted_line_subtotal', $subtotal, $item, $this );
		}

		return $subtotal;
	}

	/**
	 * Gets order total - formatted for display.
	 *
	 * @param string $tax_display only used for method signature match
	 * @param bool $display_refunded only used for method signature match
	 * @return string
	 */
	public function get_formatted_order_total( $tax_display = '', $display_refunded = true ) {
		if ( $this->get_total() > 0 && '' !== $this->get_billing_period() && ! $this->is_one_payment() ) {
			$formatted_order_total = wcs_price_string( $this->get_price_string_details( $this->get_total() ) );
		} else {
			$formatted_order_total = parent::get_formatted_order_total();
		}
		return apply_filters( 'woocommerce_get_formatted_subscription_total', $formatted_order_total, $this );
	}

	/**
	 * Gets subtotal - subtotal is shown before discounts, but with localised taxes.
	 *
	 * @param bool $compound (default: false)
	 * @param string $tax_display (default: the tax_display_cart value)
	 * @return string
	 */
	public function get_subtotal_to_display( $compound = false, $tax_display = '' ) {

		if ( ! $tax_display ) {
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
		}

		$subtotal = 0;

		if ( ! $compound ) {
			foreach ( $this->get_items() as $item ) {

				if ( ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal_tax'] ) ) {
					return '';
				}

				$subtotal += $item['line_subtotal'];

				if ( 'incl' == $tax_display ) {
					$subtotal += $item['line_subtotal_tax'];
				}
			}

			$subtotal = wc_price( $subtotal, array( 'currency' => $this->get_currency() ) );

			if ( 'excl' == $tax_display && $this->get_prices_include_tax() ) {
				$subtotal .= ' <small>' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
		} else {

			if ( 'incl' == $tax_display ) {
				return '';
			}

			foreach ( $this->get_items() as $item ) {

				$subtotal += $item['line_subtotal'];

			}

			// Add Shipping Costs
			$subtotal += $this->get_total_shipping();

			// Remove non-compound taxes
			foreach ( $this->get_taxes() as $tax ) {

				if ( ! empty( $tax['compound'] ) ) {
					continue;
				}

				$subtotal = $subtotal + $tax['tax_amount'] + $tax['shipping_tax_amount'];

			}

			// Remove discounts
			$subtotal = $subtotal - $this->get_total_discount();

			$subtotal = wc_price( $subtotal, array( 'currency' => $this->get_currency() ) );
		}

		return apply_filters( 'woocommerce_order_subtotal_to_display', $subtotal, $compound, $this );
	}

	/**
	 * Get the details of the subscription for use with @see wcs_price_string()
	 *
	 * This is protected because it should not be used directly by outside methods. If you need
	 * to display the price of a subscription, use the @see $this->get_formatted_order_total(),
	 * @see $this->get_subtotal_to_display() or @see $this->get_formatted_line_subtotal() method.
	 * If you want to customise which aspects of a price string are displayed for all subscriptions,
	 * use the filter 'woocommerce_subscription_price_string_details'.
	 *
	 * @return array
	 */
	protected function get_price_string_details( $amount = 0, $display_ex_tax_label = false ) {

		$subscription_details = array(
			'currency'                    => $this->get_currency(),
			'recurring_amount'            => $amount,
			'subscription_period'         => $this->get_billing_period(),
			'subscription_interval'       => $this->get_billing_interval(),
			'display_excluding_tax_label' => $display_ex_tax_label,
		);

		return apply_filters( 'woocommerce_subscription_price_string_details', $subscription_details, $this );
	}

	/**
	 * Cancel the order and restore the cart (before payment)
	 *
	 * @param string $note (default: '') Optional note to add
	 */
	public function cancel_order( $note = '' ) {

		// If the customer hasn't been through the pending cancellation period yet set the subscription to be pending cancellation
		if ( $this->has_status( 'active' ) && $this->calculate_date( 'end_of_prepaid_term' ) > current_time( 'mysql', true ) && apply_filters( 'woocommerce_subscription_use_pending_cancel', true ) ) {

			$this->update_status( 'pending-cancel', $note );

		// If the subscription has already ended or can't be cancelled for some other reason, just record the note
		} elseif ( ! $this->can_be_updated_to( 'cancelled' ) ) {

			$this->add_order_note( $note );

		// Cancel for real if we're already pending cancellation
		} else {

			$this->update_status( 'cancelled', $note );

		}
	}

	/**
	 * Allow subscription amounts/items to bed edited if the gateway supports it.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_editable() {

		if ( ! isset( $this->editable ) ) {

			if ( $this->has_status( array( 'pending', 'draft', 'auto-draft' ) ) ) {
				$this->editable = true;
			} elseif ( $this->is_manual() || $this->payment_method_supports( 'subscription_amount_changes' ) ) {
				$this->editable = true;
			} else {
				$this->editable = false;
			}
		}

		return apply_filters( 'wc_order_is_editable', $this->editable, $this );
	}

	/**
	 * Process payment on the subscription, which mainly means processing it for the last order on the subscription.
	 *
	 * @param $transaction_id string Optional transaction id to store in post meta
	 */
	public function payment_complete( $transaction_id = '' ) {

		if ( WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			return;
		}

		// Clear the cached completed payment count, kept here for backward compat even though it's also reset in $this->process_payment_complete()
		$this->cached_completed_payment_count = false;

		// Make sure the last order's status is updated
		$last_order = $this->get_last_order( 'all', 'any' );

		if ( false !== $last_order && $last_order->needs_payment() ) {
			$last_order->payment_complete( $transaction_id );
		}

		$this->payment_complete_for_order( $last_order );
	}

	/**
	 * When payment is completed for a related order, reset any renewal related counters and reactive the subscription.
	 *
	 * @param WC_Order $order
	 */
	public function payment_complete_for_order( $last_order ) {

		// Clear the cached completed payment count
		$this->cached_completed_payment_count = false;

		// Reset suspension count
		$this->set_suspension_count( 0 );

		// Make sure subscriber has default role
		wcs_update_users_role( $this->get_user_id(), 'default_subscriber_role' );

		// Add order note depending on initial payment
		$this->add_order_note( __( 'Payment status marked complete.', 'woocommerce-subscriptions' ) );

		$this->update_status( 'active' ); // also saves the subscription

		do_action( 'woocommerce_subscription_payment_complete', $this );

		if ( false !== $last_order && wcs_order_contains_renewal( $last_order ) ) {
			do_action( 'woocommerce_subscription_renewal_payment_complete', $this, $last_order );
		}
	}

	/**
	 * When a payment fails, either for the original purchase or a renewal payment, this function processes it.
	 *
	 * @since 2.0
	 */
	public function payment_failed( $new_status = 'on-hold' ) {

		// Make sure the last order's status is set to failed
		$last_order = $this->get_last_order( 'all', 'any' );

		if ( false !== $last_order && false === $last_order->has_status( 'failed' ) ) {
			remove_filter( 'woocommerce_order_status_changed', 'WC_Subscriptions_Renewal_Order::maybe_record_subscription_payment' );
			$last_order->update_status( 'failed' );
			add_filter( 'woocommerce_order_status_changed', 'WC_Subscriptions_Renewal_Order::maybe_record_subscription_payment', 10, 3 );
		}

		// Log payment failure on order
		$this->add_order_note( __( 'Payment failed.', 'woocommerce-subscriptions' ) );

		// Allow a short circuit for plugins & payment gateways to force max failed payments exceeded
		if ( 'cancelled' == $new_status || apply_filters( 'woocommerce_subscription_max_failed_payments_exceeded', false, $this ) ) {
			if ( $this->can_be_updated_to( 'cancelled' ) ) {
				$this->update_status( 'cancelled', __( 'Subscription Cancelled: maximum number of failed payments reached.', 'woocommerce-subscriptions' ) );
			}
		} elseif ( $this->can_be_updated_to( $new_status ) ) {
			$this->update_status( $new_status );
		}

		do_action( 'woocommerce_subscription_payment_failed', $this, $new_status );

		if ( false !== $last_order && wcs_order_contains_renewal( $last_order ) ) {
			do_action( 'woocommerce_subscription_renewal_payment_failed', $this, $last_order );
		}
	}

	/*** Refund related functions are required for the Edit Order/Subscription screen, but they aren't used on a subscription ************/

	/**
	 * Get order refunds
	 *
	 * @since 2.2
	 * @return array
	 */
	public function get_refunds() {
		return array();
	}

	/**
	 * Get amount already refunded
	 *
	 * @since 2.2
	 * @return int|float
	 */
	public function get_total_refunded() {
		return 0;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_qty_refunded_for_item( $item_id, $item_type = 'line_item' ) {
		return 0;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_total_refunded_for_item( $item_id, $item_type = 'line_item' ) {
		return 0;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  int $tax_id ID of the tax we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_tax_refunded_for_item( $item_id, $tax_id, $item_type = 'line_item' ) {
		return 0;
	}

	/**
	 * Get parent order object.
	 *
	 * @return mixed WC_Order|bool
	 */
	public function get_parent() {
		$parent_id = $this->get_parent_id();
		$order     = false;

		if ( $parent_id > 0 ) {
			$order = wc_get_order( $parent_id );
		}

		return $order;
	}

	/**
	 * Extracting the query from get_related_orders and get_last_order so it can be moved in a cached
	 * value.
	 *
	 * @return array
	 */
	public function get_related_orders_query( $id ) {
		$related_post_ids = get_posts( array(
			'posts_per_page' => -1,
			'post_type'      => 'shop_order',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_subscription_renewal',
					'compare' => '=',
					'value'   => $id,
					'type'    => 'numeric',
				),
			),
		) );

		return $related_post_ids;
	}

	/**
	 * Get the related orders for a subscription, including renewal orders and the initial order (if any)
	 *
	 * @param string $return_fields The columns to return, either 'all' or 'ids'
	 * @param string $order_type The type of orders to return, either 'renewal' or 'all'. Default 'all'.
	 * @since 2.0
	 */
	public function get_related_orders( $return_fields = 'ids', $order_type = 'all' ) {

		$return_fields = ( 'ids' == $return_fields ) ? $return_fields : 'all';

		$related_orders = array();

		$related_post_ids = WC_Subscriptions::$cache->cache_and_get( 'wcs-related-orders-to-' . $this->get_id(), array( $this, 'get_related_orders_query' ), array( $this->get_id() ) );

		if ( 'all' == $return_fields ) {

			foreach ( $related_post_ids as $post_id ) {
				$related_orders[ $post_id ] = wc_get_order( $post_id );
			}

			if ( false != $this->get_parent_id() && 'renewal' !== $order_type ) {
				$related_orders[ $this->get_parent_id() ] = $this->get_parent();
			}
		} else {

			// Return IDs only
			if ( false != $this->get_parent_id() && 'renewal' !== $order_type ) {
				$related_orders[ $this->get_parent_id() ] = $this->get_parent_id();
			}

			foreach ( $related_post_ids as $post_id ) {
				$related_orders[ $post_id ] = $post_id;
			}
		}

		return apply_filters( 'woocommerce_subscription_related_orders', $related_orders, $this, $return_fields, $order_type );
	}


	/**
	 * Gets the most recent order that relates to a subscription, including renewal orders and the initial order (if any).
	 *
	 * @param string $return_fields The columns to return, either 'all' or 'ids'
	 * @param array $order_types Can include any combination of 'parent', 'renewal', 'switch' or 'any' which will return the latest renewal order of any type. Defaults to 'parent' and 'renewal'.
	 * @since 2.0
	 */
	public function get_last_order( $return_fields = 'ids', $order_types = array( 'parent', 'renewal' ) ) {

		$return_fields  = ( 'ids' == $return_fields ) ? $return_fields : 'all';
		$order_types    = ( 'any' == $order_types ) ? array( 'parent', 'renewal', 'switch' ) : (array) $order_types;
		$related_orders = array();

		foreach ( $order_types as $order_type ) {
			switch ( $order_type ) {
				case 'parent':
					if ( false != $this->get_parent_id() ) {
						$related_orders[] = $this->get_parent_id();
					}
					break;
				case 'renewal':
					$related_orders = array_merge( $related_orders, WC_Subscriptions::$cache->cache_and_get( 'wcs-related-orders-to-' . $this->get_id(), array( $this, 'get_related_orders_query' ), array( $this->get_id() ) ) );
					break;
				case 'switch':
					$related_orders = array_merge( $related_orders, array_keys( wcs_get_switch_orders_for_subscription( $this->get_id() ) ) );
					break;
				default:
					break;
			}
		}

		if ( empty( $related_orders ) ) {
			$last_order = false;
		} else {
			$last_order = max( $related_orders );

			if ( 'all' == $return_fields ) {
				if ( false != $this->get_parent_id() && $last_order == $this->get_parent_id() ) {
					$last_order = $this->get_parent();
				} else {
					$last_order = wc_get_order( $last_order );
				}
			}
		}

		return apply_filters( 'woocommerce_subscription_last_order', $last_order, $this );
	}

	/**
	 * Determine how the payment method should be displayed for a subscription.
	 *
	 * @since 2.0
	 */
	public function get_payment_method_to_display() {

		if ( $this->is_manual() ) {

			$payment_method_to_display = __( 'Manual Renewal', 'woocommerce-subscriptions' );

		// Use the current title of the payment gateway when available
		} elseif ( false !== ( $payment_gateway = wc_get_payment_gateway_by_order( $this ) ) ) {

			$payment_method_to_display = $payment_gateway->get_title();

		// Fallback to the title of the payment method when the subscripion was created
		} else {

			$payment_method_to_display = $this->get_payment_method_title();

		}

		return apply_filters( 'woocommerce_subscription_payment_method_to_display', $payment_method_to_display, $this );
	}

	/**
	 * Save new payment method for a subscription
	 *
	 * @since 2.0
	 * @param WC_Payment_Gateway|string $payment_method
	 * @param array $payment_meta Associated array of the form: $database_table => array( value, )
	 */
	public function set_payment_method( $payment_method = '', $payment_meta = array() ) {

		if ( empty( $payment_method ) ) {

			$this->set_requires_manual_renewal( true );
			$this->set_prop( 'payment_method', '' );
			$this->set_prop( 'payment_method_title', '' );

		} else {

			// Set the payment gateway ID depending on whether we have a WC_Payment_Gateway or string key
			$payment_method_id = is_a( $payment_method, 'WC_Payment_Gateway' ) ? $payment_method->id : $payment_method;

			if ( ! empty( $payment_meta ) ) {
				$this->set_payment_method_meta( $payment_method_id, $payment_meta );
			}

			if ( $this->get_payment_method() !== $payment_method_id ) {

				// We shouldn't set the requires manual renewal prop or try to get the payment gateway while the object is being read. That prop should be set by reading it from the DB not based on settings or the payment gateway
				if ( $this->object_read ) {

					// Set the payment gateway ID depending on whether we have a string or WC_Payment_Gateway or string key
					if ( is_a( $payment_method, 'WC_Payment_Gateway' ) ) {
						$payment_gateway  = $payment_method;
					} else {
						$payment_gateways = WC()->payment_gateways->payment_gateways();
						$payment_gateway  = isset( $payment_gateways[ $payment_method_id ] ) ? $payment_gateways[ $payment_method_id ] : null;
					}

					if ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no' ) ) {
						$this->set_requires_manual_renewal( true );
					} elseif ( is_null( $payment_gateway ) || false == $payment_gateway->supports( 'subscriptions' ) ) {
						$this->set_requires_manual_renewal( true );
					} else {
						$this->set_requires_manual_renewal( false );
					}

					$this->set_prop( 'payment_method_title', is_null( $payment_gateway ) ? '' : $payment_gateway->get_title() );
				}

				$this->set_prop( 'payment_method', $payment_method_id );
			}
		}
	}

	/**
	 * Save payment method meta data for the Subscription
	 *
	 * @since 2.0
	 * @param array $payment_meta Associated array of the form: $database_table => array( value, )
	 */
	protected function set_payment_method_meta( $payment_method_id, $payment_meta ) {

		if ( ! is_array( $payment_meta ) ) {
			throw new InvalidArgumentException( __( 'Payment method meta must be an array.', 'woocommerce-subscriptions' ) );
		}

		// Allow payment gateway extensions to validate the data and throw exceptions if necessary
		do_action( 'woocommerce_subscription_validate_payment_meta', $payment_method_id, $payment_meta, $this );
		do_action( 'woocommerce_subscription_validate_payment_meta_' . $payment_method_id, $payment_meta, $this );

		foreach ( $payment_meta as $meta_table => $meta ) {
			foreach ( $meta as $meta_key => $meta_data ) {
				if ( isset( $meta_data['value'] ) ) {
					switch ( $meta_table ) {
						case 'user_meta':
						case 'usermeta':
							update_user_meta( $this->get_user_id(), $meta_key, $meta_data['value'] );
							break;
						case 'post_meta':
						case 'postmeta':
							$this->update_meta_data( $meta_key, $meta_data['value'] );
							break;
						case 'options':
							update_option( $meta_key, $meta_data['value'] );
							break;
						default:
							do_action( 'wcs_save_other_payment_meta', $this, $meta_table, $meta_key, $meta_data['value'] );
					}
				}
			}
		}

	}

	/**
	 * Now uses the URL /my-account/view-subscription/{post-id} when viewing a subscription from the My Account Page.
	 *
	 * @since 2.0
	 */
	public function get_view_order_url() {
		$view_subscription_url = wc_get_endpoint_url( 'view-subscription', $this->get_id(), wc_get_page_permalink( 'myaccount' ) );

		return apply_filters( 'wcs_get_view_subscription_url', $view_subscription_url, $this->get_id() );
	}

	/**
	 * Checks if product download is permitted
	 *
	 * @return bool
	 */
	public function is_download_permitted() {
		$sending_email = did_action( 'woocommerce_email_header' ) > did_action( 'woocommerce_email_footer' );
		$is_download_permitted = $this->has_status( 'active' ) || $this->has_status( 'pending-cancel' );

		// WC Emails are sent before the subscription status is updated to active etc. so we need a way to ensure download links are added to the emails before being sent
		if ( $sending_email && ! $is_download_permitted ) {
			$is_download_permitted = true;
		}

		return apply_filters( 'woocommerce_order_is_download_permitted', $is_download_permitted, $this );
	}

	/**
	 * Check if the subscription has a line item for a specific product, by ID.
	 *
	 * @param int A product or variation ID to check for.
	 * @return bool
	 */
	public function has_product( $product_id ) {

		$has_product = false;

		foreach ( $this->get_items() as $line_item ) {
			if ( $line_item['product_id'] == $product_id || $line_item['variation_id'] == $product_id ) {
				$has_product = true;
				break;
			}
		}

		return $has_product;
	}

	/**
	 * The total sign-up fee for the subscription if any.
	 *
	 * @param array|int Either an order item (in the array format returned by self::get_items()) or the ID of an order item.
	 * @return bool
	 * @since 2.0
	 */
	public function get_sign_up_fee() {

		$sign_up_fee = 0;

		foreach ( $this->get_items() as $line_item ) {
			try {
				$sign_up_fee += $this->get_items_sign_up_fee( $line_item );
			} catch ( Exception $e ) {
				$sign_up_fee += 0;
			}
		}

		return apply_filters( 'woocommerce_subscription_sign_up_fee', $sign_up_fee, $this );
	}

	/**
	 * Check if a given line item on the subscription had a sign-up fee, and if so, return the value of the sign-up fee.
	 *
	 * The single quantity sign-up fee will be returned instead of the total sign-up fee paid. For example, if 3 x a product
	 * with a 10 BTC sign-up fee was purchased, a total 30 BTC was paid as the sign-up fee but this function will return 10 BTC.
	 *
	 * @param array|int Either an order item (in the array format returned by self::get_items()) or the ID of an order item.
	 * @param  string $tax_inclusive_or_exclusive Whether or not to adjust sign up fee if prices inc tax - ensures that the sign up fee paid amount includes the paid tax if inc
	 * @return bool
	 * @since 2.0
	 */
	public function get_items_sign_up_fee( $line_item, $tax_inclusive_or_exclusive = 'exclusive_of_tax' ) {

		if ( ! is_object( $line_item ) ) {
			$line_item = wcs_get_order_item( $line_item, $this );
		}

		$parent_order = $this->get_parent();

		// If there was no original order, nothing was paid up-front which means no sign-up fee
		if ( false == $parent_order ) {

			$sign_up_fee = 0;

		} else {

			$original_order_item = '';

			// Find the matching item on the order
			foreach ( $parent_order->get_items() as $order_item ) {
				if ( wcs_get_canonical_product_id( $line_item ) == wcs_get_canonical_product_id( $order_item ) ) {
					$original_order_item = $order_item;
					break;
				}
			}

			// No matching order item, so this item wasn't purchased in the original order
			if ( empty( $original_order_item ) ) {

				$sign_up_fee = 0;

			} elseif ( 'true' === $line_item->get_meta( '_has_trial' ) ) {
				// Sign up is amount paid for this item on original order, we can safely use 3.0 getters here because we know from the above condition 3.0 is active
				$sign_up_fee = ( (float) $original_order_item->get_total( 'edit' ) ) / $original_order_item->get_quantity( 'edit' );
			} else {
				// Sign-up fee is any amount on top of recurring amount
				$order_line_total        = ( (float) $original_order_item->get_total( 'edit' ) ) / $original_order_item->get_quantity( 'edit' );
				$subscription_line_total = ( (float) $line_item->get_total( 'edit' ) ) / $line_item->get_quantity( 'edit' );

				$sign_up_fee = max( $order_line_total - $subscription_line_total, 0 );
			}

			// If prices inc tax, ensure that the sign up fee amount includes the tax
			if ( 'inclusive_of_tax' === $tax_inclusive_or_exclusive && ! empty( $original_order_item ) && $this->get_prices_include_tax() ) {
				$proportion   = $sign_up_fee / ( $original_order_item->get_total( 'edit' ) / $original_order_item->get_quantity( 'edit' ) );
				$sign_up_fee += round( $original_order_item->get_total_tax( 'edit' ) * $proportion, 2 );
			}
		}

		return apply_filters( 'woocommerce_subscription_items_sign_up_fee', $sign_up_fee, $line_item, $this, $tax_inclusive_or_exclusive );
	}

	/**
	 *  Determine if the subscription is for one payment only.
	 *
	 * @return bool whether the subscription is for only one payment
	 * @since 2.0.17
	 */
	public function is_one_payment() {

		$is_one_payment = false;

		if ( 0 != ( $end_time = $this->get_time( 'end' ) ) ) {

			$from_timestamp = $this->get_time( 'date_created' );

			if ( 0 != $this->get_time( 'trial_end' ) || WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $this ) ) {

				$subscription_order_count = count( $this->get_related_orders() );

				// when we have a sync'd subscription before its 1st payment, we need to base the calculations for the next payment on the first/next payment timestamp.
				if ( $subscription_order_count < 2 && 0 != ( $next_payment_timestamp = $this->get_time( 'next_payment' ) )  ) {
					$from_timestamp = $next_payment_timestamp;

				// when we have a sync'd subscription after its 1st payment, we need to base the calculations for the next payment on the last payment timestamp.
				} else if ( ! ( $subscription_order_count > 2 ) && 0 != ( $last_payment_timestamp = $this->get_time( 'last_order_date_created' ) ) ) {
					$from_timestamp = $last_payment_timestamp;
				}
			}

			$next_payment_timestamp = wcs_add_time( $this->get_billing_interval(), $this->get_billing_period(), $from_timestamp );

			if ( ( $next_payment_timestamp + DAY_IN_SECONDS - 1 ) > $end_time ) {
				$is_one_payment = true;
			}
		}

		return apply_filters( 'woocommerce_subscription_is_one_payment', $is_one_payment, $this );
	}

	/**
	 * Get the downloadable files for an item in this subscription if the subscription is active
	 *
	 * @param  array $item
	 * @return array
	 */
	public function get_item_downloads( $item ) {

		if ( ! WC_Subscriptions::is_woocommerce_pre( '3.0' ) ) {
			wcs_deprecated_function( __METHOD__, '2.2.0', 'WC_Order_Item_Product::get_item_downloads(), because WooCommerce 3.0+ now uses that' );
		}

		$files = array();

		// WC Emails are sent before the subscription status is updated to active etc. so we need a way to ensure download links are added to the emails before being sent
		$sending_email = ( did_action( 'woocommerce_email_before_order_table' ) > did_action( 'woocommerce_email_after_order_table' ) ) ? true : false;

		if ( $this->has_status( apply_filters( 'woocommerce_subscription_item_download_statuses', array( 'active', 'pending-cancel' ) ) ) || $sending_email ) {
			$files = parent::get_item_downloads( $item );
		}

		return apply_filters( 'woocommerce_get_item_downloads', $files, $item, $this );
	}

	/**
	 * Validates subscription date updates ensuring the proposed date changes are in the correct format and are compatible with
	 * the current subscription dates. Also returns the dates in the gmt timezone - ready for setting/deleting.
	 *
	 * @param array $dates array containing dates with keys: 'date_created', 'trial_end', 'next_payment', 'last_order_date_created' or 'end'. Values are MySQL formatted date/time strings in UTC timezone.
	 * @param string $timezone The timezone of the $datetime param. Default 'gmt'.
	 * @return array $dates array of dates in gmt timezone.
	 */
	public function validate_date_updates( $dates, $timezone = 'gmt' ) {

		if ( ! is_array( $dates ) ) {
			throw new InvalidArgumentException( __( 'Invalid format. First parameter needs to be an array.', 'woocommerce-subscriptions' ) );
		}

		if ( empty( $dates ) ) {
			throw new InvalidArgumentException( __( 'Invalid data. First parameter was empty when passed to update_dates().', 'woocommerce-subscriptions' ) );
		}

		$passed_date_keys = array_map( 'wcs_normalise_date_type_key', array_keys( $dates ) );
		$extra_keys       = array_diff( $passed_date_keys, $this->get_valid_date_types() );

		if ( ! empty( $extra_keys ) ) {
			throw new InvalidArgumentException( __( 'Invalid data. First parameter has a date that is not in the registered date types.', 'woocommerce-subscriptions' ) );
		}

		// Use the normalised keys for the array
		$dates = array_combine( $passed_date_keys, array_values( $dates ) );

		$timestamps = $delete_date_types = array();

		// Get a full set of subscription dates made up of passed and current dates
		foreach ( $this->get_valid_date_types() as $date_type ) {

			// While 'start' & 'last_payment' are valid date types, they are deprecated and we use 'date_created' & 'last_order_date_created' to refer to them now instead
			if ( in_array( $date_type, array( 'last_payment', 'start' ) ) ) {
				continue;
			}

			// We don't want to validate dates for relates orders when instantiating the subscription
			if ( false === $this->object_read && ( 0 === strpos( $date_type, 'last_order_date_' ) || in_array( $date_type, array( 'date_paid', 'date_completed' ) ) ) ) {
				continue;
			}

			// Honour passed values first
			if ( isset( $dates[ $date_type ] ) ) {
				$datetime = $dates[ $date_type ];

				if ( ! empty( $datetime ) && false === wcs_is_datetime_mysql_format( $datetime ) ) {
					// translators: placeholder is date type (e.g. "end", "next_payment"...)
					throw new InvalidArgumentException( sprintf( _x( 'Invalid %s date. The date must be of the format: "Y-m-d H:i:s".', 'appears in an error message if date is wrong format', 'woocommerce-subscriptions' ), $date_type ) );
				}

				if ( empty( $datetime ) ) {

					$timestamps[ $date_type ] = 0;

				} else {

					if ( 'gmt' !== strtolower( $timezone ) ) {
						$datetime = get_gmt_from_date( $datetime );
					}

					$timestamps[ $date_type ] = wcs_date_to_time( $datetime );
				}
			// otherwise get the current subscription time
			} else {
				$timestamps[ $date_type ] = $this->get_time( $date_type );
			}

			if ( 0 == $timestamps[ $date_type ] ) {
				// Last payment is not in the UI, and it should NOT be deleted as that would mess with scheduling
				if ( 'last_order_date_created' != $date_type && 'date_created' != $date_type ) {
					// We need to separate the dates which need deleting, so they don't interfere in the remaining validation
					$delete_date_types[ $date_type ] = 0;
				}
				unset( $timestamps[ $date_type ] );
			}
		}

		$messages = array();

		// And then iterate over them checking the relationships between them.
		foreach ( $timestamps as $date_type => $timestamp ) {
			switch ( $date_type ) {
				case 'end' :
					if ( array_key_exists( 'cancelled', $timestamps ) && $timestamp < $timestamps['cancelled'] ) {
						$messages[] = sprintf( __( 'The %s date must occur after the cancellation date.', 'woocommerce-subscriptions' ), $date_type );
					}

				case 'cancelled' :
					if ( array_key_exists( 'last_order_date_created', $timestamps ) && $timestamp < $timestamps['last_order_date_created'] ) {
						$messages[] = sprintf( __( 'The %s date must occur after the last payment date.', 'woocommerce-subscriptions' ), $date_type );
					}

					if ( array_key_exists( 'next_payment', $timestamps ) && $timestamp <= $timestamps['next_payment'] ) {
						$messages[] = sprintf( __( 'The %s date must occur after the next payment date.', 'woocommerce-subscriptions' ), $date_type );
					}
				case 'next_payment' :
					// Guarantees that end is strictly after trial_end, because if next_payment and end can't be at same time
					if ( array_key_exists( 'trial_end', $timestamps ) && $timestamp < $timestamps['trial_end'] ) {
						$messages[] = sprintf( __( 'The %s date must occur after the trial end date.', 'woocommerce-subscriptions' ), $date_type );
					}
				case 'trial_end' :
					if ( $timestamp <= $timestamps['date_created'] ) {
						$messages[] = sprintf( __( 'The %s date must occur after the start date.', 'woocommerce-subscriptions' ), $date_type );
					}
			}

			$dates[ $date_type ] = gmdate( 'Y-m-d H:i:s', $timestamp );
		}

		// Don't validate dates while the subscription is being read, only dates set outside of instantiation require the strict validation rules to apply
		if ( $this->object_read && ! empty( $messages ) ) {
			throw new Exception( sprintf( __( 'Subscription #%d: ', 'woocommerce-subscriptions' ), $this->get_id() ) . join( ' ', $messages ) );
		}

		return array_merge( $dates, $delete_date_types );
	}

	/**
	 * Add a product line item to the subscription.
	 *
	 * @since 2.1.4
	 * @param WC_Product product
	 * @param int line item quantity.
	 * @param array args
	 * @return int|bool Item ID or false.
	 */
	public function add_product( $product, $qty = 1, $args = array() ) {
		$item_id = parent::add_product( $product, $qty, $args );

		// Remove backordered meta if it has been added
		if ( $item_id && $product->backorders_require_notification() && $product->is_on_backorder( $qty ) ) {
			wc_delete_order_item_meta( $item_id, apply_filters( 'woocommerce_backordered_item_meta_name', __( 'Backordered', 'woocommerce-subscriptions' ) ) );
		}

		return $item_id;
	}

	/**
	 * Get the set of date types that can be set/get from this subscription.
	 *
	 * The allowed dates includes both subscription date dates, and date types for related orders, like 'last_order_date_created'.
	 *
	 * @since 2.2.0
	 * @return array
	 */
	protected function get_valid_date_types() {

		if ( empty( $this->valid_date_types ) ) {
			$this->valid_date_types = apply_filters( 'woocommerce_subscription_valid_date_types', array_merge(
				array_keys( wcs_get_subscription_date_types() ),
				array(
					'date_created',
					'date_modified',
					'date_paid',
					'date_completed',
					'last_order_date_created',
					'last_order_date_paid',
					'last_order_date_completed',
					'payment_retry',
				)
			), $this );
		}

		return $this->valid_date_types;
	}

	/************************
	 * WC_Order overrides
	 *
	 * Make some WC_Order methods do nothing.
	 ************************/

	/**
	 * Avoid running the expensive get_date_paid() query on related orders.
	 *
	 * @since 2.2.19
	 */
	public function maybe_set_date_paid() {
		return null;
	}

	/**
	 * Avoid running the expensive get_date_completed() query on related orders.
	 *
	 * @since 2.2.19
	 */
	protected function maybe_set_date_completed() {
		return null;
	}

	/************************
	 * Deprecated Functions *
	 ************************/

	/**
	 * Set or change the WC_Order ID which records the subscription's initial purchase.
	 *
	 * @param int|WC_Order $order
	 */
	public function update_parent( $order ) {
		wcs_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::set_parent_id(), because WooCommerce 3.0+ now uses that' );

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		$this->set_parent_id( wcs_get_objects_property( $order, 'id' ) );

		// And update the parent in memory
		$this->order = $order;
	}

	/**
	 * Update the internal tally of suspensions on this subscription since the last payment.
	 *
	 * @return int The count of suspensions
	 * @since 2.0
	 */
	public function update_suspension_count( $new_count ) {
		wcs_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::set_suspension_count(), because WooCommerce 3.0+ now uses setters' );
		$this->set_suspension_count( $new_count );
		return $this->get_suspension_count();
	}

	/**
	 * Checks if the subscription requires manual renewal payments.
	 *
	 * @access public
	 * @return bool
	 */
	public function update_manual( $is_manual = true ) {
		wcs_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::set_requires_manual_renewal( $is_manual ), because WooCommerce 3.0+ now uses setters' );
		$this->set_requires_manual_renewal( $is_manual );
		return $this->get_requires_manual_renewal();
	}

	/**
	 * Get the "last payment date" for a subscription, in GMT/UTC.
	 *
	 * The "last payment date" is based on the original order used to purchase the subscription or
	 * it's last renewal order, which ever is more recent.
	 *
	 * The "last payment date" is in quotation marks because this function didn't and still doesn't
	 * accurately return the last payment date. Instead, it returned and still returns the date of the
	 * last order, regardless of its paid status. This is partly why this function has been deprecated
	 * in favour of self::get_date_paid() (or self::get_related_orders_date( 'date_created', 'last' ).
	 *
	 * For backward compatibility we have to use the date created here, see: https://github.com/Prospress/woocommerce-subscriptions/issues/1943
	 *
	 * @deprecated 2.2.0
	 * @since 2.0
	 */
	protected function get_last_payment_date() {
		wcs_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::get_date( "last_order_date_created" )' );
		return $this->get_date( 'last_order_date_created' );
	}

	/**
	 * Updated both the _paid_date and post date GMT with the WooCommerce < 3.0 date storage structures.
	 *
	 * @deprecated 2.2.0
	 * @param string $datetime A MySQL formatted date/time string in GMT/UTC timezone.
	 */
	protected function update_last_payment_date( $datetime ) {
		wcs_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::set_date_paid( $datetime ) or WC_Order::set_date_created( $datetime ) on the last order, because WooCommerce 3.0 now uses those setters' );

		$last_order = $this->get_last_order();

		if ( ! $last_order ) {
			return false;
		}

		// Pass a timestamp to the WC 3.0 setters becasue WC expects MySQL date strings to be in site's timezone, but we have a date string in UTC timezone
		$timestamp = ( $datetime > 0 ) ? wcs_date_to_time( $datetime ) : 0;

		$this->set_last_order_date( 'date_paid', $timestamp );
		$this->set_last_order_date( 'date_created', $timestamp );

		return $datetime;
	}
}
