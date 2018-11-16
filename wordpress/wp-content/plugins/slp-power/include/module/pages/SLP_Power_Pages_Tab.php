<?php
defined( 'ABSPATH'     ) || exit;

require_once( SLPLUS_PLUGINDIR . 'include/module/settings/SLP_Settings.php');

/**
 * Things we only need if the pages tab is active.
 *
 * @property        SLPPower              $addon
 * @property        SLP_Settings          $settings
 *
 */
class SLP_Power_Pages_Tab extends SLPlus_BaseClass_Object {
    public $addon;
    public $settings;

    /**
     * Things we do to get started.
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'power' );
        $this->settings = new SLP_Settings( array(
            'name'        => SLPLUS_NAME . ' - ' . __( 'Pages' , 'slp-power' ),
            'form_action' => admin_url() . 'admin.php?page=slp-pages'
            ) );
        SLP_Power_Text_Pages_Tab::get_instance();
    }

    /**
     * Render the admin panel.
     */
    function render() {
        $this->settings->add_section(
            array(
                'name' => 'Navigation' ,
                'div_id' => 'navbar_wrapper' ,
                'description' => SLP_Admin_UI::get_instance()->create_Navbar() ,
                'innerdiv' => false ,
                'is_topmenu' => true ,
                'auto' => false ,
            )
        );

        $sectName = __( 'Settings' , 'slp-power' );
        $this->settings->add_section( array( 'name' => $sectName , 'auto' => true ) );

        // Group : Behavior
        //
        $groupName = __( 'Behavior' , 'slp-power' );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'dropdown' ,
            'label' => __( 'Default Page Status' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'default_page_status' ) ,
            'custom' =>
            array(
                array(
                    'label' => __( 'Draft' , 'slp-power' ) ,
                    'value' => 'draft' ,
                    'selected' => ($this->addon->options[ 'default_page_status' ] === 'draft') ,
                ) ,
                array(
                    'label' => __( 'Published' , 'slp-power' ) ,
                    'value' => 'publish' ,
                    'selected' => ($this->addon->options[ 'default_page_status' ] === 'publish') ,
                ) ,
                array(
                    'label' => __( 'Pending Review' , 'slp-power' ) ,
                    'value' => 'pending' ,
                    'selected' => ($this->addon->options[ 'default_page_status' ] === 'pending') ,
                ) ,
                array(
                    'label' => __( 'Future' , 'slp-power' ) ,
                    'value' => 'future' ,
                    'selected' => ($this->addon->options[ 'default_page_status' ] === 'future') ,
                ) ,
                array(
                    'label' => __( 'Private' , 'slp-power' ) ,
                    'value' => 'private' ,
                    'selected' => ($this->addon->options[ 'default_page_status' ] === 'private') ,
                ) ,
            ) ,
            'description' =>
            __( 'When creating new Store Pages, what should the default status be?.' , 'slp-power' ) . ' ' .
            __( 'Default mode is draft.' , 'slp-power' ) ,
            'use_prefix' => false
        ) );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'checkbox' ,
            'label' => __( 'Pages Replace Websites' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'pages_replace_websites' ) ,
            'description' => __( 'Use the Store Pages local URL in place of the website URL on the map results list.' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'pages_replace_websites' ] ,
            'use_prefix' => false
        ) );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'checkbox' ,
            'label' => __( 'Prevent New Window' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'prevent_new_window' ) ,
            'description' => __( 'Prevent Store Pages web links from opening in a new window.' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'prevent_new_window' ] ,
            'use_prefix' => false
        ) );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'checkbox' ,
            'label' => __( 'Prepend URL With Blog Path' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'prepend_permalink_blog' ) ,
            'description' => __( 'If checked the page URL will be prepended with the standard blog path. Example: ' .
                'if your permalink structure is /blog/, then your links will be /blog/store-page. ' .
                'If this is unchecked it will be /store-page.' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'prepend_permalink_blog' ] ,
            'use_prefix' => false
        ) );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'text' ,
            'label' => __( 'Permalink Starts With' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'permalink_starts_with' ) ,
            'description' =>
            __( 'This process takes a long time to update WordPress fils and ALL of your location data.' , 'slp-power' ) .
            __( 'Set the middle part of the store page URLs, defaults to "store_page".' , 'slp-power' ) .
            sprintf(
                __( '<a href="%s">Permalinks</a> needs to be set to something other than default.' , 'slp-power' ) , admin_url( 'options-permalink.php' )
            )
            ,
            'value' => $this->addon->options[ 'permalink_starts_with' ] ,
            'use_prefix' => false
        ) );

        // Group : Initial Page Features
        //
        $groupName = __( 'Initial Page Features' , 'slp-power' );

        $this->settings->add_ItemToGroup(
            array(
                'section' => $sectName ,
                'group' => $groupName ,
                'label' => '' ,
                'type' => 'subheader' ,
                'show_label' => false ,
                'use_prefix' => false ,
                'description' =>
                sprintf(
                    __( 'These settings determine how your custom %s pages are built. ' , 'slp-power' ) , SLPLus::locationPostType
                ) .
                ( $this->slplus->javascript_is_forced ?
                    '<br/><br/>' .
                    __( 'You have Force Load JavaScript ON. ' , 'slp-power' ) .
                    __( 'Themes that follow WordPress best practices and employ wp_footer() properly do not need this. ' , 'slp-power' ) .
                    __( 'Leaving it on slows down your site and disables a lot of extra features with the plugin and add-on packs. ' , 'slp-power' ) :
                    ''
                ) .
                ( $this->slplus->javascript_is_forced ?
                    __( 'For example, your Store Pages template has had the map element removed. ' , 'slp-power' ) :
                    ''
                )
            )
        );

        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'checkbox' ,
            'label' => __( 'Default Comments' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'default_comments' ) ,
            'use_prefix' => false ,
            'description' => __( 'Should comments be on or off by default when a new store page is created?' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'default_comments' ]
        ) );
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'checkbox' ,
            'label' => __( 'Default Trackbacks' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'default_trackbacks' ) ,
            'use_prefix' => false ,
            'description' => __( 'Should pingbacks/trackbacks be on or off by default when a new store page is created?' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'default_trackbacks' ]
        ) );

        if ( empty( $this->addon->options[ 'page_template' ] ) ) {
            $this->addon->options[ 'page_template' ] = SLP_Power_Pages_Admin::get_instance()->create_string_default_template();
        }
        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'type' => 'textarea' ,
            'label' => __( 'Page Template' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'page_template' ) ,
            'use_prefix' => false ,
            'description' =>
            __( 'The HTML that is used to create new store pages.' , 'slp-power' ) .
            __( 'Leave blank to reset to default layout.' , 'slp-power' )
            ,
            'value' => $this->addon->options[ 'page_template' ]
        ) );

        $this->settings->add_ItemToGroup( array(
            'section' => $sectName ,
            'group' => $groupName ,
            'label' => __( 'Read More Text' , 'slp-power' ) ,
            'setting' => $this->addon->setting_name( 'pages_read_more_text' ) ,
            'description' => __( 'Read more text to be used with [slp_pages] <!--more--> page breaks.' , 'slp-power' ) ,
            'value' => $this->addon->options[ 'pages_read_more_text' ] ,
            'use_prefix' => false
        ) );

        $this->settings->render_settings_page();
    }

}

