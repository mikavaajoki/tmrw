<?php

if ( ! class_exists( 'SLP_Notice' ) ) {
    /**
     * This class represents each individual notice.
     *
     * @author    Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2016 Charleston Software Associates
     * @package   StoreLocatorPlus\Notification
     *
     * @property    string      $level      What level  (1 = error , 5 = warning , 9 = info)
     * @property    string      $content
     * @property    string      $link
     */
    class SLP_Notice extends SLPlus_BaseClass_Object {
        public $level;
        public $content;
        public $link;
	    protected $uses_slplus = false;


        function display() {
            $retval = $this->content;
            if ( isset( $this->link ) &&
                 ! is_null( $this->link ) &&
                 ( $this->link != '' )
            ) {
                $retval .= " (<a href=\"{$this->link}\">Details</a>)";
            }

            return $retval;
        }
    }
}
