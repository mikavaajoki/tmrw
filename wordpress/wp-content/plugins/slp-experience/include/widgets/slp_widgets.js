/**
 * JavaScript for the SLP Widgets.
 *
 * @package StoreLocatorPlus\Experience\WidgetJS
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2015 Charleston Software Associates, LLC
 *
 */

var SLPWJS = SLPWJS || {};
var slpexperience = slpexperience || {};

// Enhanced Results Popup Email Form
//
SLPWJS.city_widget = {

    /**
     * Render the cities drop down.
     */
    create_cities_dropdown: function( cities ) {
        var all_cities_text = jQuery('INPUT#all_cities_text').val();
        var _select = jQuery('SELECT#slp_widget\\[city\\]');
        _select.html('');
        if ( all_cities_text ) {
            _select.append(jQuery('<option></option>').val('').html(all_cities_text));
        }
        jQuery.each( cities , function( val , text ) { _select.append(jQuery('<option></option>').val(text).html(text)); } );
        jQuery('SELECT#slp_widget\\[city\\] option:eq(0)').prop('selected',true);
        jQuery('SELECT#slp_widget\\[city\\]').show();
        jQuery('SELECT#slp_widget\\[city\\]').css( 'visibility' , 'visible' );
    },

    /**
     * Populate the cities drop down.
     */
    show_cities_in_state: function( ) {
        var data = {
            action: 'get_cities',
            source: 'slp',
            filter: 'in_state',
            filter_match: jQuery('SELECT#slp_widget\\[state\\] option:selected').val()
        };

        var selval =jQuery('SELECT#slp_widget\\[state\\] option:selected').val();

        // request cities (for this state) from server via AJAX
        // the callback will display/update the cities drop down
        jQuery.post( slp_experience.ajaxurl , data , SLPWJS.city_widget.process_cities_list );

    },

    /**
     * Process the cities answer from the server.
     *
     * @param response
     */
    process_cities_list: function( response ) {
        if ( typeof response.success === 'undefined' ) { response.success = false; }
        var valid_response = ( response.success ) && ( typeof response.cities  !== 'undefined' ) && ( response.cities.length > 0 );

        // Good Answer, build dropdown
        //
        if ( valid_response ) {
            SLPWJS.city_widget.create_cities_dropdown( response.cities );

        // Oh no.  What happened?
        //
        } else {
            if ( window.console ) {
                if ( response.success ) {
                    console.log('No cities returned from server. ');
                } else {
                    console.log('Server request to get_cities in_state failed.');
                }
            }
        }
    },

    /**
     * Show the selected
     */
    show_city_dropdown_if_selected: function( ) {
        var selval =jQuery('SELECT#slp_widget\\[city\\] option:selected').val();
        if ( selval ) {
            jQuery('SELECT#slp_widget\\[city\\]').show();
        }
    }

};

// Document Ready
jQuery( document ).ready(
    function () {
        SLPWJS.city_widget.show_city_dropdown_if_selected();
    }
);
