<?php
require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

/**
 * Holds the UI-only code.
 *
 * This allows the main plugin to only include this file in the front end
 * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
 *
 * @property        SLPPower                        $addon
 * @property-read	SLP_Power_Category_Walker_Legend 	$LegendWalker				Connect the legend walker object here.
 * @property        string                          $location_property              The location field/property that is being processed as a valid redirect.
 * @property        string                          $property_shorthand             The shorthand version of the property name.
 */
class SLP_Power_UI extends SLP_BaseClass_UI {
	public  $addon;
	public $js_requirements = array( 'google_maps' );
	public $js_settings;
	private $LegendWalker;
	public  $location_property;
	public  $property_shorthand;

	public function __construct( $options = array() ) {
		/** @var SLPlus $slplus_plugin */
		global $slplus_plugin;
		$this->addon = $slplus_plugin->addon( 'power' );		parent::__construct( $options );
	}

	/**
	 * Add the specified discrete field.
	 *
	 * @param string $property
	 * @param string $field_name
	 *
	 * @return string
	 */
	private function add_discrete_filter( $property , $field_name ) {
		if ( empty( $_GET[ $property ] ) ) {
			return '';
		}
		return sprintf( "<input type='hidden' id='%s' name='%s' value='%s'>" , $field_name, $field_name, $_GET[$property] );
	}

	/**
	 * Add the discrete filter hidden fields if needed.
	 *
	 * Used for directory builder discrete filter by city/state/country on landing page if Experience is not active.
	 *
	 * @param string $layout
	 *
	 * @return string
	 */
	public function add_discrete_filter_hidden_fields( $layout ) {
		if ( $this->slplus->AddOns->get( 'slp-experience' , 'active' ) ) {
			return $layout;
		}

		$hidden_elements  = $this->add_discrete_filter( 'sl_city' , 'addressInputCity' );
		$hidden_elements .= $this->add_discrete_filter( 'sl_state', 'addressInputState' );
		$hidden_elements .= $this->add_discrete_filter( 'sl_country', 'addressInputCountry' );

		if ( !empty( $hidden_elements ) ) {
			$layout_placeholder = '[slp_search_element add_on location="very_top"]';
			if ( stripos( $layout , $layout_placeholder ) !== false ) {
				$layout = str_ireplace( $layout_placeholder, $layout_placeholder . $hidden_elements, $layout );
			} else {
				$layout = $hidden_elements . $layout;
			}
		}

		return $layout;
	}

	/**
	 * Add UI specific hooks and filters.
	 *
	 * Overrides the base class which is just a stub placeholder.
	 *
	 * @uses \SLP_Power_UI::add_category_selectors
	 */
	public function add_hooks_and_filters() {
		parent::add_hooks_and_filters();

		add_filter( 'shortcode_slp_searchelement'   , array( $this , 'process_slp_search_element_shortcode' )   );
		add_filter( 'shortcode_storepage'           , array( $this , 'filter_ProcessStorePage'      )           );
		add_filter( 'slp_js_options'                , array( $this , 'modify_js_options'            ) , 20      );
		add_filter( 'slp_layout'                    , array( $this , 'filter_AddLegend'             ) , 95      );
		add_filter( 'slp_searchlayout'              , array( $this, 'add_category_selectors' ), 999 );
		add_filter( 'slp_searchlayout'              , array( $this , 'add_discrete_filter_hidden_fields' ) , 10 );
		add_filter( 'slp_shortcode_atts'            , array( $this , 'extend_main_shortcode'        ) , 95 , 3  );
		add_filter( 'the_title'                     , array( $this , 'modify_PageTitle'             ) , 20 , 2  );

		if ($this->addon->options['tag_selector'] !== 'none') {
			add_filter( 'slp_searchlayout' , array( $this , 'add_tag_selector_to_search_form' ) , 999 );
		}

		add_shortcode( 'slp_category'   , array( $this , 'process_tagalong_shortcode' ) );
		add_shortcode( 'slp_directory'  , array( $this , 'slp_directory_shortcode'   ) );
		add_shortcode( 'tagalong'       , array( $this , 'process_tagalong_shortcode' ) );

		$this->create_object_pages();
	}

	/**
	 * Setup the legend category walker object.
	 */
	private function create_CategoryWalkerForLegend() {
		if ( isset( $this->LegendWalker ) ) {
			return;
		}
		$this->LegendWalker = new SLP_Power_Category_Walker_Legend();
	}

