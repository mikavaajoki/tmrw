<?php
if ( ! class_exists( 'SLP_Admin_Info_Text' ) ) {
	/**
	 * Class SLP_Admin_Info_Text
	 *
	 * Extend text for admin info tab
	 *
	 * @package   StoreLocatorPlus\Admin\Info\Text
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2016 Charleston Software Associates, LLC
	 *
	 * @since     4.7.1
	 *
	 */
	class SLP_Admin_Info_Text {
		private $text_strings;

		/**
		 * SLP_Admin_Settings_Text constructor.
		 */
		public function __construct() {
			$this->initialize();
		}

		/**
		 * Do at the start.
		 */
		private function initialize() {
			$this->set_text_strings();
			add_filter( 'slp_get_text_string', array( $this, 'get_text_string' ), 10, 2 );
		}

		/**
		 * Set the strings we need on the admin panel.
		 *
		 * @param string   $text
		 * @param string[] $slug
		 *
		 * @return string
		 */
		public function get_text_string( $text, $slug ) {
			if ( $slug[0] === 'settings_group_header' ) {
				$slug[0] = 'settings_group';
			}
			if ( isset( $this->text_strings[ $slug[0] ][ $slug[1] ] ) ) {
				return $this->text_strings[ $slug[0] ][ $slug[1] ];
			}

			return $text;
		}

		/**
		 * Set our text strings.
		 */
		private function set_text_strings() {
			if ( isset( $this->text_strings ) ) {
				return;
			}
			$this->text_strings['settings_section']['how_to_use'        ] = __( 'How To Use', 'store-locator-le' );
			$this->text_strings['settings_section']['plugin_environment'] = __( 'Plugin Environment', 'store-locator-le' );

			$this->text_strings['settings_group']['details'] = __( 'Details', 'store-locator-le' );
		}
	}
}