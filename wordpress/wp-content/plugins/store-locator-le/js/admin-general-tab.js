/**
 * @package StoreLocatorPlus\Admin\GeneralTab
 *
 */

var SLP_GENERAL = SLP_GENERAL || {};

/**
 * General Tab
 */
SLP_GENERAL.messages = ( function () {

    /**
     * Clear the schedule messages list.
     */
    this.clear_schedule_messages = function () {
        jQuery( '.schedule_message_block').empty();

        var post_data = new Object();
        post_data['action']     = 'slp_clear_schedule_messages';
        jQuery.post( ajaxurl, post_data , this.process_clear_schedule_messages_response );
    };

    /**
     * Handle the clear response.
     *
     * @param response
     */
    this.process_clear_schedule_messages_response = function( response ) {
        if ( response !== 'ok' ) {
            jQuery('.schedule_message_block').html( '<span class="clear failed">***</span>' );
        }
    };

    /**
     * Public stuff
     */
    return {
        clear_schedule_messages: clear_schedule_messages
    }

} ) ();

