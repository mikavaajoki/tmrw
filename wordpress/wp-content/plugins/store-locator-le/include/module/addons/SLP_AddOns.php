<?php
require_once( SLPLUS_PLUGINDIR . 'include/unit/SLP_AddOn.php' );


/**
 * Add On Manager
 *
 * @property        array                 $available                        An array of all the available add-on packs we know about. $this->slplus->add_ons->available['slp-pro']['link']
 * @var             string                $available                        [<slug>]['name']   translated text name of add on
 * @var             string                $available                        [<slug>]['link']   full HTML anchor link to the product.  <a href...>Name</a>.
 *
 * @property        SLP_BaseClass_Addon[] $instances                        The add on objects in a named array.  The slug is the key. $instances['slp-pro'] => \SLPPro instance.
 * @property        SLP_AddOn             $list                             The list of Add Ons we've been playing with.
 * @property        boolean               $network_notified                 Network notified?
 * @property-read   boolean               $premier_subscription_valid       Is the subscription valid?
 * @property        string[]              $recommended_upgrades             Array of add-on slugs that have recommended upgrades.
 * @property        string[]              $upgrades_already_recommended     Array of add-on slugs for those we already told the user about.
 * @property        array                 $upgrade_paths                    key = the add-on slug to be upgraded, value = the slug for the add-on to upgrade to.
 * @property-read   string                $wpdk_url
 *
 */
class SLP_AddOns extends SLPlus_BaseClass_Object {
	public  $available                    = array();
	public  $instances                    = array();
	public  $list                         = array();
	public  $network_notified             = false;
	private $premier_subscription_valid;
	public  $premier_subscription_error   = null;
	public  $recommended_upgrades         = array();
	private $upgrades_already_recommended = array();
	private $upgrade_paths                = array();
	private $wpdk_url                     = 'https://wordpress.storelocatorplus.com/wp-json/wp-dev-kit/v1/';

	/**
	 * Given the text to display and the leaf (end) portion of the product URL, return a full HTML link to the product page.
	 *
	 * @param string $text
	 * @param string $addon_slug
	 *
	 * @return string
	 */
	private function create_string_product_link( $text, $addon_slug ) {

		if ( empty( $addon_slug ) ) return '';

		// If addon_slug is not a simple product slug but a URL:
		//
		if ( strpos( $addon_slug, '/' ) !== false ) {
			preg_match( '/^.*\/(.*)\/$/', $addon_slug, $matches );
			if ( isset( $matches[1] ) ) {
				$addon_slug = $matches[1];
			}
		}

		return SLP_Text::get_instance()->get_web_link( 'shop_for_' . $addon_slug );
	}

	/**
	 * Return an upgrade recommended notice.
	 *
	 * @param   string $slug The slug for the add-on needed an upgrade.
	 *
	 * @return  string
	 */
	private function create_string_for_recommendations( $slug ) {
		$legacy_name  = $this->instances[ $slug ]->name;
		$upgrade_slug = $this->recommend_upgrade( $slug );
		$upgrade_name = $this->get_product_url( $upgrade_slug );

		return
			sprintf( __( 'The %s add-on is not running as efficiently as possible. ', 'store-locator-le' ), $legacy_name ) .
			'<br/>' .
			sprintf( __( 'Upgrading to the latest %s add-on is recommended. ', 'store-locator-le' ), $upgrade_name );
	}

	/**
	 * Create the recommended upgrades notification text.
	 *
	 * @return string
	 */
	public function get_recommendations_text() {
		$html = '';
		foreach ( $this->recommended_upgrades as $slug ) {
			if ( ! in_array( $slug, $this->upgrades_already_recommended ) ) {
				$html .= $this->create_string_for_recommendations( $slug );
				$this->upgrades_already_recommended[] = $slug;
			}
		}

		return $html;
	}

	/**
	 * Fetched installed and active version info.
	 *
	 * @return array
	 */
	public function get_versions() {
		$version_info = array();

		foreach ( $this->instances as $slug => $instance ) {
			$version_info[ $slug ] = $this->get( $slug , 'version' );
		}

		return $version_info;
	}

	/**
	 * Activate global AJAX hooks to provide hooks/filters that may not run in AJAX.
	 */
	public function activate_global_ajax_hooks() {
		foreach ( $this->instances as $slug => $instance ) {
			if ( ! property_exists( $instance , 'ajax_class_name' ) ) continue;
			$ajax_class = $instance->ajax_class_name;
			if ( empty( $ajax_class ) ) continue;
			if ( ! class_exists( $ajax_class ) ) continue;
			$ajax_handler = $ajax_class::get_instance();
			if ( method_exists( $ajax_handler , 'add_global_hooks' ) ) {
				$ajax_handler->add_global_hooks();
			}
		}
	}

