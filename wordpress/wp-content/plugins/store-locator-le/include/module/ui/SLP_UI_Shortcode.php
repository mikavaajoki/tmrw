<?php
/**
 * Class SLP_UI_Shortcode
 *
 * @property-read array $ajax_response  The current location data being processed as it would come back from an AJAX request
 * @property-read array $responses      An array of ajax repsonses with the key = location ID, prevents multiple requests for the same location data.  Serves as a memory cache.
 */
abstract class SLP_UI_Shortcode extends SLP_Base_Object {
	const shortcode = 'slp_ui';
	protected $ajax_response;
	protected $responses;

	/**
	 * Do at startup.
	 */
	public final function initialize() {
		global $slplus;
		$slplus->AddOns->activate_global_ajax_hooks();
		$this->activate();
	}

	/**
	 * Activate the shortcode processing.
	 */
	public final function activate() {
		$class = get_called_class();
		add_shortcode( $class::shortcode , array( $this , 'process' ) );
	}

	/**
	 * Clear AJAX response.
	 */
	public final function clear_ajax_response() {
		$this->ajax_response = null;
	}

	/**
	 * Get location data is it would come back to JS via AJAX handler.
	 */
	protected final function set_ajax_response() {
		global $slplus;
		if ( empty( $this->responses[ $slplus->currentLocation->id ] ) ) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->responses[ $slplus->currentLocation->id ] = SLP_AJAX::get_instance()->slp_add_marker( $slplus->currentLocation->locationData );
		}
		$this->ajax_response = $this->responses[ $slplus->currentLocation->id ];
	}

	/**
	 * Process the shortcode into a string output.
	 *
	 * EXTEND THIS IN YOUR CLASS.
	 *
	 * @param array $attributes
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	protected function process( $attributes , $content , $tag ) {
		$this->set_ajax_response();
		return '';
	}
}