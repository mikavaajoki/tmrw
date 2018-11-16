<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'SLP_Premier_Security_Block_IP' ) ) {

    /**
     * Class SLP_Premier_Security_Block_IP
     *
     * Manage the functionality for blocking an IP.
     */
    class SLP_Premier_Security_Block_IP  extends SLPlus_BaseClass_Object {

        /**
         * Things we do at the start.
         */
        public function initialize() {
            if ( ! $this->slplus->AddOns->is_premier_subscription_valid() ) return;
            $this->add_hooks();
        }

        /**
         * Add the hooks needed for logging IP addresses.
         */
        private function add_hooks() {
            add_filter( 'slp_ajax_find_locations_complete' , array( $this, 'track_request_ips' ), 10, 2);
        }

        /**
         * @param array $results
         *
         * @return array
         */
        private function get_blocked_response( $results ) {
            return array(
                'count' => 0,
                'type'=> $results['type'],
                'blocked' => true
            );
        }

        /**
         * Get the users IP address.
         *
         * @return string
         */
       private function get_the_user_ip() {
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
            }
            return $ip;
        }

       /**
        * Is the IP address on the whitelist.
        *
        * @param string $ip_address
        *
        * @return bool
        */
       private function ip_address_is_whitelisted( $ip_address ) {
           if ( empty( $this->slplus->SmartOptions->ip_whitelist->value ) ) return false;

           $whitelist = preg_split( '/\s+/' , $this->slplus->SmartOptions->ip_whitelist->value );

           foreach ( $whitelist as $white_address ) {
               if ( $this->cidr_match( $ip_address, $white_address) )
                   return true;
           }

           return false;
       }

        /**
         * Test if IP address within CIDR
         *
         * @param string $ip
         * @param string $cidr
         *
         * @return bool
         */
       private function cidr_match($ip, $cidr) {
           list($subnet, $mask) = explode('/', $cidr);

           if ( is_null( $mask ) && ( $ip === $cidr ) ) {
               return true;
           }

           if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet)) {
               return true;
           }

           return false;
       }

        /**
         * Track query request IP addresses.
         *
         * @used-by add_hooks for filter slp_ajax_find_locations_complete
         *
         * @param   array $results
         * @return  array $results
         */
        public function track_request_ips( $results ) {
            $ip_address = $this->get_the_user_ip();
            if ( empty( $ip_address ) )  $this->get_blocked_response( $results );
            if ( $this->ip_address_is_whitelisted( $ip_address ) ) return $results;

            $limit_handle = 'slp-request-from-ip-' . $ip_address;
            $blocked_handle = 'slp-blocked-ip-' . $ip_address;

            $ip_is_blocked = ( get_transient( $blocked_handle ) !== false );

            // IP Blocked
            if ( $ip_is_blocked ) {
                $results = $this->get_blocked_response( $results );

            // IP OK
            } else {
                $request_list = get_transient( $limit_handle );
                if ( $request_list === false ) {
                    $request_list = array();
                }

                $request_list = $this->update_request_list( $request_list );
                $count = count( $request_list );

                set_transient( $limit_handle , $request_list , intval( $this->slplus->SmartOptions->block_ip_period->value ) );

                // IP is over limit
                if ( $count > intval( $this->slplus->SmartOptions->block_ip_limit->value ) ) {
                    set_transient( $blocked_handle , $request_list , intval( $this->slplus->SmartOptions->block_ip_release_after->value ) );
                    $results = $this->get_blocked_response( $results );
                }
            }


            return $results;
        }

        /**
         * Add latest request to the request list, drop requests outside the Block Requests Time Span
         *
         * @param array $request_list
         * @return array
         */
        private function update_request_list( $request_list ) {
            $this_time = time();
            $past_time = $this_time - intval( $this->slplus->SmartOptions->block_ip_period->value );

            // Drop any expired requests from the list
            foreach ( $request_list as $key => $value ) {
                if ( $value <= $past_time ) {
                    unset( $request_list[$key] );
                }
            }

            // Add the latest request
            $request_list[] = $this_time;

            return $request_list;
        }

    }


    /** @var SLPlus $slplus */
    global $slplus;
    if ( is_a( $slplus, 'SLPlus' ) ) {
        $slplus->add_object( new SLP_Premier_Security_Block_IP() );
    }
}
