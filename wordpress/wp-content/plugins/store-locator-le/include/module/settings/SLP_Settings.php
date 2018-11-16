<?php

if ( ! class_exists( 'SLP_Settings' ) ) {
	/** @noinspection PhpIncludeInspection */
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php' );
	/** @noinspection PhpIncludeInspection */
	require_once( SLPLUS_PLUGINDIR . 'include/module/settings/SLP_Settings_Section.php' );

	/**
	 * The UI and management interface for a page full of settings for a WordPress admin page.
	 *
	 * @property        string                 $current_admin_page
	 * @property        string                 $form_action             what action to take with the form.
	 * @property        string                 $form_enctype            form encryption type  default: '' , often 'multipart/form-data'
	 * @property        string                 $form_name
	 * @property        string                 $name
	 * @property        string                 $save_text               optional: The text for the save button. (default: blank, don't show save button)
	 * @property        SLP_Settings_Section[] $sections
	 * @var             boolean                $show_help_sidebar
	 *
	 * @property        string[]               $known_classes           named array, [ 'key' ] is an item type, => <value> is the FQ filename of the interface definition.
	 *
	 */
	class SLP_Settings extends SLPlus_BaseClass_Object {
		public $current_admin_page;
		public $form_action       = 'options.php';
		public $form_enctype      = '';
		public $form_name         = '';
		public $name              = '';
		public $save_text         = '';
		public $sections;
		public $show_help_sidebar = true;

		public $known_classes          = array();

		/**
		 * Set some pre-load defaults.
		 *
		 * @param array $options
		 */
		function __construct( $options = array() ) {
			$this->save_text          = __( 'Save' , 'store-locator-le' );
			$this->name               = SLPLUS_NAME;
			$this->current_admin_page = ! empty( $_GET[ 'page' ] ) ? plugin_basename( stripslashes( $_GET[ 'page' ] ) ) : '';
			parent::__construct( $options );

		}

		/**
		 * Create a settings page panel.
		 *
		 * Does not render the panel, it simply creates the container to add stuff to for later rendering.
		 *
		 * @param array $params named array of the section properties, name is required.
		 */
		function add_section( $params ) {

			// Not an array?  Assume it is a slug.
			if ( ! is_array( $params ) ) {
				$params = array( 'slug' => $params );
			}

			if ( ! isset( $params[ 'name' ] ) ) {
				if ( ! empty( $params[ 'section_slug' ] ) ) {
					$params[ 'slug' ] = $params[ 'section_slug' ];
				}
				if ( ! isset( $params[ 'slug' ] ) ) {
					return;
				} else {
					$params[ 'name' ] = $this->slplus->Text->get_text_string( array(
						                                                          'settings_section' ,
						                                                          $params[ 'slug' ] ,
					                                                          ) );
				}
			}

			$params[ 'slug' ] = isset( $params[ 'slug' ] ) ? $params[ 'slug' ] : $this->set_section_slug( $params[ 'name' ] );

			if ( empty( $params[ 'slug' ] ) ) {
				return;
			}

			if ( ! $this->has_section( $params[ 'slug' ] ) ) {
				$params[ 'SLP_Settings' ]            = $this;
				$this->sections[ $params[ 'slug' ] ] = new SLP_Settings_Section( $params );
			}

			$this->add_smart_options_to_section( $params[ 'slug' ] );
		}

		/**
		 * Add smart option groups and settings to this section.
		 *
		 * @param string $section the section slug
		 */
		private function add_smart_options_to_section( $section ) {
			if ( empty( $this->slplus->SmartOptions->page_layout[ $this->current_admin_page ] ) ) {
				return;
			}
			if ( empty( $this->slplus->SmartOptions->page_layout[ $this->current_admin_page ][ $section ] ) ) {
				return;
			}

			$group_params[ 'section_slug' ] = $section;
			$group_params[ 'plugin' ]       = $this->slplus;

			foreach ( $this->slplus->SmartOptions->page_layout[ $this->current_admin_page ][ $section ] as $group => $properties ) {
				$group_params[ 'group_slug' ] = $group;
				$this->add_group( $group_params );

				foreach ( $properties as $property ) {
					$this->add_ItemToGroup( $group_params , $property );
				}
			}

		}

		/**
		 * Add a group to the specified section.
		 *
		 * Params requires:
		 *  'section_slug'  => slug for a section to add the group to
		 *  'group_slug'    => slug for this group
		 *
		 * Suggested:
		 *  'group' || 'header' => the text to name the group
		 *
		 * @param $params
		 */
		function add_group( $params ) {
			if ( empty( $params[ 'section_slug' ] ) || empty( $params[ 'group_slug' ] ) ) {
				return;
			}
			if ( ! $this->has_section( $params[ 'section_slug' ] ) ) {
				$this->add_section( $params );
			}
			if ( ! isset( $params[ 'header' ] ) ) {
				$params[ 'header' ] = $this->slplus->Text->get_text_string( array(
					                                                            'settings_group' ,
					                                                            $params[ 'group_slug' ] ,
				                                                            ) );
			}

			$this->sections[ $params[ 'section_slug' ] ]->add_group( $params );
		}

		/**
		 * Add the help sidebar.
		 *
		 * @return string
		 */
		private function add_help_section() {
			if ( ! $this->show_help_sidebar ) {
				return '';
			}

			$this->enqueue_help_script();

			return '<section class="dashboard-aside-secondary">' . '<h3 class="panel-content aside-heading">' . $this->slplus->Text->get_text_string( array(
				                                                                                                                                          'admin' ,
				                                                                                                                                          'help_header' ,
			                                                                                                                                          ) ) . '</h3>' . '<div class="panel-content">' . '<h4 class="description-heading">' . $this->slplus->Text->get_text_string( array(
				                                                                                                                                                                                                                                                                     'admin' ,
				                                                                                                                                                                                                                                                                     'help_subheader' ,
			                                                                                                                                                                                                                                                                     ) ) . '</h4>' . '<div class="settings-description"></div>' . '</div>' . '</section>';
		}

		/**
		 * Same as add_item but uses named params.
		 *
		 * 'type' => textarea, text, checkbox, dropdown, slider, list, submit_button, ..custom..
		 *
		 * NOTE: If use_prefix is false the automatic option saving in SLP 4.2 add-on framework will be disabled.
		 * This can be useful for admin settings you do not want saved/restored between sessions.
		 * It can suck if you do want that to happen though and will likely find this comment after spending
		 * the past 30 minutes tearing your hair out wondering WTF is going on.
		 *
		 *
		 *
		 * @param array $params optional parameters
		 * @param mixed $option if set this is the name of the option to add
		 *
		 * @var 'section'   => string   text for section heading to put the setting in
		 * @var 'label'     => string   text that precedes the input
		 * @var 'setting'   => string   name of the setting (input ID)
		 * @var 'type'      => string   type of interface element ('checkbox'|'dropdown'|'subheader'|'submit'|'textarea')
		 * @var 'show_label'=> boolean  set to true to show the label (default: true)
		 * @var 'custom'        => string   the custom HTML output to render
		 *      'section_slug'  => string   the section slug, if set uses SLP 4.5 defaults.
		 */
		function add_ItemToGroup( $params , $option = null ) {

			// Check OK to add smart option
			if ( ! is_null( $option ) && $this->slplus->SmartOptions->exists( $option ) && ! $this->slplus->SmartOptions->{$option}->add_to_settings_tab ) {
				return;
			}

			// Handle positional parameter passing.
			//
			if ( empty( $params[ 'group_params' ] ) && ! is_null( $option ) ) {
				$params[ 'group_params' ] = $params;
				$params[ 'option' ]       = $option;
			}

			// Use new SLP 4.4.28+ Defaults
			if ( isset( $params[ 'group_params' ] ) || isset( $params[ 'group_slug' ] ) || isset( $params[ 'section_slug' ] ) || isset( $params[ 'plugin' ] ) || isset( $params[ 'option' ] ) ) {
				$new_defaults = true;

				if ( isset( $params[ 'group_params' ] ) ) {
					if ( isset( $params[ 'group_params' ][ 'section_slug' ] ) ) {
						$params[ 'section_slug' ] = $params[ 'group_params' ][ 'section_slug' ];

						if ( ! $this->has_section( $params[ 'section_slug' ] ) ) {
							$this->add_section( array( 'slug' => $params[ 'section_slug' ] ) );
						}
					}
					if ( isset( $params[ 'group_params' ][ 'group_slug' ] ) ) {
						$params[ 'group_slug' ] = $params[ 'group_params' ][ 'group_slug' ];
					}
					if ( isset( $params[ 'group_params' ][ 'plugin' ] ) ) {
						$params[ 'plugin' ] = $params[ 'group_params' ][ 'plugin' ];
					}

					if ( ! isset( $params[ 'header' ] ) ) {
						if ( isset( $params[ 'group_params' ][ 'header' ] ) ) {
							$params[ 'header' ] = $params[ 'group_params' ][ 'header' ];
						} else {
							if ( isset( $params[ 'group_slug' ] ) ) {
								$params[ 'header' ] = $this->slplus->Text->get_text_string( array( 'settings_group_header' , $params[ 'group_slug' ] ) );
							}
						}
					}
				}

			} else {
				$new_defaults = false;
			}

			if ( ! isset( $params[ 'section_slug' ] ) ) {
				$params[ 'section' ]      = isset( $params[ 'section' ] ) ? $params[ 'section' ] : __( 'Settings' , 'store-locator-le' );
				$params[ 'section_slug' ] = $this->set_section_slug( $params[ 'section' ] );
			}

			if ( ! $this->has_section( $params[ 'section_slug' ] ) ) {
				return;
			}

			$section_slug = $params[ 'section_slug' ];
			unset( $params[ 'section_slug' ] );

			$params = $this->set_item_params( $new_defaults , $params );

			$this->sections[ $section_slug ]->add_item( $params );
		}

		/**
		 * Enqueue help text manager script.
		 */
		private function enqueue_help_script() {
			if ( file_exists( SLPLUS_PLUGINDIR . 'js/admin-settings-help.js' ) ) {
				wp_enqueue_script( 'slp_admin_settings-help' , SLPLUS_PLUGINURL . '/js/admin-settings-help.js' , array() );
			}
		}

		/**
		 * Return true if the section identified by the slug exists.
		 *
		 * @param string $slug
		 *
		 * @return bool
		 */
		public function has_section( $slug ) {
			return ! empty( $this->sections[ $slug ] );
		}

		/**
		 * Put the header div on the top of every settings page.
		 */
		private function render_settings_header() {
			?>
            <div class="dashboard-header-height"><!--empty--></div>
            <header id="dashboard-header" class="dashboard-header">
                <section class="dashboard-navigation">
                    <div class="panel-content">
                        <div class="searchform">
							<?php echo $this->render_settings_header_searchform(); ?>
                        </div>
                        <div class="user-area">
							<?php echo $this->render_settings_header_user_area(); ?>
                        </div>
                    </div>
                </section>
            </header>
			<?php
		}

		/**
		 *  Render the settings area search form.
		 */
		private function render_settings_header_searchform() {
			return '';
		}

		/**
		 *  Render the settings area user area.
		 */
		private function render_settings_header_user_area() {
			$documentation_label = $this->slplus->Text->get_text_string( array( 'admin' , 'Documentation' ) );
			$html                = '<div class="user-support">' . sprintf( "<a href='%s' target='store_locator_plus' title='%s'>" , $this->slplus->Text->get_url( 'slp_docs' ) , $documentation_label ) . '<span class="dashicons dashicons-sos"></span>' . $documentation_label . '</a>' . '</div>';

			return $html;
		}

		/**
		 * Create the HTML for the plugin settings page on the admin panel.
		 */
		function render_settings_page() {
			$selectedNav = $this->slplus->clean[ 'selected_nav_element' ];
            $form_id = ( ( $this->form_name !== '' ) ? "id='{$this->form_name}' " : '' );
			$form_name = ( ( $this->form_name !== '' ) ? "name='{$this->form_name}' " : '' );
			$form_enc = ( ( $this->form_enctype !== '' ) ? "enctype='{$this->form_enctype}' " : '' );
            ?>
            <div class='dashboard-wrapper'>
                <?= $this->render_settings_header() ?>
			    <div class='dashboard-main store-locator-plus'>
                    <section class='dashboard-content'>
                        <div id='wpcsl_container' class='settings_page page_<?= $this->current_admin_page ?>'>
                            <form method='post' action='<?= $this->form_action ?>' <?= $form_id ?> <?= $form_name ?> <?= $form_enc ?> class ='slplus_settings_form'>
                                <input type='hidden' id='selected_nav_element' name='selected_nav_element' value='<?= $selectedNav ?>'>
                                <?php
                                settings_fields( SLPLUS_PREFIX . '-settings' );

                                /**
                                 * Render all top menus first.
                                 * @var SLP_Settings_Section $section
                                 */
                                foreach ( $this->sections as $section ) {
                                    if ( isset( $section->is_topmenu ) && ( $section->is_topmenu ) ) {
                                        $section->display();
                                    }
                                }
                                ?>
                                <div id="main" class="dashboard-content-inner panel-settings">
                                    <?php
                                    // Menu Area
                                    //
                                    $selectedNav = $this->slplus->clean[ 'selected_nav_element' ];
                                    $firstOne    = true;
                                    ?>
                                    <div id="wpcsl-nav" class="sub_navigation">
                                        <ul class="sub-navbar">
                                            <?php
                                            foreach ( $this->sections as $section ) {
                                                if ( $section->auto ) {
                                                    $div_id  = !empty( $section->div_id ) ? $section->div_id : $section->slug;
	                                                $link_id = "wpcsl-option-{$div_id}";
                                                    $firstClass   = ( ( "#{$link_id}" == $selectedNav ) || ( $firstOne && ( $selectedNav == '' ) ) ) ? ' first current open' : '';
                                                    $firstOne     = false;

                                                    print "<li class='top-level general {$firstClass} navbar-item'>" . sprintf( "<a id='%s_sidemenu' class='navbar-link subtab_link' data-slug='%s' href='#%s' title='%s' >%s</a>" , $link_id , $div_id , $link_id , $section->name , $section->name ) . '</li>';
                                                }
                                            }
                                            echo $this->save_button();
                                            ?>
                                        </ul>
                                    </div> <!-- wpcsl-nav -->
                                    <div id="content" class="content js settings-content">
                                        <?php

                                        // Draw each settings section as defined in the plugin config file
                                        //
                                        $firstClass = true;
                                        foreach ( $this->sections as $section ) {
                                            if ( $section->auto ) {
                                                if ( ! empty( $firstClass ) ) {
                                                    $section->first = true;
                                                    $firstClass     = false;
                                                }
                                                $section->display();
                                            }
                                        }

                                        ?>
                                    </div> <!-- settings-content -->
                                </div> <!-- main -->
                            </form>
                        </div> <!-- WPCSL Container SLP settings -->
                    </section> <!-- dashboard content SLP settings -->
			        <?= $this->add_help_section(); ?>
                </div> <!-- dashboard-main SLP settings -->
            </div> <!-- dashboard-wrapper SLP settings -->
			<?php
		}

		/**
		 * Create the save button text.
		 *
		 * Set save_text to '' to prevent the save button.
		 *
		 * @return string
		 */
		private function save_button() {
			if ( empty( $this->save_text ) ) {
				return '';
			}

			return "<li class='top-level general save-button'>" . '<div class="navsave">' . sprintf( '<input type="submit" class="button-primary" value="%s" />' , $this->save_text ) . '</div>' . '</li>';
		}

		/**
		 * Set Add Item To Group Params
		 *
		 * @param boolean $new_defaults If true use the new-style SLP 4.5 defaults
		 * @param array   $params
		 *
		 * @return array
		 */
		private function set_item_params( $new_defaults , $params ) {
			if ( ! empty ( $params[ 'option' ] ) ) {
				if ( property_exists( $this->slplus->SmartOptions , $params[ 'option' ] ) ) {
					return $this->slplus->SmartOptions->get_setting_params( $params );
				}
			}

			if ( $new_defaults ) {
				return $this->set_item_params_new( $params );
			} else {
				return $this->set_item_params_old( $params );
			}
		}

		/**
		 * Set Add Item To Group Params to new defaults.
		 *
		 * use_prefix = false
		 * show_label = true
		 * type       = text
		 * name       = params['setting']
		 *
		 * If 'plugin' param is set also use 'option' param to set the name and value for the setting.
		 *
		 * @param array $params
		 *
		 * @return array
		 */
		private function set_item_params_new( $params ) {
			if ( isset( $params[ 'plugin' ] ) ) {

				if ( isset( $params[ 'plugin' ] ) && isset( $params[ 'option' ] ) ) {
					$params[ 'option_name' ] = isset( $params[ 'option_name' ] ) ? $params[ 'option_name' ] : 'options';
					$params[ 'value' ]       = isset( $params[ 'value' ] ) ? $params[ 'value' ] : $this->get_value_for_setting( $params[ 'plugin' ] , $params[ 'option_name' ] , $params[ 'option' ] );
					$params[ 'selectedVal' ] = $params[ 'value' ];
					$params[ 'setting' ]     = $this->get_option_setting( $params );
					$params[ 'name' ]        = isset( $params[ 'name' ] ) ? $params[ 'name' ] : $params[ 'setting' ];
				} else {
					$params[ 'value' ] = isset( $params[ 'value' ] ) ? $params[ 'value' ] : '';
					if ( isset( $params[ 'setting' ] ) ) {
						$params[ 'name' ] = isset( $params[ 'name' ] ) ? $params[ 'name' ] : $params[ 'setting' ];
					}
				}

				unset( $params[ 'option' ] );
				unset( $params[ 'plugin' ] );
			}

			$params[ 'use_prefix' ] = false;
			$params[ 'show_label' ] = isset( $params[ 'show_label' ] ) ? $params[ 'show_label' ] : true;
			$params[ 'type' ]       = isset( $params[ 'type' ] ) ? $params[ 'type' ] : 'text';

			return $params;
		}

		/**
		 * Get the option setting (name)
		 *
		 * @param array $params
		 *
		 * @return string
		 */
		private function get_option_setting( $params ) {

			// Add On
			if ( isset( $params[ 'plugin' ]->option_name ) ) {
				return $params[ 'plugin' ]->option_name . '[' . $params[ 'option' ] . ']';

				// Base Plugin
			} elseif ( is_a( $params[ 'plugin' ] , 'SLPlus' ) && isset( $params[ 'option_name' ] ) ) {
				if ( $params[ 'option_name' ] === 'smart_option' ) {
					return $this->slplus->SmartOptions->get_option_name( $params[ 'option' ] );
				}

				return $params[ 'option_name' ] . '[' . $params[ 'option' ] . ']';

				// Explicit
			} else {
				return $params[ 'option' ];

			}
		}

		/**
		 * Return a section object.
		 *
		 * @param string $section_slug
		 *
		 * @return string|WP_Error
		 */
		public function get_section( $section_slug ) {
			if ( ! empty( $this->sections[ $section_slug ] ) ) {
				return $this->sections[ $section_slug ];
			}
			return new WP_Error( 'no_section' , __( 'No section' , 'store-locator-le' )  );
		}

		/**
		 * Get the value for a specific add-on option.  If empty use add-on option_defaults.   If still empty use slplus defaults.
		 *
		 * @param   mixed   $plugin      An instantiated SLP Plugin object.
		 * @param   string  $option_name Name of the options property to fetch from (default: 'options')
		 * @param    string $setting     The key name for the setting to retrieve.
		 *
		 * @return    mixed                    The value of the add-on options[<setting>], add-on option_defaults[<setting>], or slplus defaults[<setting>]
		 */
		private function get_value_for_setting( $plugin , $option_name , $setting ) {
			if ( $option_name === 'smart_option' ) {
				return $this->slplus->SmartOptions->$setting->value;
			}

			// Default: add-on options value
			//
			if ( ! empty( $plugin->{$option_name}[ $setting ] ) ) {
				$value = $plugin->{$option_name}[ $setting ];

			} else {

				// First Alternative: add-on option_defaults value.
                // TODO: check this, $value will NEVER be used per the next *if* statement :/
				//
				if ( isset( $plugin->option_defaults ) && isset( $plugin->option_defaults[ $setting ] ) ) {
					$value = $plugin->option_defaults[ $setting ];
				}

				// Second Alternative: slplus defaults value.
				//  TODO: check this, as of 4.7.3 this should be caught above and from here we can set value = ''
				if ( $this->slplus->SmartOptions->exists( $setting ) ) {
					$value = $this->slplus->SmartOptions->{$setting}->default;

				} else {
					$value = '';
				}
			}

			return $value;
		}

		/**
		 * Set Add Item To Group Params to new defaults.
		 *
		 * @param array $params
		 *
		 * @return array
		 */
		private function set_item_params_old( $params ) {
			if ( ! isset( $params[ 'name' ] ) && isset( $params[ 'setting' ] ) ) {

				// use_prefix is on by default
				//
				if ( ! isset( $params[ 'use_prefix' ] ) ) {
					$params[ 'use_prefix' ] = true;
				}

				// Using a prefix? Craft the name with that attached...
				//
				if ( $params[ 'use_prefix' ] ) {

					// If we have a prefix, set the separator to '-' by default
					//
					if ( ! isset( $params[ 'separator' ] ) ) {
						$params[ 'separator' ] = '-';
					}

					$params[ 'name' ] = SLPLUS_PREFIX . $params[ 'separator' ] . $params[ 'setting' ];

					// No prefix?  Use the name without one.
					//
				} else {
					$params[ 'name' ] = $params[ 'setting' ];
				}
			}

			if ( ! isset( $params[ 'show_label' ] ) ) {
				$params[ 'show_label' ] = true;
			}

			$defaultSettingName = '';
			if ( ( $params[ 'show_label' ] && ! isset( $params[ 'label' ] ) ) || ( ! isset( $params[ 'setting' ] ) ) ) {
				$defaultSettingName = wp_generate_password( 8 , false );
			}

			if ( $params[ 'show_label' ] && ! isset( $params[ 'label' ] ) ) {
				$params[ 'label' ] = __( 'Setting ' , 'store-locator-le' ) . $defaultSettingName;
			}

			if ( ! isset( $params[ 'setting' ] ) ) {
				$params[ 'setting' ] = $defaultSettingName;
			}

			if ( ! isset( $params[ 'type' ] ) ) {
				$params[ 'type' ] = 'text';
			}
			if ( ! isset( $params[ 'show_label' ] ) ) {
				$params[ 'show_label' ] = true;
			}

			return $params;
		}

		/**
		 * Set section slug by name if not provided.
		 *
		 * @param $name
		 *
		 * @return string
		 */
		private function set_section_slug( $name ) {
			return strtolower( str_replace( ' ' , '_' , $name ) );
		}
	}

}