<?php
defined( 'ABSPATH' ) || exit;
/**
 * Helper, non-critical methods to make WordPress plugins easier to manage.
 */
class SLP_Admin_Helper extends SLPlus_BaseClass_Object {

	/**
	 * Add a notification to the WP admin pages.
	 *
	 * @param string $text
	 * @param string $class
	 */
	public function add_wp_admin_notification( $text, $class = 'error' ) {
		if ( empty( $text ) ) { return; }
		add_action( 'admin_notices', function() use ( $text, $class ) { echo "<div class='{$class}'><p>{$text}</p></div>"; } );
	}

	/**
	 * Create a WordPress-like settings message error box.
	 *
	 * Uses same class as that for the built-in "settings saved" message.
	 *
	 * @param        $message
	 * @param string $message_detail
	 *
	 * @return string
	 */
	public function create_string_wp_setting_error_box( $message, $message_detail = '' ) {
		if ( empty( $message ) && empty( $message_detail ) ) { return ''; }

		if ( ! empty( $message ) ) {
            if ( is_array( $message ) ) { $message = '<pre>' . print_r($message,true) . '</pre>'; }
			$message .= '<br/>';
		}

		return
			'<div id="setting-error-settings_updated" class="updated settings-error">' .
			"<p><strong>{$message}</strong>${message_detail}</p>" .
			'</div>';
	}

	/**
	 * Create the bulk actions block for the top-of-table navigation.
	 *
	 * $params is a named array:
	 *
	 * The drop down components:
	 *
	 * string  $params['id'] the ID that goes in the select tag, defaults to 'actionType'
	 *
	 * string  $params['name'] the name that goes in the select tag, defaults to 'action'
	 *
	 * string  $params['onchange'] JavaScript to run on select change.
	 *
	 * string  $params['selectedVal'] if the item value matches this param, mark it as selected
	 *
	 * mixed[] $params['items'] the named array of drop down elements
	 *
	 *     $params['items'] is an array of named arrays:
	 *
	 *         string  $params['items'][0]['label'] the label to put in the drop down selection
	 *
	 *         string  $params['items'][0]['value'] the value of the option
	 *
	 *         boolean $params['items'][0]['selected] true of selected
	 *
	 * @param mixed[] $params a named array of the drivers for this method.
	 *
	 * @return string the HTML for the drop down with a button beside it
	 *
	 */
	public function createstring_DropDownMenu( $params ) {
		if ( ! isset( $params['items'] ) || ! is_array( $params['items'] ) ) {
			return '';
		}

		if ( ! isset( $params['id'] ) ) {
			$params['id'] = 'actionType';
		}
		if ( ! isset( $params['name'] ) ) {
			$params['name'] = 'action';
		}
		if ( ! isset( $params['selectedVal'] ) ) {
			$params['selectedVal'] = '';
		}
		if ( ! isset( $params['empty_ok'] ) ) {
			$params['empty_ok'] = false;
		}

		$params['disabled'] =
			( isset( $params['disabled'] ) && $params['disabled'] ) ?
				' disabled ' :
				'';

		if ( ! isset( $params['onchange'] ) || empty( $params['onchange'] ) )  {
			$params['onchange'] = '';
		} else {
			if ( stripos( $params['onchange'] , 'onchange=' ) === false ) {
				$params['onchange'] = " onChange=\"{$params['onchange']}\" ";
			}
		}

		// Drop down menu
		//
		$dropdownHTML = '';
		foreach ( $params['items'] as $item ) {
			if ( ! isset( $item['label'] ) ) {
				continue;
			}
			if ( ! $params['empty_ok'] && empty( $item['label'] ) ) {
				continue;
			}

			if ( ! isset( $item['value'] ) ) {
				$item['value'] = $item['label'];
			}
			if ( $item['value'] === $params['selectedVal'] ) {
				$item['selected'] = true;
			}
			$selected = ( isset( $item['selected'] ) && $item['selected'] ) ? 'selected="selected" ' : '';
			$dropdownHTML .= "<option {$selected} value='{$item['value']}'>{$item['label']}</option>";
		}

		return
			'<select ' .
			"id='{$params['id']}' " .
			"name='{$params['name']}' " .
			$params['disabled'] .
			$params['onchange'] .
			'>' .
			$dropdownHTML .
			'</select>';
	}

