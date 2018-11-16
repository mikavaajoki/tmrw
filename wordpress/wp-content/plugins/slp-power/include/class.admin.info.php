<?php
if ( ! class_exists( 'SLPPower_Admin_Info' ) ) {

	/**
	 * The things that modify the Admin / General Tab.
	 *
	 * @package StoreLocatorPlus\SLPPower\Admin\Info
	 * @author Lance Cleveland <lance@storelocatorplus.com>
	 * @copyright 2016 Charleston Software Associates, LLC
	 *
	 * Text Domain: slp-power
	 *
	 * @property        SLPPower          $addon
	 */
	class SLPPower_Admin_Info  extends SLPlus_BaseClass_Object {
		public  $addon;

        /**
         * Things we do at the start.
         */
        public function initialize() {
            $this->add_hooks_and_filters();
        }

        /**
         * WP and SLP hooks and filters.
         */
        private function add_hooks_and_filters() {
            add_filter( 'slp_version_report_' . $this->addon->short_slug , array( $this , 'show_activated_modules' ) );
        }

        /**
         * Show activated modules.
         *
         * @param $version
         *
         * @return mixed
         */
        public function show_activated_modules( $version ) {
            $active_modules = array();
            if ( $this->slplus->SmartOptions->use_pages->is_true ) {
                $active_modules[] = 'Pages';
            }
            if ( $this->slplus->SmartOptions->use_contact_fields->is_true ) {
                $active_modules[] = 'Contacts';
            }

            if ( ! empty( $active_modules ) ){
                $active_modules = '<br/><span class="label">+</span>' . join( ' , ' , $active_modules );
            } else {
                $active_modules = '';
            }

            return $version . $active_modules;
        }
    }
}