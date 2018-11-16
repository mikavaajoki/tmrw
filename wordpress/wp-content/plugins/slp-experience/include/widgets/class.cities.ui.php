<?php

require_once(SLPLUS_PLUGINDIR . '/include/base_class.userinterface.php');

/**
 * Class SLPWidget_cities_UI
 *
 * Handle all the UI elements for the widget.
 *
 * Putting this in a separate include file makes the admin loading "lighter".
 *
 * @property        SLP_Experience      $addon              The widget add-on.
 * @property        array               $current_instance   The current instance values being rendered.
 * @property-read   string              $selected_city      The city selected via the widget search request.
 * @property-read   string              $selected_state     The state selected via the widget search request.
 * @property        SLPlus              $slplus             The base plugin.
 * @property        SLPWidget_cities    $widget
 *
 */
class SLPWidget_cities_UI extends SLP_BaseClass_UI {
    public  $addon;
    private $current_instance;
    private $selected_city;
    private $selected_state;
    public  $slplus;
    public  $widget;

    /**
     * Create the linked state to city drop down menus.
     *
     * @return string
     */
    private function create_state_then_city_dropdowns() {

        /**
         *  @var     SLPWidget_states_UI     $ui_states
         */
        $ui_states = $this->widget->create_ui_object( 'states' );
        add_filter( 'slp_widget_state_dropdown_onchange' , array( $this , 'trigger_cities_ajax_on_state_selection' ) );

        $output = '';
        $output .= $ui_states->create_string_state_dropdown();
        $output .= $this->create_string_city_dropdown();

        return $output;
    }

    /**
     * Build the city dropdown.
     *
     * @return string the HTML for the city dropdown
     */
    function create_string_city_dropdown() {

        $options = '';
        $selected_city = $this->get_selected_city();
        if ( empty( $selected_city ) ) {
            if ( ! empty( $this->current_instance['all_cities_text'] ) ) {
                $options .= '<option value="" >' . $this->current_instance['all_cities_text'] . '</option>';
            }

        } else {
            if ( ! empty( $this->current_instance['all_cities_text'] ) ) {
                $options .= '<option value="" >' . $this->current_instance['all_cities_text'] . '</option>';
            }
            $options .= $this->create_string_city_options();
        }

        $display_style = ( empty( $this->current_instance['all_cities_text'] ) ) ? 'none' : 'block';

        $visibility_style = ( $this->slplus->is_CheckTrue( $this->current_instance['hide_city_until_needed'] ) ? 'hidden' : 'visible' );

        return
            "<div id='widget_city_selector' class='slp_widget_selector' >".
            "<select id='slp_widget[city]' name='slp_widget[city]' style='display:{$display_style}; visibility:{$visibility_style};'>".
                $options .
            '</select>'.
            '</div>'
            ;
    }

    /**
     * Create the option list for the states.
     *
     * @return string
     */
    private function create_string_city_options() {
        add_filter( 'slp_extend_get_SQL' , array( $this->addon , 'select_cities_in_state')	 );
        $sql_parameters = array( $this->get_selected_state() );
        $cities = $this->slplus->database->get_Record( 'select_cities_in_state', $sql_parameters, 0, ARRAY_A , 'get_col' );

        // Selected value
        //
        $selected_city = $this->get_selected_city();

        // If we have city data show it in the pulldown
        //
        $options ='';
        if ( is_array( $cities ) ) {
            foreach($cities as $city) {
                $selected = ( $city === $selected_city ) ? ' selected ' : '';
                $options .= "<option value='{$city}' {$selected}>{$city}</option>";
            }
        }
        return $options;
    }

    /**
     * Determines which city was selected via the cities widget.
     */
    private function get_selected_city() {
        if ( ! isset( $this->selected_city  ) ) {
            $this->selected_city = ! empty($_REQUEST['slp_widget']['city']) ? $_REQUEST['slp_widget']['city'] : '';
        }
        return $this->selected_city;
    }

    /**
     * Determines which state was selected via the state widget.
     */
    private function get_selected_state() {
        if ( ! isset( $this->selected_state  ) ) {
            $this->selected_state = isset($_REQUEST['slp_widget']['state']) ? $_REQUEST['slp_widget']['state'] : '';
        }
        return $this->selected_state;
    }

    /**
     * Render the widget on the front end UI.
     *
     * @param $args
     * @param $instance
     */
    function render_widget( $args, $instance ) {
        $this->current_instance = $instance;

        // Form Processing
        //
        $method = $instance['query'] ? 'GET' : 'POST';

        // Title Block
        //
        $title_block =
            ( ! empty( $instance['title'] ) )                                 ?
                $args['before_title'] . $instance['title'] . $args['after_title'] :
                '';

        // Hidden Input
        //
        $hidden_input = '';
        foreach ( $instance['vars'] as $var ) {
            $hidden_input .= "<input type='hidden' name='{$var[0]}' value='{$var[1]}' />";
        }
        if ( ! empty( $instance['all_cities_text'] ) ) {
            $hidden_input .= "<input type='hidden' id='all_cities_text' name='all_cities_text' value='{$instance['all_cities_text']}' />";
        }

        // User Input
        //
        $user_input = $this->create_state_then_city_dropdowns();

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

    /**
     * Set JavaScript that trigger cities dropdown replacement on state selection.
     *
     * @return string
     */
    function trigger_cities_ajax_on_state_selection() {

        // TODO: Make this a JavaScript method that talks to the SLP backend to create the cities dropdown elements.
        //
        $change_js = "SLPWJS.city_widget.show_cities_in_state();";
        return ' onChange="' . $change_js . '" ';
    }
}

