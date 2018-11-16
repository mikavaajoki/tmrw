<?php
defined( 'ABSPATH' ) || die();

/**
 * Class SLP_Style
 *
 * The legacy Plugin Style interface  using CSS files with headers.
 * Replaced with the REST API Style Gallery Service in 4.7.3.
 *
 * This is only here to continue to support sites that may have employed custom CSS styles.
 *
 * @var             string  $css_dir                The theme CSS directory, absolute.
 * @property-read   string  $current_slug           The current theme slug.
 * @property-read   mixed[] $themeDetails           A named array containing meta data about the CSS theme.
 *
 * @property-read   array   $addon_settings         Array of style attributes (key) and the addons (inner array) that provide the settings.
 *                  key = <attribute slug> , value = array defined next:
 *                     key = add-on slug, value = admin field name for setting
 */
class SLP_Style extends SLPlus_BaseClass_Object {
	private $css_dir;
	private $current_slug;
	private $themeDetails;
	private $theme_list;

	private $addon_settings = array(
		'bubble' => array(
			'slp-experience'   => 'slp-experience[bubblelayout]',
			'slp-enhanced-map' => 'bubblelayout',
		),

		'layout' => array(
			'slp-experience' => 'slp-experience[layout]',
			'slp-pro'        => 'csl-slplus-layout',
		),

		'results' => array(
			'slp-experience'       => 'slp-experience[resultslayout]',
			'slp-enhanced-results' => 'csl-slplus-ER-options[resultslayout]',
		),

		'results_header' => array(
			'slp-premier' => 'options[results_header]',
		),

		'search' => array(
			'slp-experience'       => 'slp-experience[searchlayout]',
			'slp-enhanced-results' => 'csl-slplus-ES-options[searchlayout]',
		),
	);

	/**
	 * Build an HTML string to show under the theme selection box.
	 *
	 * @return string
	 */
	private function createstring_ThemeDetails() {
		$HTML = "\n<div id='{$this->current_slug}_details' class='theme_details'>";

		// Description
		//
		if ( empty ( $this->themeDetails[ $this->current_slug ]['description'] ) ) {
			$HTML .= __( 'No description has been set for this style.', 'store-locator-le' );
		} else {
			$HTML .= $this->themeDetails[ $this->current_slug ]['description'];
		}

		// Theme Image
		// Show Image
		//
		if ( is_readable( SLPLUS_PLUGINDIR . 'images/plugin_styles/' . $this->current_slug . '.jpg' ) ) {
			$HTML .=
				sprintf( '<span class="style_sample"><img src="%s" alt="%s example" title="%s example"></span>',
					SLPLUS_PLUGINURL . '/images/plugin_styles/' . $this->current_slug . '.jpg',
					$this->current_slug,
					$this->current_slug
				);
		}

		// Add On Packs
		//
		if ( ! empty( $this->themeDetails[ $this->current_slug ]['add-ons'] ) ) {
			$HTML .= $this->create_string_addon_packs();
		}

		$HTML .= '</div>';

		return $HTML;
	}

	/**
	 * Create the output string for add on packs used by themes.
	 *
	 * @return string
	 */
	private function create_string_addon_packs() {
		if ( $this->current_slug === 'a_gallery_style' ) {
			return '';
		}

		$html                = '';
		$contributing_addons = array();
		$wanted_addons       = array();

		// For each attribute we want to set...
		//
		foreach ( $this->themeDetails[ $this->current_slug ] as $style_attribute => $slp_option_value ) {

			// Which add-ons provide this attribute?
			//
			$add_ons_providing_attribute = $this->get_addons_that_provide_attribute( $style_attribute );  // 'addon-slug' => 'setting_field_name'
			if ( is_null( $add_ons_providing_attribute ) ) {
				continue;
			}                                  // None?  Skip this attribute.

			$add_on_slugs          = array_keys( $add_ons_providing_attribute );
			$attribute_provided_by = $this->get_first_active_addon( $add_on_slugs );

			// Nothing is providing this attribute.   List the plugins that are desired.
			//
			if ( is_null( $attribute_provided_by ) ) {
				$wanted_addons = array_unique( array_merge( $wanted_addons, (array) $add_on_slugs[0] ) );

				// Output the settings management field and list on the supported plugin list.
				//
			} else {
				$html .= $this->create_string_addon_layout( $add_ons_providing_attribute[ $attribute_provided_by ], $slp_option_value );
				if ( ! in_array( $attribute_provided_by, $contributing_addons ) ) {
					$contributing_addons[] = $attribute_provided_by;
				}
			}
		}

		if ( ! empty( $wanted_addons ) ) {
			$html .=
				'<span class="add_on_info">' .
				$this->slplus->Text->get_text_string( array( 'admin', 'plugin_style_inactive_addons' ) ) .
				'</span>';
			$html .= $this->create_string_addon_links( $wanted_addons );
		}

		if ( ! empty( $contributing_addons ) ) {
			$html .=
				'<span class="add_on_info">' .
				$this->slplus->Text->get_text_string( array( 'admin', 'plugin_style_active_addons' ) ) .
				'</span>' .
				$this->create_string_addon_links( $contributing_addons );
		}

		return $html;
	}