	/**
	 * Create and attach the admin processing object.
	 */
	private function create_object_pages() {
		if ( ! $this->addon->using_pages ) {
			return;
		}
		$this->pages = SLP_Power_Pages_UI::get_instance();
		$this->pages->ui = $this;
		$this->pages->add_hooks_and_filters();
	}

	/**
	 * Add our custom category selection div to the search form.
	 *
	 * @uses \SLP_Power_Category_Selector_Manager::get_html
	 *
	 * @param boolean $has_label    Whether or not we should show the label.
	 *
	 * @return string the HTML for this div appended to the other HTML
	 */
	private function create_string_category_selector( $has_label ) {

		// Only With Category shortcode.
		//
		if ( ! empty( $this->slplus->options['only_with_category'] ) ) {
			require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
			$category_id_list = $this->slplus->Power_Category_Manager->convert_category_name_list_to_id_list( $this->slplus->options['only_with_category'] );
			$this->slplus->SmartOptions->show_cats_on_search->value = 'only_with_category';
		} else {
			$category_id_list = '';
		}

		// Only With Category is set
		//
		if ( $this->slplus->SmartOptions->show_cats_on_search->value === 'only_with_category' ) {

			if ( ! empty( $category_id_list ) ) {
				$HTML = "<input type='hidden' name='cat' id='cat' " . "value='{$category_id_list}' " . "textvalue='{$this->slplus->options['only_with_category']}' " . '/>';
			} else {
				$HTML = "<!-- only_with_category term {$this->slplus->options['only_with_category']}  does not exist -->";
			}

		// All other processing
		//
		} else {

			$cat_selector = SLP_Power_Category_Selector_Manager::get_instance();
			$HTML = $cat_selector->get_html( $this->slplus->SmartOptions->show_cats_on_search->value , $has_label );

		}

		return $HTML;
	}


	/**
	 * Create the LegendHTML String.
	 *
	 * @return string
	 */
	private function create_string_legend_html() {
		$this->create_CategoryWalkerForLegend();

		$options =
			array(
				'echo'              => 0,
				'hierarchical'      => 0,
				'depth'             => 99,
				'hide_empty'        => $this->slplus->SmartOptions->hide_empty->is_true,
				'style'             => 'none',
				'taxonomy'          => 'stores',
				'walker'            => $this->LegendWalker
			);

		return '<div id="tagalong_legend"><div id="tagalong_list">' . wp_list_categories( $options ) . '</div></div>';
	}

	/**
	 * Puts the tag list on the search form for users to select tags by drop down.
	 *
	 * @param string[] $tags tags as an array of strings
	 * @param boolean $showany show the any pulldown entry if true
	 * @return string
	 */
	function createstring_SearchFormTagDropdown($tags, $showany = false) {

		$HTML = "<select id='tag_to_search_for' >";

		// Show Any Option (blank value)
		//
		if ($showany) {
			$HTML.= "<option value=''>" . $this->addon->options[ 'tag_dropdown_first_entry' ] . '</option>';
		}

		foreach ($tags as $selection) {
			$clean_selection = preg_replace('/\((.*)\)/', '$1', $selection);
			$HTML.= "<option value='$clean_selection' ";
			$HTML.= (preg_match('#\(.*\)#', $selection)) ? " selected='selected' " : '';
			$HTML.= ">$clean_selection</option>";
		}
		$HTML.= "</select>";
		return $HTML;
	}

	/**
	 *  Puts the tag list on the search form for users to select tags by radio buttons
	 *
	 * @param string[] $tags tags as an array of strings
	 * @param bool $showany how the any pulldown entry if true
	 * @return string
	 */
	function createstring_SearchFormTagRadioButtons($tags, $showany = false) {
		$HTML = '';
		$thejQuery = "onClick='" .
		             "jQuery(\"#tag_to_search_for\").val(this.value);" .
		             (
		             $this->slplus->is_CheckTrue( $this->addon->options['tag_autosubmit'] ) ?
			             "jQuery(\"#searchForm\").submit();" :
			             ''
		             ) .
		             "'"
		;
		$oneChecked = false;
		$hiddenValue = '';
		foreach ($tags as $tag) {
			$checked = false;
			$clean_tag = preg_replace("/\((.*?)\)/", '$1', $tag);
			if (
				($clean_tag != $tag) ||
				(!$oneChecked && !$showany && ($tag == $tags[0]))
			) {
				$checked = true;
				$oneChecked = true;
				$hiddenValue = $clean_tag;
			}
			$HTML .=
				'<span class="slp_checkbox_entry">' .
				"<input type='radio' name='tag_to_search_for' value='$clean_tag' " .
				$thejQuery .
				($checked ? ' checked' : '') . ">" .
				$clean_tag .
				'</span>'
			;
		}

		if ($showany) {
			$HTML .=
				'<span class="slp_radio_entry">' .
				"<input type='radio' name='tag_to_search_for' value='' " .
				$thejQuery .
				($oneChecked ? '' : 'checked') . ">" .
				"Any" .
				'</span>';
		}

		// Hidden field to store selected tag for processing
		//
		$HTML .= "<input type='hidden' id='tag_to_search_for' name='hidden_tag_to_search_for' value='$hiddenValue'>";

		if (!empty($HTML)) {
			$HTML = '<div class="slp_checkbox_group">' . $HTML . '</div>';
		}

		return $HTML;
	}

