/**
 * Power JS for Admin Locations Tab
 */

// Setup the Power namespace
var SLPPOWER_ADMIN_LOCATIONS = SLPPOWER_ADMIN_LOCATIONS || {};

/**
 * Location Filters.
 */
var slp_power_filters = function () {
    var state_list_populated = false;
    var country_list_populated = false;

    /**
     * Initialize.
     */
    this.initialize = function () {
        jQuery('#state_filter').click( this.get_state_list );
        jQuery('#country_filter').click( this.get_country_list );
    };

    /**
     * Get the list of countries and populate the drop down.
     */
    this.get_country_list = function() {
        if ( SLPPOWER_ADMIN_LOCATIONS.filters.country_list_populated ) {
            return;
        }

        jQuery('div#country_filter_spinner').addClass('is-active');

        var post_data = {};
        post_data['action']     = 'slp_get_country_list';

        var ajax_settings = {};
        ajax_settings['url']    = ajaxurl;
        ajax_settings['method'] = 'POST';
        ajax_settings['dataType'] = 'jsonp';
        ajax_settings['data'] = post_data;

        var request = jQuery.ajax( ajax_settings );

        request.always( function( response ) { SLPPOWER_ADMIN_LOCATIONS.filters.populate_country_list( response.responseText ); } );
    };

    /**
     *
     * @param response
     */
    this.populate_country_list = function( response ) {
        jQuery('div#country_filter_spinner').removeClass( 'is-active' );

        var json_response = JSON.parse( response );
        if ( json_response.success ) {
            var country_dropdown = jQuery( 'select#country_filter' );
            for ( var cnt = 0 ; cnt < json_response.data.count ; cnt++ ) {
                country_dropdown.append(jQuery('<option />').attr( 'value' , json_response.data.states[cnt] ).text( json_response.data.states[cnt] ));
            }

            SLPPOWER_ADMIN_LOCATIONS.filters.country_list_populated = true;
        } else {
            SLPPOWER_ADMIN_LOCATIONS.filters.state_list_populated = false;
        }
    };

    /**
     * Get the list of states and populate the drop down.
     */
    this.get_state_list = function() {
        if ( SLPPOWER_ADMIN_LOCATIONS.filters.state_list_populated ) {
            return;
        }

        jQuery('div#state_filter_spinner').addClass('is-active');

        var post_data = {};
        post_data['action']     = 'slp_get_state_list';

        var ajax_settings = {};
        ajax_settings['url']    = ajaxurl;
        ajax_settings['method'] = 'POST';
        ajax_settings['dataType'] = 'jsonp';
        ajax_settings['data'] = post_data;

        var request = jQuery.ajax( ajax_settings );

        request.always( function( response ) { SLPPOWER_ADMIN_LOCATIONS.filters.populate_state_list( response.responseText ); } );
    };

    /**
     *
     * @param response
     */
    this.populate_state_list = function( response ) {
        jQuery('div#state_filter_spinner').removeClass('is-active');

        var json_response = JSON.parse( response );
        if ( json_response.success ) {
            var state_dropdown = jQuery( 'select#state_filter' );
            for ( var cnt = 0 ; cnt < json_response.data.count ; cnt++ ) {
                state_dropdown.append(jQuery('<option />').attr( 'value' , json_response.data.states[cnt] ).text( json_response.data.states[cnt] ));
            }

            SLPPOWER_ADMIN_LOCATIONS.filters.state_list_populated = true;
        } else {
            SLPPOWER_ADMIN_LOCATIONS.filters.state_list_populated = false;
        }
    }
};

/**
 * Location Messages.
 */
var slp_power_messages = function () {

    /**
     * Clear the import messages list.
     */
    this.clear_import_messages = function () {
        jQuery( '.import_message_block').empty();

        var post_data = {};
        post_data['action']     = 'slp_clear_import_messages';
        jQuery.post( ajaxurl, post_data , this.process_clear_import_messages_response );
    };

    /**
     * Handle the clear response.
     *
     * @param response
     */
    this.process_clear_import_messages_response = function( response ) {
        if ( response !== 'ok' ) {
            jQuery('.import_message_block').html( '<span class="clear failed">***</span>' );
        }
    };
};

