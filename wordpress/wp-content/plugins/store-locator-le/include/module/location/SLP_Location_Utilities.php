<?php
class SLP_Location_Utilities extends SLP_Base_Object {

	/**
	 * Create the city_state_zip formatted output.
	 */
	public function create_city_state_zip() {
		global $slplus;
		$output = '';
		if ( trim( $slplus->currentLocation->city ) !== '' ) {
			$output = $slplus->currentLocation->city;
			if ( trim( $slplus->currentLocation->state ) !== '' ) {
				$output .= ', ';
			}
		}

		if ( trim( $slplus->currentLocation->state ) !== '' ) {
			$output .= $slplus->currentLocation->state;
			if ( trim( $slplus->currentLocation->zip ) !== '' ) {
				$output .= ' ';
			}
		}

		if ( trim( $slplus->currentLocation->zip ) !== '' ) {
			$output .= $slplus->currentLocation->zip;
		}
		
		return $output;
	}

	/**
	 * Create the email hyperlink.
	 *
	 * @param string $email
	 *
	 * @return string
	 */
	public function create_email_link( $email ) {
		if ( empty( $email ) ) return '';

		return
			sprintf(
				'<a href="mailto:%s" target="_blank" class="storelocatorlink"><nobr>%s</nobr></a>',
				esc_attr( $email ),
				SLP_Text::get_instance()->get_text( 'label_email' )
			);
	}
}