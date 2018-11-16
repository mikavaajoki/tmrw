// Experience Namespace
//
var SLPEXP = {};


// Popup Email Form
//
SLPEXP.email_form = {
    built: false,

    /**
     * Build the jQuery email form.
     *
     * Blank, no from/to/subject/message.
     */
    build_form: function( ) {
        if ( this.built ) return;
        jQuery( '#email_form').dialog(
            {
                autoOpen: false ,
                minHeight: 50 ,
                minWidth: 450 ,
                title: slpexperience_settings.email_form_title ,
                buttons: {
                    "Send"   : function() {
                        SLPEXP.email_form.send_email();
                        jQuery( this ).dialog( "close" );
                        } ,
                    "Cancel" : function() { jQuery( this).dialog( "close" ); }
                }
            }
            );
        this.built = true;
    },

    /**
     * Send the form content to the AJAX email handler.
     */
    send_email: function() {
        var ajax = new slp.Ajax();
        var action = {
            action: 'email_form' ,
            formdata: jQuery('#the_email_form').serialize()
        };
        ajax.send( action , function ( response ) { } );
        return true;
    },

    /**
     * Fill in the from/to/subject/message.
     *
     * @param sl_id the store ID
     */
    set_form_content: function( sl_id ) {
        jQuery( '#the_email_form input[name=sl_id]').val( sl_id );
    },

    /**
     * Show the email form.
     *
     * @param sl_id the store ID
     */
    show_form: function( sl_id ) {
        SLPEXP.email_form.set_form_content( sl_id );
        jQuery( '#email_form').dialog( "open" );
        return false;
    }

}

/**
 * Location List Object for SLPEXP
 */
SLPEXP.location_list = {

    first_load: true, // Is this the first time loading the directory?

    /**
     * Clear the widget search fields.
     */
    clear_widget_search: function( ) {
        jQuery('#searchForm [id^=slp_widget]').remove();
    },

    /**
     * Disable Initial Directory
     */
    disable_directory:  function () {
        if ( SLPEXP.location_list.first_load &&
            ( typeof( slplus.options.disable_initial_directory ) !== 'undefined' ) &&
            ( slplus.options.disable_initial_directory == '1' )
        )  {
            jQuery('div#map_sidebar').hide();
        } else {
            jQuery('div#map_sidebar').show();
            jQuery('#map_sidebar').off( 'contentchanged' , SLPEXP.location_list.disable_directory );
        }
        SLPEXP.location_list.first_load = false;
    },

    /**
     * Subscribe to the slp.js location string related filters.
     *
     * Subscribe to geocode_results
     *
     * @see https://api.jquery.com/jQuery.Callbacks/
     */
    setup_subscriptions: function () {
        jQuery('#map_sidebar').on('contentchanged', SLPEXP.location_list.clear_widget_search );
		jQuery('#map_sidebar').on('contentchanged', SLPEXP.email_form.build_form );
    }

}

/**
 * Address Quick Search Object for SLPEXP
 */
SLPEXP.address_quicksearch = {

    /**
     * Attach the address input handler if needed.
     */
    attach_ui_handler: function() {

        // Check that the quicksearch is enabled.
        //
        if (
            ( typeof slplus.options['address_autocomplete'] != 'undefined' ) &&
            ( slplus.options['address_autocomplete'] === 'zipcode' )
        ) {
            jQuery( '#addressInput').autocomplete(
                {
                    source: SLPEXP.address_quicksearch.query_locations,
                    minLength: slplus.options.address_autocomplete_min
                }
            );
        }
    },

    /**
     * Query locations for autocomplete on zip.
     *
     * @param request
     * @param callback
     */
    query_locations: function( request , callback ) {
        post_vars = { action: 'slp_list_location_zips' , address: request.term };
        jQuery.post(
            slplus.ajaxurl,
            post_vars ,
            function (results) { callback( JSON.parse(results) ); }
        );
    }
}

/**
 * The Search Form Object for SLPEXP
 */
SLPEXP.search_form = {

    // Select the incoming state on the search form
    //
    show_state_selection: function () {
        var state_widget ='[id=slp_widget\\[state\\]]';
        if ( jQuery( state_widget ).length ) {
            jQuery('#searchForm [id=addressInputState]:visible').val(jQuery(state_widget).val());
        }
    }
}

/**
 * The address and city/state/country selector manager for 'either_or' mode.
 *
 */