	/**
	 * Create the tag input div wrapper and label.
	 *
	 * @param string $innerHTML the input field portion of the div.
	 * @return string the div and label tags added.
	 */
	function createstring_TagDiv( $innerHTML ) {
		return
			'<div id="search_by_tag" class="search_item">' .
			'<label for="tag_to_search_for">' .
			$this->addon->options['tag_label'] .
			'</label>' .
			$innerHTML .
			'</div>';
	}

	/**
	 * Add our custom tag selection div to the search form.
	 *
	 * @return string the HTML for this div appended to the other HTML
	 */
	private function createstring_TagSelector() {
		if ( isset( $this->slplus->SmartOptions->allow_tag_in_url ) && $this->slplus->SmartOptions->allow_tag_in_url->is_true ) {
			$this->slplus->options['only_with_tag'] = ! empty( $_REQUEST['only_with_tag'] ) ? $_REQUEST['only_with_tag'] : '';
		}

		// Only With Tag shortcode.
		// Set tag selector to hidden.
		//
		if (!empty($this->slplus->options['only_with_tag'])) {
			$this->addon->options['tag_selector'] = 'hidden';
			$this->addon->options['tag_selections'] = sanitize_title($this->slplus->options['only_with_tag']);
		}

		// No Tag List For Drop Down
		// Set tag selector to textinput
		//
		if ($this->addon->options['tag_selector'] === 'dropdown') {
			if (
				isset( $this->slplus->options['tags_for_pulldown' ]) ||
				isset( $this->slplus->options['tags_for_dropdown' ])
			) {
				$this->addon->options['tag_selections'] = $this->slplus->options['tags_for_pulldown'] . $this->slplus->options['tags_for_dropdown'];
			}

			// No pre-selected tags, use input box
			//
			if ($this->addon->options['tag_selections'] === '') {
				$this->addon->options['tag_selector'] = 'textinput';
				$this->addon->options['tag_selections'] = (isset($this->slplus->options['only_with_tag'])) ? $this->slplus->options['only_with_tag'] : '';
			}
		}

		// Process the category selector type
		//
		switch ($this->addon->options['tag_selector']) {

			// None
			//
			case 'none':
				$HTML = '';
				break;

			// Hidden Field
			//
			case 'hidden':
				$value = ! empty( $this->addon->options['tag_selections'] ) ? $this->addon->options['tag_selections'] : '';
				$text_value = !empty( $this->slplus->options['only_with_tag'] ) ? $this->slplus->options['only_with_tag'] : '';
				$HTML = "<input type='hidden' name='tag_to_search_for' id='tag_to_search_for' value='{$value}' textvalue='{$text_value}' />";
				break;

			// Text Input
			//
			case 'textinput':
				$HTML = $this->createstring_TagDiv(
					"<input type='text' " .
					"name='tag_to_search_for' " .
					"id='tag_to_search_for' size='50' " .
					"value='{$this->addon->options['tag_selections']}' " .
					"/>"
				);
				break;

			// Radio Button Selector for Tags on UI
			//
			case 'radiobutton':
				$tag_selections = explode(",", $this->addon->options['tag_selections']);
				if (count($tag_selections) > 0) {
					$HTML = $this->createstring_SearchFormTagRadioButtons(
						$tag_selections, $this->slplus->is_CheckTrue( $this->addon->options['tag_show_any'] )
					);
					$HTML = $this->createstring_TagDiv($HTML);
				} else {
					$HTML = '';
				}
				break;

			// Drop Down Style Selector for Tags on UI
			//
			case 'dropdown':
				$tag_selections = explode(",", $this->addon->options['tag_selections']);
				if (count($tag_selections) > 0) {
					$HTML = $this->createstring_SearchFormTagDropdown(
						$tag_selections, $this->slplus->is_CheckTrue( $this->addon->options['tag_show_any'] )
					);
					$HTML = $this->createstring_TagDiv($HTML);
				} else {
					$HTML = '';
				}
				break;

			default:
				$HTML = '';
				break;
		}

		return $HTML;
	}