	/**
	 * Create the string for the theme layouts.
	 *
	 * @param   string $slp_option_name  which option name should be set by this
	 * @param   string $slp_option_value what to set this setting to
	 *
	 * @return  string                      HTML for the hidden settings div used by JavaScript to set the options.
	 */
	private function create_string_addon_layout( $slp_option_name, $slp_option_value ) {
		return "<pre class='theme_option_value hidden' settings_field='{$slp_option_name}'>" . esc_textarea( $slp_option_value ) . '</pre>';
	}

	/**
	 * Get the HTML for a list of add-ons linked to their product pages.
	 *
	 * @param   string[] $add_on_slugs
	 *
	 * @return  string       HTML for links to add-ons.
	 */
	private function create_string_addon_links( $add_on_slugs ) {
		$html = '';
		foreach ( $add_on_slugs as $add_on_slug ) {
			$short_slug = str_replace( 'slp-', '', $add_on_slug );
			if ( ! empty( $short_slug ) ) {
				$html .= '<span class="product_link">' . $this->slplus->Text->get_web_link( 'shop_for_' . $short_slug ) . '</span>';
			}
		}

		return $html;
	}

	/**
	 * Return an array of add-on slugs and field names that provide the given Plugin Style attribute.
	 *
	 * Preferred add-on is listed first.
	 *      key = add-on slug, value = slp option (setting) field name on admin UI.
	 *
	 * Returns null if nothing is known to provide this attribute.
	 *
	 * @param   string $style_attribute A style attribute slug.
	 *
	 * @return  array|null                      A list of add-on slugs the provide the given attribute.
	 */
	private function get_addons_that_provide_attribute( $style_attribute ) {
		if ( isset( $this->addon_settings[ $style_attribute ] ) ) {
			return $this->addon_settings[ $style_attribute ];
		}

		return null;
	}

	/**
	 * Get the first active add-on in a list of addon slugs.
	 *
	 * @param   string[] $addon_slugs
	 *
	 * @return  null | string       the string of the first add-on slug that is active.
	 */
	private function get_first_active_addon( $addon_slugs ) {
		foreach ( $addon_slugs as $addon_slug ) {
			if ( $this->slplus->AddOns->get(  $addon_slug  , 'active' ) ) {
				return $addon_slug;
			}
		}

		return null;
	}

	/**
	 * Create a list of add-on packs that this plugin style works best with.
	 *
	 * @param   array $header_data The CSS stylesheet header info that contains the metadata.
	 *
	 * @return  string                          Comma-separate list of the add-ons this plugin style likes to use.
	 */
	private function get_theme_addons( $header_data ) {
		$add_on_list = array();
		if ( ! empty( $header_data['bubble'] ) ) {
			$add_on_list[] = 'slp-enhanced-map';
		}
		if ( ! empty( $header_data['layout'] ) ) {
			$add_on_list[] = 'slp-pro';
		}
		if ( ! empty( $header_data['results'] ) ) {
			$add_on_list[] = 'slp-enhanced-results';
		}
		if ( ! empty( $header_data['results_header'] ) ) {
			$add_on_list[] = 'slp-premier';
		}
		if ( ! empty( $header_data['search'] ) ) {
			$add_on_list[] = 'slp-enhanced-search';
		}

		// Tagalong
		//
		if (
			( stripos( $header_data['layout'], 'tagalong' ) !== false ) ||
			( stripos( $header_data['results'], 'iconarray' ) !== false ) ||
			( stripos( $header_data['bubble'], 'tagalong' ) !== false ) ||
			( stripos( $header_data['results'], 'tagalong' ) !== false ) ||
			( stripos( $header_data['search'], 'dropdown_with_label="category"' ) !== false )
		) {
			$add_on_list[] = 'slp-tagalong';
		}

		// PRO Additional
		//
		if ( ! in_array( 'slp-pro', $add_on_list ) ) {
			if (
				( stripos( $header_data['results'], 'tags' ) !== false ) ||
				( stripos( $header_data['bubble'], 'tags' ) !== false )
			) {
				$add_on_list[] = 'slp-pro';
			}
		}

		// Search Additional
		//
		if ( ! in_array( 'slp-enhanced-search', $add_on_list ) ) {
			if (
				( stripos( $header_data['search'], 'search_box_title' ) !== false ) ||
				( stripos( $header_data['layout'], 'search_box_title' ) !== false ) ||
				( stripos( $header_data['search'], 'dropdown_with_label="state"' ) !== false ) ||
				( stripos( $header_data['search'], 'dropdown_with_label="city"' ) !== false ) ||
				( stripos( $header_data['search'], 'dropdown_with_label="country"' ) !== false )
			) {
				$add_on_list[] = 'slp-enhanced-search';
			}
		}

		return join( ',', $add_on_list );
	}

