<?php

if (! class_exists('SLPWidget_states_UI')) {
    require_once(SLPLUS_PLUGINDIR . '/include/base_class.userinterface.php');


    /**
     * Class SLPWidget_states_UI
     *
     * Handle all the UI elements for the widget.
     *
     * Putting this in a separate include file makes the admin loading "lighter".
     *
     * @property        SLP_Widgets     $addon      The widget add-on.
     * @property        SLPlus          $slplus     The base plugin.
     */
    class SLPWidget_states_UI extends SLP_BaseClass_UI {
        public $addon;
        public $slplus;

        /**
         * Build the state dropdown.
         *
         * @return string the HTML for the state dropdown
         */
        function create_string_state_dropdown() {
            $on_change = apply_filters( 'slp_widget_state_dropdown_onchange' , '' );

            return
                "<div id='widget_state_selector' class='slp_widget_selector'>".
                "<select id='slp_widget[state]' name='slp_widget[state]' {$on_change}>".
                    "<option value=''>".
                    $this->addon->options['first_entry_for_state_selector'].
                    '</option>'.
                    $this->create_string_state_options().
                '</select>'.
                '</div>'
                ;
        }

        /**
         * Create the option list for the states.
         *
         * @return string
         */
        private function create_string_state_options() {
            $myOptions = '';

            $cs_array=$this->slplus->database->get_Record(
                array( 'select_states' , 'where_default_validlatlong', 'where_not_private', 'where_valid_state' , 'group_by_state' , 'order_by_state') ,
                array(),
                0,
                ARRAY_A,
                'get_col'
            );

            // Selected value
            //
            if ( $this->addon->widget->is_initial_widget_search( $_REQUEST) ) {
                $selected_value = isset( $_REQUEST['slp_widget']['state'] ) ? $_REQUEST['slp_widget']['state'] : '';
            } else {
                $selected_value = '';
            }

            // If we have state data show it in the pulldown
            //
            if ($cs_array) {
                foreach($cs_array as $sl_value) {
                    $selected = ( $sl_value === $selected_value ) ? ' selected ' : '';
                    $myOptions.=
                        "<option value='$sl_value' {$selected}>" .
                        $sl_value."</option>";
                }
            }
            return $myOptions;
        }

        /**
         * Render the widget on the front end UI.
         *
         * @param $args
         * @param $instance
         */
        function render_widget( $args, $instance ) {
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

            // User Input
            //
            $user_input = '';
            $user_input = $this->create_string_state_dropdown();
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

}