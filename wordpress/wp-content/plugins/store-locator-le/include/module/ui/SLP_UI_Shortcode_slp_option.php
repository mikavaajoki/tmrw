<?php
/**
 * Class SLP_UI_Shortcode_slp_option
 */
class SLP_UI_Shortcode_slp_option extends SLP_UI_Shortcode {
	const shortcode = 'slp_option';

	/**
	 * Insert add-on options into the [slp_option <js|nojs|name>="option_value"] shortcode.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public final function modify( $attributes ) {

		foreach ($attributes as $name=>$value) {
			switch (strtolower($name)) {
				case 'js':
					if ( isset( $this->addon->options[$value] ) ) {
						$this->slplus->options[$value] = $this->addon->options[$value];
					}
					return $attributes;

				case 'nojs':
					if ( isset( $this->addon->options[$value] ) ) {
						$this->slplus->options_nojs[$value] = $this->addon->options[$value];
					}
					return $attributes;

				case 'name':
					if ( isset( $this->addon->options[ $value ] ) ) {
						$attributes['value'] = $this->addon->options[$value];
						$attributes['key'] = $value;
						$attributes['type'] = $this->addon->short_slug;
					}
					return $attributes;

				default:
					break;
			}
		}
		return $attributes;
	}

	/**
	 * Return a plugin option value.
	 *
	 * TODO make this mimic the JS output.
	 *
	 * [slp_option name="<option_name>"] - render value of option_name setting checking SmartOptions, then nojs, then js options.
	 *
	 * [slp_option name="<option_name>" ifset] - only render the span if the option is not empty
	 *
	 * [slp_option name="<option_name>" ifset="div"] - only render span if option is not empty and also wrap that span in a div
	 *
	 * @param array $attributes
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public final function process( $attributes , $content , $tag ) {
		global $slplus;

		$keys = array_keys( $attributes );

		$defaults = array(
			'name' => $attributes[ $keys[0] ],
			'type' => '',
			'key'  => ''
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		$attributes = apply_filters( 'shortcode_slp_option', $attributes );

		$only_if_set  = false;
		$option_value = '';
		$div          = '';

		foreach ( $attributes as $att => $value ) {

			// Named attributes
			if ( (int) $att !== $att ) {
				switch ( $att ) {
					case 'ifset':
						$only_if_set = true;
						$div         = '<div class="option_%s">%s</div>';
						break;

					case 'js':
					case 'nojs':
					case 'name':
						if ( $slplus->SmartOptions->exists( $value ) ) {
							$option_value = $slplus->SmartOptions->{$value}->value;
							$type         = 'smart';
						} elseif ( array_key_exists( $value, $slplus->options_nojs ) ) {
							$option_value = isset( $slplus->options_nojs[ $value ] ) ? $slplus->options_nojs[ $value ] : '';
							$type         = 'nojs';
						} else {
							$option_value = isset( $slplus->options[ $value ] ) ? $slplus->options[ $value ] : '';
							$type         = 'js';
						}
						$key = $value;
						break;

					case 'key' :
						$key = $attributes['key'];
						break;

					case 'type':
						$type = $attributes['type'];
						break;

					case 'value':
						$option_value = $attributes['value'];
						break;
				}

			// Positional attributes
			} else {
				switch ( strtolower( $value ) ) {
					case 'ifset':
						$only_if_set = true;
						break;
				}
			}
		}

		if ( ! empty( $option_value ) || ! $only_if_set ) {
			$output = sprintf( '<span id="slp_option_%s_%s">%s</span>', $type, $key, $option_value );
			if ( ! empty( $div ) ) {
				$output = sprintf( $div, $key, $output );
			}
		} else {
			$output = '';
		}

		return $output;
	}
}