/**
 * Power locations setup.
 */
var slp_power_locations = function () {

    /**
     * Initialize our Power ups.
     */
    this.initialize = function() {
        jQuery('a[data-action="create_page"]').click( this.create_page );
        jQuery('#import_button').click( function() { AdminUI.doAction( 'import' ) } );
    };

    /**
     * Make it so.   Create the page with AJAX (maybe REST someday) magic.
     * @param event
     */
    this.create_page = function( event ) {
        var data = {};
        data[ 'action'             ] = 'slp_create_page';
        data[ 'id'                 ] = jQuery( this ).attr( 'data-id' );
        data[ 'screenoptionnonce'  ] = jQuery( '#screenoptionnonce' ).val();

        var messages = {
            'message_ok' : 'SEO page created.',
            'message_info' : 'Could not create SEO page.',
            'message_failure' : 'Could not communicate with the server.',
        };

        var tr = jQuery( '#location-' + data['location_id'] );

        SLP_ADMIN.ajax.post( data , messages );
    }
};

/**
 * Infobox class.
 */
var slp_power_infobox = function() {
    var current_message = '';

    /**
     * Update the info box.
     */
    this.update = function( json_data ) {

        // Show alerts returned in json data.
        //
        if ( json_data.alert ) {
            alert( json_data.alert);
            return;
        }

        // No message? Leave.
        //
        if ( ! json_data.message ) {
            return;
        } else {
            this.current_message = json_data.message;
        }
        jQuery('#slp-power_messages').append( this.create_string_message_div() );
        jQuery('#slp-power_message_board').show();
    } ,

        /**
         * Create the message string div.
         */
        this.create_string_message_div = function() {
            return (
                '<div class="slp-power_message">' +
                this.current_message +
                '</div>'
            );
        }
};

/**
 * Async Uploader
 */
