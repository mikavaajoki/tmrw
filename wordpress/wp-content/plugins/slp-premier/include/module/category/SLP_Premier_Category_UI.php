<?php
defined('ABSPATH') || exit;

/**
 * Add stuff to the Tagalong UI.
 */
class SLP_Premier_Category_UI extends SLPlus_BaseClass_Object {
	private $class;

	/**
	 * Init
	 */
	public function initialize() {
		if ( ! $this->slplus->AddOns->is_premier_subscription_valid() ) return;
		if ( ! $this->slplus->AddOns->get(  'slp-power'  , 'active' ) ) return;
		$this->slplus->AddOns->instances['slp-premier']->create_object_category();
	}

	/**
	 * Create the button bar selector.
	 *
	 * Show a button bar for categories.
	 *
	 * @param boolean $has_label
	 *
	 * @return string
	 */
	public static function create_string_for_button_bar_selector( $has_label ) {
		global $slplus;
		return $slplus->Premier_Category_UI->get_checklist( 'button_bar' , $has_label);
	}

	/**
     * Create the single parent selector.
	 *
	 * Shows one drop down for each parent category.  If no parents this is empty.
     *
	 * @param boolean $has_label
	 *
     * @return string
     */
    public static function create_string_for_single_parent_selector( $has_label ) {
    	global $slplus;
        $term_ids = $slplus->AddOns->instances['slp-power']->stores_taxonomy->get_parent_categories();
        if ( empty( $term_ids ) ) {
        	return '';
        }

        $html = '';
        foreach ( $term_ids as $term_id ) {
            $category_html =
                wp_dropdown_categories(
                    array(
                        'child_of' => $term_id,
                        'echo' => 0,
                        'hierarchical' => 1,
                        'depth' => 99,
                        'hide_empty' => $slplus->SmartOptions->hide_empty->is_true,
                        'hide_if_empty' => true,
                        'orderby' => 'NAME',
                        'show_option_all' =>$slplus->SmartOptions->show_option_all->value . ' ' .
                                            $slplus->AddOns->instances['slp-power']->stores_taxonomy->get_term( $term_id , 'name' )
                            ,
                        'name'     => 'cat[]',
                        'taxonomy' => SLPLUS::locationTaxonomy
                    )
                );

	        $cat_manager = SLP_Power_Category_Selector_Manager::get_instance();
            $html .= $cat_manager->create_string_dropdown_div( $category_html , $term_id );
        }

        return $html;
    }

	/**
	 * Horizontal category checkboxes.
	 *
	 * @param boolean $has_label
	 *
	 * @return string
	 */
	public static function create_string_horizontal_checkboxes( $has_label ) {
		global $slplus;
		return $slplus->Premier_Category_UI->get_checklist( 'horizontal', $has_label);
	}

	/**
	 * Vertical category checkboxes.
	 *
	 * @param boolean $has_label
	 *
	 * @return string
	 */
	public static function create_string_vertical_checkboxes( $has_label ) {
		global $slplus;
		return $slplus->Premier_Category_UI->get_checklist( 'vertical' , $has_label );
	}

	/**
	 * Generate the checklist HTML for categories.
	 *
	 * @param string  $class
	 * @param boolean $has_label
	 *
	 * @return string
	 */
	public function get_checklist( $class , $has_label ) {
		if ( ! function_exists( 'wp_terms_checklist' ) ) {
			require( ABSPATH . 'wp-admin/includes/template.php' );
		}
		$this->class = $class;

		$cat_man_do = SLP_Power_Category_Manager::get_instance();
		add_filter( 'get_terms' , array( $cat_man_do , 'add_location_counts_to_terms' ) , 10 , 4 );
		$HTML = $this->get_div_html( $has_label , wp_terms_checklist( 0, $this->get_taxonomy_query_args() ) );
		remove_filter( 'get_terms' , array( $cat_man_do , 'add_location_counts_to_terms' ) , 10 );

		return $HTML;
	}

	/**
	 * @return mixed
	 */
	public function get_class() {
		return $this->class;
	}

	/**
	 * Generate the checklist HTML for categories.
	 *
	 * @param boolean   $has_label
	 * @param string    $selector_html
	 *
	 * @return string
	 */
	private function get_div_html( $has_label , $selector_html ) {
		$label = $has_label ? $this->get_label_html() : '';

		return<<<HTML
			<div class="search_item category {$this->class}">
				{$label}
				<div class='category checklist {$this->class}'> 
					{$selector_html}
				</div>
			</div>	
HTML;
	}

	/**
	 * Get the HTML for the label.
	 * 
	 * @return string
	 */
	private function get_label_html() {
		$label_len = strlen( $this->slplus->SmartOptions->label_category->value );
		return "<label for='tax_input[stores]' class='text length_{$label_len}'>{$this->slplus->SmartOptions->label_category->value}</label>";
	}

	/**
	 * Set the arguments needed for a taxonomy query.
	 *
	 * @return array
	 */
	private function get_taxonomy_query_args() {
		return array(
			'taxonomy'              => SLPLUS::locationTaxonomy,
			'checked_ontop'         => false,
			'echo'                  => false,
			'walker'                => new SLP_Premier_Category_Walker_Checklist
		);		
	}
}