	/**
	 * Extends the main SLP shortcode approved attributes list, setting defaults.
	 *
	 * This will extend the approved shortcode attributes to include the items listed.
	 * The array key is the attribute name, the value is the default if the attribute is not set.
	 *
	 * @usedby add_hooks_and_filters() via the slp_shortcode_atts filter
	 *
	 * @param   array   $valid_attributes       current list of approved attributes
	 * @param   array   $attributes_in_use      what the user supplied
	 * @param   array   $content                stuff between the shortcode and end shortcode (nothing)
	 *
	 * @return array
	 */
	public function extend_main_shortcode( $valid_attributes, $attributes_in_use , $content ) {
		return array_merge(
			array(
				'only_with_category'        => null,
				'only_with_tag'             => null,
				'tags_for_pulldown'         => null,
				'tags_for_dropdown'         => null,
			), $valid_attributes
		);
	}

	/**
	 * Add the Tagalong shortcode processing to whatever filter/hook we need it latched on to.
	 *
	 * The [tagalong] shortcode, used here, is setup in slp_init.
	 */
	function filter_AddLegend($layoutString) {
		add_shortcode( 'slp_category'   , array( $this , 'process_tagalong_shortcode' ) );
		add_shortcode( 'tagalong'       , array( $this , 'process_tagalong_shortcode' ) );
		return do_shortcode($layoutString);
	}

	/**
	 * Add Tagalong category selector to search layout.
	 *
	 * @used-by \SLP_Power_UI::add_hooks_and_filters
	 */
	public function add_category_selectors($layout) {
		if ( $this->slplus->SmartOptions->hide_search_form->is_true ) return $layout;

		if ( ! empty ( $this->slplus->options['only_with_category'] ) ) {
			$this->slplus->SmartOptions->show_cats_on_search->value = '1';
		}
		if ( empty( $this->slplus->SmartOptions->show_cats_on_search->value ) ) {
			return $layout;
		}

		if (preg_match('/\[slp_search_element\s+.*dropdown.*="category".*\]/i',$layout)) {
			return $layout;
		}

		return $layout . '[slp_search_element dropdown_with_label="category"]';
	}

	/**
	 * Perform extra search form element processing.
	 *
	 * @param mixed[] $attributes
	 * @return mixed[]
	 */
	function filter_ProcessStorePage($attributes) {
		$this->addon->set_LocationCategories();

		// No categories set?  Get outta here...
		//
		if ( count( $this->addon->current_location_categories ) < 1 ) { return $attributes; }

		foreach ($attributes as $name=>$value) {

			switch (strtolower($name)) {

				case 'field':
					switch ($value) {
						case 'iconarray':
							return array(
								'hard_coded_value' => $this->addon->create_string_icon_array()
							);
							break;

						default:
							break;
					}
					break;

				default:
					break;
			}
		}

		return $attributes;
	}

	/**
	 * Is the current page request a valid directory request?
	 *
	 * @return bool true if the request is a valid directory link.
	 */
	public function is_a_valid_directory_redirect() {

		// Not a valid redirect if not calling the initial csl_ajax_onload
		// or the nonce is not set properly/
		//
		if ( $this->slplus->SmartOptions->use_nonces->is_true ) {
			if (!isset($_REQUEST['directory_nonce'])) {
				return false;
			}
			if (!wp_verify_nonce($_REQUEST['directory_nonce'], 'show_locations')) {
				return false;
			}
		}

		// Location fields we can process with immediate search results.
		//
		foreach ( $this->addon->location_fields as $location_property => $property_shorthand ) {
			if ( isset( $_REQUEST[$location_property] )  && ! empty( $_REQUEST[$location_property] ) ) {
				$this->location_property    = $location_property;
				$this->property_shorthand   = $property_shorthand;
				return true;
			}
		}

		return false;
	}