	/**
	 * Extract the label & key from a CSS file header.
	 *
	 * @param string $filename - a fully qualified path to a CSS file
	 *
	 * @return mixed - a named array of the data.
	 */
	private function get_theme_info( $filename ) {
		$dataBack = array();
		if ( ! empty( $filename ) ) {
			$all_headers =
				array(
					'description'    => 'description',
					'label'          => 'label',
					'layout'         => 'Pro Pack Locator Layout',
					'bubble'         => 'Enhanced Map Bubble Layout',
					'results'        => 'Enhanced Results Results Layout',
					'results_header' => 'Results Header',
					'search'         => 'Enhanced Search Search Layout',

					'new_bubble' => 'Bubble Layout',
					'new_layout' => 'Locator Layout',
					'new_result' => 'Results Layout',
					'new_search' => 'Search Layout',
				);
			$all_headers = apply_filters( 'slp_plugin_style_headers', $all_headers );
			$dataBack    = get_file_data( $filename, $all_headers );

			if ( ! empty( $dataBack['new_bubble'] ) && empty( $dataBack['bubble'] ) ) {
				$dataBack['bubble'] = $dataBack['new_bubble'];
				unset( $dataBack['new_bubble'] );
			}
			if ( ! empty( $dataBack['new_layout'] ) && empty( $dataBack['layout'] ) ) {
				$dataBack['layout'] = $dataBack['new_layout'];
				unset( $dataBack['new_layout'] );
			}
			if ( ! empty( $dataBack['new_result'] ) && empty( $dataBack['results'] ) ) {
				$dataBack['results'] = $dataBack['new_result'];
				unset( $dataBack['new_result'] );
			}
			if ( ! empty( $dataBack['new_search'] ) && empty( $dataBack['search'] ) ) {
				$dataBack['search'] = $dataBack['new_search'];
				unset( $dataBack['new_search'] );
			}

			$dataBack = $this->set_layout_defaults( $dataBack );

			$path_parts          = pathinfo( $filename );
			$dataBack['file']    = $path_parts['filename'];
			$dataBack['add-ons'] = $this->get_theme_addons( $dataBack );
		}

		return $dataBack;
	}

	/**
	 * Get the list of themes from the options table, adding new entries from the CSS directory if files have been added.
	 *
	 * This looks in the ./css directory for any new files.
	 * If they are newer than options_nojs['themes_last_updated'] then add them to the plugin styles array.
	 *
	 * @return  array       The theme array: key = the label for the drop down, value = the value for the dropdown = base file name (no path, no suffix) : stored in option csl-slplus-theme_array.
	 */
	public function get_theme_list() {
		if ( isset( $this->theme_list ) ) {
			return $this->theme_list;
		}

		if ( ! is_dir( $this->css_dir ) ) {
			$this->slplus->notifications->add_notice(
				2,
				sprintf( __( 'The styles directory:<br/>%s<br/>is missing. ', 'store-locator-le' ), $this->css_dir ) .
				__( 'Create it to enable styles and get rid of this message.', 'store-locator-le' )
			);

			return array();
		}

		$themes_changed = false;

		// Remove from drop down list if style file does not exist in the plugin dir
		//
		$themeArray = get_option( SLPLUS_PREFIX . '-theme_array', array() );
		foreach ( $themeArray as $theme_title => $theme ) {
			if ( empty( $theme_title ) || empty( $theme ) || ! file_exists( $this->css_dir . $theme . '.css' ) ) {
				unset( $themeArray[ $theme_title ] );
				$themes_changed = true;
			}
		}

		// Check for theme files
		//
		$current_theme_date = $this->slplus->options_nojs['themes_last_updated'];
		$new_themes         = array();
		if ( $dh = opendir( $this->css_dir ) ) {
			while ( ( $file = readdir( $dh ) ) !== false ) {
				if ( ! preg_match( '/\.css$/', $file ) ) {
					continue;
				}    // SKIP: File does not have .css suffix
				if ( ! is_readable( $this->css_dir . $file ) ) {
					continue;
				}    // SKIP: Cannot be read
				if ( ! is_file( $this->css_dir . $file ) ) {
					continue;
				}    // SKIP: No a regular file.
				if ( $file == 'admin.css' ) {
					continue;
				} // SKIP: admin.css

				// Get File Info
				//
				$file_mod_time = filemtime( $this->css_dir . $file );
				$newEntry      = $this->get_theme_info( $this->css_dir . $file );

				if ( empty( $newEntry['label'] ) || empty( $newEntry['file'] ) ) {
					continue;
				}

				// This one is not in our plugin style list.
				//
				if ( ! array_key_exists( $newEntry['label'], $themeArray ) ) {
					$new_themes[ $newEntry['label'] ] = $newEntry['file'];

					$themes_changed = true;

					if ( $file_mod_time > $this->slplus->options_nojs['themes_last_updated'] ) {
						$this->slplus->options_nojs['themes_last_updated'] = $file_mod_time;
					}
				}
			}
			closedir( $dh );
		}

		// If we processed a newer theme file, update the timestamp and store the theme details.
		//
		if ( $themes_changed ) {
			if ( $current_theme_date !== $this->slplus->options_nojs['themes_last_updated'] ) {
				$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
			}

			// Remove empties and sort
			$themeArray = array_merge( $themeArray, $new_themes );
			$themeArray = array_filter( $themeArray );
			uksort( $themeArray, 'strcasecmp' );
			update_option( SLPLUS_PREFIX . '-theme_array', $themeArray );
		}

		$this->theme_list = $themeArray;

		return $this->theme_list;
	}

