<?php
defined('ABSPATH') || exit;

/**
 * Manage the category selectors.
 *
 * @property        SLPPower                      $addon
 * @property-read	array                        $categoryDropDowns 			The category drop downs.
 * @property-read	int                          $node_level					Which level of the nested drop down tree are we processing?
 * @property        SLP_Power_Category_Selector[] $selectors                  named array, key = slug, value = selector object
 *
 */
class SLP_Power_Category_Selector_Manager extends SLPlus_BaseClass_Object {
    public  $addon;
    private $categoryDropDowns = array();
    private $node_level = 0;
    public  $selectors;

    /**
     * Initialize the manager.
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'Power' );
        $this->create_default_selectors();
    }

    /**
     * Create the default selectors.
     *
     * @uses create_string_for_cascade_selector()
     * @uses create_string_for_single_selector()
     */
    private function create_default_selectors() {

        // No
        $this->selectors['none'] =
            new SLP_Power_Category_Selector( array(
                'label'         => __('Hidden','slp-power'),
                'value'         =>'none',
                'selected'      => ( $this->slplus->SmartOptions->show_cats_on_search->value === '' )
            ));

        // Single
        $this->selectors['single'] =
            new SLP_Power_Category_Selector( array(
                'label'         => __('Single Drop Down','slp-power'),
                'value'         => 'single',
                'selected'      => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'single' ),
                'ui_builder'    => array( $this , 'create_string_for_single_selector' )
            ));

        // Cascading
        $this->selectors['cascade'] =
            new SLP_Power_Category_Selector( array(
                'label'         => __('Cascading Drop Down','slp-power'),
                'value'         => 'cascade',
                'selected'      => ( $this->slplus->SmartOptions->show_cats_on_search->value === 'cascade' ),
                'ui_builder'    => array( $this , 'create_string_for_cascade_selector' )
            ));


