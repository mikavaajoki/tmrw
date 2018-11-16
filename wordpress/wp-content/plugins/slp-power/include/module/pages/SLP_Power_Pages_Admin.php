<?php
defined( 'ABSPATH' ) || exit;
/**
 * Admin pages stuff.
 *
 * @property    SLPPower            $addon
 * @property    SLP_Power_Admin     $admin
 * @property    SLP_Power_Pages_Tab $tab
 */
class SLP_Power_Pages_Admin extends SLPlus_BaseClass_Object {
    public $addon;
    public $admin;
    public $tab;

    /**
     * Things we do to get started.
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'power' );
        $this->add_hooks_and_filters(); // Only called after SLP init and if in admin mode and if pages is active.
        $this->save_pages_settings();
    }

    /**
     * Add a location action button.
     *
     * @used-by \SLP_Power_Pages_Admin::add_hooks_and_filters           filter: slp_manage_locations_buttons
     *
     * @param array         buttons array
     *
     * @return array        modified/extended buttons array
     */
    public function add_action_buttons( $buttons ) {
        $buttons[ 'create_page' ] = array(
        	'title' => __( 'Create SEO Page' , 'slp-power' ),
	        'class' => 'dashicons-media-document ' . ( ! empty( $this->slplus->currentLocation->linked_postid ) ? 'haspage_icon' : 'createpage_icon' ),
        );

        return $buttons;
    }

    /**
     * Add pages admin hooks and filters if pages is active.
     *
     * @uses \SLP_Power_Pages_Admin::create_content
     */
    public function add_hooks_and_filters() {
        // Pages Content Management
        add_filter( 'slp_pages_content'                     , array( $this , 'replace_id_placeholder'       )           );
        add_filter( 'slp_location_page_attributes'          , array( $this , 'create_content'               )           );
        add_action( 'publish_' . SLPlus::locationPostType   , array( $this , 'update_content_on_publish'    ) , 90 , 2  );
        add_action( 'before_delete_post'                    , array( $this , 'delete_page'                  )           );

        // Locations Tab
        add_action( 'slp_build_locations_panels'            , array( $this , 'extend_location_edit'         ) , 30      );
        add_filter( 'slp_column_data'                       , array( $this , 'extend_location_columns'      ) , 90, 3   );
        add_filter( 'slp_manage_expanded_location_columns'  , array( $this , 'extend_elocation_columns'     )           );
        add_filter( 'slp_locations_manage_bulkactions'      , array( $this , 'extend_bulk_actions'          )           );
        add_filter( 'slp_manage_locations_buttons'          , array( $this , 'add_action_buttons'           )           );
    }

    /**
     * Set the page content when location create/update page is called.
     *
     * @used-by \SLP_Power_Pages_Admin::add_hooks_and_filters       filter: slp_location_page_attributes
     *
     * @param   array $page_data
     *
     * @return  array
     */
    public function create_content( $page_data ) {
        if ( ( empty( $_REQUEST['action'] ) || ( ( $_REQUEST['action'] !== 'createpage' ) && (  $_REQUEST['action'] !== 'slp_create_page' ) )) && isset( $page_data['ID'] ) && ! empty ( $page_data[ 'ID' ] ) ) {
        	return $page_data;
        }
        $new_attributes =
            array_merge( $page_data , array(
                    'post_content' => $this->create_string_content() ,
                    'post_status' => $this->addon->options[ 'default_page_status' ]
                )
            );
        return $new_attributes;
    }

