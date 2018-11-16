<?php

/**
 * Class SLP_UI_Shortcode_slp_location
 */
class SLP_UI_Shortcode_slp_location extends SLP_UI_Shortcode {
	const shortcode = 'slp_location';

	/**
	 * Start looking for shortcode strings and check formatting.
	 */
	public function check_formatting() {
		add_shortcode( self::shortcode, array( $this , 'format' ) );
	}

	/**
	 * Stop checking formatting.
	 */
	public function stop_checking_format() {
		$this->activate();
	}

	/**
	 * Looks at the shortcode entries in a string and makes sure they are properly formatted.
	 * Add extended attributes if necessary.
	 * Does NOT output the final data, that is done in JavaScript.
	 *
	 * Attributes for this shortcode include:
	 *     <field name> where field name is a locations table field.
	 *
	 * @param array $initial_attributes
	 *
	 * @return string
	 */
	public function format( $initial_attributes ) {
		$shortcode_label = 'slp_location';
		$fldName         = '';
		$attributes      = '';

		// Process the keys
		//
		if ( is_array( $initial_attributes ) ) {
			foreach ( $initial_attributes as $key => $value ) {
				$key   = strtolower( $key );
				$value = preg_replace( '/[\W^[.]]/', '', htmlspecialchars_decode( $value ) );
				switch ( $key ) {

					// First attribute : field name placeholders
					//
					case '0':
						$fldName = strtolower( $value );

						switch ( $fldName ):

							// slp_location with more attributes
							//
							case 'web_link':
							case 'pro_tags':
								$attributes .= ' raw';
								break;

							case 'distance_1'     :
								$fldName = 'distance';
								$attributes .= ' format="decimal1"';
								break;

							case 'hours':
								$attributes = ' format text';
								break;

							// convert to slp_option
							//
							case 'map_domain'     :
							case 'distance_unit'  :
								$shortcode_label = 'slp_option';
								break;

							case 'directions_text':
								$shortcode_label = 'slp_option';
								$fldName         = 'label_directions';
								break;

							// Leave untouched
							//
							default:
								break;

						endswitch;
						break;

					default:
						$attributes .=
							' ' .
							(
							is_numeric( $key ) ?
								$value :
								$key . '="' . $value . '"'
							);
						break;
				}
			}
		}

		return "[{$shortcode_label} {$fldName}{$attributes}]";
	}



	/**
	 * Process the shortcode into a string output.
	 *
	 * TODO: check web_link is being setup properly
	 * TODO: finish replicating the AJAX shortcode modifiers here.
	 *
	 * @param array $attributes
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function process( $attributes , $content , $tag ) {
		global $slplus;
		$keys = array_keys( $attributes );
		$field_name = $attributes[ $keys[0] ];
		$modifier = '';
		$modarg = '';
		$modifier2 = '';
		$modarg2 = '';
		$value = '';

		$this->set_ajax_response();

		// Standard fields
		//
		if ( isset( $this->ajax_response[ $field_name ] )  ) {
			$value = $this->ajax_response[ $field_name ];

		// Extended Fields
		//
		} elseif ( strpos( $field_name , '.' ) !== false ) {
			$data_parts = explode( '.' , $field_name );
			$marker_array_name = $data_parts[0];
			if ( empty( $this->ajax_response[ $marker_array_name ] ) ) return '';
			$marker_array_field = $data_parts[1];
			$value = $this->ajax_response[$marker_array_name][$marker_array_field];
			if ( empty( $value ) ) return '';
			$field_name = $marker_array_name . '_' . $marker_array_field;

		// non-existent property
		//
		} else {
			return '';
		}

		$output = $this->shortcode_modifier( array(
			'value' => $value,
			'modifier'   => $modifier,
            'modarg'     => $modarg,
            'field_name' => $field_name,
            'marker'     => $this->ajax_response
		) );

		if ( ! empty( $modifier2 ) ) {
			$output = $this->shortcode_modifier( array(
				'value' => $output,
				'modifier'   => $modifier2,
				'modarg'     => $modarg2,
				'field_name' => $field_name,
				'marker'     => $this->ajax_response
			) );
		}

		return $output;
	}

	/**
	 * Shortcode modifier.
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	private function shortcode_modifier( $settings ) {
		global $slplus;

		$defaults = array(
			'value' => '',
			'modifier' => '',
			'modarg' => '',
			'field_name' => '',
			'marker' => $this->ajax_response
		);
		$args = wp_parse_args( $settings , $defaults );

		$raw_output = true;
		$prefix = '';
		$suffix = '';
		$value = $args[ 'value' ];

		if ( $args[ 'field_name' ] === 'hours' ) {
			$raw_output = false;
		}

		$newOutput = $raw_output ? $value : html_entity_decode( $value );

		return $prefix . $newOutput . $suffix;
	}

	/**
	 * Set the location URL.
	 * @return string
	 */
	private function set_LocationURL() {
		global $slplus;
		$power = $slplus->addon( 'power' );
		$url = '';
		if ( $slplus->is_CheckTrue( $power->options[ 'pages_replace_websites' ] ) ) {
			$url = $this->ajax_response->pages_url;

		} else if ( ! empty( $this->ajax_response->url ) ) {
			$prefix = '';
			if (
				( strpos( $this->ajax_response->url , 'http://' ) === false ) &&
				( strpos( $this->ajax_response->url , 'https://') === false )
			) {
				$prefix = 'http://';
			}

			if ( filter_var( $prefix . $this->ajax_response->url , FILTER_VALIDATE_URL ) ) {
				$url = $prefix . $this->ajax_response->url;
			}
		}
		return $url;
	}

	/**
	 * Output for special slp_location data elements.
	 *
	 * These are the non-field data elements, such as distance_1 which is normally
	 * calculated in the JavaScript processor.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	private function special_processing( $attributes ) {
		global $slplus;
		$output = '';
		switch ( $attributes[0] ) {
			case 'web_link':
				$power = $slplus->addon( 'power' );
				$url = $this->set_LocationURL();
				$target = $slplus->is_CheckTrue( $power->options[ 'prevent_new_window' ] ) ? '_self' : '_blank' ;
				/** @noinspection PhpUndefinedMethodInspection */
				$output = "<a href='{$url}' target='{$target}' class='storelocatorlink'>" . SLP_Text::get_instance()->get_text( 'label_website' ) . '</a>' . '<br/>'
				;

				break;
		}

		return $output;
	}
}