	/**
	 * Get the most recently installed version, does not have to be active currently.
	 *
	 * @param   string $slug
	 * @return  string
	 */
	public function get_installed_version( $slug ) {
		$plugin = $this->get( $slug );
		$plugin->was_active;  // This forces both active and previously active versions to be set.
		return $plugin->version;
	}

	/**
	 * Return the product URL of the specified registered/active add-on pack.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function get_product_url( $slug ) {

		// Active object, get from meta
		//
		if ( isset( $this->instances[ $slug ] ) && is_object( $this->instances[ $slug ] ) ) {

			// Newer meta interface
			//
			if ( method_exists( $this->instances[ $slug ], 'get_meta' ) ) {
				return $this->create_string_product_link( $this->instances[ $slug ]->name, $this->instances[ $slug ]->get_meta( 'PluginURI' ) );
			}

			// Older meta interface
			// Remove after all plugins are updated to have get_meta()
			//
			return $this->create_string_product_link( $this->instances[ $slug ]->name, $this->instances[ $slug ]->metadata['PluginURI'] );
		}

		// Manually registered in available array, link exists.
		//
		if ( isset( $this->available[ $slug ]['link'] ) ) {
			return $this->available[ $slug ]['link'];
		}
		if ( isset( $this->available[ $slug ] ) ) {
			switch ( $slug ) {
				case 'slp-experience':
					return $this->create_string_product_link( $this->available[ $slug ]['name'], 'experience' );

				case 'slp-power':
					return $this->create_string_product_link( $this->available[ $slug ]['name'], 'power' );

				case 'slp-premier':
					return $this->create_string_product_link( $this->available[ $slug ]['name'], 'premier_subscription' );


				// TODO: Remove these defunct legacy products. Needs to come out of SME, GFI , EML
				//
				case 'slp-pro' :
					return $this->create_string_product_link( $this->available[ $slug ]['name'], 'slp4-pro' );
			}

		}

		return '';
	}

	/**
	 * Get the add on (SLP_AddOn object)
	 *
	 * @param string        $slug
	 * @param string|null   $property   if provided will try to get this property
	 *
	 * @return mixed                    Returns the AddOn object if no property is specified, otherwise it returns the property value
	 */
	public function get( $slug , $property = null ) {
		if ( ! array_key_exists( $slug , $this->list ) ) {
			$args[ 'slug' ] = $slug;
			if ( ! empty( $this->instances[ $slug ] ) ) {
				$args[ 'instance' ] = $this->instances[ $slug ];
			}
			$this->list[ $slug ] = new SLP_AddOn( $args );
		}
		if ( is_null( $property ) ) {
			return $this->list[ $slug ];
		} else {
			return $this->list[ $slug ]->$property;
		}
	}

	/**
	 * Returns true if an add on, specified by its slug, is active.
	 *
	 * TODO: remove when add ons use slplus->AddOns->get( <slug> , 'active' ) ( CEX , GFI )
	 *
	 * @deprecated 4.7.6 use slplus->AddOns->get( <slug> , 'active' )
	 * @param string $slug
	 *
	 * @return boolean
	 */
	public function is_active( $slug ) {
		$plugin = $this->get( $slug );
		return $plugin->active;
	}

	/**
	 * Add a sanctioned add on pack to the available add ons array.
	 *
	 * @param string              $slug
	 * @param string              $name
	 * @param boolean             $active
	 *
	 * @param SLP_BaseClass_Addon $instance
	 */
	private function make_available( $slug, $name, $active = false, $instance = null ) {
		if (
			! isset( $this->available[ $slug ] ) ||
			is_null( $this->available[ $slug ]['addon'] ) && ! is_null( $instance )
		) {

			$this->available[ $slug ] = array(
				'name'   => $name,
				'active' => $active,
				'addon'  => $instance
			);
		}
	}

