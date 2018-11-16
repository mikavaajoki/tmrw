<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SLP_Object_WithOptions' ) ) :

	/**
	 * Class SLP_Object_WithOptions
	 *
	 * @property   string $class_prefix  the prefix that goes before all our classes
	 * @property   string  $dir          the root directory for this theme
	 * @property   string  $option_name  the name of the wp_option option_name we are using
	 * @property   array   $options      key = option slug, array of attributes()
	 *                                   value = the value of the option
	 * @property   array   $objects      key = class name, array of attributes()
	 *                                  object = the instantiated object
	 *                                  subdir = the subdirectory (from theme root) that contains the class definition
	 */
	class SLP_Object_WithOptions extends SLP_Object_With_Objects {
		protected $class_prefix;
		public $dir;
		protected $option_name;
		protected $options = array();

		/**
		 * Get the value, running it through a filter.
		 *
		 * @param string $property
		 *
		 * @return mixed     null if not set or the value
		 */
		function __get( $property ) {
			if ( array_key_exists( $property , $this->options ) ) {
				return $this->options[ $property ]['value'];
			}

			return parent::__get( $property );
		}

		/**
		 * Allow isset to be called on private properties.
		 *
		 * @param $property
		 *
		 * @return bool
		 */
		public function __isset( $property ) {
			if ( array_key_exists( $property , $this->options ) && isset( $this->options[ $property ]['value'] ) ) {
				return true;
			}

			return parent::__isset( $property );
		}

		/**
		 * Allow value to be set directly.
		 *
		 * @param $property
		 *
		 * @param $value
		 * @return SLP_Option|null
		 */
		public function __set( $property, $value ) {
			if ( array_key_exists( $property , $this->options ) ) {
				$this->options[ $property ]['value'] = $value;
				return $this->options[ $property ];
			}
		}

		/**
		 * Get an option attribute.
		 *
		 * @param string $property
		 * @param string $attribute
		 *
		 * @return mixed
		 */
		public function get_option( $property , $attribute ) {
			if ( array_key_exists( $property , $this->options ) && isset( $this->options[ $property ][$attribute] ) ) {
				return $this->options[ $property ][$attribute];
			}
			return null;
		}

		/**
		 * Grab the specified option from wp_options and put it on top of any default values we've got.
		 */
		public function load_options() {
			if ( empty( $this->option_name ) ) {
				return;
			}
			$wp_options = get_option( $this->option_name , array() );
			foreach ( $wp_options as $property => $value ) {
				$this->$property = $value;
			}
		}

		/**
		 * Set an option attribute.
		 *
		 * @param string $property
		 * @param string $attribute
		 * @param mixed $value
		 *
		 * @return bool
		 */
		public function set_option( $property , $attribute , $value ) {
			if ( array_key_exists( $property , $this->options ) ) {
				$this->options[ $property ][$attribute] = $value;
				return true;
			}
			return false;
		}
	}

endif;