	/**
	 * Modify the HTML <title> tag page title of the current page.
	 *
	 * @param string $title
	 * @param string $sep
	 * @param int    $sep_pos
	 *
	 * @return mixed
	 */
	public function modify_HTMLPageTitle( $title , $sep , $sep_pos ) {
		if ( has_shortcode( $title , 'slp_directory' ) ) {
			$new_title = do_shortcode(html_entity_decode($title));
		} else {
			$new_title = $title;
		}
		return $new_title;
	}

	/**
	 * Modify the slplus.options object going into SLP.js
	 *
	 * @used-by \SLP_UI::localize_script
	 *
	 * @triggered-by filter slp_js_options
	 *
	 * @param   array $options
	 *
	 * @return  array
	 */
	function modify_js_options( $options ) {
		$my_options = array();

		// If we have a valid directory link,
		// tweak the initial search form processor to push out results
		// immediately for the specified data match.
		//
		if ( $this->is_a_valid_directory_redirect() ) {
			$my_options['immediately_show_locations'         ]   = '1';
			$my_options['ignore_radius'                      ]   = '1';
			$my_options['use_sensor'                         ]   = '0';
			$my_options['no_homeicon_at_start'               ]   = '1';
			$my_options[$this->property_shorthand.'_selector']   = 'hidden';
			$my_options[$this->property_shorthand            ]   = $_REQUEST[$this->location_property];
		}
		$my_options['use_same_window'] = $this->addon->options[ 'prevent_new_window' ];

		// Lowest Priority On Left
		return array_merge( $options, $this->addon->options, $my_options );
	}

	/**
	 * Modify the page title of the current page.
	 *
	 * @param string $title
	 * @param int    $id
	 *
	 * @return mixed
	 */
	public static function modify_PageTitle( $title , $id = 0 ) {
		if ( has_shortcode( $title , 'slp_directory' ) ) {
			$new_title = do_shortcode($title);
		} else {
			$new_title = $title;
		}
		return $new_title;
	}

	/**
	 * Add tags selector to search layout if it is not already part of the layout.
	 *
	 * @param $layout
	 * @return string
	 */
	public function add_tag_selector_to_search_form($layout) {
		if (
			preg_match('/\[slp_search_element\s+.*dropdown_with_label="tag".*\]/i', $layout) ||
			preg_match('/\[slp_search_element\s+.*selector_with_label="tag".*\]/i', $layout)
		) {
			return $layout;
		}
		return $layout . '[slp_search_element selector_with_label="tag"]';
	}


	/**
	 * Perform extra search form element processing.
	 *
	 * @param mixed[] $attributes
	 * @return mixed[]
	 */
	public function process_slp_search_element_shortcode( $attributes ) {

		foreach ($attributes as $name => $value) {

			$new_name = strtolower($name);

			switch ( $new_name ) {

				// dropdown_with_label="category"
				// dropdown_with_label="tag"
				// selector_with_label="category"
				// selector_with_label="tag"
				//
				case 'dropdown_with_label':
				case 'selector_with_label':
				case 'dropdown':
				case 'selector':
					$has_label = ( strpos( $new_name, '_with_label' ) !== false );
					switch ($value) {
						case 'category':
							return array(
								'hard_coded_value' =>
									! empty( $this->slplus->SmartOptions->show_cats_on_search->value )    ?
										$this->create_string_category_selector( $has_label )          :
										''
							);

						case 'tag':
							if ( $new_name === 'dropdown_with_label' ) {
								$this->addon->options['tag_selector'] = 'dropdown';
							}
							return array(
								'hard_coded_value' => $this->createstring_TagSelector()
							);
					}
					break;
			}
		}

		return $attributes;
	}

	/**
	 * Process the Tagalong shortcode
	 */
	public function process_tagalong_shortcode($atts) {
		$html = '';

		if (is_array($atts)) {
			$theKeys = array_map('strtolower',$atts);
			foreach ( $theKeys as $key ) {
				switch ( $key ) {
					case 'legend':
						$html .= $this->create_string_legend_html();
						break;
				}
			}
		}
		return $html;
	}

	/**
	 * Create the SLPDirectory Shortcode Processor object.
	 */
	public function slp_directory_shortcode( $attributes, $content, $name ) {
		$slp_directory_shortcode = new SLP_Power_Directory_UI_Shortcode_slp_directory(
			array(
				'addon'     => $this->addon,
				'attributes'=> (array) $attributes,
				'content'   => $content,
				'name'      => $name
			)
		);

		$output = $slp_directory_shortcode->createstring_ShortcodeOutput();
		$slp_directory_shortcode->remove_sql_filter();

		return $output;
	}
}