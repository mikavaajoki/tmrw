<?php
defined( 'ABSPATH' ) || exit;

/**
 * UI pages stuff on loaded of using pages is enabled.
 *
 * [slp_pages] is used to create a list of Store Pages that are online.
 *
 * @property-read   string[]    $categories_selected    Array of selected categories.
 * @property-read   int         $max_locations          Maximum number of locations to return.
 * @property-read   string      $order                  ASC or DESC
 * @property-read   string      $orderby                Field to sort pages list by.
 * @property-read   array       $page_retrieval_args    The args used to fetch the page list.
 * property-read    boolean     $show_featured          If not set, show all.  If set true = show featured, false = show NOT featured.
 * @property-read   string      $style                  Style of listing
 *
 */
class SLP_Power_Pages_UI_Shortcode_slp_pages extends SLPlus_BaseClass_Object {
    private $categories_selected;
    private $content;
    private $max_locations;
    private $no_map;
    private $order;
    private $orderby;
    private $page_retrieval_args;
    private $show_featured;
    private $style;

	/**
	 * Things we do at the start.
	 */
    public function initalize() {
    	$this->reset();
    }

	/**
	 * Reset our properties to their initial state.
	 *
	 * This is needed when we use a singleton method as the memory space is re-used on each shortcode call.
	 * We need to make sure that process_shortcode starts with a clean slate on each call.
	 */
    private function reset() {
	    $this->categories_selected = array();
	    $this->content = '';
	    $this->max_locations = -1;
	    $this->no_map = false;
	    $this->order = 'ASC';
	    $this->orderby = null;

	    $this->page_retrieval_args;
	    $this->show_featured = null;

	    $this->style = 'summary';
    }

    /**
     * Create the list of Store Pages.
     *
     * @return string   the output
     */
    private function create_page_list() {
        switch ( $this->style ) {
            case 'list':
            case 'bullet':
                return $this->create_bullet_list();

            case 'full':
                return $this->create_full_list();

            case 'summary':
                return $this->create_summary_list();

	        default:
	        	return $this->create_custom_list();
        }
    }

    /**
     * Style 'bullet' - creates a bullet list of location names.
     *
     * @return string
     */
    private function create_bullet_list() {
        $output =
            '<li><h1 class="slp_pages title"><a href="[storepage post=permalink]">[storepage post=post_title]</a></li>'
        ;
        return
	        sprintf( '<div class="%s bullet">' ,  $this->slplus->SmartOptions->pages_directory_wrapper_css_class ) .
            '<ul>'.
            $this->create_string_location_list( $output) .
            '</ul></div>'
            ;

    }

	/**
	 * Create a custom list.
	 */
    private function create_custom_list() {
    	return
		    sprintf( '<div class="%s custom">' ,  $this->slplus->SmartOptions->pages_directory_wrapper_css_class ) .
		    $this->create_string_location_list( html_entity_decode($this->content ) ) .
		    '</div>'
		    ;
    }

    /**
     * Style 'full' - creates a full list of location data.
     *
     * @return string
     */
    private function create_full_list() {
    	$no_map_attr = $this->no_map ? 'no_map=1' : '';
        $output =
            '<h1 class="slp_pages title"><a href="[storepage post=permalink]">[storepage post=post_title]</a></h1>' .
            "[storepage post=post_content {$no_map_attr}]"
            ;
        return
            sprintf( '<div class="%s full">' ,  $this->slplus->SmartOptions->pages_directory_wrapper_css_class ) .
            $this->create_string_location_list( $output ).
            '</div>'
            ;
    }

    /**
     * Style 'summary' - creates a summary list of location data.
     *
     * @return string
     */
    private function create_summary_list() {
        $output =
            '<h1 class="slp_pages title"><a href="[storepage post=permalink]">[storepage post=post_title]</a></h1>' .
            '<p  class="slp_pages address">[storepage field=address]</p>'
            ;
        return
	        sprintf( '<div class="%s summary">' ,  $this->slplus->SmartOptions->pages_directory_wrapper_css_class ) .
            $this->create_string_location_list( $output) .
            '</div>'
            ;
    }

    /**
     * Create the location list string.
     *
     * @param string $output    The output HTMl with shortcodes.
     *
     * @return string
     */
    private function create_string_location_list( $output ) {
        $this->set_page_retrieval_args();
        $page_list = new WP_Query( $this->page_retrieval_args );
        $html = '';
        if ( ! empty( $page_list ) && ! empty( $page_list->posts ) ) {
            foreach ( $page_list->posts as $page ) {
                $location_id = $this->slplus->database->get_Value( array( 'selectslid' , 'wherelinkedpostid' , 'limit_one') , $page->ID );
                if ( is_wp_error( $location_id ) ) {
	                return '';
                }
                $this->slplus->currentLocation->set_PropertiesViaDB( $location_id );

                // Show featured attribute filter
                if ( ! is_null( $this->show_featured ) ) {
                	if ( $this->show_featured && ! $this->slplus->currentLocation->featured ) {
                		continue;
	                }
	                if ( ! $this->show_featured && $this->slplus->currentLocation->featured ) {
	                    continue;
	                }
                }

                $content = do_shortcode( $output );
                $content = apply_filters( 'the_content' , $content );
                $content = str_replace( ']]>', ']]&gt;', $content );

                $addon = $this->slplus->addon( 'Power' );
                if ( ! empty( $addon->options[ 'pages_read_more_text' ] ) ) {
                    $extended_content = get_extended( $content );
                    $stripped_content = wp_strip_all_tags( $extended_content['extended'] , true );
                    if ( empty( $stripped_content ) ) {
                        $content = $extended_content['main'];
                    } else {
                        $content =
                            $extended_content['main'] .
                            sprintf( '<span class="slp_pages more"><a href="%s">%s</a></span>',
                                get_permalink( $page->ID ),
                                $addon->options[ 'pages_read_more_text' ]
                            );
                    }
                }
                $html .= sprintf(
                    '<div id="slp_page_location_%d" class="%s">%s</div>',
                    $location_id,
	                $this->slplus->SmartOptions->pages_directory_entry_css_class,
                    $content
                );
            }
        }

        return $html;
    }

