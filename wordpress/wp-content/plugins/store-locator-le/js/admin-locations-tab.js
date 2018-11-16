/*global location_manager */
/**
 * SLP JS for Admin Locations Tab
 */
var SLP_Location_Manager = SLP_Location_Manager || {};

/**
 * Vue locations.
 */
SLP_Location_Manager.vue = ( function() {
	var load_app,
        update_app;

	this.initialize = function () {
	    // Load locations
		this.load_app = new Vue({
			el: '#wpcsl-option-load',
			data: location_manager,
			methods: {
				/**
                 * Load Locations From A Remote WordPress Site
				 */
				load_locations: function() {
				    var _this = this,
				        full_url = ( _this.boolean.site_import_protocol ? 'https://' : 'http://' ) + _this.site_import_url + '/wp-json/store-locator-plus/v2/locations';
				    _this.boolean.loading_locations = true;
				    _this.notices = [];
                    fetch(  full_url  )
                        .then(
                            function( list_response ) {
                                list_response.json()
                                    .then( function( locations ) {
									     locations.forEach(
                                             function( location ) {
                                                 fetch( full_url + '/' + location.sl_id )
                                                 .then( function ( location_details ) {
                                                    location_details.json()
                                                        .then( function( location ) {
                                                            _this.locations.push( location );
                                                            _this.add_location(location);
                                                        });
                                                     }
                                                 )
                                                 .catch( function( err ) {
                                                     _this.notices.push( err.message );
                                                 });
                                             }
                                         );
									_this.boolean.loading_locations = false;
                                    });
                            }
                        )
                        .catch( function( err ) {
                            _this.notices.push( err.message );
						    _this.boolean.loading_locations = false;
                        });
                },

				/**
                 * Add a location to this site.
                 *
				 * @param data
				 */
				add_location: function( location ) {
					var _this = this;
					jQuery.ajax({
						url: _this.rest_url + 'locations/',
						method: 'POST',
						data: JSON.stringify( location ),
						contentType: 'application/json',
						beforeSend: function ( xhr ) {
							xhr.setRequestHeader( 'X-WP-Nonce', _this.rest_nonce );
						},
						success: function( response ) {
						    location.is_loaded = true;
						},
						error: function( error_response ) {
							error_response.json()
                                .then( function( error ) {
                                    _this.notices = [ error.message ];
                                });
						}
					});

                }
			}
		});

	    // Add/update locations
		this.update_app = new Vue({
			el: '#wpcsl-option-add',
			data: {
				act: 'add',
                show_add_dialog: false,

                location: {},
                location_manager: location_manager
			},

			methods: {
			    close_add_dialog: function( event ) { SLP_Location_Manager.Editor.close(); },
				submit_form : function () { AdminUI.doAction(this.act); }
			}
		});
	};

	/**
	 * Public methods.
	 */
	return {
	    load_app: load_app,
		update_app: update_app,
		initialize: initialize
	}
} )();

/**
 * Location Form Handler
 *
 * @constructor
 */
var SLP_Location_Form_Handler = function () {
    /**
     * Populate a location card field.
     * @param field
     * @param source
     * @param destination
     * @returns {*}
     */
    this.populate_field = function ( field, source , destination ) {
        var field_finder = "[data-field='" + field + "']";
        this.set_value( destination.find( field_finder ) ,  this.get_value( source.find( field_finder ) ) );
        return field;
    };

    /**
     * Get value of an element either html() or val() depending on type.
     * @param element
     * @returns {*|{}}
     */
    this.get_value = function( element ) {
        var el_val;

        if ( element.attr( 'data-value' ) ) {
            el_val = element.attr( 'data-value' );
        } else if ( element.attr( 'value' ) ) {
            el_val =  element.val();
        } else {
            el_val = element.html();
        }

        return el_val;
    };

    /**
     * Set value of an element either html() or val() depending on type.
     * @param element
     * @param value
     * @returns {*|{}}
     */
    this.set_value = function( element , value ) {
        if ( element.is( ':checkbox' ) ) {
            element.prop( 'checked' , value > 0 );
            return;
        }
        if ( element.is( 'input' ) ) {
            element.val(value);
            return;
        }
        element.html( value );
    };

    /**
     * Hide.
     */
    this.hide = function ( element ) {
        element.removeClass('unhidden');
        element.addClass('hidden');
        element.hide();
    };

    /**
     * Show.
     */
    this.show = function ( element ) {
        element.removeClass('hidden');
        element.addClass('unhidden');
        element.show();
    };
};

/**
 * Admin Locator Page Maps
 *
 * @constructor
 */
