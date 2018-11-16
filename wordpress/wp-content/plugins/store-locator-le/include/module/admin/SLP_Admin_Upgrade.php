<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Admin_Upgrade
 *
 * Converts settings and moves files around when necessary between SLP version changes.
 */
class SLP_Admin_Upgrade extends SLPlus_BaseClass_Object {

	/**
	 * @var array $legacy_options the named array of legacy options to their new serialized counterpart
	 *
	 * The following metadata rules apply:
	 *
	 * TYPE -
	 * For type = 'nojs' , store the new setting in the SLPlus->options_nojs[] array.
	 * For all other types, store the new setting in SLPlus->options[].
	 *
	 * If the legacy value is set in wp_options, bring that over to the serialized array.
	 *
	 * DEFAULT -
	 * If default is set AND there is no legacy setting in the wp_options table,
	 * set the options|option_nojs array entry to the specified default.
	 *
	 * If default is NOT set AND there is no legacy setting, use the value
	 * set in the SLPlus class.
	 *
	 * CALLBACK -
	 * If callback is set, process the legacy or default value through the specified function.
	 *
	 * $option_name                               = '';
	 * $serial_key                                = '';
	 * $this->slplus->options[ $serial_key ] = $option_value;
	 */
	private $legacy_options = array(
		'csl-slplus-force_load_js'    => array( 'key' => 'force_load_js', 'type' => 'nojs', 'since' => '4.1.03' ),
		'csl-slplus_label_directions' => array( 'key' => 'label_directions', 'since' => '4.3.00' ),
		'label_email'                 => array( 'key' => 'label_email', 'since' => '4.3.00' ),
		'csl-slplus_label_fax'        => array( 'key' => 'label_fax', 'since' => '4.3.00' ),
		'csl-slplus_label_hours'      => array( 'key' => 'label_hours', 'type' => 'nojs', 'since' => '4.3.00' ),
		'csl-slplus_label_phone'      => array( 'key' => 'label_phone', 'since' => '4.3.00' ),
		'csl-slplus_map_center'       => array( 'key' => 'map_center', 'since' => '4.2.67' ),
		'csl-slplus-map_language'     => array( 'key' => 'map_language', 'type' => 'nojs', 'since' => '4.3.00' ),
		'csl-slplus_maxreturned'      => array( 'key'   => 'max_results_returned',
		                                        'type'  => 'nojs',
		                                        'since' => '4.0.033'
		),
		'csl-slplus-no_google_js'     => array( 'key' => 'no_google_js', 'type' => 'nojs', 'since' => '4.4.28' ),
		'sl_distance_unit'            => array( 'key' => 'distance_unit', 'since' => '4.2.04' ),
		'sl_google_map_country'       => array( 'key'      => 'default_country',
		                                        'type'     => 'nojs',
		                                        'callback' => 'sanitize_key',
		                                        'since'    => '4.5-alpha-01'
		),
		'sl_google_map_domain'        => array( 'key' => 'map_domain', 'since' => '4.2.04' ),
		'sl_instruction_message'      => array( 'key' => 'instructions', 'type' => 'nojs', 'since' => '4.3.00' ),
		'sl_load_locations_default'   => array( 'key' => 'immediately_show_locations', 'since' => '4.1.03' ),
		'sl_map_height'               => array( 'key' => 'map_height', 'type' => 'nojs', 'since' => '4.2.67' ),
		'sl_map_height_units'         => array( 'key' => 'map_height_units', 'type' => 'nojs', 'since' => '4.2.67' ),
		'sl_map_end_icon'             => array( 'key' => 'map_end_icon', 'since' => '4.2.67' ),
		'sl_map_home_icon'            => array( 'key' => 'map_home_icon', 'since' => '4.2.67' ),
		'sl_map_radii'                => array( 'key' => 'radii', 'since' => '4.4.00' ),
		'sl_map_type'                 => array( 'key'      => 'map_type',
		                                        'type'     => 'js',
		                                        'callback' => 'fix_map_type',
		                                        'since'    => '4.2.67'
		),
		'sl_map_width'                => array( 'key' => 'map_width', 'type' => 'nojs', 'since' => '4.2.67' ),
		'sl_map_width_units'          => array( 'key' => 'map_width_units', 'type' => 'nojs', 'since' => '4.2.67' ),
		'sl_num_initial_displayed'    => array( 'key'   => 'initial_results_returned',
		                                        'type'  => 'nojs',
		                                        'since' => '4.0.033'
		),
		'sl_radius_label'             => array( 'key' => 'label_radius', 'type' => 'nojs', 'since' => '4.4.31' ),
		'sl_remove_credits'           => array( 'key' => 'remove_credits', 'type' => 'nojs', 'since' => '4.2.67' ),
		'sl_search_label'             => array( 'key' => 'label_search', 'type' => 'nojs', 'since' => '4.4.31' ),
		'sl_website_label'            => array( 'key' => 'label_website', 'since' => '4.4.67' ),
		'sl_zoom_level'               => array( 'key' => 'zoom_level', 'since' => '4.3.00' ),
		'sl_zoom_tweak'               => array( 'key' => 'zoom_tweak', 'since' => '4.3.00' ),
	);

