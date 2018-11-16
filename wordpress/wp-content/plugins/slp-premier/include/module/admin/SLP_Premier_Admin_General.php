<?php
/**
 * The things that modify the Admin / General Tab.
 *
 * @property    SLP_Premier                    $addon
 * @property    SLP_Premier_Admin             $admin
 * @property    SLP_Premier_Admin_General_Text $Admin_General_Text
 */
class SLP_Premier_Admin_General extends SLPlus_BaseClass_Object {
	public $addon;
	public $admin;
	private $group_params;

	/**
	 * Things we do at the start.
	 */
	function initialize() {
		SLP_Premier_Admin_General_Text::get_instance();
		$this->group_params = array( 'plugin' => $this->addon , 'section_slug' => null , 'group_slug' => null );

        // WooCommerce
        //
        if ( $this->addon->is_woo_running() ) {
            add_action( 'slp_generalsettings_modify_userpanel'  , array( $this , 'add_woo_group' ), 10, 3 );
        }

		add_action( 'slp_build_general_settings_panels'     , array( $this , 'add_schedule_subtab'           ), 90    );

		if ( ! $this->slplus->AddOns->is_premier_subscription_valid() ) {
			$this->slplus->notifications->add_notice( 'information' , __( 'Enter your subscription details on the General Admin tab to access additional settings.' , 'slp-premier' ) );
			return;
		}
	}

	/**
	 * General / Schedule
	 *
	 * @param   SLP_Settings    $settings
	 */
	public function add_schedule_subtab( $settings ) {
		$this->group_params[ 'section_slug' ] = 'schedule';
		$this->group_params[ 'group_slug'   ] = 'tasks';
		$settings->add_group( $this->group_params );


	}

    /**
     * Add General / UI
     *
     * @param SLP_Settings  $settings
     * @param string        $section_name
     * @param array         $section_params
     */
    function add_woo_group( $settings , $section_name , $section_params ) {
        $group_params['group_slug']     = 'general_woocommerce';
        $group_params['section_slug']   = $section_params['slug'];
        $group_params['plugin']         = $this->addon;
        $group_params['header']         = __( 'WooCommerce' , 'slp-premier' );
        $group_params['intro']          = __( 'These settings impact how Store Locator Plus interacts with Woocommerce. ' , 'slp-premier' );

        $settings->add_group( $group_params );

        $settings->add_ItemToGroup(array(
            'group_params'  => $group_params,
            'type'          => 'checkbox'  ,
            'option'        => 'show_location_on_order_email',
            'label'         => __('Order Email Location Info' , 'slp-premier' ),
            'description'   => __( 'Shows location address, phone, and email on order email to customer.' , 'slp-premier' )
        ));
    }

}
