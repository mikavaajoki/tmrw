/**
 * A base class that helps add-on packs separate admin functionality.
 *
 * JavaScript Revealing Module Pattern:
 * @link https://scotch.io/bar-talk/4-javascript-design-patterns-you-should-know
 * @link http://www.adequatelygood.com/JavaScript-Module-Pattern-In-Depth.html
 *
 * @type {SLPPREMIER_ADMIN}
 */

// Setup the Premier namespace
var SLPPREMIER_ADMIN = SLPPREMIER_ADMIN || {};

/**
 * Boundaries Map class.
 */
SLPPREMIER_ADMIN.boundaries_map = ( function () {

    /**
     * @type {google.maps.Map}
     */
    var map = null;

    /**
     * @type {google.maps.LatLngBounds}
     */
    var bounds = null;

    /**
     * @type {google.maps.Rectangle}
     */
    var rectangle = null;

    /**
     * Listen for the map submenu being shown.
     */
    this.initialize = function () {
        if ( ! jQuery( '#boundaries_map') ) { return; }

        set_map_visibility_based_on_dropdown();
        jQuery( '#options\\[boundaries_influence_type\\]').on( 'change' , set_map_visibility_based_on_dropdown );

        if ( jQuery( '#boundaries_map:visible') ) {
            display_map();
        }

        jQuery( '#wpcsl-option-search' ).on('is_shown', show_map_div );
    };

    /**
     * Create boundaries map.
     */
    this.display_map = function () {
        if (slppremier_settings.bounds) {
            var sw_bounds = new google.maps.LatLng( slppremier_settings.bounds.min_lat , slppremier_settings.bounds.min_lng );
            var ne_bounds = new google.maps.LatLng( slppremier_settings.bounds.max_lat , slppremier_settings.bounds.max_lng );
            bounds = new google.maps.LatLngBounds( sw_bounds, ne_bounds);
        }

        var map_options = {
            center: bounds.getCenter(),
            mapTypeControl: false,
            overviewMapControl: false,
            panControl: false,
            rotateControl: false,
            scaleControl: false,
            scrollwheel: false,
            streetViewControl: false,
            zoom: 6
        };

        map = new google.maps.Map( document.getElementById('boundaries_map' ) , map_options );
        map.fitBounds( bounds );
        show_bounds();
        google.maps.event.addListener( rectangle , 'bounds_changed' , update_bounds );
    };

    /**
     * Set visibility based on drop down selection.
     */
    this.set_map_visibility_based_on_dropdown = function () {
        var current_dropdown = document.getElementById( 'options[boundaries_influence_type]' );
        var selected_value = jQuery('option:selected', current_dropdown).val();
        if ( selected_value !== 'none' ) {
            jQuery('#boundaries_map_wrapper').show();
            SLPPREMIER_ADMIN.boundaries_map.display_map();  // We are completely out of scope here, need to use FQPN
        } else {
            jQuery('#boundaries_map_wrapper').hide();
        }
    };

    /**
     * Show the active bounds.
     */
    this.show_bounds = function () {
        rectangle = new google.maps.Rectangle({
            editable: true,
            map: map,
            bounds: bounds
        });
    };

    /**
     * Show the map div.
     *
     * Remember, "this" in here represents the clicked hyperlink, NOT the BoundariesMap object.
     *
     * @returns {boolean}
     */
    this.show_map_div = function ( ) {
        display_map();
        return true;
    };


    /**
     * Update the bounds.
     */
    this.update_bounds = function ( event ) {
        var sw_boundary = getBounds().getSouthWest();
        var min_lat = short_coord(sw_boundary.lat());
        var min_lng = short_coord(sw_boundary.lng());
        jQuery('#sw_boundary').text( '(' + min_lat  + ',' + min_lng + ')' );

        var ne_boundary = getBounds().getNorthEast();
        var max_lat = short_coord(ne_boundary.lat());
        var max_lng = short_coord(ne_boundary.lng());

        jQuery('#ne_boundary').text( '(' + max_lat + ',' + max_lng + ')' );

        jQuery('#options\\[boundaries_influence_type\\]').val('boundary');
        jQuery('#slp-premier-options\\[boundaries_influence_min_lat\\]').val(min_lat);
        jQuery('#slp-premier-options\\[boundaries_influence_min_lng\\]').val(min_lng);
        jQuery('#slp-premier-options\\[boundaries_influence_max_lat\\]').val(max_lat);
        jQuery('#slp-premier-options\\[boundaries_influence_max_lng\\]').val(max_lng);
    };

    /**
     * Shorten a coordinate to 5 positions after decimal.
     *
     * @param {string} coordinate
     *
     * @returns {number}
     */
    this.short_coord = function ( coordinate ) {
        return Math.floor( coordinate * 100000 + 0.5 ) / 100000;
    };

    /**
     * Public
     */
    return {
        initialize: initialize,
        display_map: display_map
    }
} ) ();