var SLP_Locations_map = function () {
    var map;
    var marker;

    /**
     * Show the map.
     */
    this.show_map = function ( location_element ) {
        if ( typeof location_element === 'undefined'      ) return;
        var the_map_div = location_element.find( '.location_map' );
        if (  typeof the_map_div === 'undefined'      ) return;

        var lat =SLP_Location_Manager.Form_Handler.get_value( location_element.find('[data-field="sl_latitude"]') );
        var lng =SLP_Location_Manager.Form_Handler.get_value( location_element.find('[data-field="sl_longitude"]') );
        if ( ! lat ) return;
        if ( ! lng ) return;

        var center = new google.maps.LatLng( lat , lng );

        var map_options = {
            center: center,
            disableDefaultUI: true,
            draggable: true,
            disableDoubleClickZoom: true,
            keyboardShortcuts: false,
            zoom: 14,
        };
        this.map = new google.maps.Map( the_map_div[0] , map_options );


        var marker_options = {
            position: center,
            map: this.map,
            title: location_element.find('[data-field="sl_store"]').html(),
            icon: this.set_icon( location_element ),
        };
        this.marker =  new google.maps.Marker( marker_options );

        // FILTER: location_map_initialized
        // Fires after the location map has been initialized
        //
        slp_AdminFilter( 'location_map_initialized' ).publish();
    };

    /**
     * Set the location icon
     *
     * @param location_element
     */
    this.set_icon = function ( location_element ) {
        var icon = location_element.find('[data-field="marker"] img').attr('src');
        if ( ! icon ) {
            icon = location_element.find('[data-field="category_marker"]').attr('src');
            if ( ! icon ) {
                icon = location_manager.default_marker;
            }
        }
        return icon;
    };

    /**
     * Add marker at center
     */
    this.add_marker_at_center = function () {
        this.centerMarker = new slp_Marker(this, '', this.homePoint, this.mapHomeIconUrl);
    };


};

/**
 * Table Class
 */
var SLP_Locations_table = function () {

    /**
     * Initialize the table header.
     */
    this.initialize = function () {
        jQuery('a[data-action="delete"]').click( this.delete_location );
        slp_Admin_Filter( 'locations_table_init' ).publish();

    };

    /**
     * Delete Location button
     */
    this.delete_location = function ( event ) {
        var data = new Object();
        data[ 'action'             ] = 'slp_delete_location';
        data[ 'location_id'        ] = jQuery( this ).attr( 'data-id' );
        data[ 'screenoptionnonce'  ] = jQuery( '#screenoptionnonce' ).val();

        var messages = {
            'message_ok' : 'Location deleted.',
            'message_info' : 'Could not delete location.',
            'message_failure' : 'Could not communicate with the server.',
        };


        var tr = jQuery( '#location-' + data['location_id'] );
        tr.css( "background-color" , "#F2F2F2" );
        tr.fadeOut( 600 );

        // Do this when delete is OK.
        slp_Admin_Filter( 'ajax_post_ok' ).subscribe( function( response ) { tr.remove(); } );

        // Do this when delete did not work (backend).
        slp_Admin_Filter( 'ajax_post_info' ).subscribe( function( response ) { tr.fadeIn( 200 ); } );

        // Do this when delete did not work (service AWOL).
        slp_Admin_Filter( 'ajax_post_failure' ).subscribe( function( response ) { tr.fadeIn( 200 ); } );

        SLP_ADMIN.ajax.post( data , messages );
    };


    /**
     * Show the location details.
     *
     * @param event
     */
    this.show_location_details = function( event ) {
        var post_data = new Object();
        post_data['action']      = 'slp_get_location';
        post_data['location_id']  = jQuery( this ).attr( 'data-id' );
        alert(  post_data[ 'location_id' ] );
    }

};

/**
 * Table Header Class
 */
var SLP_Locations_table_header = function () {

        /**
         * Initialize the table header.
         */
        this.initialize = function () {
            jQuery('#do_action_apply_to_all').click( this.execute_apply_to_all );
            jQuery('#do_action_apply').click( this.execute_apply );
        };

        /**
         * Run the apply button.
         */
        this.execute_apply = function() {
            SLP_Location_Manager.table_header.execute_apply_bulk_action( false );
        };

        /**
         * Run the apply to all button.
         */
        this.execute_apply_to_all = function() {
            SLP_Location_Manager.table_header.execute_apply_bulk_action( true );
        };

        /**
         * Run the apply to all button.
         */
        this.execute_apply_bulk_action = function( all ) {
            var action_val = jQuery('#actionType').val();
            if ( action_val === '-1' ) { return false; }
            var action_text = jQuery('#actionType option:selected').text();
            if ( confirm( 'Are you sure you want to ' + action_text + '?' ) ) {
                if ( all ) {
                    jQuery('<input />').attr('type', 'hidden')
                        .attr('name', "apply_to_all")
                        .attr('value', "1")
                        .appendTo('#locationForm');
                }
                AdminUI.doAction( action_val );
            }
            return false;
        };
    };

