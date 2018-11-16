<?php
defined( 'ABSPATH' ) || exit;
/**
 * Class WP_Widget_SLP
 *
 * Extend the WP Widget Class with some common SLP methods.
 *
 * To Use:
 * 1) configure_widget_properties() in your extended class
 * 2) configure_widget_settings() in your extended class
 * 3) create a class.<slug>.ui.php file with the user interface render_widget($args,$instance) method.
 *
 * @property    string                  $description_admin  The description to go on the admin UI widget box.
 * @property    SLP_Widget_Setting[]    $settings           The settings for a widget instance.
 * @property    string                  $slug               The slug for this widget.
 * @property    SLPlus                  $slplus             The Store Locator Plus plugin.
 * @property    string                  $title_admin        The title to go on the admin UI widget box.
 * @property    string                  $title_ui           The default title for the front-end UI widget box.
 * @property    SLPWidget_states_UI     $ui                 The invoked UI class object.
 * @property    string                  $ui_class_name      Class name for this widget's UI class.
 *
 * @see http://codex.wordpress.org/Widgets_API
 */
class WP_Widget_SLPExp extends WP_Widget {
    public      $addon;
    protected   $description_admin;
    public      $settings = array();
    protected   $slug;
    public      $slplus;
    protected   $title_admin;
    protected   $title_ui;
    protected   $ui;
    protected   $ui_class_name = '';

    /**
     * Creates a widget and registers it with WordPress.
     *
     */
    public function __construct()  {
        global $slplus_plugin;
        $this->slplus = $slplus_plugin;
        $this->addon  = $this->slplus->addon( 'experience' );

        $this->configure_widget_properties();         // Per-widget properties, SHOULD overrid.
        $this->set_global_settings();     // Set for EVERY widget, should not override.
        $this->configure_widget_settings();     // Per-widget settings, SHOULD override.

        $this->ui_class_name = 'SLPWidget_' . $this->slug . '_UI';
        parent::__construct( 'slp_widget_' . $this->slug,  $this->title_admin, array( 'description' => $this->description_admin ) );
    }

    /**
     * Set the properties for this widget.  Things like the admin box title, description, etc.
     *
     * OVERRIDE in extension.
     */
    protected function configure_widget_properties() {}

    /**
     * Set the settings for this widget.  The stuff the user fills out in the admin forms.
     *
     * OVERRIDE in extension.
     */
    protected function configure_widget_settings() {
        // see configure_global_settings above
    }

    /**
     * Create a hypertext link to the widget pack support docs.
     *
     * @return string
     */
    function create_string_docs_link() {
        return
            sprintf(
                '<a href="%s" target="csa">%s</a>',
                $this->slplus->support_url . '/our-add-ons/experience/' ,
                __('Documentation', 'slp-experience')
            );
    }

    /*
     * Create the HTML string for the admin form entries.
     */
    function create_string_widget_input( $field_slug , $label , $value ) {
        $field_id   = $this->get_field_id( $field_slug );
        $field_name = $this->get_field_name( $field_slug );
        $value = esc_attr( $value );

        $admin_label_suffix = _x( ':' , 'separator after a field label' , 'slp-experience' );


        $html  = "<label for='{$field_id}'>{$label}{$admin_label_suffix}</label>";
        $html .= "<input class='widefat' id='{$field_id}' name='{$field_name}' type='text' value='{$value}' />";

        return $html;
    }

    /*
     * Create the HTML string for the admin form entries.
     */
    function create_string_widget_checkbox( $field_slug , $label , $value ) {
        $field_id   = $this->get_field_id( $field_slug );
        $field_name = $this->get_field_name( $field_slug );
        $checked = ( $value ) ? 'checked' : '';


        $html  = "<input class='widefat' id='{$field_id}' name='{$field_name}' type='checkbox' {$checked} />";
        $html .= "<label for='{$field_id}'>{$label}</label><br/>";

        return $html;
    }