/**
 * Center Map class.
 */
SLPPREMIER_ADMIN.center_map  = ( function () {

        /**
         * @type {google.maps.Map}
         */
        var map = null;

        /**
         * Listen for the map submenu being shown.
         */
        this.initialize = function () {
            if ( jQuery( '#center_map:visible') ) {
                display_map();
            }
            jQuery( '#wpcsl-option-map' ).on('is_shown', show_map_div );
        };

        /**
         * Show the map div.
         *
         * Remember, "this" in here represents the clicked hyperlink, NOT the CenterMap object.
         *
         * @returns {boolean}
         */
        this.show_map_div = function ( ) {
            display_map();
            return true;
        };

        this.display_map = function () {
            this.map = new google.maps.Map( document.getElementById('center_map' ) ,
                {
                    center: {lat: parseFloat(slppremier_settings.map_center_lat) , lng: parseFloat(slppremier_settings.map_center_lng) },
                    mapTypeControl: false,
                    overviewMapControl: false,
                    panControl: false,
                    rotateControl: false,
                    scaleControl: false,
                    scrollwheel: false,
                    streetViewControl: false,
                    zoom: 6
                }
            );

            var marker = new google.maps.Marker(
                {
                    map: this.map,
                    position: this.map.getCenter(),
                    title: slppremier_settings.map_center_lat + ' , ' + slppremier_settings.map_center_lng,
                }
            );
        };

        /**
         * Public
         */
        return {
            initialize: initialize
        }


    } ) ();

/**
 * Location Map class.
 */
SLPPREMIER_ADMIN.location_map = ( function () {

    /**
     * @type {google.maps.Map}
     */
    var map = null;

    /**
     * If the location map is present do stuff.
     */
    this.initialize = function () {
        if ( ! jQuery( '#admin_map_location:visible') ) { return; }
        if ( ! SLP_ADMIN.has_pubsub ) { return; }
        slp_AdminFilter( 'location_map_initialized' ).subscribe( show_territory_bounds );
    };

    /**
     * Count the corners (vertices) in our polygon.
     *
     * @returns {number}
     */
    this.count_corners = function () {
        var corners = 0;
        if ( jQuery( '#latitude_ne' ).text() && jQuery( '#longitude_ne' ).text() ) { corners++; }
        if ( jQuery( '#latitude_sw' ).text() && jQuery( '#longitude_sw' ).text() ) { corners++; }
        return corners;
    };

    /**
     * Show the territory bounds.
     */
    this.show_territory_bounds = function() {
        if ( count_corners() < 2 ) { return; }
        var sw_corner = new google.maps.LatLng( jQuery( '#latitude_sw' ).text() ,  jQuery( '#longitude_sw' ).text() );
        var ne_corner = new google.maps.LatLng( jQuery( '#latitude_ne' ).text() ,  jQuery( '#longitude_ne' ).text() );
        var bounds = new google.maps.LatLngBounds( sw_corner , ne_corner );

        var territory = new google.maps.Rectangle({
            bounds: bounds,
            strokeColor: '#FFA500',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#FFA500',
            fillOpacity: 0.35,
            map: SLP_Location_Manager.locations_map.map,
        });

        // Mark SW corner
        this.sw_marker =  new google.maps.Marker({
            position: sw_corner,
            map: SLP_Location_Manager.locations_map.map,
            title: sw_corner.toString(),
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 2,
            }
        });

        // Mark NE corner
        this.ne_marker =  new google.maps.Marker({
            position: ne_corner,
            map: SLP_Location_Manager.locations_map.map,
            title: ne_corner.toString(),
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 2,
            }
        });


        // Include home marker when zooming. When territories get more complex they may NOT cover the home marker.
        //
        var new_bounds = new google.maps.LatLngBounds( sw_corner , ne_corner );
        new_bounds.extend( SLP_Location_Manager.locations_map.marker.getPosition() )
        SLP_Location_Manager.locations_map.map.fitBounds( new_bounds );

    };

    /**
     * Public
     */
    return {
        initialize: initialize
    }



} ) ();

// Document Ready
jQuery( document ).ready(
    function () {

        // Google Is Loaded - allow mappy stuff.
        //
        if ( typeof ( google ) !== 'undefined' ) {

            // Experience Tab
            //
            if (pagenow === 'store-locator-plus_page_slp_experience') {
                SLPPREMIER_ADMIN.boundaries_map.initialize();
                SLPPREMIER_ADMIN.center_map.initialize();
            }

            // Locations Tab
            //
            if (pagenow === 'store-locator-plus_page_slp_manage_locations') {
                SLPPREMIER_ADMIN.location_map.initialize();
            }
        }


    }
);