    /**
     * Create the action entry string based on the params entries.
     *
     * params['label'] - required, the text to show for the link
     * params['ifok']  - boolean, if true only show this item if the pages_url for the current location is set
     *
     * @param mixed[] $params
     * @return string
     */
    private function create_string_action_entry( $params ) {
        // If all these things are true that means we have no page URL
        // and we are not supposed to be showing this... get out
        //
        if ( ($this->slplus->currentLocation->pages_url == '') && isset( $params[ 'ifpageexists' ] ) && $params[ 'ifpageexists' ] ) {
            return '';
        }
        if ( !isset( $params[ 'label' ] ) || empty( $params[ 'label' ] ) ) {
            return '';
        }

        // Set title if not set
        if ( !isset( $params[ 'title' ] ) ) {
            $params[ 'title' ] = $params[ 'label' ];
        }

        // Set link if not set
        if ( !isset( $params[ 'link' ] ) ) {
            $params[ 'link' ] = '#';
        }

        // Rel used?
        //
        if ( isset( $params[ 'rel' ] ) ) {
            $params[ 'rel' ] = " rel='{$params[ 'rel' ]}' ";
        } else {
            $params[ 'rel' ] = '';
        }

        // Target used?
        //
        if ( isset( $params[ 'target' ] ) ) {
            $params[ 'target' ] = " target='{$params[ 'target' ]}' ";
        } else {
            $params[ 'target' ] = '';
        }

        // Class used?
        //
        if ( isset( $params[ 'class' ] ) ) {
            $params[ 'class' ] = " class='{$params[ 'class' ]}' ";
        } else {
            $params[ 'class' ] = '';
        }

        return "<a {$params[ 'class' ]} {$params[ 'rel' ]} title='{$params[ 'title' ]}' href='{$params[ 'link' ]}' {$params[ 'target' ]}>{$params[ 'label' ]}</a>";
    }

    /**
     * Create the action output for add/edit locations.
     * @return string
     */
    private function create_string_actions() {
        $actions = $this->set_page_actions();
        $action_count = count( $actions );
        $i = 1;
        $out = '<div class="row-actions">';
        foreach ( $actions as $action => $link ) {
            ( $i++ == $action_count ) ? $sep = '' : $sep = ' | ';
            $out .= "<span class='$action'>$link$sep</span>";
        }
        $out .= '</div>';
        return $out;
    }

    /**
     * Create the content for a Store Page.
     *
     * Creates the content for the page.  If plus pack is installed
     * it uses the plus template file, otherwise we use the hard-coded
     * layout.
     *
     * @return string
     */
    private function create_string_content() {

        // Make sure we have a default template.
        //
        if ( empty( $this->addon->options[ 'page_template' ] ) ) {
            $this->addon->options[ 'page_template' ] = $this->create_string_default_template();
        }

        // FILTER: slp_pages_content
        //
        return apply_filters( 'slp_pages_content' , $this->addon->options[ 'page_template' ] );
    }

    /**
     * Create the default Store Page content.
     *
     *
     *
     * @return string - HTML content that is the WordPress page content.
     */
    public function create_string_default_template() {
        $content = '<span class="storename">[storepage field=store]</span>' . "\n" .
                   '[storepage field=image type=image]' . "\n" .
                   '[storepage field=address]' . "\n" .
                   '[storepage field=address2]' . "\n" .
                   '[storepage field=city] [storepage field=state] [storepage field=zip] ' . "\n" .
                   '[storepage field=country]' . "\n" .
                   '<h1>' . __( 'Description' , 'slp-power' ) . '</h1>' . "\n" .
                   '<p>[storepage field=description]</p>' . "\n" .
                   '<h1>' . __( 'Contact Info' , 'slp-power' ) . '</h1>' . "\n" .
                   SLP_Text::get_instance()->get_text( 'label_phone' ) . '[storepage field=phone]' . "\n" .
                   SLP_Text::get_instance()->get_text( 'label_fax' ) . '[storepage field=fax]' . "\n" .
                   '[storepage field=email type=mailto]' . "\n" .
                   '[storepage field=url type=hyperlink]' . "\n" .
                   '[storepage map=location]' . "\n";
        ;

        return apply_filters( 'slp_pages_default_content' , $content );
    }

    /**
     * Create a short Store Page URL for use on manage locations interface.
     *
     * @param string $fullURL
     * @return string the short hyperlinked URL
     */
    function create_string_short_url( $fullURL ) {
        $pattern = '/^(.*?)=/';
        $shortURL = preg_replace( $pattern , '' , $fullURL );
        $shortURL = str_replace( get_site_url() , '' , $shortURL );
        return "<a href='$fullURL' target='csa'>$shortURL</a>";
    }

