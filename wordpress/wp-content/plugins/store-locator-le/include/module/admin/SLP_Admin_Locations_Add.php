<?php
defined( 'ABSPATH' ) || exit;

/**
 * Store Locator Plus basic admin user interface.
 *
 * @property        array               $group_params       The metadata needed to build a settings group.
 * @property-read   array               $section_params     The metadata needed to build a settings section.
 * @property        SLP_Settings        $settings           SLP Settings Interface reference SLPlus->ManageLocations->settings
 * @property        SLPlus              $slplus
 * @property        SLP_Template_Vue    $vue
 */
class SLP_Admin_Locations_Add extends SLPlus_BaseClass_Object {
	public  $group_params;
	private $locations_group = '';
	private $section_params;
	public  $settings;
	private $vue;

	/**
	 * Initialize this object.
	 */
	public function initialize() {
		$this->vue = SLP_Template_Vue::get_instance();

		$this->settings = $this->slplus->Admin_Locations->settings;
		$this->locations_group          = $this->slplus->Text->get_text_string( 'location' );
		$this->section_params[ 'name' ] = $this->slplus->Text->get_text_string( 'add' );
		$this->section_params[ 'slug' ] = 'add';
		$this->section_params[ 'innerdiv' ] = false;

		$this->section_params[ 'opening_html' ] = <<< HTML
		<v-app>
            <input type="hidden" id="act" name="act" v-model="act" />
            <input type='hidden' name='id' id='id' value='' />
            <input type='hidden' name='locationID' id='locationID' value='' />
            <input type='hidden' name='linked_postid' data-field="linked_postid" value='' />
            
			<v-dialog v-model="show_add_dialog" hide-overlay persistent scrollable>
				<v-card tile>
					<v-toolbar card dark color="primary">
					<v-btn icon @click.native="close_add_dialog" dark>
					  <v-icon>close</v-icon>
					</v-btn>
					<v-toolbar-title>{{location_manager.text.form_title}}</v-toolbar-title>
					</v-toolbar>
					<v-card-text>
						<v-layout row>
HTML;


		$this->section_params[ 'closing_html' ] = <<< HTML_END
						</v-layout>
					</v-card-text>
					<v-divider></v-divider>
					<v-card-actions>					
			            <v-btn @click.native="submit_form" color="primary">{{location_manager.text.save}}</v-btn>              
			            <v-btn @click.native="close_add_dialog">{{location_manager.text.cancel}}</v-btn>
            		</v-card-actions>
				</v-card>				
			</v-dialog>
			
		</v-app>
HTML_END;


		$this->settings->add_section( $this->section_params );

		// Common params for all groups in this section.
		//
		$this->group_params[ 'section_slug' ] = $this->section_params[ 'slug' ];
		$this->group_params[ 'plugin' ]       = $this->slplus;

	}

	/**
	 * Address Block
	 */
	private function address_block() {
		$this->group_params[ 'header' ]     = '';
		$this->group_params[ 'group_slug' ] = 'location';
		$this->group_params[ 'div_group' ]  = 'left_side flex sm12 md7';
		$this->settings->add_group( $this->group_params );

		$this->settings->add_ItemToGroup( array(
			'section'      => $this->section_params[ 'name' ] ,
			'group_params' => $this->group_params ,
			'show_label'   => false,
			'wrapper'      => false,
			'type'         => 'custom' ,
			'custom'       =>  $this->vue->get_content( 'locations_add_address' ),
		) );
	}

	/**
	 * Build the add or edit interface.
	 */
	public function build_interface() {
		$this->address_block();
		$this->extended_data_block();
		$this->map();

		do_action( 'slp_modify_location_add_form' );
	}