var slp_power_location_import = function() {
    var csv_file    = jQuery( '#csv_file' );
    var geo_card    = jQuery( '.geocode_card' );
    var geo_status_pump;
    var import_card = jQuery( '.import_card' );

    this.initialize = function() {

        /**
         * Interval Updates
         */
        import_card.on( 'get_update' , this.get_import_update );
        import_card.on( 'click' , this.get_import_update );
        geo_card.on( 'get_update' , this.get_geocode_update );
        geo_card.on( 'click' , this.get_geocode_update );

        /**
         * On CSV File Input Change
         */
        csv_file.on('change', function(e) {
            e.preventDefault();

            // Once we start an import any clicking of the Locations | List menu item should reload.
            jQuery( '#wpcsl-option-current_locations_sidemenu' ).click( function() { window.location = jQuery( '#locationForm' ).attr( 'action' ); } );

            if ( ! csv_file[0].files[0].name ) return;

            var formData = new FormData();

            formData.append( 'data_type' , 'location_csv' );
            formData.append('action', 'upload-attachment');
            formData.append('async-upload', csv_file[0].files[0]);
            formData.append('name', csv_file[0].files[0].name);
            formData.append('_wpnonce', location_import.nonce);

            var upload_div_id;
            var uploaded_file;

            /**
             * Post ajax to WP async-uploader
             */
            jQuery.ajax({
                url: location_import.upload_url,
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                type: 'POST',

                /**
                 * Upload Success
                 *
                 * @param resp
                 */
                success: function(resp) {
                    uploaded_file = resp.data.filename;

                    if ( resp.success ) {
                        jQuery.get( location_import.cron_url );
                        csv_file.val('');
                        var att_id = '0';
                        var pct_complete = (( resp.data.meta.offset / resp.data.meta.size ) * 100).toFixed(2) + '%';
                        var new_import_card = jQuery( '[data-attachment_id="' + att_id + '"]' );
                        att_id =  resp.data.id;
                        new_import_card.attr( 'data-attachment_id' ,att_id );
                        var card_header = new_import_card.find( '.header_link' );
                        card_header.text( uploaded_file );
                        card_header.attr( 'href' , resp.data.link );
                        var progress_bar = new_import_card.find( '.progress' );
                        progress_bar.attr( 'aria-valuetext' , pct_complete );
                        progress_bar.find('.progress-meter').css( 'width' , pct_complete );
                        progress_bar.find('.progress-meter-text').text( pct_complete );

                        AdminUI.notifications.remove_all();
                        new_import_card.removeClass( 'hidden' );
                        new_import_card.trigger( 'get_update' );

                    } else {
                        AdminUI.notifications.add( 'info' , 'Uploading ' + uploaded_file + ' failed.' );
                        if ( resp.data.message ) {
                            AdminUI.notifications.add( 'error' , resp.data.message );
                        }

                    }
                },

                /**
                 * Before Sending File...
                 */
                beforeSend: function() {
                    upload_div_id = AdminUI.notifications.add( 'info' , 'Uploading ' + csv_file[0].files[0].name + '&hellip;' );
                },

                /**
                 *
                 * @returns {*}
                 */
                xhr: function() {
                    var myXhr = jQuery.ajaxSettings.xhr();

                    if ( myXhr.upload ) {
                        myXhr.upload.addEventListener( 'progress', function(e) {
                            if ( e.lengthComputable ) {
                                var perc = ( e.loaded / e.total ) * 100;
                                perc = perc.toFixed(2);
                                AdminUI.notifications.update( upload_div_id , 'Server received ' + perc + '% of ' + uploaded_file );
                            }
                        }, false );
                    }

                    return myXhr;
                }
            });
        });

    };

    /**
     * Geocode Card Update
     */
    this.get_geocode_update = function( obj ) {
        if ( geo_status_pump ) return;

        var card = jQuery( obj.currentTarget );
        geo_card.find( '.reload_icon' ).fadeOut( 300 );
        geo_status_pump = setInterval(function() {
            jQuery.getJSON(location_import.rest_geocode_url )
                .done(function (resp) {
                    var all_encoded = true;
                    jQuery.each( resp.data.jobs , function( i , item ) {
                        var pct_complete = (( (item.start_uncoded - resp.data.current_uncoded) / item.start_uncoded ) * 100).toFixed(2);
                        var progress_bar = card.find( '#geocode_' + item.max );
                        if ( progress_bar.length < 1 ) {
                            var progress_bar = card.find( '#geocode_0' );
                            progress_bar.attr( 'id' , 'geocode_' + item.max );
                            progress_bar.removeClass( 'hidden' );
                        }
                        progress_bar.attr('aria-valuenow', pct_complete);
                        progress_bar.attr('aria-valuetext', pct_complete + '%' );
                        progress_bar.find('.progress-meter').css('width', pct_complete + '%' );
                        progress_bar.find('.progress-meter-text').text( pct_complete + '%' );
                        if ( all_encoded && ( pct_complete < 100 ) ) {
                            all_encoded = false;
                        }
                    });


                    // Update current record
                    if ( resp.data.current_location !== '' ) {
                        geo_card.find('.current_record').html(resp.data.current_location);
                    }

                    // Clear this out.
                    if ( ( resp.data.current_uncoded <= 0 ) || all_encoded ) {
                        clearInterval( geo_status_pump );
                        geo_card.fadeOut( 3000 );
                    }
                })
                .fail( function( resp ) {
                    clearInterval( geo_status_pump );
                    console.log( 'geo_pump request failed' );
                })
            ;
        } , 1500 );
    };

    /**
     * Import Card Update
     */
    this.get_import_update = function( obj ) {
        var card = jQuery( obj.currentTarget );
        var att_id = card.attr( 'data-attachment_id' );
        var progress_bar = card.find('.progress');
        var import_status_pump = setInterval(function() {
            jQuery.getJSON(location_import.rest_imports_url)
                .done(function (resp) {
                    var pct_complete = 100;
                    var record = '';
                    if ( resp.data[att_id] ) {
                        var data = resp.data[att_id];
                        pct_complete = (( data.meta.offset / data.meta.size ) * 100).toFixed(2);
                        record = data.meta.record;
                    }
                    progress_bar.attr('aria-valuenow', pct_complete);
                    progress_bar.attr('aria-valuetext', pct_complete + '%' );
                    progress_bar.find('.progress-meter').css('width', pct_complete + '%' );
                    progress_bar.find('.progress-meter-text').text( pct_complete + '%' );
                    card.find('.current_record').html( record );

                    if ( pct_complete == 100 ) {
                        clearInterval(import_status_pump);
                        card.attr( 'data-attachment_id' , 0 );
                        card.fadeOut( 3000 );
                        geo_card.trigger( 'get_update' );
                        geo_card.fadeIn( 2000 );

                    }
                })
                .fail( function( resp ) {
                    clearInterval( import_status_pump );
                    console.log( 'progresspump request failed' );
                })
            ;
        } , 1500 );
    };
};

