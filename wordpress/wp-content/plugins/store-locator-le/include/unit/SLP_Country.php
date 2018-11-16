<?php

if ( ! class_exists('SLP_Country') ) {

	/**
	 * Class SLP_Country
	 *
	 * Contains the map manipulate and other data that drives SLP for each country.
	 *
	 * @package StoreLocatorPlus\Country
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2015 - 2016 Charleston Software Associates, LLC
	 *
	 * @property string $slug 				the slug, a sanitized key version of name
	 * @property string $name 				full text name
	 * @property string $google_domain 		the Google maps domain
	 * @property string $cctld 				the registered cctld
	 * @property string $map_center_lat		the default lat for the center of the country
	 * @property string $map_center_lng		the default lng for the center of the country
	 *
	 * @see SLP_Country https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains#Country_code_top-level_domains
	 *
	 * @since 4.3.00
	 */
	class SLP_Country extends SLPlus_BaseClass_Object {
		public $name;
		public $google_domain;
		public $cctld;
		public $slug;
		public $map_center_lat;
		public $map_center_lng;

		function initialize( ) {
			if ( is_string( $this->name ) ) {
				$this->slug = isset( $this->cctld) ? $this->cctld : sanitize_key( $this->name );
			}
		}

		/**
		 * For sanitize_key filters that FUBAR the sanitize_key method in the constructor.
		 *
		 * This should not be necessary, but some plugins and themes are broken.
		 *
		 * @return string
		 */
		function __toString() {
			return $this->name;
		}
	}

}