<?php
if ( ! class_exists( 'SLP_Object_With_Objects' ) ) :

	/**
	 * Class SLP_Object_With_Objects
	 *
	 * @property   string $class_prefix  the prefix that goes before all our classes
	 * @property   string  $dir          the root directory for this theme
	 * @property   array   $objects      key = class name, array of attributes()
	 *                                      auto_instantiate = true , instantiate the object when this object initializes
	 *                                      object  = the instantiated object
	 *                                      options = default startup options
	 *                                      subdir  = the subdirectory (from theme root) that contains the class definition
	 */
	class SLP_Object_With_Objects extends SLPlus_BaseClass_Object {
		protected $class_prefix;
		public $dir;
		protected $objects = array();

		/**
		 * Get the value, running it through a filter.
		 *
		 * @param string $property
		 *
		 * @return mixed     null if not set or the value
		 */
		function __get( $property ) {

			if ( ! empty( $this->objects) && array_key_exists( $property , $this->objects ) && isset( $this->objects[ $property ]['object'] ) ) {
				return $this->objects[ $property ]['object'];
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

			if ( is_array( $this->objects) && array_key_exists( $property , $this->objects ) && ! empty( $this->objects[ $property ]['object'] )  && is_object( $this->objects[ $property ]['object'] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @param $object
		 */
		public function add_object( $object ) {
			if ( ! is_object ( $object ) ) {
				return;
			}
			$key = preg_replace( "/^{$this->class_prefix}/" , '' , get_class( $object ) );
			if ( empty( $key ) ) {
				return;
			}
			$this->objects[ $key ] = array( 'object' => $object );
		}

		/**
		 * Augment the class names so we can reference simple property names.
		 *
		 * @param string $class
		 *
		 * @return string
		 */
		private function augment_class( $class ) {
			return $this->class_prefix . $class;
		}

		/**
		 * Any object with auto_instantiate set to true is instantiate during initialization.
		 */
		private function auto_instantiate() {
			if ( ! empty ( $this->objects ) ){
				foreach ( $this->objects as $slug => $properties ) {
					if ( ! empty( $properties[ 'auto_instantiate' ] ) ) {
						$options = ! empty ( $properties[ 'options' ] ) ? $properties[ 'options' ] : array() ;
						$this->instantiate( $slug , $options );
					}
				}
			}
		}

		/**
		 * Set the dir property.
		 */
		protected function initialize() {
			if ( empty( $this->objects ) ) {
				return;
			}
			$this->class_prefix = empty( $this->class_prefix ) ? 'SLP_' : $this->class_prefix;
			if ( empty ( $this->dir ) ) {
				if ( ! empty( $this->addon->dir ) ) {
					$this->dir = $this->addon->dir;
				} else {
					$this->dir = SLPLUS_PLUGINDIR;
				}
			}

			$this->set_default_object_options();
			$this->auto_instantiate();
			$this->at_startup();
		}

		/**
		 * Instantiate an object of the noted class.
		 *
		 * @param string $class
		 * @param array  $options
		 *
		 * @return null|object
		 */
		public function instantiate( $class , $options = array() ) {
			if ( ! array_key_exists( $class, $this->objects ) ) {
				return null;
			}
			if ( ! isset( $this->$class ) ) {
				$full_class = $this->augment_class( $class );
				if ( !isset( $this->objects[$class]['subdir'] ) ) {
					$this->objects[$class]['subdir'] = '';
				}
				include_once( $this->dir . '/' . $this->objects[$class]['subdir'] . $full_class .'.php' );
				if ( ! class_exists( $full_class ) ) {
					return null;
				}
				if ( ! empty ( $this->objects[$class]['options'] ) ) {
					$options = array_merge( $options, $this->objects[$class]['options'] );
				}
				$this->objects[ $class ]['object'] = new $full_class( $options );
			}
			return $this->objects[ $class ]['object'];
		}

		/**
		 * Things to do at startup after this baby is initialized.  Override in your class.
		 */
		protected function at_startup() {}

		/**
		 * Set default options for objects.  Override in your class.
		 */
		protected function set_default_object_options() {}
	}

endif;