    /**
     * Create and attach the UI object for this widget
     *
     * @param       string      $slug
     * @return      mixed
     */
    function create_ui_object( $slug = null ) {
        $ui_property = is_null( $slug ) ? 'ui'                 : 'ui_' . $slug;
        $ui_class    = is_null( $slug ) ? $this->ui_class_name : 'SLPWidget_' . $slug . '_UI';
        $slug        = is_null( $slug ) ? $this->slug          : $slug;

        if ( !isset( $this->$ui_property ) ) {
            require_once($this->addon->dir . "include/widgets/class.{$slug}.ui.php");
            $this->$ui_property = new $ui_class(
                array(
                    'addon'     => $this->addon,
                    'slplus'    => $this->slplus,
                    'widget'    => $this,
                )
            );
        }

        return $this->$ui_property;
    }

    /**
     * Render the admin interface widget form.
     *
     * @param   array                 $instance
     */
    public function form($instance) {
        if (!is_array($instance)) { $instance = array(); }
        $this->set_property_values( $instance );

        print '<p>';


        foreach ( $this->settings as $setting ) {
            switch ( $setting->admin_input ) {
                case 'input':
                case 'text':
                    print $this->create_string_widget_input( $setting->slug , $setting->admin_label , $instance[ $setting->slug ] );
                    break;

                case 'checkbox':
                    print $this->create_string_widget_checkbox( $setting->slug , $setting->admin_label , $instance[ $setting->slug ] );
                    break;

                default:
                    break;
            }
        }

        print '</p>';

        print '<p>' . $this->create_string_docs_link() . '</p>';
    }


    /**
     * Set default global properties for the widget.
     */
    private function set_global_settings() {
        require_once($this->addon->dir . "include/class.widget.settings.php");
        $this->settings['slug' ] = new SLP_Widget_Setting(array(
            'slug'          => 'slug' ,
            'admin_input'   => 'none' ,
            'default_value' => $this->slug
        ));
        $this->settings['title'] = new SLP_Widget_Setting(array(
            'slug'          => 'title',
            'admin_input'   => 'text' ,
            'admin_label'   => __( 'Title' , 'slp-experience' ),
            'default_value' => $this->title_ui
        ));
        $this->settings['button_label']  = new SLP_Widget_Setting(array(
            'slug'          => 'button_label',
            'admin_input'   => 'input' ,
            'admin_label'   => __('Button Label', 'slp-experience'),
            'default_value' => __('Go!' , 'slp-experience' )
        ));
        $this->settings['map_url_full'] = new SLP_Widget_Setting( array(
            'slug'          => 'map_url_full' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Map Page URL' , 'slp-experience' ),
            'default_value' => '' ,
        ));
    }

    /**
     * Set the instance properties based on settings and default values.
     *
     * @param $instance
     */
    private function set_property_values( &$instance ) {
        foreach ( $this->settings as $setting ) {
            $instance[ $setting->slug ] = isset ( $instance[ $setting->slug ] ) ? $instance[ $setting->slug ] : $setting->default_value;
        }
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array_map('strip_tags', $new_instance);

        // Separate the map URL into base and params portions
        //
        $map_url_parts = explode('?', $instance['map_url_full']);
        $instance['map_url'] = (count($map_url_parts) > 0) ? $map_url_parts[0] : $instance['map_url_full'];
        $instance['map_url_vars'] = (count($map_url_parts) > 1) ? $map_url_parts[1] : '';

        // Build the variable array
        //
        $instance['query'] = !empty($instance['map_url_vars']);
        $vars = array();
        if ($instance['query']) {
            $vars = explode('&', $instance['map_url_vars']);
            foreach ($vars as $idx => $var) {
                $vars[$idx] = explode('=', $var);
            }
        }
        $instance['vars'] = $instance['query'] ? $vars : array();

        /**
         * FILTER: slp_widget_save_form_instance
         *
         * Modify the default SLP widget instance variable during save.
         * Normally used to save checkboxes.
         *
         * $instance['checkbox_name'] = ( isset( $new_instance['checkbox_name'] ) );
         *
         * @param       array   $instance       the original settings for this widget instance
         * @param       array   $new_instance   the new settings
         *
         * @returns     array               the modified instance
         *
         */
        $instance = apply_filters( 'slp_widget_save_form_instance' , $instance , $new_instance );

        return $instance;
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $this->set_property_values( $instance );
        $this->create_ui_object();
        $this->ui->render_widget( $args , $instance );
    }
}