/**
 * Location Card
 * @constructor
 */
var SLP_Location_Card = function () {

    /**
     * Initialize the UX
     */
    this.initialize = function () {
        jQuery('span.store_name').click( function() {
            SLP_Location_Manager.Card.show_location_details( jQuery( this ).parents( 'tr').first() );
        });
        jQuery( '.location-card' ).click( function() {
            SLP_Location_Manager.Form_Handler.hide( jQuery('.location-card') );
        });
    };

    /**
     * Show the location details.
     */
    this.show_location_details = function( table_row ) {
        var location_cells = table_row.children(".slp_manage_locations_cell");
        var location_card = jQuery('.location-card');

        var already_displayed = [ 'sl_store_complex' ];
        var content = '';

        // Display Header
        //
        var header = jQuery('.location-card .primary .card-header' );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_store'    , table_row, header ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_address'  , table_row, header ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_city'     , table_row, header ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_state'    , table_row, header ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_zip'      , table_row, header ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_country'  , table_row, header ) );

        // Display footer
        //
        var footer = jQuery('.location-card .primary .card-footer' );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_id'       , table_row, footer ) );

        // Display Secondary
        //
        var secondary = jQuery('.location-card .secondary' );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_latitude' , table_row, secondary ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_longitude', table_row, secondary ) );
        already_displayed.push( SLP_Location_Manager.Form_Handler.populate_field( 'sl_url'      , table_row, secondary ) );

        // Extra Data
        //
        var field;
        var label;
        var card_grid= jQuery( '.location-card .card-body .card-info-grid' );
        card_grid.html( '' );
        for ( var cnt = 0 , len = location_cells.length; cnt < len; cnt++ ) {
            if ( ! location_cells[cnt]                   ) continue;
            if (   location_cells[ cnt ].innerHTML == '' ) continue;
            field = jQuery( location_cells[cnt] ).attr( 'data-field' );
            label = jQuery( location_cells[cnt] ).attr( 'data-colname' );
            if ( jQuery.inArray( field, already_displayed ) !== -1 ) continue;
            card_grid.append(
                '<div class="col">' +
                    '<strong class="label">' + label + '</strong>' +
                    '<span contenteditable="true" data-field="' + field + '">' + location_cells[ cnt ].innerHTML + '</span>' +
                '</div>'
            );
        }

        var table_position = jQuery( '#manage_locations_table' ).offset();
        var table_width = jQuery( '#manage_locations_table' ).outerWidth();

        var row_position = table_row.offset();
        var row_height = table_row.outerHeight();

        var card_height = jQuery( '.location-card' ).outerHeight();

        var card_top = row_position.top + row_height;
        var card_right = jQuery( window ).width() - ( table_position.left + table_width );

        if ( card_top + card_height > jQuery( window ).height() ) {
            card_top = row_position.top - card_height;
        }


        location_card.css({ top: card_top , right: card_right });
        SLP_Location_Manager.Form_Handler.show( location_card );

        SLP_Location_Manager.locations_map.show_map( location_card );

    };

    /**
     * Clear the more info box.
     */
    this.clear_more_info = function() {
        jQuery('.settings-description').toggleClass('is-visible').html('');
    }
};

/**
 * Location Editor
 *
 * @constructor
 */