	/**
	 * Convert the legacy settings to the new serialized settings.
	 *
	 */
	private function convert_legacy_settings() {

		foreach ( $this->legacy_options as $legacy_option => $new_option_meta ) {
			$since_version = isset( $new_option_meta['since'] ) ? $new_option_meta['since'] : null;

			// Run the conversion if the current installed SLP version is less then the since version (changed in version) for this option.
			//
			if ( is_null( $since_version ) || ( version_compare( $this->slplus->installed_version, $since_version, '<=' ) ) ) {


				// Get the legacy option
				//
				$option_value = get_option( $legacy_option, null );

				// If there was a legacy option or a default setting override.
				// Set that in the new serialized option string.
				// Otherwise leave it at the default setup in the SLPlus class.
				//
				if ( ! is_null( $option_value ) ) {

					// Callback processing
					//
					if ( isset( $new_option_meta['callback'] ) ) {
						$option_value = call_user_func_array( $new_option_meta['callback'], array( $option_value ) );
					}

					// Set the serialized option
					//
					if ( isset( $new_option_meta['type'] ) && ( $new_option_meta['type'] === 'nojs' ) ) {
						$this->slplus->options_nojs[ $new_option_meta['key'] ] = $option_value;
					} else {
						$this->slplus->options[ $new_option_meta['key'] ] = $option_value;
					}

					// Delete the legacy option
					//
					delete_option( $legacy_option );
				}

			}
		}

	}

	/**
	 * Switch things from NOJS to JS or vice-versa.
	 */
	private function convert_serial_settings() {
		$options_to_move = array(
			'label_email',
			'label_website'
		);
		foreach ( $options_to_move as $key ) {
			if ( isset( $this->slplus->options_nojs[ $key ] ) ) {
				$this->slplus->options[ $key ] = $this->slplus->options_nojs[ $key ];
				unset( $this->slplus->options_nojs[ $key ] );
			}
		}

		$move_from_js_to_nojs = array(
			'hide_search_form',
			'initial_results_returned',
			'maplayout',
			'radii',
			'radius_behavior',
			'searchlayout',
		);
		foreach ( $move_from_js_to_nojs as $key ) {
			if ( isset( $this->slplus->options[ $key ] ) ) {
				$this->slplus->options_nojs[ $key ] = $this->slplus->options[ $key ];
				unset( $this->slplus->options[ $key ] );
			}
		}

		$remove = array(
			'admin_locations_per_page',
			'message_no_api_key',
		);
		foreach ( $remove as $key ) {
			if ( isset( $this->slplus->options[ $key ] ) ) {
				unset( $this->slplus->options[ $key ] );
			}
			if ( isset( $this->slplus->options_nojs[ $key ] ) ) {
				unset( $this->slplus->options_nojs[ $key ] );
			}
		}

	}

