<?php
require_once(SLPLUS_PLUGINDIR . '/include/base_class.userinterface.php');

/**
 * Class SLPWidget_search_and_results_UI
 *
 * Display an address search and show a list of locations in a widget area.
 *
 * @property    SLP_Experience                  $addon      The widget add-on.
 * @property    SLPlus                          $slplus     The base plugin.
 * @property    SLPWidget_search_and_results    $widget
 *
 * TODO: this is defunct and needs to be rebuilt.
 */
class SLPWidget_search_and_results_UI extends SLP_BaseClass_UI {
    public  $addon;
    public  $slplus;
    public  $widget;

    /**
     * What HTML do we want to wrap our widget with?  Start it with this..
     *
     * @var string $before_widget
     */
    private $before_widget  = '<div class="slp_basic_widget">';

    /**
     * What HTML do we want to wrap our widget with?  End it with this..
     *
     * @var string $after_widget
     */
    private $after_widget   = '</div>';

    /**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
     *
     * <div class="slp_basic_widget">
     *     <div id="slpw_searchform">
     *          <div id="slpw_addressline"></div>
     *          <div id="slpw_submitline"></div>
     *     </div>
     *     <div id="slpw_resultsbox">
     *        <div id="slpw_searching" >SPINNER</div>
     *        <div id="slpw_results">
     *            <div class="slpw_result [slpw_even|slpw_odd]">RESULT</div>
     *        </div>
     *     </div>
     * </div>
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
    public function render_widget($args, $instance) {
        extract( $args );

            $this->slplus->UI->setup_stylesheet_for_slplus();
            $this->slplus->UI->localize_script();
            if (!defined('SLPLUS_SHORTCODE_RENDERED')) { define('SLPLUS_SHORTCODE_RENDERED',true); }

		$title = apply_filters( 'widget_title', $instance['title'] );

        $instance['radius'              ] = isset($instance['radius'            ])?$instance['radius'           ]:100;
        $instance['none_found'          ] = isset($instance['none_found'        ])?$instance['none_found'       ]:__('Nothing Found!','slp-experience');
        $instance['immediate'           ] = isset($instance['immediate'         ])?$instance['immediate'        ]:true;
        $instance['immediate_address'   ] = isset($instance['immediate_address' ])?$instance['immediate_address']:'';
        $instance['simple_output'       ] = isset($instance['simple_output'     ])?$instance['simple_output'    ]:true;
        ?>
        <script type="text/javascript">
            var slpWidgets = {
                'immediate'         : '<?php echo $instance['immediate'         ];    ?>',
                'immediate_address' : '<?php echo $instance['immediate_address' ];    ?>',
                'max_results'       : '<?php echo $instance['max_results'       ];    ?>',
                'none_found'        : '<?php echo $instance['none_found'        ];    ?>',
                'simple_output'     : '<?php echo $instance['simple_output'     ];    ?>',
            };
        </script>
        <?php
        
        $folders = apply_filters("slpWidgets(getFolders)", array());

		echo $this->before_widget;
        echo '<div id="slpw_searchform">';
            if ( ! empty( $title ) )
                echo $title;
            ?>
            <form onsubmit="search_slp_now(); return false;">
            <div id="slpw_addressline">
                <?php if (!$instance['use_placeholder']) { ?><label for="address_input"><?php echo $instance['search_label']; ?></label> <?php } ?>
                <input type="text" id="address_input_slpw_basic" <?php if ($instance['use_placeholder']) { echo "placeholder='{$instance['search_label']}'"; } ?> />
            </div>
            <div id="slpw_submitline">
                <?php
                // If there is a radius label show the drop down menu
                // leave radius label blank to use a default value
                //
                if (!empty($instance['radius_label'])) {

                    if ( ! $this->slplus->is_CheckTrue( $this->addon->options[ 'hide_radius_selector' ] ) ) {
                            $this->slplus->options['radius_options'] =
                                "<input type='hidden' id='radiusSelect' name='radiusSelect' value='". $this->slplus->UI->find_default_radius() . "'>";
                    } else {
                        $this->slplus->options['radius_options'] = $this->slplus->UI->create_string_radius_selector_options();
                    }

                    // SLPlus default is a drop down, render it with a preceding label.
                    //
                    if (strpos($this->slplus->options['radius_options'],"type='hidden'")===false) {
                        print "<label for='radiusSelect'>{$instance['radius_label']}</label>" .
                            "<select id='radiusSelect'>{$this->slplus->options['radius_options']}</select>"
                            ;

                    // SLPlus default is a hidden field.  We need no label or select for that!
                    //
                    } else {
                        print $this->slplus->options['radius_options'];
                    }

                // Use the default radius set for the widget when the label is blank.
                //
                } else {
                    print "<input type='hidden' id='radiusSelect' name='radiusSelect' value='{$instance['radius']}'>";
                }
                ?>
                <input type="submit" id="slpw_addressSubmit" value="<?php echo $instance['button_label']; ?>" />
            </div>
            </form>
        </div>
        <div id="sl_arrow_container"><div id="sl_search_arrow"></div></div>
        <div id="slpw_resultsbox">
            <div id="slpw_searching" style="display:none"><img src="<?php echo $folders['PluginUrl'] .'/images/spinner.gif'  ?>" /></div>
            <div id="slpw_results"></div>
        </div>
        <?php
		echo $this->after_widget;
    }
}