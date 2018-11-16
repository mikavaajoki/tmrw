<?php
defined( 'ABSPATH' ) || exit;
/**
 * Class SLPPower_Admin_ExperienceSettings
 *
 * The things that modify the Admin / Experience Tab.
 *
 * @property        SLPPower     $addon
 * @property-read   array        $group_params
 * @property-read   SLP_Settings $Settings
 */
class SLPPower_Admin_ExperienceSettings extends SLPlus_BaseClass_Object {
	public  $addon;
	private $group_params;
	private $Settings;

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
		$this->group_params = array( 'plugin' => $this->addon, 'section_slug' => null, 'group_slug' => null );
		add_action( 'slp_build_map_settings_panels', array( $this, 'modify_tab' ), 95 );
	}

	/**
	 * Results
	 */
	private function modify_results() {
		$this->group_params['section_slug'] = 'results';
		$this->modify_results_appearance();
	}

	/**
	 * Results / Appearance
	 */
	private function modify_results_appearance() {
		$this->group_params['group_slug'] = 'appearance';

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_output_processing',
			'type'         => 'dropdown',
			'label'        => __( 'Show Tags In Output', 'slp-power' ),
			'description'  => __( 'How should tags be output in the results below the map and the info bubble?', 'slp-power' ),
			'custom'       => array(
				array( 'label' => __( 'As Entered', 'slp-power' ), 'value' => 'as_entered' ),
				array( 'label' => __( 'Hide Tags', 'slp-power' ), 'value' => 'hide' ),
				array( 'label' => __( 'On Separate Lines', 'slp-power' ), 'value' => 'replace_with_br' ),
			),
		) );
	}

	/**
	 * Search
	 */
	private function modify_search() {
		$this->group_params['section_slug'] = 'search';
		$this->modify_search_appearance();
		$this->modify_search_functionality();
		$this->modify_search_labels();
	}

	/**
	 * Search / Appearance
	 */
	private function modify_search_appearance() {
		$this->group_params['group_slug'] = 'appearance';

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_selector',
			'type'         => 'dropdown',
			'label'        => __( 'Search Form Tag Input', 'slp-power' ),
			'description'  => __( 'Select the type of tag input that you would like to see on the search form.', 'slp-power' ),
			'custom'       => array(
				array( 'label' => __( 'None', 'slp-power' ), 'value' => 'none' ),
				array( 'label' => __( 'Hidden', 'slp-power' ), 'value' => 'hidden' ),
				array( 'label' => __( 'Drop Down', 'slp-power' ), 'value' => 'dropdown' ),
				array( 'label' => __( 'Radio Button', 'slp-power' ), 'value' => 'radiobutton' ),
				array( 'label' => __( 'Text Input', 'slp-power' ), 'value' => 'textinput' ),
			),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_show_any',
			'type'         => 'checkbox',
			'label'        => __( 'Add All To Tags Dropdown', 'slp-power' ),
			'description'  => __( 'Add an "any" selection on the tag drop down list thus allowing the user to show all locations in the area, not just those matching a selected tag.', 'slp-power' ),
		) );


		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_dropdown_first_entry',
			'label'        => __( 'Tag Select All Text', 'slp-power' ),
			'description'  => __( 'What should the "any" tag say? ', 'slp-power' ) .
			                  __( 'The first entry on the search by tag pulldown.', 'slp-power' ),
		) );

	}

	/**
	 * Search / Functionality
	 */
	private function modify_search_functionality() {
		$this->group_params['group_slug'] = 'functionality';

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_selections',
			'label'        => __( 'Default Tag Selections', 'slp-power' ),
			'description'  =>
				__( 'For Hidden or Text tag input enter a default value to be used in the field, if any. ', 'slp-power' ) .
				__( 'For Drop Down tag input enter a comma (,) separated list of tags to show in the search pulldown, mark the default selection with parenthesis (). ', 'slp-power' ) .
				__( 'This is a default setting that can be overriden on each page within the shortcode.', 'slp-power' ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_autosubmit',
			'type'         => 'checkbox',
			'label'        => __( 'Tag Autosubmit', 'slp-power' ),
			'description'  => __( 'Force the form to auto-submit when the tag is selected with a radio button.', 'slp-power' ),
		) );
	}

	/**
	 * Search / Labels
	 */
	private function modify_search_labels() {
		$this->group_params['group_slug'] = 'labels';

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->group_params,
			'option'      => 'tag_label',
			'label'        => __( 'Tags Label', 'slp-power' ),
			'description'  => __( 'Search form label to prefix the tag selector.', 'slp-power' ),
		) );
	}

	/**
	 * View
	 */
	private function modify_view() {
		$this->group_params['section_slug'] = 'view';

		$this->modify_view_style();
	}

	/**
	 * View / Style
	 */
	private function modify_view_style() {
		$this->group_params['group_slug'] = 'style';

		if ( $this->addon->using_pages && $this->slplus->AddOns->get( 'slp-premier' , 'active') ) {
			$this->Settings->add_ItemToGroup( array(
				'group_params' => $this->group_params,
				'type'         => 'subheader',
				'label'        => __( 'Pages', 'slp-power' ),
			) );
			// Make sure we have a default template.
			//
			if ( empty( $this->addon->options[ 'page_template' ] ) ) {
				$this->addon->admin->create_object_pages();
				$this->addon->options[ 'page_template' ] = $this->addon->admin->pages->create_string_default_template();
			}
			$this->Settings->add_ItemToGroup( array(
				'group_params' => $this->group_params,
				'type'         => 'textarea',
				'label'        => __( 'Page Template', 'slp-power' ),
				'option'      => 'page_template',
				'description'  =>
					__( 'The HTML that is used to create new store pages.', 'slp-power' ) .
					__( 'Leave blank to reset to default layout.', 'slp-power' )
				,
			) );
		}
	}

	/**
	 * Modify entries on the tab.
	 *
	 * @param SLPlus_Settings $Settings
	 */
	public function modify_tab( $Settings ) {
		$this->Settings = $Settings;
		$this->modify_results();
		$this->modify_search();
		$this->modify_view();
	}
}