        /**
         * Filter to augment the default category selector types.
         *
         * @used-by \SLP_Premier_Category::add_category_selectors
         *
         * @params  SLP_Power_Category_Selector[]  $this->selectors
         *
         * @return SLP_Power_Category_Selector[]
         */
        $this->selectors = apply_filters( 'slp_power_default_selectors' , $this->selectors );
    }

    /**
     * Create a drop down object for all items with a parent category as specified.
     *
     * Recursive, calls the same method for each child.
     *
     * @param string $parent_cat the parent category (int)
     * @param mixed $grandparent_cat the grandparent category (int) or null
     * @return null
     */
    private function create_dropdown_for_category($parent_cat,$grandparent_cat=null) {
        if (!ctype_digit($parent_cat)) { return; }

        $categories = get_categories(
            array(
                'hierarchical'      => false,
                'hide_empty'        => false,
                'orderby'           => 'name',
                'parent'            => $parent_cat,
                'taxonomy'          => SLPlus::locationTaxonomy
            )
        );

        if (count($categories)<=0) { return; }

        $dropdownItems = array();
        $dropdownItems[] =
            array(
                'label' => $this->slplus->SmartOptions->show_option_all->value,
                'value' => ''
            );
        foreach ($categories as $category) {
            $dropdownItems[] =
                array(
                    'label' => $category->name,
                    'value' => $category->term_id
                );
            $this->create_dropdown_for_category($category->term_id,$parent_cat);
        }
        $this->categoryDropDowns[] = array(
            'grandparent' => $grandparent_cat,
            'parent'    => $parent_cat,
            'id'        => 'catsel_'.$parent_cat,
            'name'      => 'catsel_'.$parent_cat,
            'items'     => $dropdownItems,
            'onchange'  =>
                "jQuery('#children_of_{$parent_cat}').children('div.category_selector.child').hide();" .
                "childDD='#children_of_'+jQuery('option:selected',this).val();" .
                "jQuery(childDD).show();" .
                "jQuery(childDD+' option:selected').prop('selected',false);" .
                "jQuery(childDD+' option:first').prop('selected','selected');" .
                "if (jQuery('option:selected',this).val()!=''){jQuery('#cat').val(jQuery('option:selected',this).val());}" .
                "else{jQuery('#cat').val(jQuery('#catsel_{$grandparent_cat} option:selected').val());}"
        );
        return;
    }

    /**
     * Create a cascading drop down array for location categories.
     *
     */
    private function create_string_cascading_category_dropdown() {

        // Build the category drop down object array, recursive.
        //
        $this->create_dropdown_for_category('0');

        // Create the drop down HTML for each level
        //
        if (count($this->categoryDropDowns) > 0) {
            $this->categoryDropDowns = array_reverse($this->categoryDropDowns);
            $HTML =
                "<div id='tagalong_cascade_dropdowns'>" .
                '<input type="hidden" id="cat" name="cat" value=""/>'       .
                $this->create_string_nested_dropdown_divs($this->categoryDropDowns[0]['parent']) .
                '</div>'
            ;
        } else {
            $HTML = '';
        }

        return $HTML;
    }

    /**
     * Create the div-wrapped HTML with label for the category selector.
     *
     * @param  string $dropdown_html
     * @param  int    $term_id
     * @return string
     */
    public function create_string_dropdown_div( $dropdown_html , $term_id = 0 ) {
        if ( empty( $dropdown_html ) ) { return ''; }

        if ( ! empty( $this->slplus->SmartOptions->label_category->value ) ) {
            $label_html =
                '<label for="cat">' .
                    $this->slplus->SmartOptions->label_category->value .
                '</label>'
                ;
        } else {
            $label_html = '<label for="cat">&nbsp;</label>';
        }

        $term_slug = ( $term_id > 0 ) ? '_' . $this->addon->stores_taxonomy->get_term( $term_id , 'slug' ) : '';

        return
            "<div id='tagalong_category_selector{$term_slug}' class='tagalong_category_selector tagalong_term${term_slug} search_item'>" .
                $label_html .
                $dropdown_html .
            '</div>'
            ;

    }

    /**
     * Create the HTML output string for the single selector type.
     *
     * @used-by create_default_selectors()
     *
     * @param boolean $has_label
     *
     * @return string
     */
    private function create_string_for_cascade_selector( $has_label ) {
        return $this->create_string_dropdown_div( $this->create_string_cascading_category_dropdown() );
    }

    /**
     * Create the HTML output string for the single selector type.
     *
     * Called via the call_user_func() in get_html().
     *
     * @used-by create_default_selectors()
     *
     * @param boolean $has_label
     *
     * @return string
     */
    private function create_string_for_single_selector( $has_label ) {
        return
            $this->create_string_dropdown_div(
                wp_dropdown_categories(
                    array(
                        'echo'              => 0,
                        'hierarchical'      => 1,
                        'depth'             => 99,
                        'hide_empty'        => $this->slplus->SmartOptions->hide_empty->is_true,
                        'hide_if_empty'     => true,
                        'orderby'           => 'NAME',
                        'show_option_all'   => $this->slplus->SmartOptions->show_option_all->value,
                        'taxonomy'          => SLPlus::locationTaxonomy
                    )
                )
            );
    }

    /**
     * Create nested divs with the drop down menus within.
     *
     * @param string $parent_category_id (int)
     * @return string
     */
    private function create_string_nested_dropdown_divs($parent_category_id) {
        $this->node_level++;
        $HTML = '';
        foreach ($this->categoryDropDowns as $dropdown) {
            if ($dropdown['parent']===$parent_category_id) {
                $HTML .=
                    "<div id='div_{$dropdown['id']}' name='div_{$dropdown['id']}' class='category_selector parent' >" .
                    $this->slplus->Helper->createstring_DropDownMenu(
                        array(
                            'id'        => $dropdown['id'],
                            'name'      => $dropdown['name'],
                            'onchange'  => $dropdown['onchange'],
                            'items'     => $dropdown['items']
                        )
                    ) .
                    '</div>'
                ;
                foreach ($dropdown['items'] as $item) {
                    $HTML .= $this->create_string_nested_dropdown_divs($item['value']);
                }
            }
        }

        if (!empty($HTML)) {
            $parent_or_child = (($this->node_level === 1) ? 'parent':'child');
            $HTML =
                "<div id='children_of_{$parent_category_id}' class='category_selector {$parent_or_child} level_{$this->node_level}'>" .
                $HTML .
                '</div>';
        }

        $this->node_level--;
        return $HTML;
    }

    /**
     * Return the selectors as a named array or object array.
     *
     * @used-by \SLP_Power_Options::get_show_cats_on_search_items
     *
     * @return array
     */
    public function get_selectors() {
        $return_array = array();
        foreach ( $this->selectors as $slug => $selector ) {
	        $return_array[] = (array) $selector;
        }
        return $return_array;
    }

    /**
     * Return the UI HTML for the named selector.
     *
     * @used-by \SLP_Power_UI::create_string_category_selector
     *
     * @param string $selector_slug
     * @param boolean $has_label
     *
     * @return mixed
     */
    public function get_html( $selector_slug , $has_label ) {
        if (
            isset( $this->selectors[ $selector_slug ]                   )     &&
            isset( $this->selectors[ $selector_slug ]->ui_builder       )     &&
            is_callable( $this->selectors[ $selector_slug ]->ui_builder )
        ) {
            return call_user_func( $this->selectors[$selector_slug]->ui_builder , $has_label );
        }
        return '';
    }

}