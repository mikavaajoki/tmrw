<?php
defined( 'ABSPATH' ) || exit;

require_once( SLPLUS_PLUGINDIR . 'include/unit/SLP_Setting_item.php' );

/**
 * The vision list.
 *
 * @property SLP_Setting_item[] $items
 * @property int                            $items_to_load_at_start     how many items to load from source to start
 */
class SLP_Settings_vision_list extends SLP_Setting {
    public $items_per_page = 3;
    public $items_to_load_at_start = 9;
    public $items;

    /**
     * Things we do when starting out.
     */
    protected function at_startup() {
        $this->get_items();
    }

    /**
     * Render me.
     */
    public function display() {
        $this->data[ 'page_len' ]     = $this->items_per_page;
        $this->data[ 'pages_loaded' ] = 3;

        $classes         = $this->get_classes_string();
        $data            = $this->get_data_string();
        $id              = sprintf( 'input-group-%s' , $this->id );
        $activate_label  = __( 'Select' , 'store-locator-le' );
        $customize_label = __( 'Active' , 'store-locator-le' );
        ?>
        <div class='<?php echo $classes; ?>' <?= $data; ?> id='<?php echo $id; ?>'>
            <input type="hidden" id="active_text" value="<?= $customize_label ?>"/>
            <input type="hidden" id="select_text" value="<?= $activate_label ?>"/>
            <input type="hidden" id="activating_text" value="<?php echo __( 'Activating...' , 'store-locator-le' ); ?>"/>
            <input type="hidden" id="<?php echo $this->id; ?>" name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"/>
            <div class="vision_list theme-browser">
                <?php
                if ( ! empty( $this->items ) ) {
                    /**
                     * @var SLP_Setting_item $item
                     */
                    foreach ( $this->items as $item ) {
                        $selected = ( $this->value === $item->clean_title );

                        if ( $selected ) {
                            $item->classes[] = 'active';
                        }

                        $item_data = $this->get_data_string( $item->data );
                        ?>
                        <div class="vision_list_item theme <?php echo $this->get_classes_string( $item->classes ); ?>" data-style="<?= $item->clean_title ?>">
                            <div class="vision_list_details">
                                <div class="vision_list_text">
                                    <?php echo $item->description; ?>
                                </div>
                                <div class="theme-id-container">
                                    <h2 class="theme-name"><?php echo $item->title; ?></h2>
                                    <?php if ( $item->has_actions ) { ?>
                                        <div class="theme-actions">
                                            <?php if ( $selected ) { ?>
                                                <a class="button button-secondary customize" <?php echo $item_data; ?> aria-label="<?php echo $activate_label; ?>"><?php echo $customize_label; ?></a>
                                            <?php } else { ?>
                                                <a class="button button-secondary activate" <?php echo $item_data; ?> aria-label="<?php echo $activate_label; ?>"><?php echo $activate_label; ?></a>
                                            <?php } ?>

                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    // Overflow


                } else {
                    echo __( 'No items found.' , 'store-locator-le' );
                }
                ?>
            </div>
            <?php $this->render_description(); ?>
        </div>
        <?php
    }


    /**
     * Override.  Get the items for the list.
     */
    protected function get_items() {}


}

