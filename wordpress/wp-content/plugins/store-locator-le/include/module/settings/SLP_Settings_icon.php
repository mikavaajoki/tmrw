<?php
defined( 'ABSPATH' ) || exit;

/**
 * The icon setting.
 */
class SLP_Settings_icon extends SLP_Setting {
    public $uses_slplus = true;

    /**
     * Need media library for this.
     */
    public function at_startup() {
        wp_enqueue_media();
        $this->data['base_id'] = $this->id;
    }

    /**
     * The icon HTML.
     *
     * @param string $data
     * @param string $attributes
     *
     * @return string
     */
    protected function get_content( $data, $attributes ) {
        return
            "<input type='text' id='{$this->id}' name='{$this->name}' {$data} value='{$this->display_value}' {$attributes}/>" .
            $this->media_button_html( $data ) .
            SLP_Admin_UI::get_instance()->create_string_icon_selector( $this->id, $this->id . '_icon' )
            ;
    }

    /**
     * Set the media button HTML
     *
     * @param string $data
     *
     * @return string
     */
    private function media_button_html( $data ) {
        return
             '<div class="wp-media-buttons">' .
                 "<button type='button' class='button insert-media add_media' {$data}>".
                    '<span class="dashicons dashicons-admin-media"></span>'.
                    __( 'Use Media Image' , 'store-locator-le' ) .
                '</button>' .
            '</div>'
            ;
    }

    /**
     * Takover render label.
     */
    protected function render_label() {
        if ( ! $this->show_label ) {
            return;
        }
        $icon_src = ! empty( $this->display_value ) ? $this->display_value : $this->slplus->SmartOptions->map_end_icon;
        ?>
        <div class="label input-label">
            <label for='<?= $this->name ?>'><?= $this->label ?></label>
            <span class="icon"><img id='<?= $this->id ?>_icon' alt='<?= $this->name ?> icon' src='<?= $icon_src ?>' class='slp_settings_icon'></span>
        </div>
        <?php
    }

}