	/**
	 * Network notice.
	 *
	 */
	public function network_notice() {
		if ( $this->network_notified ) {  // already notified
			return;
		}
		$this->network_notified = true;

		if ( ! is_multisite() ) { // not multisite
			return;
		}

		if ( empty( $this->instances ) ) { // no premium add ons
			return;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {    // not network active
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( ! is_plugin_active_for_network( $this->slplus->slug ) ) {
			return;
		}

		if ( $this->na_multisite_premier_license_is_valid() ) {  // premier license valid
			return;
		}

		SLP_Admin_Helper::get_instance()->add_wp_admin_notification(
			__( 'Please purchase a <a href="https://wordpress.storelocatorplus.com/product/premier-subscription/" target="store_locator_plus">Store Locator Plus Premier Subscription</a> to license your add ons for a multisite installation. ', 'store-locator-le' )
		);
	}

	/**
	 * Check Premier License for NA Multisite
	 *
	 * Use transients so we only check once per week.
	 */
	private function na_multisite_premier_license_is_valid() {

		// Set boolean for mainsite on network.
		//
		$main_id  = get_main_network_id();
		$blog_id  = get_current_blog_id();
		$mainsite = ( $blog_id === $main_id );

		// If mainsite, make sure we are not changing the SID/UID.
		//
		if ( $mainsite ) {
			$this->saving_new_subscription();
		} else {
			switch_to_blog( $main_id );
		}

		$temp_nojs = $this->slplus->WPOption_Manager->get_wp_option( 'nojs' );
		$uid = $temp_nojs['premium_user_id'];
		$sid = $temp_nojs['premium_subscription_id'];

		if ( empty( $uid ) || empty( $sid ) ) {
			if ( ! $mainsite ) restore_current_blog();
			return false;
		}

		$license_status = get_transient( 'slp-multisite-license-status' );

		if ( $license_status === false ) {
			$license_status = $this->get_premier_license_status( $uid, $sid );
			set_transient( 'slp-multisite-license-status', $license_status, WEEK_IN_SECONDS );
		}

		if ( ! $mainsite ) restore_current_blog();

		return ( $license_status === 'valid' );
	}

	/**
	 * Get Premier License Status
	 *
	 * Use transients so we only check once per week.
	 *
	 * @params string   $uid
	 * @params string   $sid
	 *
	 * @return string
	 */
	private function get_premier_license_status( $uid = null, $sid = null ) {
		if ( defined( 'DOMAIN_CURRENT_SITE' ) ) {
			if ( ( DOMAIN_CURRENT_SITE === 'dashboard.storelocatorplus.com' ) ||
			     ( DOMAIN_CURRENT_SITE === 'dashbeta.storelocatorplus.com'  ) ||
			     ( DOMAIN_CURRENT_SITE === 'dashboard.test'        ) ||
			     ( DOMAIN_CURRENT_SITE === 'demo.storelocatorplus.com'      )
			) return 'valid';
		}

		if ( is_multisite() ) {
			switch_to_blog( defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1 );
			$opts = $this->slplus->WPOption_Manager->get_wp_option( 'nojs' );
			$uid  = $opts[ 'premium_user_id' ];
			$sid  = $opts[ 'premium_subscription_id' ];

		} else {
			if ( ! $this->slplus->SmartOptions->has_been_setup ) {
				// Newly saved settings
				if ( isset( $_POST[ 'options_nojs' ][ 'premium_user_id' ] ) && isset( $_POST[ 'options_nojs' ][ 'premium_subscription_id' ] ) && ! empty( $_POST[ '_wpnonce' ] ) && ( $_POST[ 'action' ] === 'update' ) ) {
					$uid = (int) $_POST[ 'options_nojs' ][ 'premium_user_id' ];
					$sid = $_POST[ 'options_nojs' ][ 'premium_subscription_id' ];

					// Pre-existing settings
				} else {
					$opts = $this->slplus->WPOption_Manager->get_wp_option( 'nojs' );
					$uid  = $opts[ 'premium_user_id' ];
					$sid  = $opts[ 'premium_subscription_id' ];
				}
			}
		}

		if ( empty( $uid ) ) {
			$uid = $this->slplus->options_nojs['premium_user_id'];
		}
		if ( empty( $sid ) ) {
			$sid = $this->slplus->options_nojs['premium_subscription_id'];
		}

		if ( ! $this->valid_license_format( $uid, $sid ) ) {
		    $this->premier_subscription_error = new WP_Error( 'invalid_premier_license' , sprintf( __( 'License for user %s subscripton %s is not in a valid format.' , 'store-locator-le') , $uid , $sid ) );
			if ( is_multisite() ) restore_current_blog();
			return 'invalid';
		}

        $license_status = get_transient( 'slp-premier-subscription-status' );

		if ( $license_status !== 'valid' ) {
			$url = $this->wpdk_url . "license/validate/slp-premier/{$uid}/{$sid}";

			$request = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => '15', ) );
			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
				$license_status = preg_replace( '/\W/', '', $request['body'] );
				if ( $license_status === 'valid' ) {
                    $this->premier_subscription_error = null;
                } else {
                    $this->premier_subscription_error = new WP_Error( 'expired_premier_license' , sprintf( __( 'The license for user %s subscripton %s has expired.' , 'store-locator-le') , $uid , $sid ) );
                }
            } else {
				$license_status = 'invalid';
                $this->premier_subscription_error = new WP_Error( 'offline_premier_license' , __( 'The license server did not respond as expected.' , 'store-locator-le')  );
			}
			set_transient( 'slp-premier-subscription-status', $license_status, defined( 'MYSLP_VERSION' ) ? YEAR_IN_SECONDS : DAY_IN_SECONDS );
		}

