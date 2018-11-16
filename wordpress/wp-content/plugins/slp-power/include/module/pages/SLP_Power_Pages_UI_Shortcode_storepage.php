<?php
defined( 'ABSPATH' ) || exit;

/**
 * UI pages stuff on loaded of using pages is enabled.
 *
 * [storepage] is used to render live details about a location, typically on a Store Page.
 * Used by the Pages templates.
 */
class SLP_Power_Pages_UI_Shortcode_storepage extends SLPlus_BaseClass_Object {
	private $no_map = false;

    /**
     * Create an url hyperlink base on the type field provided.
     *
     * @param string $field
     * @param string $title
     * @param string $data
     *
     * @return string
     */
    private function create_string_hyperlink( $field , $title='' , $data ) {
        if ( is_null( $field ) ) {
            return '';
        }
        if ( empty( $data ) ) {
            return '';
        }
        if ( empty( $title ) ) {
        	$title = $data;
        }
        return sprintf( '<a href="%s" target="store_locator_plus">%s</a>' , esc_url( $data ) , $title );
    }

	/**
	 * Wrap output with tag and class if tag is specified.
	 *
	 * @param string $html
	 * @param string $tag
	 * @param string $class
	 *
	 * @return string
	 */
    private function wrap_with_tag( $html , $tag , $class  ) {
	    if ( ! empty( $tag ) ) {
	    	$class = ! empty( $class ) ? " class='{$class}'" : '';
		    $output = sprintf( '<%s%s>%s</%s>' , $tag , $class, $html , $tag );
	    } else {
		    $output = $html;
	    }
	    return $output;
    }

    /**
     * Create an image string if the field and content are valid.
     *
     * @param string $field
     * @param string $title
     * @param string $data
     *
     * @return string
     */
    private function create_string_image( $field , $title, $data ) {
        if ( is_null( $this->slplus->currentLocation->id ) ) { return ''; }

        if ( is_null( $field ) ) {
            return '';
        }
        if ( empty( $data ) ) {
            return '';
        }
        if ( empty( $title ) ) {
            $title = $this->slplus->currentLocation->store;
        }
        $title = sprintf( 'title="%s" alt="%s"' , $title , $title );

        return  sprintf( '<img src="%s" class="store_page_image field-%s" %s>' , esc_url( $data ) , $field , $title );
    }

    /**
     * Create an email hyperlink base on the type field provided.
     *
     * @param string $field
     * @param string $data
     *
     * @return string
     */
    private function create_string_mailto( $field, $data  ) {
        if ( is_null( $field ) ) {
            return '';
        }
        if ( empty( $data ) ) {
            return '';
        }
        return sprintf( '<a href="%s" target="store_locator_plus">%s</a>' , 'mailto:' . $data , $data );
    }

    /**
     * Generate a map for the current location.
     */
    private function generate_map() {
        if ( is_null( $this->slplus->currentLocation->id ) || $this->no_map ) { return ''; }

        $map_style = 'style="' .
                     "height: {$this->slplus->options_nojs[ 'map_height' ]}{$this->slplus->options_nojs[ 'map_height_units' ]}; " .
                     "width: {$this->slplus->options_nojs[ 'map_width' ]}{$this->slplus->options_nojs[ 'map_width_units' ]}; " .
                     '"';

        $html = "<div id='storepage-{$this->slplus->currentLocation->id}-map' title='location_map' data-location_id='{$this->slplus->currentLocation->id}' class='map-canvas-box' {$map_style}>" .
                "<div id='map-canvas-{$this->slplus->currentLocation->id}' class='map-canvas'></div>" .
                "</div>";

        return $html;
    }

    /**
     * Return Post value for the linked post.
     *
     * @param $value
     *
     * @return string
     */
    private function get_post_data( $value ) {
        $args = array(
            'echo'      => false,
            'post_type' => SLPLus::locationPostType,
            'include'   => array( $this->slplus->currentLocation->linked_postid ),
        );
        $page = get_pages( $args );

        if ( is_array( $page ) && isset( $page[0] ) ) {

            /**
             * @var WP_Post $the_post
             */
            $the_post = $page[0];

            switch ( $value ) {

                case 'post_content':
                    $cleaned_content = preg_replace( '/post\W+post_content\W+/i' , '' , $the_post->post_content ); // try not to loop this
                    return do_shortcode( $cleaned_content );

                case 'permalink':
                    return get_permalink( $the_post->ID );

                default:
                    return isset( $the_post->$value ) ? $the_post->$value : '';
            }
        }

        return '';
    }

    /**
     * Manage the shortcode
     *
     * @used-by \SLP_Power_Pages_UI::add_hooks_and_filters
     *
     * @param array     $attributes     named array of attributes set in shortcode
     * @param string    $content        the existing content that we will modify
     *
     * @return string the modified HTML content
     */
    function process_shortcode( $attributes , $content = null ) {
    	$defaults = array(
		    'class'  => '',
    		'no_map' => false,
		    'tag'    => '',
	    );

    	$output = $content;

        // Pre-process the attributes.
        //
        // This allows third party plugins to man-handle the process by
        // tweaking the attributes.  If, for example, they were to return
        // array('hard_coded_value','blah blah blah') that is all we would return.
        //
        // FILTER: shortcode_storepage
        //
        $attributes = apply_filters( 'shortcode_storepage' , wp_parse_args( (array) $attributes , $defaults ) );

        // Process the attributes
        //
        $field = null;
        $title = null;
        $type  = '';
        foreach ( $attributes as $key => $value ) {
            $key = strtolower( $key );
            switch ( $key ) {

                // Field attribute: output specified field
                //
                case 'field':
                    if ( ! is_null( $this->slplus->currentLocation->id ) ) {
                        $field  = preg_replace( '/\W/', '', htmlspecialchars_decode( $value ) );
                        $field  = preg_replace( '/^sl_/', '', strtolower( $field ) );
                        $data = $this->slplus->currentLocation->$field;
                        $type = 'data';
                    }
                    break;


                case 'title':
                    $title = $value;
                    break;

                case 'type':
                	$type = $value;
                    break;

                case 'hard_coded_value':
                    $output = $value;
                    $type = '';
                    break;

                case 'map':
                    $output = $this->generate_map();
                    $type = '';
                    break;

                case 'post':
	                $this->no_map = $this->slplus->is_CheckTrue( $attributes[ 'no_map' ] );
                    $output       = $this->get_post_data( $value );
                    $type = '';
                    break;
            }
        }

        // Output type
	    //
	    if ( ! empty( $type ) ) {
		    switch ( $type ) {
			    case 'data':
			    	$output = $data;
			    	break;

			    case 'hyperlink':
				    $output = $this->create_string_hyperlink( $field ,  $title , $data);
				    break;

			    case 'image':
				    $output = $this->create_string_image( $field , $title , $data );
				    break;

			    case 'mailto':
				    $output = $this->create_string_mailto( $field , $data );
				    break;
		    }
	    }

	    if ( ! empty( $output ) && ! empty( $attributes[ 'tag' ] ) ) {
        	$output = $this->wrap_with_tag( $output , $attributes[ 'tag' ] , $attributes[ 'class' ] );
	    }


        return $output;
    }
}
