<?php
defined( 'ABSPATH' ) || exit;

/**
 * Augment the SLP text tables.
 *
 * @var array    text    array of our text modifications key => SLP text manager slug, value = our replacement text
 */
class SLP_Power_Text extends SLPlus_BaseClass_Object {
    private $text;

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
		add_filter('slp_get_text_string', array( $this, 'augment_text_string' ) , 10, 2);
	}

    /**
     * Replace the SLP Text Manager Strings at startup.
     *
     * @param string $text the original text
     * @param string $slug the slug being requested
     *
     * @return string            the new SLP text manager strings
     */
    public function augment_text_string($text, $slug) {
        $this->init_text();

        if (!is_array($slug)) $slug = array( 'general' , $slug );

        if (isset($this->text[$slug[0]]) && isset($this->text[$slug[0]][$slug[1]])) {
            return $this->text[$slug[0]][$slug[1]];
        }

        return $text;
    }

    /**
     * Initialize our text modification array.
     */
    private function init_text() {
        if (isset($this->text)) return;

	    $this->text['description'][ 'ajax_orderby_catcount' ] = __( 'When enabled the results will be ordered by those with the most categories assigned appearing first.', 'slp-power');
	    $this->text['description'][ 'category_url'          ] = __( 'The URL where you want the icon to be linked.', 'slp-power');
	    $this->text['description'][ 'default_icons'         ] = __( 'Do not use custom category destination markers on map, use default Map Settings markers.', 'slp-power');
	    $this->text['description'][ 'hide_empty'            ] = __( 'Hide the empty categories from the category selector. Location Pages in draft mode are considered "empty".','slp-power');
	    $this->text['description'][ 'label_category'        ] = __( 'The label for the category selector.','slp-power');
	    $this->text['description'][ 'map_marker'            ] = __( 'This is the graphic used as the map marker on the map.', 'slp-power');
	    $this->text['description'][ 'medium_icon'           ] = __( 'This graphic appears as the icon or icon array in the result listing or map info bubble.', 'slp-power');
	    $this->text['description'][ 'marker_rank'           ] = __( 'Rank for category (1, 2, 3...) lower numbers have higher precedence for map markers.', 'slp-power');
	    $this->text['description'][ 'show_cats_on_search'   ] = __( 'How to show the category selector on the search form.','slp-power') . ' ' .
	                                                            __( 'No will not show the category selector.','slp-power') . ' ' .
	                                                            __( 'Single shows the category selector as a single drop down with indents if there are parent/children relations.','slp-power') . ' ' .
	                                                            __( 'Cascading shows a single drop down showing parents then children drop downs on an as-needed basis.','slp-power');
	    $this->text['description']['show_icon_array'        ] = __( 'When enabled an array of icons will be created in the below map results and info bubble.', 'slp-power');
	    $this->text['description']['show_legend_text'       ] = __('When enabled text will appear under each category icon in the legend. ','slp-power') .
                                                                __('Add a category legend to the output with the [slp_category legend] shortcode under Settings / View / Layout.','slp-power') ;

	    $this->text['description']['show_option_all'        ] = __( 'If set, prepends this text to select "any category" as an option to the selector. Set to blank to not provide the any selection.','slp-power');
	    $this->text['description']['url_target'             ] = __( 'Target window for the URL (_blank = new window/tab, _self = same window/tab, csa = window/tab named "csa").', 'slp-power');

	    $this->text['general'    ]['auto_refresh'           ] = __( 'Click to auto-refresh.'  , 'slp-power' );
	    $this->text['general'    ]['categories'             ] = __( 'Categories' , 'slp-power' );
	    $this->text['general'    ]['category_url'           ] = __( 'Category URL' , 'slp-power' );
	    $this->text['general'    ]['download'               ] = __( 'Download' , 'slp-power' );
	    $this->text['general'    ]['geocoding'              ] = __( 'Geocoding'  , 'slp-power' );
	    $this->text['general'    ]['geocode_after_import'   ] = __( 'Locations will be geocoding after they are imported.'  , 'slp-power' );
	    $this->text['general'    ]['geocode_in_progress'    ] = __( 'Locations are still being geocoded.'  , 'slp-power' );
	    $this->text['general'    ]['geocoding_location'     ] = __( 'Geocoding Location ID'  , 'slp-power' );
	    $this->text['general'    ]['imports'                ] = __( 'Imports'  , 'slp-power' );
	    $this->text['general'    ]['imported'               ] = __( 'Imported'  , 'slp-power' );
	    $this->text['general'    ]['importing'              ] = __( 'Importing: '  , 'slp-power' );
	    $this->text['general'    ]['import_in_progress'     ] = __( 'Locations are being loaded from the CSV file.'  , 'slp-power' );
	    $this->text['general'    ]['marker_rank'            ] = __( 'Rank'  , 'slp-power' );
	    $this->text['general'    ]['medium_icon'            ] = __( 'Medium Icon'  , 'slp-power' );
	    $this->text['general'    ]['no_active_imports'      ] = __( 'There are no location imports running.'  , 'slp-power' );
	    $this->text['general'    ]['no_active_geocoding'    ] = __( 'Geocoding of locations has finished.'  , 'slp-power' );
	    $this->text['general'    ]['processing'             ] = __( 'Processing'  , 'slp-power' );
	    $this->text['general'    ]['reading_line'           ] = __( 'Reading Line'  , 'slp-power' );
	    $this->text['general'    ]['slp_settings'           ] = __( 'Store Locator Plus Settings'  , 'slp-power' );
	    $this->text['general'    ]['url_target'             ] = __( 'URL Target'  , 'slp-power' );

	    $this->text['label']['ajax_orderby_catcount'        ] = __( 'Order By Category Count'           , 'slp-power');
        $this->text['label']['csv_import'                   ] = __( 'CSV Imports'                       , 'slp-power');
	    $this->text['label']['default_icons'                ] = __( 'Do Not Use Category Markers'       , 'slp-power');
	    $this->text['label']['label_category'               ] = __( 'Category Selector Label'           , 'slp-power');
        $this->text['label']['hide_empty'                   ] = __( 'Hide Empty Categories'             , 'slp-power');
	    $this->text['label']['show_cats_on_search'          ] = __( 'Category Selector'                 , 'slp-power');
	    $this->text['label']['show_icon_array'              ] = __( 'Show Category Icons'               , 'slp-power');
	    $this->text['label']['show_legend_text'             ] = __( 'Add Text To Category Legend'       , 'slp-power');
	    $this->text['label']['show_option_all'              ] = __( 'Category Selector First Entry'     , 'slp-power');

	    $this->text['label']['search_appearance_category_header' ] = __('Category Selector', 'slp-power');

	    $this->text['option_default']['label_category'      ] = __('Category','slp-power');

    }
}