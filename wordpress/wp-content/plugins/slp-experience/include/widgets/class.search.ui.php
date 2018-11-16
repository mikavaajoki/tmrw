<?php
defined( 'ABSPATH' ) || exit;

require_once(SLPLUS_PLUGINDIR . '/include/base_class.userinterface.php');

/**
 * Class SLPWidget_search_UI
 *
 * Handle all the UI elements for the widget.
 *
 * Putting this in a separate include file makes the admin loading "lighter".
 */
class SLPWidget_search_UI extends SLP_BaseClass_UI {

    /**
     * Create the address input box.
     *
     * @param $instance
     * @return string
     */
    function create_string_address_input( $instance ) {
        if (!$instance['use_placeholder']) {
            $html = "<label for='address_input'>{$instance['search_label']}</label>";
            $placeholder = '';
        } else {
            $html ='';
            $placeholder = "placeholder='{$instance['search_label']}' ";
        }
        $address = ( isset( $_REQUEST['widget_address'] ) ? $_REQUEST['widget_address'] : '' );
        $html .=
            "<input type='text' name='widget_address' " .
            "id='address_input_slpw_adv' {$placeholder} " .
            "value='{$address}' " .
            "/>";

        return $html;
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    function render_widget($args, $instance) {
        // Form Processing
        //
        $method = $instance['query'] ? 'GET' : 'POST';

        // Title Block
        //
        $title_block =
            ( ! empty( $instance['title'] ) )                                 ?
                $args['before_title'] . $instance['title'] . $args['after_title'] :
                '' ;

        // Hidden Input
        //
        $hidden_input = '';
        foreach ( $instance['vars'] as $var ) {
            $hidden_input .= "<input type='hidden' name='{$var[0]}' value='{$var[1]}' />";
        }
        $hidden_input .= "<input type='hidden' name='slp_widget[search]' value='1' />";
        $hidden_input .= "<input type='hidden' name='radius' value='{$instance['radius']}' />";


        // User Input
        //
        $user_input = $this->create_string_address_input( $instance );
        $user_input .= "<input type='submit' value='{$instance['button_label']}' class='slp_widget_button' /> ";

        // Render
        //
        print
            $args['before_widget'] .

            $title_block .

            "<form action='{$instance['map_url']}' method='{$method}' >" .

            $hidden_input .

            $user_input .

            '</form>' .

            $args['after_widget']
        ;

    }
}