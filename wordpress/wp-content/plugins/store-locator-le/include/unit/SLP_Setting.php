<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Setting is the admin interface and rendering engine for a single SLP Option.
 */
class SLP_Setting extends SLPlus_BaseClass_Object {
    protected $attributes = array();
    protected $classes = array();
    protected $data = array();
    protected $content = '';
    public $custom;
    public $data_field;
    public $description;
    public $disabled = false;
    public $display_value;
    public $id;
    public $label;
    public $name;
    public $wrapper = true;
    public $onChange = '';
    public $placeholder = '';
    public $related_to = '';
    public $show_label = true;
    public $type = 'custom';
    public $value;

    public $uses_slplus = false;

    /**
     * Initialize.
     */
    protected function initialize() {
        $this->set_data();
        $this->set_value();
        $this->set_display_value();
        $this->add_base_classes();
        $this->set_attributes();
        $this->at_startup();
    }

    /**
     * Override this in your class to do things at startup after we are initialized. Optional.
     */
    protected function at_startup() {}

    /**
     * Render the setting using the content frmo your override get_content().
     *
     * Only override this if you do not want the standard HTML wrapper.
     */
    public function display() {
        $data = $this->get_data_string();
        $attributes = $this->get_attribute_string();
        $this->content = $this->get_content( $data, $attributes );
        $this->wrap_in_default_html();
    }

    /**
     * Add our base classes
     */
    private function add_base_classes() {
        $this->classes[] = 'input-group';
        $this->classes[] = 'wpcsl-' . $this->type;
    }

    /**
     * Create the HTML attribute string by joining the attributes array.
     * @return string
     */
    protected function get_attribute_string() {
        if ( empty( $this->attributes ) ) {
            return '';
        }
        return join( ' ' , $this->attributes );
    }

    /**
     * Create the HTML classes string by joining the classes array.
     *
     * @return string
     */
    protected function get_classes_string( $classes_array = null ) {
        if ( is_null( $classes_array ) ) {
            $classes_array = $this->classes;
        }
        if ( empty( $classes_array ) ) {
            return '';
        }
        return join( ' ' , $classes_array );
    }

    /**
     * Get the content to be displayed.  Override to generate the custom content for your setting.
     *
     * @param string $data          The data-* attributes
     * @param string $attributes    All other attributes.
     * @return string
     */
    protected function get_content( $data , $attributes ) {
        return '';
    }

    /**
     * Create the HTML data string by joining the data array.
     * @return string
     */
    protected function get_data_string( $data_array = null ) {
        if ( is_null( $data_array ) ) {
            $data_array = $this->data;
        }
        if ( empty( $data_array ) ) {
            return '';
        }
        $html_snippet = '';
        foreach ( $data_array as $slug=>$value ) {
            $html_snippet .= sprintf( ' data-%s="%s"' , $slug, $value );
        }
        return $html_snippet;
    }

    /**
     * Render the description if needed.
     */
    public function render_description() {
        if ( empty( $this->description ) ) {
            return;
        }
        ?>
        <div class="input-description">
            <span class="input-description-text"><?= $this->description; ?></span>
        </div>
        <?php
    }

    /**
     * Render the label if needed.
     */
    protected function render_label() {
        if ( ! $this->show_label ) {
            return;
        }
        ?>
        <div class="label input-label">
            <label for='<?= $this->name ?>'><?= $this->label ?></label>
        </div>
        <?php
    }

    /**
     * Set extra attributes.
     */
    private function set_attributes() {
        $this->set_on_change_attribute();
        $this->set_placeholder_attribute();
        $this->set_disabled_attribute();
    }

    /**
     * Set the data.
     */
    private function set_data() {
        $this->set_id();

        if ( ! isset( $this->data_field ) ) {
            $this->data_field = $this->id;
        }
        $this->data['field'] = $this->data_field;

        if ( ! empty( $this->related_to ) ) {
            $this->data['related_to'] = $this->related_to;
        }
    }

    /**
     * Set disabled attribute
     */
    private function set_disabled_attribute() {
        if ( ! $this->disabled ) {
            return;
        }
        $this->attributes['disabled'] = "disabled='disabled'";
        $this->classes[] = 'disabled';
    }

    /**
     * Set the display value
     */
    protected function set_display_value() {
        if ( isset( $this->display_value ) ) {
            return;
        }
        $this->display_value =  esc_html( $this->value );
    }

    /**
     * Set the ID
     */
    private function set_id() {
        if ( isset( $this->id ) ) {
            return;
        }
        $this->id = $this->name;
    }

    /**
     * Set onChange  attribute
     */
    private function set_on_change_attribute() {
        if ( empty( $this->onChange ) ) {
            return;
        }
        $this->attributes['onchange'] = sprintf( "onchange='%s'" , $this->onChange );
    }

    /**
     * Set placeholder attribute
     */
    private function set_placeholder_attribute() {
        if ( empty( $this->placeholder ) ) {
            return;
        }
        $this->attributes['placeholder'] = sprintf( "placeholder='%s'" , $this->placeholder );
    }

    /**
     * Set the value
     */
    private function set_value() {
        if ( isset( $this->value ) ) {
            return;
        }
        global $slplus_plugin;
        $this->slplus = $slplus_plugin;
        $this->value = $this->slplus->WPOption_Manager->get_wp_option( $this->name );
        if ( is_array( $this->value ) ) {
            $this->value = print_r( $this->value, true );
        }
        $this->value = htmlspecialchars( $this->value );
    }

    /**
     * Wrap our HTML in default divs.
     */
    protected function wrap_in_default_html() {
        if (! $this->wrapper ) {
            echo $this->content;
            return;
        }
	    $classes = sprintf( "class='%s'",$this->get_classes_string() );
	    $id = ! empty( $this->id ) ? sprintf( 'id="input-group-%s"' , $this->id ) : '';
	    $relation = empty( $this->data['related_to'] ) ? '' : "data-related_to='{$this->data['related_to']}'" ;
        ?>
        <div <?= $classes ?> <?= $id ?> <?= $relation ?> >
            <?php $this->render_label(); ?>
            <div class="input input-field">
                <?= $this->content ?>
            </div>
            <?php $this->render_description(); ?>
        </div>
        <?php
    }

}