    /**
     * Set filters on which pages to return.
     *
     * @param   string $category_value
     */
    private function filter_by_category( $category_value ) {
        $this->categories_selected = explode(',', $category_value);
    }

    /**
     * Manage the shortcode
     *
     * @used-by \SLP_Power_Pages_UI::add_hooks_and_filters
     *
     * @param array $attributes {
     *      Named array of attributes set in shortcode.
     *
     *      @type string $category  Name of category to include.
     *      @type string $style     Listing style can be 'bullet' or 'summary'. Default 'summary'.
     * }
     *
     * @param string $content the existing content that we will modify
     *
     * @return string the modified HTML content
     */
    public function process_shortcode( $attributes , $content = null ) {
	    SLP_Power_Pages_UI::get_instance()->setup_css_and_js();

	    $this->reset();

        // Process the attributes
        //
	    $smart_options = null;
	    $page_options = array();
        $field = null;
        $title = null;
        $attributes = (array) $attributes;
        foreach ( $attributes as $key => $value ) {
            $key = strtolower( $key );
            switch ( $key ) {

                case 'category':
                    $this->filter_by_category( $value );
                    break;

	            case 'featured':
	            	$this->show_featured = $this->slplus->is_CheckTrue( $value );
	            	break;

	            case 'maxlocations':
                case 'max_locations':
                    $this->set_max_locations( $value );
                    break;

	            case 'nomap':
	            case 'no_map':
		            $this->no_map = $this->slplus->is_CheckTrue( $value );
		            break;

                case 'order':
                    $this->set_order( $value );
                    break;

                case 'orderby':
                case 'order_by':
                    $this->set_orderby( $value );
                    break;


                case 'style':
                    if ( ! empty( $value ) ) {
                        $this->style = $value;
                        if ( $value === 'custom' ) {
                        	$this->content = $content;
                        }
                    }
                    break;

	            default:
	            	if ( is_null( $smart_options ) ) {
	            		/** @var  SLP_SmartOptions $smart_options */
			            $smart_options = SLP_SmartOptions::get_instance();
			            $page_options=$smart_options->get_page_options( 'slp-pages' );
		            }
		            if ( in_array( $key , $page_options ) ) {
						$smart_options->set( $key , $value );
		            }
		            break;

            }
        }

        return $this->create_page_list();
    }

    /**
     * Set the maximum locations to return.
     *
     * @param int $max_locations
     */
    private function set_max_locations( $max_locations ) {
        $max_locations = (int) $max_locations;
        $this->max_locations = ( $max_locations < 1 ) ? -1 : $max_locations;
    }

    /**
     * ASC/DESC sort order of the pages.
     *
     * @param string $order   a* = ascending , d* = descending
     */
    private function set_order( $order ) {
        $this->order = substr( strtolower( $order ) , 0 ) === 'a' ? 'ASC' : 'DESC';
    }

    /**
     * Field to sort the pages by.
     *
     * @param string $orderby   a* = ascending , d* = descending
     */
    private function set_orderby( $orderby ) {
    	if ( empty( $orderby ) ) return;
        $this->orderby = strtolower( $orderby );
        if ( substr( $this->orderby, 0, 4 ) === 'rand') {
            $this->orderby = 'rand';
        }
    }

    /**
     * Set the page retrieval arguments.
     */
    private function set_page_retrieval_args() {
        if ( is_null( $this->page_retrieval_args ) ) {
            $this->page_retrieval_args = array(
                'echo'      => false,
                'post_type' => SLPLus::locationPostType,
                'title_li'  => '',
                'nopaging'  => ( $this->max_locations < 1 ),
                'order'     => $this->order,
                'posts_per_page'    => $this->max_locations,
            );

            if ( ! is_null( $this->orderby ) ) {
                $this->page_retrieval_args['orderby'] = $this->orderby;
            }

            if ( ! empty ( $this->categories_selected ) ) {
                $this->page_retrieval_args['tax_query'] = array(  array(
                    'taxonomy' => SLPlus::locationTaxonomy,
                    'field'    => 'name' ,
                    'terms'    => $this->categories_selected
                    )
                );
            }

        }
    }
}