		if ( is_multisite() ) restore_current_blog();
		return $license_status;
	}

	/**
	 * Is the license format valid.
	 *
	 * @return bool
	 */
	private function valid_license_format( $uid, $sid ) {
		if ( empty( $uid ) || empty( $sid ) ) {
			return false;
		}

		if ( preg_match( '/^(\d+)$/', $uid ) !== 1 ) {
			return false;
		}

		if ( preg_match( '/^(\d+)_(\d+)$/', $sid ) !== 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Is the premier subscription valid?
	 *
	 * @return bool
	 */
	public function is_premier_subscription_valid() {
		if ( ! isset( $this->premier_subscription_valid ) ) $this->premier_subscription_valid = ( $this->get_premier_license_status() === 'valid' );
		return $this->premier_subscription_valid;
	}

	/**
	 * Recommend an add-on for upgrading a legacy plugin.
	 *
	 * @param    string $slug
	 *
	 * @return    string    the slug of the add-on to upgrade to.
	 */
	public function recommend_upgrade( $slug ) {
		if ( empty( $this->upgrade_paths ) ) {
			$this->set_upgrade_paths();
		}

		return ( array_key_exists( $slug, $this->upgrade_paths ) ? $this->upgrade_paths[ $slug ] : $slug );
	}

	/**
	 * Register an add on object to the manager class.
	 *
	 * @param string              $slug
	 * @param SLP_BaseClass_Addon $object
	 */
	public function register( $slug, $object ) {
		if ( ! is_object( $object ) ) {
			return;
		}

		if ( property_exists( $object, 'short_slug' ) ) {
			$short_slug = $object->short_slug;
		} else {
			$slug_parts = explode( '/', $slug );
			$short_slug = str_replace( '.php', '', $slug_parts[ count( $slug_parts ) - 1 ] );
		}

		if ( ! isset( $this->instances[ $short_slug ] ) || is_null( $this->instances[ $short_slug ] ) ) {
			$this->instances[ $short_slug ] = $object;
			$this->make_available( $short_slug, $object->name, true, $object );
			$this->network_notice();
		}
	}

	/**
	 * Check to make sure we are not saving a new subscription first.
	 */
	private function saving_new_subscription() {
		if ( ! empty( $_POST ) && ! empty( $_REQUEST ) && ! empty( $this->slplus->clean[ 'action' ] ) && ( $this->slplus->clean[ 'action' ] === 'update' ) &&
		     ! empty( $this->slplus->clean[ 'page' ] ) && ( ( $this->slplus->clean[ 'page' ] === 'slp_general' ) || ( $this->slplus->clean[ 'page' ] === 'slp-network-admin' ) )
		) {
			$pre_sid = $this->slplus->options_nojs['premium_user_id'] . $this->slplus->options_nojs['premium_subscription_id'];

			$post_sid = $_POST['options_nojs']['premium_user_id'] . $_POST['options_nojs']['premium_subscription_id'];

			// Premier UID or SID changed.
			if ( $pre_sid !== $post_sid ) {
				$this->slplus->options_nojs['premium_user_id']         = $_POST['options_nojs']['premium_user_id'];
				$this->slplus->options_nojs['premium_subscription_id'] = $_POST['options_nojs']['premium_subscription_id'];
				delete_transient( 'slp-multisite-license-status' );
			}
		}
	}

	/**
	 * Set the add-on upgrade paths.
	 */
	private function set_upgrade_paths() {
		$this->upgrade_paths['slp-enhanced-map']     = 'slp-experience';
		$this->upgrade_paths['slp-enhanced-results'] = 'slp-experience';
		$this->upgrade_paths['slp-enhanced-search']  = 'slp-experience';
		$this->upgrade_paths['slp-widget']           = 'slp-experience';
	}

	/**
	 * Was the specified SLP add on ever active?
	 *
	 * @param string $slug  the add on slug
	 *
	 * @return bool         true if the add on appears to have ever been active
	 */
	public function was_active( $slug ) {
		$plugin = $this->get( $slug );
		return $plugin->was_active;
	}
}
	