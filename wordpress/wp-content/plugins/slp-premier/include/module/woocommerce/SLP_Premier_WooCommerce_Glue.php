<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add WooCommerce functionality.
 *
 * @property        SLP_Premier  $addon
 * @property        array       $current_products
 *                                  key = SKU , value = price or null for default
 */
class SLP_Premier_WooCommerce_Glue extends SLPlus_BaseClass_Object {
	public $addon;
    private $current_products = array();

	/**
	 * Initialize.
	 */
    protected function initialize() {
        $this->addon = $this->slplus->addon( 'Premier' );
    }

    /**
     * Add phone and email to the formatted location address.
     *
     * @param $address_fields
     *
     * @return mixed
     */
    function add_details_to_location_address( $address_fields ) {
        $address_fields['phone'] = '<br/>';
        $address_fields['email'] = '<br/>';
        return $address_fields;
    }


	/**
	 * Add extended data fields to support WooCommerce features.
	 */
	public function add_extended_data_fields() {
        if ( version_compare( $this->addon->options[ 'woo_data_version' ] , '4.4.01' , '<=' )  ) {
            $this->add_woo_version_4_4_01_fields();
            $this->addon->options[ 'woo_data_version' ] = $this->addon->version;
            update_option( $this->addon->option_name , $this->addon->options );
        }
	}

    /**
     * Add Location ID To Order Meta
     *
     * @param $id
     * @param $item_id
     * @param $product
     * @param $qty
     * @param $args
     */
    public function add_location_id_to_order_meta( $id, $item_id, $product = null, $qty = null , $args = null ) {
        wc_add_order_item_meta( $item_id, '_location_id', $this->slplus->currentLocation->id );
    }

    /**
     * Add Woo related data fields for version 4.4.01
     */
    private function add_woo_version_4_4_01_fields() {
        $this->slplus->database->extension->add_field(
            __('Woo Products'   ,'slp-premier') , 'varchar' ,
            array(
                'slug'          => 'woo_products'           ,
                'addon'         => $this->addon->short_slug ,
                'display_type'  => 'input'                  ,
                'help_text'     => __( 'Related WooCommerce products and dynamic pricing.' , 'slp-premier' )
            ) ,
            'wait'
        );
        $this->slplus->database->extension->update_data_table( array('mode'=>'force'));
    }

    /**
     * Create the Woo Buy Buttons.
     *
     * @param   array   $marker_data
     *
     * @return  array
     */
    private function create_buy_buttons( $marker_data ) {
        if ( !isset( $marker_data['data']['woo_products'] ) || empty( $marker_data['data']['woo_products'] ) ) { return $marker_data; }

        $marker_data['woo_buy_links'] = $this->create_string_buy_buttons( $marker_data['data']['woo_products'] , $marker_data['id'] );

        return $marker_data;
    }

    /**
     * Generate a series of buy buttons for this location for each associated product.
     *
     * @param   array   $product_list
     * @param   int     $location_id
     *
     * @return string
     */
    private function create_string_buy_buttons( $product_list , $location_id ) {
        $this->set_current_products( $product_list );

        $product_strings = array();
        foreach ( $this->current_products as $sku => $price ) {

            $woo_pid = wc_get_product_id_by_sku( $sku ) ;
            if ( $woo_pid === 0 ) { return ''; }
            $woo_product = wc_get_product( $woo_pid );
            if ( ! is_null( $price ) ) { $woo_product->set_price( $price ); }
            $woo_title = $woo_product->get_title();

            $buy_link =
                sprintf( '<a href="%s" alt="%s" title="%s" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button product_type_%s">%s</a>' ,
                    wc_get_cart_url() . '?location=' . $location_id . '&add-to-cart=' . $woo_product->get_id(),
                    $woo_title,
                    $woo_title,
                    esc_attr( $woo_product->get_id() ),
                    esc_attr( $woo_product->get_sku() ),
                    '1' ,
                    esc_attr( $woo_product->product_type ),
                    esc_html( $woo_product->add_to_cart_text() )
                );

            $product_strings[] =
                '<div class="woo_buy_block">'.
                '<span class="woo_product_title">' . $woo_title                         . '</span>' .
                '<span class="woo_price">'         .  $woo_product->get_price_html()    . '</span>' .
                '<span class="woo_buy_link">'      . $buy_link                          . '</span>' .
                '</div>'
            ;
        }
        return join('<br/>',$product_strings);

    }

    /**
     * Get the product info for the given SKU.
     *
     * @param   string  $sku
     *
     * @return  string
     */
    private function get_product_edit_link( $sku ) {
        $woo_pid = wc_get_product_id_by_sku( $sku ) ;
        if ( $woo_pid === 0 ) { return $sku; }
        $woo_product = wc_get_product( $woo_pid );
        $woo_title = $woo_product->get_title();
        return
            sprintf( '<a href="%s" alt="%s" title="%s">%s</a>' ,
                get_admin_url(null, 'post.php?action=edit&post=' . $woo_pid ),
                $woo_title,
                $woo_title,
                $sku
            );
    }

    /**
     * Modify the AJAX response.
     *
     * @param   array   $marker_data
     * @return  array
     */
    function modify_marker_data( $marker_data ) {
        return $this->create_buy_buttons( $marker_data );
    }