	/**
	 * Add extended data to location add/edit form.
	 */
	private function extended_data_block() {
		$this->slplus->Admin_Locations->set_active_columns();
		$this->slplus->Admin_Locations->filter_active_columns();
		if ( empty( $this->slplus->Admin_Locations->active_columns ) ) {
			return;
		}

		$data = ( (int) $this->slplus->currentLocation->id > 0 ) ? $this->slplus->database->extension->get_data( $this->slplus->currentLocation->id ) : null;

		// For each extended data field, add an item.
		//
		$groups = array();
		foreach ( $this->slplus->Admin_Locations->active_columns as $data_field ) {
			$slug         = $data_field->slug;
			$display_type = $this->set_extended_data_display_type( $data_field );
			if ( $display_type === 'none' ) {
				continue;
			}

			$this->slplus->database->extension->set_options( $slug );

			$group_name = $this->set_extended_data_group( $data_field );

			// Group does not exist, add it to settings.
			//
			if ( ! in_array( $group_name , $groups ) ) {
				$groups[] = $group_name;

				$this->group_params[ 'header' ]       = $group_name;
				$this->group_params[ 'group_slug' ]   = sanitize_key( $group_name );
				$this->group_params[ 'div_group' ]    = 'left_side flex sm12 md7';
				$this->group_params[ 'section_slug' ] = $this->section_params[ 'slug' ];
				$this->group_params[ 'plugin' ]       = $this->slplus;

				$this->settings->add_group( $this->group_params );

				// Group exists, only need to set slug
				//
			} else {
				$this->group_params[ 'group_slug' ] = sanitize_key( $group_name );
				unset( $this->group_params[ 'header' ] );
			}

			// Standard data types
			//
			if ( $display_type !== 'callback' ) {
				$args = array(
					'group_params' => $this->group_params ,
					'label'        => $data_field->label ,
					'id'           => $slug ,
					'name'         => $slug ,
					'data_field'   => $slug ,
					'value'        => ( ( is_null( $data ) || ! isset( $data[ $slug ] ) ) ? '' : $data[ $slug ] ) ,
					'type'         => $display_type ,
					'description'  => $this->slplus->database->extension->get_option( $data_field->slug , 'help_text' ) ,
					'custom'       => $this->slplus->database->extension->get_option( $data_field->slug , 'custom' ) ,
				);
				if ( $display_type === 'checkbox' ) {
					$args[ 'display_value' ] = false;
				}

				$this->settings->add_ItemToGroup( $args );

				// Callback Display Type
				//
			} else {

				/**
				 * ACTION:     slp_add_location_custom_display
				 *
				 * Runs when the extended data display type is set to callback.
				 *
				 * @param   SLP_Settings $settings     SLP Settings Interface reference SLPlus->ManageLocations->settings
				 * @param   array[]      $group_params The metadata needed to build a settings group.
				 * @param   array[]      $data_field   The current extended data field meta.
				 */
				do_action( 'slp_add_location_custom_display' , $this->settings , $this->group_params , $data_field );
			}
		}
	}

	/**
	 * Render a map of where the location is.
	 */
	private function map() {
		$this->group_params[ 'header' ]     = '';
		$this->group_params[ 'group_slug' ] = 'map';
		$this->group_params[ 'div_group' ]  = 'right_side flex xs4';
		$this->settings->add_group( $this->group_params );
		$this->settings->add_ItemToGroup( array(
			'group_params' => $this->group_params ,
			'show_label'   => false ,
			'wrapper'      => false,
			'type'         => 'custom' ,
			'custom'       => "<div v-show='(location.sl_latitude!==\"\")' id='edit_location_map' class='location_map'></div>" ,
		) );
	}

	/**
	 * Set the display type.
	 *
	 * @param    array $data_field
	 *
	 * @return    string the display_type
	 */
	private function set_extended_data_display_type( $data_field ) {
		$display_type = $this->slplus->database->extension->get_option( $data_field->slug , 'display_type' );
		if ( is_null( $display_type ) ) {
			switch ( $data_field->type ) {

				case 'boolean':
					$display_type = 'checkbox';
					break;

				case 'text':
					$display_type = 'textarea';
					break;

				default:
					$display_type = 'text';
					break;
			}
		}

		return $display_type;
	}

	/**
	 * Set the SLPlus_Settings group name.
	 *
	 * @param    array $data_field
	 *
	 * @return    string        the SLPlus_Settings group name
	 */
	private function set_extended_data_group( $data_field ) {
		if ( is_null( $this->slplus->AddOns ) || empty( $data_field->option_values[ 'addon' ] ) ) {
			$group_name = __( 'Extended Data ' , 'store-locator-le' );

		} else {
			$group_name = $this->slplus->AddOns->instances[ $data_field->option_values[ 'addon' ] ]->name;
		}

		return $group_name;
	}

}
