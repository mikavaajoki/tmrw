<?php
defined( 'ABSPATH' ) || exit;

/**
 * The Notification System.
 *
 * Puts notifications on top of wpCSL plugin admin pages.
 *
 * @property        boolean                                 $enabled
 * @property        string                                  $name
 * @property-read   SLP_Notification[]                      $notices
 */
class SLP_Notifications_Manager extends SLPlus_BaseClass_Object {
    private $alert_action;
    private $alert_icons = array();
    private $notices = null;
    public  $enabled = true;
    private $log_only = false;

    /**
     * Things we do at the start.
     */
    public function initialize() {
        if ( defined( 'DOING_CRON' ) ) {
            $this->enabled = false;
	    } else {
            $this->setup();
	    }
    }

    /**
     * Set me up.
     */
    private function setup() {
	    require_once( SLPLUS_PLUGINDIR . 'include/unit/SLP_Notice.php' );

	    $this->alert_action = '<span class="button close"><i class="fa fa-close"></i></span>';

	    $this->alert_icons['success']     = 'fa-check';
	    $this->alert_icons['information'] = 'fa-info';
	    $this->alert_icons['warning']     = 'fa-exclamation';
	    $this->alert_icons['is-error']    = 'fa-exclamation-triangle';
    }

    /**
     * Add a notification to the notice stack
     *
     * @param mixed $level - int (1 severe, 9 info) string 'error','warning','info'
     * @param string $content - the message
     * @param string $link - url
     */
    function add_notice($level = 1, $content='', $link = null) {
        if ( ! $this->enabled ) { return; }

        switch ($level):
            case '1':
                $level = 'is-error';
                break;
            case '5':
            case '6':
            case '9':
                $level = 'information';
                break;
            case '10':
            case 'neutral':
            default:
                $level = 'success';
	            break;
        endswitch;

        $this->notices[] = new SLP_Notice( array( 'level' => $level, 'content' => $content, 'link' => $link ) );
    }

    /**
     * Disable Logging
     */
    public function disable_logging() {
	    $this->log_only = true;
	    $this->enabled = true;
    }

    /**
     * Reset the notices to a blank array.
     */
    public function delete_all_notices() {
	    if ( ! $this->enabled ) { return; }
	    $this->notices = null;
    }

    /**
     * Enable Logging
     */
    public function enable_logging() {
        $this->log_only = true;
        $this->enabled = true;
        $this->setup();
    }

    /**
     * Render the notices to the browser page.
     */
    function display() {
        if ( ! $this->enabled ) { return; }
        echo $this->get_html();
    }

    /**
     * Return a list of notices.
     *
     * @return SLP_Notification[]
     */
    public function get() {
        return $this->notices;
    }

   /**
    * Return a formatted HTML string representing the notification.
    *
    * @return string - the HTML or simple string output
    */
   public function get_html() {
       if ( ! $this->enabled || empty( $this->notices ) ) {
           return '';
       }

        $notice_output = '';

       /**
        * @var SLP_Notice $notice
        */
        foreach ($this->notices as $notice) {
            $notice_output .=
                sprintf(
                    '<div class="slp-notification alert-center %s active">' .
	                    '<div class="alert-icon"><i class="fa %s"></i></div>' .
	                    '<div class="alert-content">%s</div>' .
	                    '<div class="alert-action">%s</div>' .
	                '</div>'
	                ,
	                $notice->level ,
                    $this->alert_icons[ $notice->level ],
	                $notice->display() ,
	                $this->alert_action
                );
        }

        $this->delete_all_notices();

        return $notice_output;
    }
}