	/**
	 * Create the details divs for the SLP styles.
	 *
	 * @param mixed[] $themeArray
	 *
	 * @return string the div HTML
	 */
	public function setup_ThemeDetails( $themeArray ) {
		$HTML       = '';
		$newDetails = false;

		// Get an array of metadata for each style present.
		//
		$this->themeDetails = get_option( SLPLUS_PREFIX . '-theme_details' );

		// Check all our styles for details
		//
		foreach ( $themeArray as $label => $theme_slug ) {

			// No details? Read from the CSS File.
			//
			if ( ! $this->has_theme_details( $theme_slug ) ) {
				if ( is_readable( $this->css_dir . $theme_slug . '.css' ) ) {
					$themeData = $this->get_theme_info( $this->css_dir . $theme_slug . '.css' );

					if ( ! empty( $themeData['label'] ) ) {
						$themeData['fqfname']              = $this->css_dir . $theme_slug . '.css';
						$this->themeDetails[ $theme_slug ] = $themeData;
						$newDetails                        = true;
					}
				}
			}

			if ( $this->has_theme_details( $theme_slug ) ) {
				$this->current_slug = $theme_slug;
				$HTML .= $this->createstring_ThemeDetails();
			}
		}

		// If we read new details, go save to disk.
		//
		if ( $newDetails ) {
			update_option( SLPLUS_PREFIX . '-theme_details', $this->themeDetails );
		}

		return $HTML;
	}

	/**
	 * Return true if this theme slug has details.
	 *
	 * @param $theme_slug
	 *
	 * @return bool
	 */
	private function has_theme_details( $theme_slug ) {
		return (
			isset( $this->themeDetails[ $theme_slug ] ) &&
			! empty( $this->themeDetails[ $theme_slug ] ) &&
			isset( $this->themeDetails[ $theme_slug ]['label'] ) &&
			! empty( $this->themeDetails[ $theme_slug ]['label'] )
		);
	}

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
		$this->css_dir        = SLPLUS_PLUGINDIR . 'css/';
		$this->addon_settings = apply_filters( 'slp_plugin_style_addon_settings', $this->addon_settings );
	}

	/**
	 * Set layout defaults when not specified in the Plugin Style header.
	 *
	 * @param   array $plugin_style_meta
	 *
	 * @return  array
	 */
	private function set_layout_defaults( $plugin_style_meta ) {
		$layout_settings = array(
			'bubble'  => 'bubblelayout',
			'layout'  => 'layout',
			'results' => 'resultslayout',
			'search'  => 'searchlayout',
		);

		foreach ( $layout_settings as $setting => $default_setting ) {
			if ( empty( $plugin_style_meta[ $setting ] ) ) {
				if ( $this->slplus->SmartOptions->exists( $default_setting ) ) {
					$plugin_style_meta[ $setting ] = $this->slplus->SmartOptions->{$default_setting}->default;
				}
			}
		}

		return $plugin_style_meta;
	}
}
