<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'SLP_WPOption_Manager' ) ){
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php' );

	/**
	 *  Assist with WP option I/O to add fitlers to delete/get/update wp_options.
	 *
	 * @property        string       $option_slug        Used by the get_wp_option('js') call.
	 * @property        string       $option_nojs_slug   Used by the get_wp_option('nojs') call.
	 */
	class SLP_WPOption_Manager extends SLPlus_BaseClass_Object {
		public  $option_nojs_slug;
		public  $option_slug;

		/**
		 * Delete a smart option.
		 * 
		 * @param string $option        The slug for the option to delete
		 */
		public function delete_smart_option( $option ) {
			if ( ! property_exists( $this->slplus->SmartOptions , $option ) ) {
				if ( array_key_exists( $option , $this->slplus->options ) ) {
					unset( $this->slplus->options[ $option ] );
					$this->update_wp_option( 'js' );
				} elseif ( array_key_exists( $option , $this->slplus->options_nojs ) ) {
					unset( $this->slplus->options_nojs[ $option ] );
					$this->update_wp_option( 'nojs' );
				}
			}
		}

		/**
		 * Delete the Store Locator Plus options from the WordPress options table.
		 *
		 * Default option name is csl-slplus-options per $this->option_slug.
		 *
		 * @param string $which_option 'default' , 'js' , 'nojs' or the option name.
		 *
		 * @return mixed
		 */
		public function delete_wp_option( $which_option = 'default' ) {
			return delete_option( $this->set_the_slug( $which_option, 'delete' ) );
		}

		/**
		 * Fetch the Store Locator Plus options from the WordPress options table.
		 *
		 * Default option name is csl-slplus-options per $this->option_slug.
		 *
		 * @param string $which_option 'default' , 'js' , 'nojs' or the option name.
		 * @param mixed  $default
		 *
		 * @return mixed
		 */
		public function get_wp_option( $which_option = 'default', $default = false ) {
			return get_option( $this->set_the_slug( $which_option, 'get' ), $default );
		}

		/**
		 * Things we do at startup.
		 */
		public function initialize() {
			if ( ! isset( $this->option_slug ) ) {
				$this->option_slug      = SLPLUS_PREFIX . '-options';
				$this->option_nojs_slug = SLPLUS_PREFIX . '-options_nojs';
			}
		}

		/**
		 * Set the option slug.
		 *
		 * @param string $which_option 'js', 'nojs', or the option name
		 * @param string $mode         'get' or 'update'
		 *
		 * @return mixed|null
		 */
		private function set_the_slug( $which_option, $mode ) {
			switch ( $which_option ) {
				case 'default':
				case 'js':
					$slug_to_fetch = $this->option_slug;
					break;
				case 'nojs':
					$slug_to_fetch = $this->option_nojs_slug;
					break;
				default:
					$slug_to_fetch = $which_option;
					break;
			}

			/**
			 * FILTER: slp_option_slug
			 *
			 * @param   string $slug_to_fetch the name of the wp_options table key to fetch with get_option().
			 * @param   string $get_or_update 'get' or 'update
			 *
			 * @return  string      a modified slug
			 */
			return apply_filters( 'slp_option_slug', $slug_to_fetch, $mode );

		}

		/**
		 * Update the WordPress option.
		 *
		 * @param string $which_option  'default' , 'js' , 'nojs' or the option name.
		 * @param mixed  $option_values values to be stored
		 *
		 * @return mixed
		 */
		public function update_wp_option( $which_option = 'default', $option_values = null ) {
			if ( is_null( $option_values ) ) {
				switch ( $which_option ) {
					case 'default':
					case 'js':
						$option_values = $this->slplus->options;
						break;
					case 'nojs':
						$option_values = $this->slplus->options_nojs;
						break;
					default:
						break;
				}
			}

			$option_name = $this->set_the_slug( $which_option, 'update' );

			return update_option( $option_name, $option_values );
		}
	}

	/**
	 * Make use - creates as a singleton attached to slplus->object['WPOption_Manager']
	 *
	 * @var SLPlus $slplus
	 */
	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( new SLP_WPOption_Manager() );
	}
}