	/**
	 * Create the bulk actions block for the top-of-table navigation.
	 *
	 * $params is a named array:
	 *
	 * The drop down components:
	 *
	 * string  $params['id'] the ID that goes in the select tag, defaults to 'actionType'
	 * string  $params['name'] the name that goes in the select tag, defaults to 'action'
	 * string  $params['onchange'] JavaScript to run on select change.
	 * mixed[] $params['items'] the named array of drop down elements
	 *     $params['items'] is an array of named arrays:
	 *         string  $params['items'][0]['label'] the label to put in the drop down selection
	 *         string  $params['items'][0]['value'] the value of the option
	 *         boolean $params['items'][0]['selected] true of selected
	 *
	 * string  $params['buttonLabel'] the text that goes on the accompanying button, defaults to 'Apply'
	 * string  $params['onclick'] JavaScript to run on button click.
	 *
	 * @param mixed[] $params a named array of the drivers for this method.
	 *
	 * @return string the HTML for the drop down with a button beside it
	 *
	 */
	public function createstring_DropDownMenuWithButton( $params ) {
		if ( ! isset( $params['items'] ) || ! is_array( $params['items'] ) ) {
			return '';
		}
		$params['id'            ] = ! empty( $params['id'           ] ) ? $params['id'          ] : 'actionType';
		$params['name'          ] = ! empty( $params['name'         ] ) ? $params['name'        ] : 'action';
		$params['buttonlabel'   ] = ! empty( $params['buttonlabel'  ] ) ? $params['buttonlabel' ] : __( 'Apply', 'store-locator-le' );
		$params['onchange'      ] = ! empty( $params['onchange'     ] ) ? $params['onchange'    ] : '';
		$params['onclick'       ] = ! empty( $params['onclick'      ] ) ? $params['onclick'     ] : '';
		$params['class'         ] = ! empty( $params['class'        ] ) ? $params['class'       ] : '';

		// Render The Div
		//
		return
			'<div class="alignleft actions">' .
			$this->createstring_DropDownMenu( $params ) .
			"<input id='doaction_{$params['id']}' class='{$params['class']} button action' type='submit'" .
			'value="' . $params['buttonlabel'] . '" name="" ' .
			( ! empty( $params['onclick'] ) ? 'onClick="' . $params['onclick'] . '"' : '' ) .
			'/>' .
			'</div>';
	}

	/**
	 * Generate the HTML for a sub-heading label in a settings panel.
	 *
	 * TODO: Move directly into MySLP-Dashboard and EM if we still want to support those.
	 *
	 * @param string $label
	 * @param boolean $use_h3  - use h3 tag like an add_ItemToGroup subheader.
	 *
	 * @return string HTML
	 */
	public function create_SubheadingLabel( $label , $use_h3 = false ) {
		if ( $use_h3 ) {
			$output = "<h3>$label</h3>";
		} else {
			$output = "<p class='slp_admin_info'><strong>$label</strong></p>";
		}
		return $output;
	}

	/**
	 * Check if an item exists out there in the "ether".
	 *
	 * @param string $url - preferably a fully qualified URL
	 *
	 * @return boolean - true if it is out there somewhere
	 */
	public function webItemExists( $url ) {
		if ( ( $url == '' ) || ( $url === null ) ) {
			return false;
		}
		$response              = wp_safe_remote_head( $url, array( 'timeout' => 10 , 'sslverify' => false ) );
		$accepted_status_codes = array( 200, 301, 302 );
		if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
			return true;
		}

		$message = is_wp_error( $response ) ? $response->get_error_message() : __( 'code ' , 'store-locator-le' ) . wp_remote_retrieve_response_code( $response ) . '-' . wp_remote_retrieve_response_message( $response );
		error_log( "Could not access {$url} {$message}" );
		return false;
	}
}
