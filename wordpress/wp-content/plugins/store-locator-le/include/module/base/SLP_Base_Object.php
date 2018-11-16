<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Base_Object
 *
 * New Base Object with singleton support.
 * The model on which all new SLP classes should be based.
 */
class SLP_Base_Object {

	/**
	 * SLP_Base_Object constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options=array() ) {
		$this->set_properties( $options );
		$this->initialize();
	}

	/**
	 * Return an instance of the object which is also registered to the slplus global less the SLP_ part.
	 *
	 * @param mixed     $options         object init params
	 *
	 * @return SLPlus_BaseClass_Object
	 */
	public static function get_instance( $options=array() ) {
		static $instance;
		if ( ! isset( $instance ) ) {
			$class = get_called_class();
			$instance = new $class( $options );
		}
		return $instance;
	}

	/**
	 * Do these things when this object is invoked. Override in your class.
	 */
	protected function initialize() {}

	/**
	 * Set our properties.
	 *
	 * @param array $options
	 */
	public function set_properties( $options = array() ) {
		if ( empty( $options ) ) return;
		if ( ! is_array( $options ) ) return;
		foreach ( $options as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->$property = $value;
			}
		}
	}
}