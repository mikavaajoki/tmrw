<?php
defined('ABSPATH') || exit;

/**
 * A category selector object.
 *
 * @property    string $label      Plain text label for the UI.
 * @property    string $value      The slug saved in addon->options that identifies this selector type.
 * @property    boolean $selected   Is this the active selector type (true).
 * @property    callable $ui_builder The function that returns a string that will be used for the HTML output.
 */
class SLP_Power_Category_Selector {
    public $label;
    public $value;
    public $selected;
    public $ui_builder;

	function __construct( $options = array() ) {
		foreach ( $options as $property => $value ) {
			if ( property_exists( $this, $property ) ) $this->$property = $value;
		}
	}
}