    /**
     * Set the link in store locations table to null when a store page is permanently deleted.
     *
     * @used-by \SLP_Power_Pages_Admin::add_hooks_and_filters       filter: before_delete_post
     *
     * @param int $pageID
     * @return boolean
     */
    public function delete_page( $pageID ) {
        $locationID = $this->slplus->database->get_Value(
            array( 'selectslid' , 'wherelinkedpostid' ) , $pageID
        );
        if ( is_wp_error( $locationID ) ) {
        	return false;
        }
        $this->slplus->currentLocation->set_PropertiesViaDB( $locationID );

        if ( ($this->slplus->currentLocation->linked_postid !== '') ||
             ($this->slplus->currentLocation->pages_url !== '' )
        ) {
            $this->slplus->currentLocation->delete_page_links();
        }
        return true;
    }

    /**
     * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
     *
     * @param mixed[] $BulkActions
     * @return mixed[]
     */
    function extend_bulk_actions( $BulkActions ) {
        return
            array_merge(
                $BulkActions , array(
                    array(
                        'label' => __( 'Pages, Create' , 'slp-power' ) ,
                        'value' => 'createpage' ,
                    ) ,
                    array(
                        'label' => __( 'Pages, Delete Permanently' , 'slp-power' ) ,
                        'value' => 'deletepage' ,
                    ) , )
            );
    }

    /**
     * Add the Store Pages URL column.
     *
     * @param array $theColumns - the array of column data/titles
     * @return array - modified columns array
     */
    public function extend_elocation_columns( $theColumns ) {
        return array_merge( $theColumns , array(
                'sl_pages_url' => __( 'Pages URL' , 'slp-power' ) ,
            )
        );
    }

    /**
     * Render the extra fields on the manage location table.
     *
     * SLP Filter: slp_column_data
     *
     * @param string $theData  - the option_value field data from the database
     * @param string $theField - the name of the field from the database (should be sl_option_value)
     * @param string $theLabel - the column label for this column (should be 'Categories')
     * @return string the modified data
     */
    function extend_location_columns ( $theData , $theField , $theLabel ) {
        if ( $theField === 'sl_pages_url' ) {
            $theData = '';
            if ( $this->slplus->currentLocation->pages_url != '' ) {
                $theData .= '<span class="infoid floater">' . get_post_status( $this->slplus->currentLocation->linked_postid ) . '</span>';
                $theData .= $this->create_string_short_url( $this->slplus->currentLocation->pages_url );
            }
            $theData .= $this->create_string_actions();
        }
        return $theData;
    }

    /**
     * Extend the location edit form.
     *
     * TODO: Convert to new group params and slug based system.    Called by slp_build_locations_panels.
     */
    function extend_location_edit() {
        if ( empty( $this->slplus->currentLocation->pages_url ) ) {
            return;
        }

        $section_name = __( 'Edit' , 'slp-power' );
        $group_name = $this->addon->name;

        $shortSPurl = preg_replace( '/^.*?store_page=/' , '' , $this->slplus->currentLocation->pages_url );
        if ( $this->slplus->currentLocation->linked_postid >= 0 ) {
            $pageEditLink = sprintf( '<a href="%s" class="action_icon edit_icon" target="_blank"></a>' , admin_url() . 'post.php?post=' . $this->slplus->currentLocation->linked_postid . '&action=edit' );
        }

        $pages_html = '<div id="slp_pages_fields" class="slp_editform_section">' .
                      "<label for='pages_url'>" .
                      sprintf( __( 'Store Page ID %d is at ' , 'slp-power' ) , $this->slplus->currentLocation->linked_postid ) .
                      "</label>" .
                      $pageEditLink . ' ' .
                      "<a name='pages_url' href='{$this->slplus->currentLocation->pages_url}' target='csa'>$shortSPurl</a>" .
                      $this->create_string_actions() .
                      '</div>'
        ;

	    SLP_Admin_UI::get_instance()->ManageLocations->settings->add_ItemToGroup( array(
            'setting' => 'slp_tagalong_fields' ,
            'custom' => $pages_html ,
            'type' => 'custom' ,
            'show_label' => false ,
            'section' => $section_name ,
            'group' => $group_name ,
        ) );
    }

    /**
     * Render the admin panel.
     */
    function render_pages_tab() {
	    $this->tab = SLP_Power_Pages_Tab::get_instance();
        $this->tab->render();
    }

    /**
     * Replace the content sl_id to the real id value.
     *
     * @param string $content
     * @return string
     */
    function replace_id_placeholder( $content ) {
        return str_replace( "%sl_id%" , $this->slplus->currentLocation->id , $content );
    }