var SLP_Location_Editor = function () {
    var add_edit_div = jQuery( '#wpcsl-option-add' );

    /**
     * Initialize
     */
    this.initialize = function( ) {
        jQuery( 'a[data-action="edit"]' ).click( function() {
			jQuery( '#wpcsl-option-add' ).show();
            SLP_Location_Manager.Editor.load_and_show_form( jQuery( this ) );
        } );
        jQuery( '#wpcsl-option-add_sidemenu' ).click( function() {
            SLP_Location_Manager.Editor.load_and_show_form( jQuery( this ) );
        } );
    };

    /**
     * Close the form.
     */
    this.close = function() {
		SLP_Location_Manager.vue.update_app.show_add_dialog = false;
        jQuery( '#wpcsl-option-current_locations_sidemenu' ).click();
    }

    /**
     * Load and show the edit form.
     *
     * @param action_button
     */
    this.load_and_show_form = function( action_button ) {
		this.load_form( add_edit_div, action_button);

        SLP_Location_Manager.Form_Handler.hide( jQuery('.location-card') );
        jQuery( '#myslp-header' ).click( this.close );
        jQuery( '#dashboard-header' ).click( this.close );

        SLP_Location_Manager.vue.update_app.show_add_dialog = true;

        SLP_Location_Manager.locations_map.show_map( add_edit_div );
    };

    /**
     * Load Form
     *
     * @param form_div
     * @param action_button
     */
    this.load_form = function ( form_div , action_button ) {
        var input;
        var locationForm = jQuery('#locationForm');
        var location_id = action_button.attr( 'data-id' );
        var table_row;
        var button_label;

        // Update
        if ( location_id ) {
			SLP_Location_Manager.vue.update_app.act = 'save';
            table_row = jQuery('#manage_locations_table tr[data-id="' + location_id + '"]');
            locationForm.find('#id').val(location_id);
            locationForm.find('#locationID').val(location_id);
            locationForm.find( '#wpcsl_settings_group-map' ).show();
            button_label = location_manager.edit_text;

			// Load data from table row
			table_row.find( '[data-field]' ).each( function ( index ) {
				SLP_Location_Manager.vue.update_app.location[ jQuery( this ).attr( 'data-field' ) ] = jQuery( this ).attr( 'data-value' );
			});


        // Add
        } else {
			SLP_Location_Manager.vue.update_app.act = 'add';
            table_row = action_button;
            locationForm.find('#id').val(location_id);
            locationForm.find('#locationID').val(location_id);
            locationForm.find( '#wpcsl_settings_group-map' ).hide();
            button_label = location_manager.add_text;

			SLP_Location_Manager.vue.update_app.location = {};
        }

        // Labels
		SLP_Location_Manager.vue.update_app.location_manager.text.form_title = button_label;
        form_div.find( 'input :submit' ).each( function( index ) {
        	var _this = jQuery( this );
			_this.attr('alt', button_label);
			_this.attr('title', button_label);
			_this.attr('onclick', '');
        });

        // Fields
        form_div.find( '[data-field]' ).each( function ( index ) {
            SLP_Location_Manager.Form_Handler.populate_field( jQuery( this ).attr( 'data-field' ), table_row , form_div )
        });



        /**
         * Allow add ons to do form edit magic.
         *
         * @filter  location_edit_init
         *
         * @param   object   location_edit_options   options with the table row and form div
         * @return  object                           A (un)modified option array.
         */
        var location_edit_options = new Object();
        location_edit_options[ 'table_row' ] = table_row;
        location_edit_options[ 'form_div'  ] = form_div;
        slp_Admin_Filter( 'location_edit_init' ).publish( location_edit_options );
    };


};

/**
 * Locations Tab Admin JS
 */
jQuery(document).ready(
    function() {
		SLP_Location_Manager.vue.initialize();

        SLP_Location_Manager.Form_Handler = new SLP_Location_Form_Handler();
        SLP_Location_Manager.locations_map = new SLP_Locations_map();

        SLP_Location_Manager.table = new SLP_Locations_table();
        SLP_Location_Manager.table.initialize();


        SLP_Location_Manager.table_header = new SLP_Locations_table_header();
        SLP_Location_Manager.table_header.initialize();

        var dataTable_options = new Object();
        dataTable_options['stripeClasses'] = [];
        dataTable_options['info'] = false;
        dataTable_options['paging'] = false;
        dataTable_options['searching'] = false;
        dataTable_options['responsive'] = true;
        dataTable_options['colReorder'] = true;
        dataTable_options['columnDefs'] = [
            { targets: [0,1] , visible: true , orderable: false },
            { targets: '_all' , visible: true , searchable: false, orderable: true },
        ];


        dataTable_options['ordering'] = (location_manager.all_displayed);

        // Manage Locations Table : DataTable Handler
        //
        SLP_Location_Manager.table = jQuery('#manage_locations_table').DataTable(dataTable_options);

        // No locations, show add form.
        //
        if ( jQuery('#wpcsl-option-current_locations div.section_description').is(':empty') ) {
            jQuery('#wpcsl-option-add_location_sidemenu').click();
        }

        // Location Details
        //
        SLP_Location_Manager.Editor = new SLP_Location_Editor();
        SLP_Location_Manager.Editor.initialize();

        // Location Details
        //
        SLP_Location_Manager.Card = new SLP_Location_Card();
        SLP_Location_Manager.Card.initialize();
    }
);