SLPEXP.selector_handler = {

    address_value: '',
    current_value: {
        'addressInputState' : ''
    },

    /**
     * Attach the city/state/zip and address interaction handler if needed.
     */
    attach_ui_handler: function() {

        // City/State/Zip Selector Disables Address
        if  (
            ( typeof slplus.options['selector_behavior'] != 'undefined' ) &&
            ( slplus.options['selector_behavior'] === 'either_or' )
        ) {
            jQuery( '#addressInput').click( SLPEXP.selector_handler.activate );
            jQuery( '#addressInputCity').click( SLPEXP.selector_handler.activate );
            jQuery( '#addressInputState').click( SLPEXP.selector_handler.activate );
            jQuery( '#addressInputCountry').click( SLPEXP.selector_handler.activate );
        }
    } ,

    /**
     * Activate the given input element.  Store selections if necessary.
     */
    activate: function( ) {
        element_name = jQuery( this ).attr('name');

        switch ( element_name ) {
            case 'addressInputCity'     :
            case 'addressInputState'    :
            case 'addressInputCountry'  :
                SLPEXP.selector_handler.save_address();
                SLPEXP.selector_handler.restore_dropdown_selection( element_name );
                break;

            case 'addressInput' :
                SLPEXP.selector_handler.save_dropdown_selection('addressInputCity'   );
                SLPEXP.selector_handler.save_dropdown_selection('addressInputState'  );
                SLPEXP.selector_handler.save_dropdown_selection('addressInputCountry');
                SLPEXP.selector_handler.restore_address();
                break;
        }

    },

    restore_address: function() {
        current_address_value = jQuery( '#addressInput' ).val();

        // Save the address value if it is not empty
        //
        if ( ! current_address_value ) {
            jQuery('#addressInput').val( this.address_value );
            this.address_value = '';
        }
    },


    /**
     * Restore dropdown selection.
     */
    restore_dropdown_selection: function( element_name ) {
        element_id    = '#' + element_name;
        current_value = jQuery( element_id ).find( 'option:selected').val();

        // If the dropdown is empty AND the previously saved value is NOT empty...
        //
        if ( ( ! current_value ) && ( this.current_value[element_name] ) ) {
            option_by_val = element_id + ' option[value="' + SLPEXP.selector_handler.current_value[element_name] + '"]';

            jQuery( element_id ).val( SLPEXP.selector_handler.current_value[element_name] );
            jQuery( element_id ).blur();
            jQuery( element_id ).focus();
            jQuery( option_by_val ).prop( 'selected' , true );

            jQuery( element_id ).show();

            this.current_value[element_name] = '';
        }
    },

    /**
     * Save address.
     */
    save_address: function() {
        current_address_value = jQuery( '#addressInput' ).val();

        // Save the address value if it is not empty
        //
        if ( current_address_value ) {
            SLPEXP.selector_handler.address_value = current_address_value;
            jQuery('#addressInput').val('');
        }
    } ,

    /**
     * Save dropdown selection.
     */
    save_dropdown_selection: function( element_name ) {
        element_id    = '#' + element_name;
        current_value = jQuery( element_id ).find( 'option:selected').val();

        if ( current_value ) {
            SLPEXP.selector_handler.current_value[element_name] = current_value;
            jQuery( element_id ).val( '' );
        }
    }
}

/**
 * The Google Map Manager for SLPEXP
 */
SLPEXP.map = {

    /**
     * Subscribe to the slp.js map options filters.
     *
     * Subscribe to map_options
     *
     * @see https://api.jquery.com/jQuery.Callbacks/
     */
    setup_subscriptions: function () {
        slp_Filter('map_options').subscribe(this.modify_options);
    },

    /**
     * Hide the StreetView on the Google Map interface.
     *
     * @param map_options
     */
    modify_options: function( map_options ) {
        map_options.mapTypeControl  = ( slpexperience_settings.map_options_mapTypeControl === '1' );
        map_options.scrollwheel     = ( slpexperience_settings.map_options_scrollwheel    === '1' );
        map_options.scaleControl    = ( slpexperience_settings.map_options_scaleControl   === '1' );
        map_options.hide_bubble     = ( slpexperience_settings.hide_bubble                === '1' );
    }
}

// Document Ready
//
jQuery( document ).ready(
    function () {
        //SLPEXP.email_form.build_form();

        SLPEXP.selector_handler.attach_ui_handler();

        SLPEXP.address_quicksearch.attach_ui_handler();

        SLPEXP.map.setup_subscriptions();

        SLPEXP.location_list.setup_subscriptions();
        
        SLPEXP.search_form.show_state_selection();

        jQuery('#map_sidebar').on( 'contentchanged' , SLPEXP.location_list.disable_directory );
    }
);
