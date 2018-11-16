<?php
if ( ! class_exists( 'SLP_Country_Manager' ) ) {

	/**
	 * Class SLP_Country
	 *
	 * Contains the map manipulate and other data that drives SLP for each country.
	 *
	 * @property    SLP_Country[] $countries      the index key is the slug for the country.
	 */
	class SLP_Country_Manager extends SLPlus_BaseClass_Object {
		public $countries;

		/**
		 * Create a Country Manager and make sure we have access to the country object.
		 */
		function initialize() {
			require_once( SLPLUS_PLUGINDIR . 'include/unit/SLP_Country.php' );
			$this->load_country_data();
		}

		/**
		 * Load up the countries supported by Store Locator Plus.
		 */
		public function load_country_data() {
			if ( ! empty( $this->countries ) ) {
				return;
			}

			/**
			 * @var SLP_Country $new_country
			 */
			$new_country                           = new SLP_Country( array(
				'name'           => __( 'United States', 'store-locator-le' ),
				'google_domain'  => 'maps.google.com',
				'cctld'          => 'us',
				'map_center_lat' => '37.09024',
				'map_center_lng' => '-95.712891',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Algeria', 'store-locator-le' ),
				'google_domain'  => 'maps.google.dz',
				'cctld'          => 'dz',
				'map_center_lat' => '28.02505',
				'map_center_lng' => '1.64535',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'American Samoa', 'store-locator-le' ),
				'google_domain'  => 'maps.google.as',
				'cctld'          => 'as',
				'map_center_lat' => '-12.716089',
				'map_center_lng' => '-170.253983',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Andorra', 'store-locator-le' ),
				'google_domain' => 'maps.google.ad',
				'cctld'         => 'ad',
				'map_center_lat' => '42.541816',
				'map_center_lng' => '1.597564',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Angola', 'store-locator-le' ),
				'google_domain'  => 'maps.google.co.ao',
				'cctld'          => 'ao',
				'map_center_lat' => '-11.20269',
				'map_center_lng' => '17.873887',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Antigua and Barbuda', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.ag',
				'cctld'         => 'ag',
				'map_center_lat' => '17.328304',
				'map_center_lng' => '-62.005747',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Argentina', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.ar',
				'cctld'         => 'ar',
				'map_center_lat' => '-38.341656',
				'map_center_lng' => '-63.28125',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Australia', 'store-locator-le' ),
				'google_domain'  => 'maps.google.com.au',
				'cctld'          => 'au',
				'map_center_lat' => '-25.274398',
				'map_center_lng' => '133.775136',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Austria', 'store-locator-le' ),
				'google_domain' => 'maps.google.at',
				'cctld'         => 'at',
				'map_center_lat' => '47.635784',
				'map_center_lng' => '13.590088',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Bahamas', 'store-locator-le' ),
				'google_domain' => 'maps.google.bs',
				'cctld'         => 'bs',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Bahrain', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.bh',
				'cctld'         => 'bh',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Bandladesh', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.bd',
				'cctld'         => 'bd',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Belgium', 'store-locator-le' ),
				'google_domain' => 'maps.google.be',
				'cctld'         => 'be',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Belize', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.bz',
				'cctld'         => 'bz',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Benin', 'store-locator-le' ),
				'google_domain' => 'maps.google.bj',
				'cctld'         => 'bj',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Bolivia', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.bo',
				'cctld'         => 'bo',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Botswana', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.bw',
				'cctld'         => 'bw',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Brazil', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.br',
				'cctld'         => 'br',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Bulgaria', 'store-locator-le' ),
				'google_domain' => 'maps.google.bg',
				'cctld'         => 'bg',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Burundi', 'store-locator-le' ),
				'google_domain' => 'maps.google.bi',
				'cctld'         => 'bi',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Cameroon', 'store-locator-le' ),
				'google_domain' => 'maps.google.cm',
				'cctld'         => 'cm',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Canada', 'store-locator-le' ),
				'google_domain' => 'maps.google.ca',
				'cctld'         => 'ca',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Central African Republic', 'store-locator-le' ),
				'google_domain' => 'maps.google.cf',
				'cctld'         => 'cf',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Chile', 'store-locator-le' ),
				'google_domain' => 'maps.google.cl',
				'cctld'         => 'cl',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'China', 'store-locator-le' ),
				'google_domain' => 'ditu.google.com',
				'cctld'         => 'cn',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Congo', 'store-locator-le' ),
				'google_domain' => 'maps.google.cg',
				'cctld'         => 'cg',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Czech Republic', 'store-locator-le' ),
				'google_domain' => 'maps.google.cz',
				'cctld'         => 'cz',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Democratic Republic of Congo', 'store-locator-le' ),
				'google_domain' => 'maps.google.cd',
				'cctld'         => 'cd',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Denmark', 'store-locator-le' ),
				'google_domain' => 'maps.google.dk',
				'cctld'         => 'dk',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Djibouti', 'store-locator-le' ),
				'google_domain' => 'maps.google.dj',
				'cctld'         => 'dj',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Ecuador', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.ec',
				'cctld'         => 'ec',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Estonia', 'store-locator-le' ),
				'google_domain' => 'maps.google.ee',
				'cctld'         => 'ee',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Ethiopia', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.et',
				'cctld'         => 'et',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Finland', 'store-locator-le' ),
				'google_domain' => 'maps.google.fi',
				'cctld'         => 'fi',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'France', 'store-locator-le' ),
				'google_domain'  => 'maps.google.fr',
				'cctld'          => 'fr',
				'map_center_lat' => '42.227638',
				'map_center_lng' => '2.213749',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Gabon', 'store-locator-le' ),
				'google_domain' => 'maps.google.ga',
				'cctld'         => 'ga',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Gambia', 'store-locator-le' ),
				'google_domain' => 'maps.google.gm',
				'cctld'         => 'gm',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Germany', 'store-locator-le' ),
				'google_domain'  => 'maps.google.de',
				'cctld'          => 'de',
				'map_center_lat' => '51.165691',
				'map_center_lng' => '10.451526',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Ghana', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.gh',
				'cctld'         => 'gh',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Greece', 'store-locator-le' ),
				'google_domain' => 'maps.google.gr',
				'cctld'         => 'gr',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Guatemala', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.gt',
				'cctld'         => 'gt',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Guyana', 'store-locator-le' ),
				'google_domain' => 'maps.google.gy',
				'cctld'         => 'gy',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Hong Kong', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.hk',
				'cctld'         => 'hk',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Hungary', 'store-locator-le' ),
				'google_domain' => 'maps.google.hu',
				'cctld'         => 'hu',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'India', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.in',
				'cctld'         => 'in',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Indonesia', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.id',
				'cctld'         => 'id',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Israel', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.il',
				'cctld'         => 'il',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Italy', 'store-locator-le' ),
				'google_domain' => 'maps.google.it',
				'cctld'         => 'it',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Japan', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.jp',
				'cctld'         => 'jp',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Kenya', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.ke',
				'cctld'         => 'ke',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Lesotho', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.ls',
				'cctld'         => 'ls',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Liechtenstein', 'store-locator-le' ),
				'google_domain' => 'maps.google.li',
				'cctld'         => 'li',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Lithuania', 'store-locator-le' ),
				'google_domain' => 'maps.google.lt',
				'cctld'         => 'lt',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Macedonia', 'store-locator-le' ),
				'google_domain' => 'maps.google.mk',
				'cctld'         => 'mk',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Madagascar', 'store-locator-le' ),
				'google_domain' => 'maps.google.mg',
				'cctld'         => 'mg',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Malawi', 'store-locator-le' ),
				'google_domain' => 'maps.google.mw',
				'cctld'         => 'mw',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Malaysia', 'store-locator-le' ),
				'google_domain' => 'maps.google.my',
				'cctld'         => 'my',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Mauritius', 'store-locator-le' ),
				'google_domain' => 'maps.google.mu',
				'cctld'         => 'mu',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Mexico', 'store-locator-le' ),
				'google_domain' => 'maps.google.mx',
				'cctld'         => 'mx',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Mozambique', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.mz',
				'cctld'         => 'mz',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Namibia', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.na',
				'cctld'         => 'na',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Netherlands', 'store-locator-le' ),
				'google_domain'  => 'maps.google.nl',
				'cctld'          => 'nl',
				'map_center_lat' => '52.132633',
				'map_center_lng' => '5.291266',

			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'New Zealand', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.nz',
				'cctld'         => 'nz',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Nigeria', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.ng',
				'cctld'         => 'ng',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Norway', 'store-locator-le' ),
				'google_domain' => 'maps.google.no',
				'cctld'         => 'no',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Paraguay', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.py',
				'cctld'         => 'py',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Peru', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.pe',
				'cctld'         => 'pe',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Philippines', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.ph',
				'cctld'         => 'ph',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Poland', 'store-locator-le' ),
				'google_domain' => 'maps.google.pl',
				'cctld'         => 'pl',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Portugal', 'store-locator-le' ),
				'google_domain' => 'maps.google.pt',
				'cctld'         => 'pt',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Republic of Ireland', 'store-locator-le' ),
				'google_domain' => 'maps.google.ie',
				'cctld'         => 'ie',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Romania', 'store-locator-le' ),
				'google_domain' => 'maps.google.ro',
				'cctld'         => 'ro',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Russia', 'store-locator-le' ),
				'google_domain' => 'maps.google.ru',
				'cctld'         => 'ru',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Rwanda', 'store-locator-le' ),
				'google_domain' => 'maps.google.rw',
				'cctld'         => 'rw',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Sao Tome and Principe', 'store-locator-le' ),
				'google_domain' => 'maps.google.st',
				'cctld'         => 'st',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Senegal', 'store-locator-le' ),
				'google_domain' => 'maps.google.sn',
				'cctld'         => 'sn',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Seychelles', 'store-locator-le' ),
				'google_domain' => 'maps.google.sc',
				'cctld'         => 'sc',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Serbia', 'store-locator-le' ),
				'google_domain'  => 'maps.google.rs',
				'cctld'          => 'rs',
				'map_center_lat' => '44.025419',
				'map_center_lng' => '20.923339',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Sierra Leone', 'store-locator-le' ),
				'google_domain' => 'maps.google.sl',
				'cctld'         => 'sl',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Singapore', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.sg',
				'cctld'         => 'sg',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'South Africa', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.za',
				'cctld'         => 'za',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'South Korea', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.kr',
				'cctld'         => 'kr',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Spain', 'store-locator-le' ),
				'google_domain' => 'maps.google.es',
				'cctld'         => 'es',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Sri Lanka', 'store-locator-le' ),
				'google_domain' => 'maps.google.lk',
				'cctld'         => 'lk',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'           => __( 'Sweden', 'store-locator-le' ),
				'google_domain'  => 'maps.google.se',
				'cctld'          => 'se',
				'map_center_lat' => '60.128161',
				'map_center_lng' => '18.653501',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Switzerland', 'store-locator-le' ),
				'google_domain' => 'maps.google.ch',
				'cctld'         => 'ch',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Taiwan', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.tw',
				'cctld'         => 'tw',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Tanzania', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.tz',
				'cctld'         => 'tz',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Thailand', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.th',
				'cctld'         => 'th',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Togo', 'store-locator-le' ),
				'google_domain' => 'maps.google.tg',
				'cctld'         => 'tg',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Uganda', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.ug',
				'cctld'         => 'ug',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'United Arab Emirates', 'store-locator-le' ),
				'google_domain' => 'maps.google.ae',
				'cctld'         => 'ae',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'United Kingdom', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.uk',
				'cctld'         => 'uk',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Uruguay', 'store-locator-le' ),
				'google_domain' => 'maps.google.com.uy',
				'cctld'         => 'uy',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Venezuela', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.ve',
				'cctld'         => 've',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Zambia', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.zm',
				'cctld'         => 'zm',
			) );
			$this->countries[ $new_country->slug ] = $new_country;

			$new_country                           = new SLP_Country( array(
				'name'          => __( 'Zimbabwe', 'store-locator-le' ),
				'google_domain' => 'maps.google.co.zw',
				'cctld'         => 'zw',
			) );
			$this->countries[ $new_country->slug ] = $new_country;
		}
	}

	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( new SLP_Country_Manager() );
	}
}