	/**
	 * Fix all of the \\\\\ in Smart Options , replace with a single \.
	 *
	 * @uses \SLP_Admin_Upgrade::replace_multiple_backslashes_with_one
	 */
	private function fix_backslashes() {
		if ( ! version_compare( $this->slplus->installed_version, '4.8.8', '<' ) ) {
			return;
		}

		array_walk( $this->slplus->options, array( $this, 'replace_multiple_backslashes_with_one' ) );
		array_walk( $this->slplus->options_nojs, array( $this, 'replace_multiple_backslashes_with_one' ) );
	}

	/**
	 * Fix default country.
	 *
	 * Called from activation class.
	 */
	private function fix_default_country() {
		if ( version_compare( $this->slplus->installed_version, '4.5.02', '<' ) ) {
			$original_country = $this->slplus->options_nojs['default_country'];
			/** @noinspection PhpIncludeInspection */
			require_once( SLPLUS_PLUGINDIR . 'include/module/i18n/SLP_Country_Manager.php' );
			if ( isset( $this->Country_Manager->countries[ $this->slplus->options_nojs['default_country'] ] ) ) {
				return;
			}

			/**
			 * @var SLP_Country $country
			 */
			foreach ( $this->slplus->Country_Manager->countries as $new_slug => $country ) {
				$old_slug = sanitize_key( $country->name );
				if ( $old_slug === $original_country ) {
					$this->slplus->options_nojs['default_country'] = $new_slug;
					break;
				}
			}
		}
	}

	/**
	 * Convert old road map types.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function fix_map_type( $value ) {
		switch ( $value ) {
			case 'G_SATELLITE_MAP':
				return 'satellite';
			case 'G_HYBRID_MAP':
				return 'hybrid';
			case 'G_PHYSICAL_MAP':
				return 'terrain';
			default:
				return 'roadmap';
		}
	}

	/**
	 * Migrate the settings from older releases to their new serialized home.
	 */
	public function migrate_settings() {

		// No longer used
		//
		delete_option( SLPLUS_PREFIX . '_disable_search' );
		delete_option( SLPLUS_PREFIX . '_use_email_form' );
		delete_option( 'sl_use_name_search' );
		delete_option( 'sl_location_table_view' );
		delete_option( 'slplus_broadcast' );
		delete_option( 'sl_admin_locations_per_page' );
		unset( $this->slplus->options_nojs[ 'build_target' ] );


		// Always re-load theme details data.
		//
		delete_option( SLPLUS_PREFIX . '-api_key' );
		delete_option( SLPLUS_PREFIX . '-theme_details' );
		delete_option( SLPLUS_PREFIX . '-theme_array' );
		delete_option( SLPLUS_PREFIX . '-theme_lastupdated' );

		// Migrate singular options to serialized options
		//
		$this->convert_legacy_settings();
		$this->convert_serial_settings();

		// Fix map domain
		//
		if ( $this->slplus->options['map_domain'] === 'maps.googleapis.com' ) {
			$this->slplus->options['map_domain'] = 'maps.google.com';
		}

		// Fix map center
		//
		if ( isset( $this->slplus->options_nojs['map_center'] ) ) {
			if ( empty( $this->slplus->options['map_center'] ) && ! empty ( $this->slplus->options_nojs['map_center'] ) ) {
				$this->slplus->options['map_center'] = $this->slplus->options_nojs['map_center'];
			}
			unset( $this->slplus->options_nojs['map_center'] );
		}

		$this->fix_default_country();
		$this->fix_backslashes();

		// Save Serialized Options
		//
		update_option( SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs );
		update_option( SLPLUS_PREFIX . '-options', $this->slplus->options );
	}

	/**
	 * Replace multiple backslashes in a setting with a single backslash. Array map pass by reference.
	 *
	 * @used-by \SLP_Admin_Upgrade::fix_backslashes
	 *
	 * @param $value
	 *
	 * @param $key
	 */
	public function replace_multiple_backslashes_with_one( &$value, $key ) {
		if ( strpos( $value, '\\\\' ) === false ) {
			return;
		}
		$value = preg_replace( '/\\\\{2,}/', '\\', $value );
		if ( $this->slplus->SmartOptions->exists( $key ) ) {
			$this->slplus->SmartOptions->$key->value = stripslashes( $value );
		}
	}
}