/**
 * Location Edit Loader
 */
var slp_power_location_edit = function () {
    /**
     * Connect to the SLP location editor form loader
     */
    this.setup_subscriptions = function () {
        slp_Admin_Filter('location_edit_init').subscribe(this.setup_edit);
    };

    /**
     * Setup the form for editing a location
     */
    this.setup_edit = function ( location_edit_options ) {

        // Category Checklist
        //

        // Uncheck All
        location_edit_options.form_div.find( 'input[name="tax_input[stores][]"]').each( function ( index ) {
            jQuery( this ).prop( 'checked' , false );
        });

        // Add mode - done.
        if ( SLP_Location_Manager.vue.update_app.act === 'add' ) {
            return;
        }

        // Edit mode - check all location cats
        location_edit_options.table_row.find( 'a.category_edit_link' ).each( function ( index ) {
            location_edit_options.form_div.find( '#in-stores-' + jQuery( this ).attr( 'data-value' ) ).prop( 'checked' , true );

        });
    };
};

// Document Ready
jQuery( document ).ready(
    function () {
        SLPPOWER_ADMIN_LOCATIONS.power_up = new slp_power_locations();
        SLPPOWER_ADMIN_LOCATIONS.power_up.initialize();

        SLPPOWER_ADMIN_LOCATIONS.filters = new slp_power_filters();
        SLPPOWER_ADMIN_LOCATIONS.filters.initialize();
        SLPPOWER_ADMIN_LOCATIONS.messages = new slp_power_messages();

        // If we don't have SLP 4.9.3 quick_save support...
        if ( typeof SLP_ADMIN.options.quick_save === 'undefined' ) {
            jQuery('.quick_save').find(':input').on('change', function (e) {
                SLP_ADMIN.options.change_option(e.currentTarget);
            });
        }

        // Process incoming request actions
        //
        switch (location_import.action) {

            // Export immediate mode, load CSV into iFrame.
            //
            case 'export':
                jQuery('#power_csv_download').attr('src',
                    ajaxurl + '?' +
                    jQuery.param(
                        {
                            action: 'slp_download_locations_csv',
                            filename: 'locations',
                            formdata: jQuery('#locationForm').serialize()
                        }
                    )
                );
                break;

            // Export, locally hosted.
            //
            case 'export_local':
                var infobox = new slp_power_infobox();
                infobox.update({'message': location_import.download_file_message});
                break;
        }

        SLPPOWER_ADMIN_LOCATIONS.upload_ux = new slp_power_location_import();
        SLPPOWER_ADMIN_LOCATIONS.upload_ux.initialize();

        SLPPOWER_ADMIN_LOCATIONS.location_editor = new slp_power_location_edit();
        SLPPOWER_ADMIN_LOCATIONS.location_editor.setup_subscriptions();
    }
);