    /**
     * Set this->current_products building a current_products[sku]=price list.
     *
     * @param   string  $sku_list   a comma-separate list of sku:product
     */
    private function set_current_products( $sku_list ) {
        $this->current_products = array();
        if ( empty( $sku_list ) ) { return; }
        $product_list = explode(',',$sku_list);
        foreach ( $product_list as $product ) {
            $product_info = explode( ':' , $product );

            if ( ! empty( $product_info[1] ) ) { $price = sprintf('%0.2f', $product_info[1] ); }
            else { $price = null; }

            $this->current_products[ $product_info[0] ] = $price;
        }
    }

    /**
     * Set product pricing according to what is in the location settings.
     *
     * This is for displaying the cart only.   It is not adjusting the "checkout" price.
     *
     * @param $woo_data
     *
     * @return mixed
     */
    function set_product_price_on_add_to_cart( $woo_data ) {
        if ( ! isset( $_REQUEST['location'] ) || empty( $_REQUEST['location']    ) ) { return $woo_data; }
        if ( ! $this->slplus->currentLocation->isvalid_ID( $_REQUEST['location'] ) ) { return $woo_data; }

        $this->slplus->currentLocation->get_location( $_REQUEST['location'] );
        $woo_data['location_id'] = $this->slplus->currentLocation->id;


        $this->set_current_products( $this->slplus->currentLocation->exdata['woo_products'] );

        // Dynamic Pricing
        //
        if (
              isset  ( $this->current_products[ $woo_data['data']->sku ] ) &&
            ! is_null( $this->current_products[ $woo_data['data']->sku ] ) &&
            ! empty  ( $this->current_products[ $woo_data['data']->sku ] )
        ) {
            $woo_data['data']->set_price( $this->current_products[ $woo_data['data']->sku ] );
            $woo_data['data']->post->post_title =
                $woo_data['data']->post->post_title . "<br/>\n" .
                sprintf( '<span class="product_subtitle in_cart">%s</span>', $this->slplus->currentLocation->store );

        // Standard Pricing
        //
        } else {
            $woo_data['data']->post->post_title =
                $woo_data['data']->post->post_title . "<br/>\n" .
                sprintf( '<span class="product_subtitle in_cart">%s</span>', __( 'Standard Pricing' , 'slp-premier' ) );
                ;
        }

        return $woo_data;
    }

    /**
     * Set the custom pricing within the session data as well.
     *
     * @param array     $session_data
     * @param array     $values
     * @param string    $key            unique cart id
     *
     * @return mixed
     */
    function set_product_price_on_session( $session_data, $values, $key ) {
        if ( ! isset( $session_data['location_id'] ) || empty( $session_data['location_id']    ) ) { return $session_data; }
        if ( ! $this->slplus->currentLocation->isvalid_ID( $session_data['location_id']        ) ) { return $session_data; }

        $this->slplus->currentLocation->get_location( $session_data['location_id'] );

        $this->set_current_products( $this->slplus->currentLocation->exdata['woo_products'] );

        // Dynamic Pricing
        //
        $sku = $session_data['data']->get_sku();
        if (
            isset  ( $this->current_products[ $sku ] ) &&
            ! is_null( $this->current_products[ $sku ] ) &&
            ! empty  ( $this->current_products[ $sku ] )
        ) {
            $session_data['data']->set_price( $this->current_products[ $session_data['data']->sku ] );
            $session_data['data']->post->post_title =
                $session_data['data']->post->post_title . "<br/>\n" .
                sprintf( '<span class="product_subtitle in_cart">%s</span>', $this->slplus->currentLocation->store );

            // Standard Pricing
            //
        } else {
            $session_data['data']->post->post_title =
                $session_data['data']->post->post_title . "<br/>\n" .
                sprintf( '<span class="product_subtitle in_cart">%s</span>', __( 'Standard Pricing' , 'slp-premier' ) );
            ;
        }
        return $session_data;
    }

    /**
     * Show the location details on the email
     *
     * @param $item_id
     * @param $item
     * @param $order
     */
    function show_location_details_in_email( $item_id, $item, $order ) {
        if ( empty(  $item['item_meta']['_location_id'] ) ) {
            return;
        }
        add_filter( 'slp_formatted_location_address_elements' , array( $this , 'add_details_to_location_address' ) );
        $this->slplus->currentLocation->get_location( $item['item_meta']['_location_id'] );
        echo '<br/>' . __( 'Location for service: ' , 'slp-premier' ) . '<br/>';
        echo $this->slplus->currentLocation->get_formatted_address();

        remove_filter( 'slp_formatted_location_address_elements' , array( $this , 'add_details_to_location_address' ) );
    }

    /**
     * Show the Woo product prices on the manage locations table.
     *
     * @param    string $value_to_display the value of this field
     * @param    string $field_name the name of the field from the database
     * @param    string $column_label the column label for this column
     *
     * @return    string
     */
    function show_woo_products_on_manage_locations( $value_to_display, $field_name, $column_label ) {
        if ( $field_name !== 'woo_products' ) { return $value_to_display; }

        $this->set_current_products( $value_to_display );

        $product_strings = array();
        foreach ( $this->current_products as $sku => $price ) {
            if ( is_null( $price ) ) { $price = __('default' , 'slp-premier' ); }
            $product_strings[] = sprintf('<strong>%s</strong> %s' , $this->get_product_edit_link( $sku ), $price );
        }
        return join('<br/>',$product_strings);
    }
}
