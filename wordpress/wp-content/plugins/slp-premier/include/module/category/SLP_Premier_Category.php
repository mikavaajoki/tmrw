<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Premier_Category
 *
 * Things that impact Admin, UI, Cron, AJAX
 */
class SLP_Premier_Category extends SLPlus_BaseClass_Object {
	/**
	 * Start this up.
	 */
	public function initialize() {
		if ( ! $this->slplus->AddOns->is_premier_subscription_valid() ) return;
		if ( ! $this->slplus->AddOns->get(  'slp-power'  , 'active' ) ) return;

		add_filter( 'slp_power_default_selectors', array( $this, 'add_category_selectors' ), 20, 1 );
	}

	/**
	 * Add new category selectors.
	 *
	 * @param SLP_Power_Category_Selector[] $selectors
	 *
	 * @return SLP_Power_Category_Selector[]
	 */
	function add_category_selectors( $selectors ) {

		// Button Bar
		$selectors['button_bar'] =
			new SLP_Power_Category_Selector( array(
				                                 'label'    => __( 'Button Bar', 'slp-premier' ),
				                                 'value'    => 'button_bar',
				                                 'selected' => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'button_bar' ),
				                                 'ui_builder' => 'SLP_Premier_Category_UI::create_string_for_button_bar_selector'
			                                 ) );

		// Checkboxes
		$selectors['horizontal_checkboxes'] =
			new SLP_Power_Category_Selector( array(
                 'label'    => __( 'Checkboxes, Horizontal', 'slp-premier' ),
                 'value'    => 'horizontal_checkboxes',
                 'selected' => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'horizontal_checkboxes' ),
                 'ui_builder' => 'SLP_Premier_Category_UI::create_string_horizontal_checkboxes'
             ) );
		$selectors['vertical_checkboxes'] =
			new SLP_Power_Category_Selector( array(
				                                 'label'    => __( 'Checkboxes, Vertical', 'slp-premier' ),
				                                 'value'    => 'vertical_checkboxes',
				                                 'selected' => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'vertical_checkboxes' ),
				                                 'ui_builder' => 'SLP_Premier_Category_UI::create_string_vertical_checkboxes'
			                                 ) );

		// Single Parent
		$selectors['single_parents'] =
			new SLP_Power_Category_Selector( array(
				'label'    => __( 'Single Parents', 'slp-premier' ),
				'value'    => 'single_parents',
				'selected' => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'single_parents' ),
                 'ui_builder' => 'SLP_Premier_Category_UI::create_string_for_single_parent_selector'
			) );

		return $selectors;
	}
}