    /**
     * Save settings on pages, set permalink flush.
     */
    private function save_pages_settings() {
    	if ( empty( $_REQUEST[ 'page' ] ) ) return;
    	if ( $_REQUEST['page'] !== 'slp-pages' ) return;

        $this->addon->options['permalink_flush_needed'] = '1'; // TODO: Only if settings that change permalinks have changed.

        $this->slplus->WPOption_Manager->update_wp_option( $this->addon->option_name, $this->addon->options );

	    $this->slplus->SmartOptions->save( 'slp-pages' );

    }

    /**
     * Set Page Actions.
     *
     * @return array
     */
    private function set_page_actions() {
        $actions = array();

        // Things we only do for posts with a linked post ID
        //
        if ( $this->slplus->currentLocation->linked_postid > 0 ) {
            $pageStatus = get_post_status( $this->slplus->currentLocation->linked_postid );

            // Recreate
            //
            $link = $this->create_string_action_entry( array(
                'ifpageexists' => true ,
                'label' => __( 'Recreate' , 'slp-power' ) ,
                'link' => SLP_Admin_UI::get_instance()->ManageLocations->hangoverURL . '&act=createpage&sl_id=' . $this->slplus->currentLocation->id
            ) );
            if ( !empty( $link ) ) {
                $actions[ 'create' ] = $link;
            }

            // Edit
            //
            $link = $this->create_string_action_entry( array(
                'ifpageexists' => true ,
                'label' => __( 'Edit' , 'slp-power' ) ,
                'link' => admin_url() . 'post.php?post=' . $this->slplus->currentLocation->linked_postid . '&action=edit'
            ) );
            if ( !empty( $link ) ) {
                $actions[ 'edit' ] = $link;
            }

            // Trash
            //
            if ( $pageStatus !== 'trash' ) {
                $link = $this->create_string_action_entry( array(
                    'ifpageexists' => true ,
                    'label' => __( 'Trash' , 'slp-power' ) ,
                    'class' => 'submitdelete' ,
                    'link' => get_delete_post_link( $this->slplus->currentLocation->linked_postid )
                ) );
                if ( !empty( $link ) ) {
                    $actions[ 'trash' ] = $link;
                }
            }

            // Delete Permanently
            //
            $link = $this->create_string_action_entry( array(
                'ifpageexists' => true ,
                'label' => __( 'Delete' , 'slp-power' ) ,
                'class' => 'submitdelete' ,
                'link' => get_delete_post_link( $this->slplus->currentLocation->linked_postid , '' , true )
            ) );
            if ( !empty( $link ) ) {
                $actions[ 'delete' ] = $link;
            }


            // View/Preview
            //
            switch ( $pageStatus ) {
                // View mode for published, private items
                //
                case 'private':
                case 'publish':
                    $link = $this->create_string_action_entry( array(
                        'ifpageexists' => true ,
                        'label' => __( 'View' , 'slp-power' ) ,
                        'rel' => 'permalink' ,
                        'target' => 'csa' ,
                        'link' => $this->slplus->currentLocation->pages_url
                    ) );
                    if ( !empty( $link ) ) {
                        $actions[ 'view' ] = $link;
                    }
                    break;

                // All others - preview mode
                //
                default:
                    $link = $this->create_string_action_entry( array(
                        'ifpageexists' => true ,
                        'label' => __( 'Preview' , 'slp-power' ) ,
                        'rel' => 'permalink' ,
                        'target' => 'csa' ,
                        'link' => get_site_url() . '?post_type=store_page&p=' . $this->slplus->currentLocation->linked_postid . '&preview=true'
                    ) );
                    if ( !empty( $link ) ) {
                        $actions[ 'view' ] = $link;
                    }
                    break;
            }
        }
        return $actions;
    }

    /**
     * Check if the content of Store Page post is blank when it's being published.
     * If it is, set content to default template value.
     *
     * @param mixed $post_id
     * @param mixed $post
     */
    function update_content_on_publish( $post_id , $post ) {
        if ( trim( $post->post_content ) == '' ) {
            $post->post_content = $this->create_string_default_template();
            wp_update_post( $post );
        }
    }
}
