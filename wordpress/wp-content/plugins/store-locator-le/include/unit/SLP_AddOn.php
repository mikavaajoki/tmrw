<?php

if ( ! class_exists('SLP_AddOn') ) {

	/**
	 * Details about an Add On
	 *
	 * @property-read    boolean                $active         is this add on active now?
	 * @propery-read     stdClass               $cache          a cached (transient) copy of some key properties
	 * @property         SLP_BaseClass_Addon    $instance 		the instantiated add on object
	 * @property         string                 $latest_version get the latest version available from the update server.
	 * @property         string                 $name 			full text name
	 * @property         string                 $slug 			the slug, shorthand such as slp-premier
	 * @property-read    string                 $version        the version of an ACTIVE add on
	 * @property-read    boolean                $was_active     true if this add on was ever active on this site
	 */
	class SLP_AddOn extends SLPlus_BaseClass_Object {
		const TRANSIENT_BASE = 'slp_addon_';

		public $instance;
		public $slug;

		private $active;
		private $cache;
		private $latest_version;
		private $name;
		private $version;
		private $was_active;

		/**
		 * Get a property.
		 *
		 * @uses _get_active, _get_cache, _get_latest_version, _get_name, _get_version, _get_was_active
		 *
		 * @param mixed $property
		 * @return mixed
		 */
		function __get( $property ) {
			if ( property_exists( $this , $property ) ) {
				if ( $this->__isset( $property ) ) {
					return $this->$property;
				} else {
					$method = '_get_'.$property;
					if ( method_exists( $this , $method ) ) {
						return call_user_func( array( $this, $method ) );
					}
				}
			}
			return null;
		}

		/**
		 * Allow isset to be called on private properties.
		 *
		 * @param $property
		 *
		 * @return bool
		 */
		public function __isset( $property ) {
			return isset( $this->$property );
		}

		/**
		 * Get active.
		 * @used-by __get
		 * @return bool|mixed
		 */
		private function _get_active() {
			$this->__get( 'version' );      // Also sets active flag.
			return $this->active;
		}

		/**
		 * Get cache.
		 * @used-by __get
		 * @return mixed
		 */
		private function _get_cache() {
			$this->cache = get_transient( SLP_AddOn::TRANSIENT_BASE . $this->slug );
			return $this->cache;
		}

		/**
		 * Get the latest version from the update server.
		 * @used-by __get
		 * @return string
		 */
		private function _get_latest_version() {
			if ( $this->__get( 'cache' ) ) {
				$this->latest_version = $this->cache->latest_version;
			}

			if ( empty( $this->latest_version ) ) {
				$update_engine = SLP_AddOn_Updates::get_instance();
				$this->latest_version = $update_engine->getRemote_version( $this->slug );
				$this->cache_me_if_you_can();
			}
			return $this->latest_version;
		}

		/**
		 * Get name.
		 * @used-by __get
		 * @return string
		 */
		private function _get_name() {
			if ( isset( $this->instance ) ) {
				$this->name = $this->instance->name;
			} else {
				$this->name = $this->slplus->Text->get_text_string( array( 'link_text' , $this->slug ) );
			}
			return $this->name;
		}

		/**
		 * Get version of an active instance. Also sets active property.
		 * @used-by __get
		 * @return string
		 */
		private function _get_version() {
			if ( isset( $this->instance ) ) {
				$this->version = $this->instance->options['installed_version'];
				$this->active = true;
			} else {
				$this->version = '';
				$this->active = false;
			}
			return $this->version;
		}

		/**
		 * Get was_active.
		 * @used-by __get
		 * @return bool
		 */
		private function _get_was_active() {
			if ( $this->__get('active') ) {
				$this->was_active = true;
			} else {
				switch ( $this->slug ) {
					case 'slp-pro':
						$option_name = SLPLUS_PREFIX . '-' . strtoupper( str_replace( 'slp-' , '' , $this->slug ) ) . '-options';
						break;
					case 'slp-premier':
						$option_name = $this->slug . '-options';
						break;
					case 'slp-experience':
					case 'slp-power':
					default:
						$option_name = $this->slug;
						break;
				}
				$wp_option =   $this->slplus->WPOption_Manager->get_wp_option( $option_name , array() );
				$this->version = !empty( $wp_option[ 'installed_version' ] ) ? $wp_option[ 'installed_version' ] : '';
				$this->was_active = ! empty( $this->version );
			}
			return $this->was_active;
		}

		/**
		 * For sanitize_key filters that FUBAR the sanitize_key method in the constructor.
		 *
		 * This should not be necessary, but some plugins and themes are broken.
		 *
		 * @return string
		 */
		function __toString() {
			return $this->__get( 'name' );
		}

		/**
		 * Cache a copy of this add on less instance.
		 */
		private function cache_me_if_you_can() {
			$this->cache = new stdClass;
			$this->cache->slug = $this->slug;
			$this->cache->latest_version = $this->latest_version;

			set_transient( SLP_AddOn::TRANSIENT_BASE . $this->slug , $this->cache , DAY_IN_SECONDS );
		}
	}

}