// noinspection JSUnusedAssignment
/**
 * @fileoverview    SLPPREMIER add on user interface helpers.
 * @version 4.9
 *
 * Uses the module pattern.
 * @link https://en.wikipedia.org/wiki/Module_pattern
 * @link https://toddmotto.com/mastering-the-module-pattern/
 *
 */

/* global cslmap, google, slp_Filter, slp, slplus, slppremier_settings */

// Setup the Premier namespace
var SLPPREMIER = SLPPREMIER || {};

/**
 * The Location List Class
 */
SLPPREMIER.location_list = (function () {
  var current_page = 0 // Which results page are we on?
  var map_center

  /**
   * Add pagination to the location output.
   */
  this.add_pagination = function () {

    if (!cslmap.latest_response) {
      return
    }

    // Create page div
    //
    var div = document.createElement('div')
    div.innerHTML = cslmap.latest_response.premier.pagination_block

    // Attach to sidebar
    //
    var sidebar = document.getElementById('map_sidebar')
    sidebar.insertBefore(div, sidebar.firstChild)

    // Add on click elements
    //
  }

  /**
   * Add a header to results output.
   *
   * @param results_html
   */
  this.add_results_header = function (results_html) {
    slp_Filter('replace_shortcodes_options').subscribe(SLPPREMIER.utils.use_our_options)
    results_html.content = slplus.options.results_header.replace_shortcodes({})
    if (results_html.content.indexOf('add_locations_here') >= 0) {
      results_html.insert_rows_at = '#add_locations_here'
    }
    slp_Filter('replace_shortcodes_options').unsubscribe(SLPPREMIER.utils.use_our_options)
  }

  /**
   * Submit next page of locations request.
   */
  this.get_next_page = function () {
    current_page++
    map_center = cslmap.lastCenter
    jQuery.post(
      slplus.ajaxurl,
      {
        lat: map_center.lat(),
        lng: map_center.lng(),
        radius: cslmap.lastRadius,
        action: 'csl_ajax_onload',
        page: current_page
      },
      cslmap.process_ajax_response
    )
  }

  /**
   * Submit previous page of locations request.
   */
  this.get_previous_page = function () {
    current_page--
    map_center = cslmap.lastCenter
    jQuery.post(
      slplus.ajaxurl,
      {
        lat: map_center.lat(),
        lng: map_center.lng(),
        radius: cslmap.lastRadius,
        action: 'csl_ajax_onload',
        page: current_page
      },
      cslmap.process_ajax_response
    )
  }

  /**
   * Mark location result strings as finished to prevent wrapping in a div.
   *
   * @param results_html
   */
  this.mark_results_finished = function (results_html) {
    results_html.finished = true
  }

  /**
   * Subscribe to the slp.js location string related filters.
   *
   * Subscribe to geocoder_request
   * Subscribe to geocode_results
   *
   * @see https://api.jquery.com/jQuery.Callbacks/
   */
  this.setup_location_list_subscriptions = function () {

    // Do Not Wrap Results Enabled
    if (slplus.options.results_no_wrapper !== '0') {
      slp_Filter('wrap_location_results').subscribe(mark_results_finished)
    }

    // Results Header
    //
    if (slplus.options.results_header) {
      slp_Filter('location_results_header').subscribe(add_results_header)
    }

    // Pagination Enabled
    //
    if (slplus.options.pagination_enabled !== '0') {
      jQuery('#map_sidebar').on('contentchanged', add_pagination)
    }
  }

  /**
   * Public methods.
   */
  return {
    setup_location_list_subscriptions: setup_location_list_subscriptions,
    get_next_page: get_next_page,
    get_previous_page: get_previous_page

  }
})()

/**
 * The Premier Location Search Class
 */
SLPPREMIER.location_search = (function () {

  /**
   * Subscribe to the slp.js geocoding related filters.
   *
   * Subscribe to geocoder_request
   * Subscribe to geocode_results
   *
   * @see https://api.jquery.com/jQuery.Callbacks/
   */
  this.setup_geocoding_subscriptions = function () {

    // Country Influence Enabled
    if (slppremier_settings.region_influence_enabled === '1') {
      slp_Filter('geocoder_request').subscribe(add_region_to_geocoder_request)
    }

    // Locations Influence Enabled
    if (slplus.options.boundaries_influence_type !== 'none') {
      slp_Filter('geocoder_request').subscribe(add_location_bounds_to_geocoder_request)
      slp_Filter('geocode_results').subscribe(set_best_result)
    }

    // Show Address Guess Enabled
    //
    if (slppremier_settings.show_address_guess === '1') {
      slp_Filter('geocode_results').subscribe(show_google_address_guess)
    }
  }

  /**
   * Extend the geocoder request to add bounds to influence the google address
   * lookup.
   *
   * Uses the location data set in SLP to set the sw/ne corners of a bounding
   * box to influence how Google guesses which address smoeone means when they
   * type in text into an address search box.
   *
   * @param {google.maps.GeocoderRequest} geocoder_request
   */
  this.add_location_bounds_to_geocoder_request = function (geocoder_request) {
    var _this = this
    if (slppremier_settings.bounds) {
      var sw_bounds = new google.maps.LatLng(
        slppremier_settings.bounds.min_lat,
        slppremier_settings.bounds.min_lng)
      var ne_bounds = new google.maps.LatLng(
        slppremier_settings.bounds.max_lat,
        slppremier_settings.bounds.max_lng)
      geocoder_request['bounds'] = new google.maps.LatLngBounds(sw_bounds,
        ne_bounds)
    }
  }

  /**
   * Extend the geocoder request to add region to influence the google address
   * lookup.
   *
   * @param {google.maps.GeocoderRequest} geocoder_request
   */
  this.add_region_to_geocoder_request = function (geocoder_request) {
    var _this = this
    if (slppremier_settings.region) {
      geocoder_request['region'] = slppremier_settings.region
    }
  }

  /**
   * Find the first Google guessed address within the lat/lng bounds set by
   * add_location_bounds_to_geocoder_request.
   *
   * @param geocode_results
   */
  this.set_best_result = function (geocode_results) {
    if (!geocode_results.request.bounds) {
      return
    }

    if (slppremier_settings.bounds) {
      var total_results = geocode_results.all.length
      for (var result_entry = 0; result_entry <
      total_results; result_entry++) {
        if (geocode_results.request.bounds.contains(
          geocode_results.all[result_entry].geometry.location)) {
          geocode_results.best = geocode_results.all[result_entry]
          return
        }
      }
    }
  }

  /**
   * Show the best guess address to the user, replacing what they typed in the
   * address box.
   *
   * @param geocode_results
   */
  this.show_google_address_guess = function (geocode_results) {
    if ( slp.important_object.address_input.val()) {
      slp.important_object.address_input.val(geocode_results.best.formatted_address)
    }
  }

  /**
   * Auto submit dropdowns.
   */
  this.autosubmit_dropdowns = function () {
    cslmap.load_markers(cslmap.lastCenter, cslmap.lastRadius)
  }

  /**
   * Public methods.
   */
  return {
    setup_geocoding_subscriptions: setup_geocoding_subscriptions,
    autosubmit_dropdowns: autosubmit_dropdowns
  }
})()

/**
 * The Premier Loading Indicator
 */
SLPPREMIER.loading_indicator = (function () {
    var loader_div,
        loader_box,
        original_content,
        original_content_z,
        indicator
      ;

	/**
	 * Initialize
	 */
	this.initialize = function () {
	    if ( ! slplus.options.loading_indicator || ! slplus.options.loading_indicator_location ) {
	      return;
        }

        var loader_location = slp.important_object[ slplus.options.loading_indicator_location ];

		loader_location.wrap( '<div id="slp_loader_div"></div>' );
		loader_div = jQuery( '#slp_loader_div' );
		original_content = loader_div.children( ':first-child' );
		loader_div.prepend( '<div class="loader_box"><div class="loader"></div></div>' );
		loader_box = loader_div.children( ':first-child' );
		indicator = loader_box.children( ':first-child' );

		set_indicator_properties();

		slp_Filter( 'load_marker_action' ).subscribe( premier_show_locations_processing );
		slp_Filter( 'location_search_responded' ).subscribe( premier_remove_locations_processing );
	};

	/**
     * Locations Lookup Started
	 */
	this.premier_show_locations_processing = function () {
	    if ( ! loader_div ) return;

		if ( slplus.options.loading_indicator_location === 'results' ) {
			original_content.hide();
		}

		loader_box.show();
    };

	/**
     * Locations Loaded
	 */
	this.premier_remove_locations_processing = function () {
		if ( ! loader_div ) return;

		if ( slplus.options.loading_indicator_location === 'results' ) {
			original_content.show();
		}

		loader_box.hide();
	};

	/**
     * Set properties based on loading location.
	 */
	this.set_indicator_properties = function () {
		var in_height,
            size;

		this.original_content_z = original_content.zindex;
        console.log( 'oz: ' + this.original_content_z );

		indicator.addClass( slplus.options.loading_indicator_color );

        if ( slplus.options.loading_indicator_location === 'results' ) {
          return;
        }

        size = original_content.height();

        if ( slplus.options.loading_indicator_location === 'search_form' ) {
            indicator.css({
                height: size,
                width: size,
                "border-width": Math.max( 2 , size * .1 )
            });
        }

		in_height = indicator.height();

        if ( slplus.options.loading_indicator_location === 'map' ) {
            loader_box.css({
                height: in_height * 2,
                margin: (size - in_height - 10 )/2 + 'px auto',
				'background-color': '#424242' ,
			    opacity: 0.5
            });
        } else if ( slplus.options.loading_indicator_location === 'search_form' ) {
			loader_box.css({
				height: original_content.outerHeight(),
				'background-color': '#424242' ,
				opacity: 0.5
			});
		}

		indicator.css({
			margin: ( ( loader_box.outerHeight() - indicator.outerHeight() ) / 2 )+ 'px auto'
		});

    };

	/**
	 * Public methods.
	 */
	return {
		initialize: initialize
	}
})();


/**
 * The Premier Google Map Manager
 *
 * @var {google.maps.LatLngBounds} map_bounds
 */
SLPPREMIER.map = (function () {
  var map_bounds, MapDragEndListener, mapIdleListener,
      MapMoverActive = false;

  /**
   * Add triggers for map movement.
   */
  this.add_map_movement_triggers = function () {
    MapDragEndListener = google.maps.event.addListener(cslmap.gmap, 'dragend', search_when_map_stops_moving)
    jQuery( '#map_sidebar' ).off( 'contentchanged' ,  add_map_movement_triggers )
  }

  /**
   * Strip out any locations that are not on the map.
   *
   * @param {object} location_search
   */
  this.cull_off_map_locations = function (location_search) {
    location_search.response = location_search.response.filter(
      function (location) {
        return  map_bounds.contains( { lat: parseFloat( location.lat ) , lng: parseFloat( location.lng ) } )
      }
    )
    location_search.count = location_search.response.length
    slp_Filter('locations_found').unsubscribe(cull_off_map_locations)
  }

  /**
   * Subscribe to the slp.js map options filters.
   *
   * Subscribe to map_options
   *
   * @see https://api.jquery.com/jQuery.Callbacks/
   */
  this.setup_map_subscriptions = function () {
    slp_Filter('map_options').subscribe( this.modify_map_options )
    if (slplus.options.map_marker_tooltip === '0') {
      slp_Filter('marker').subscribe(modify_marker)
    }

    // Bubble Footnote Set
    //
    if (slplus.options.bubble_footnote) {
      slp_Filter('replace_shortcodes_options').subscribe(SLPPREMIER.utils.use_our_options)
    }

    // Map Built
    //
    if (slplus.options.search_on_map_move !== '0') {
      jQuery( '#map_sidebar' ).on( 'contentchanged' ,  add_map_movement_triggers )
    }
  }

  /**
   * Hide the StreetView on the Google Map interface.
   *
   * @param map_options
   */
  this.modify_map_options = function (map_options) {
	  map_options.zoomControl = (slplus.options.map_option_zoomControl === '1')
	  map_options.fullscreenControl = (slplus.options.map_option_fullscreenControl === '1')
      map_options.streetViewControl = (slplus.options.map_option_hide_streetview !== '1')
      map_options.clickableIcons = (slplus.options.map_options_clickableIcons === '1')
  }

  /**
   * Hide the StreetView on the Google Map interface.
   *
   * @param map_options
   */
  this.modify_marker = function (marker) {
    marker.__title = ''
  }

  /**
   * Run search when map stops moving.
   */
  this.search_when_map_stops_moving = function () {
    mapIdleListener = google.maps.event.addListener(cslmap.gmap, 'idle', search_displayed_map_area );
  }

  /**
   * Search within the displayed map area.
   */
  this.search_displayed_map_area = function () {
    google.maps.event.removeListener( mapIdleListener );
    map_bounds = cslmap.gmap.getBounds()

    var SearchRadius = google.maps.geometry.spherical.computeDistanceBetween(map_bounds.getNorthEast(), map_bounds.getSouthWest()) / 2000
    if (slplus.options.distance_unit === 'miles') {
      SearchRadius = SearchRadius * 0.621371
    }

    slplus.options.immediately_show_locations = '0'
    slp_Filter('locations_found').subscribe(turn_off_auto_bound)
    slp_Filter('locations_found').subscribe(cull_off_map_locations)

    var map_ab_setting = cslmap.auto_bound;
    cslmap.auto_bound = false;
    cslmap.load_markers( null , SearchRadius );
    cslmap.auto_bound = map_ab_setting;
  }

  /**
   * Turn off CSL Map auto_bound.
   */
  this.turn_off_auto_bound = function () {
    cslmap.auto_bound = false
    jQuery( '#map_sidebar' ).on( 'contentchanged' ,  turn_on_auto_bound ) // turn this back on when done.
  }

  /**
   * Turn on CSL Map auto_bound.
   */
  this.turn_on_auto_bound = function () {
    cslmap.auto_bound = true
    jQuery( '#map_sidebar' ).off( 'contentchanged' ,  turn_on_auto_bound ) // turn this back on when done.
    slp_Filter('location_search_processed').unsubscribe(turn_on_auto_bound)  // stop doing this until map moves again
  }

  /**
   * Public methods.
   */
  return {
      setup_map_subscriptions: setup_map_subscriptions,
      modify_map_options: modify_map_options
  }
})()

/**
 * Premier Results Manager
 */
SLPPREMIER.results = (function () {
  var last_active_marker
  var last_marker_meta = {}
  var that = this
  var shade_listener

  /**
   * Initialize
   */
  this.initialize = function () {
    jQuery('*').on('result_clicked', interact_with_marker)
  }

  /**
   * Handle All Marker Interaction
   * This allows us to control the order for fine control over the UX.
   */
  this.interact_with_marker = function (obj, event) {
    var map_marker = event.data.marker.__gmarker

    // Same marker we just were modifying - do nothing.
    if (map_marker === that.last_active_marker) {
      return
    }

    var not_first_marker = (typeof that.last_active_marker !== 'undefined')

    var marker_meta = {
      map: map_marker.getMap(),
      icon: map_marker.getIcon(),
      label: map_marker.getLabel(),
      zindex: map_marker.getZIndex()
    }

    // Cluster Is Active
    //
    if (slplus.options.clusters_enabled === '1') {
      if (not_first_marker) {
        that.last_active_marker.setMap(that.last_marker_meta.map)
      }
      map_marker.setMap(cslmap.gmap)
    }

    // Move Map
    //
    if (slplus.options.results_click_map_movement === 'center') {
      cslmap.gmap.setCenter(map_marker.getPosition())
    }

    // Set Icon
    if (slplus.options.results_click_marker_icon_behavior !== 'as_is') {
      if (not_first_marker) {
        that.last_active_marker.setIcon(that.last_marker_meta.icon)
      }
      map_marker.setIcon(slplus.options.results_click_marker_icon)
    }

    // Animate
    //
    if (slplus.options.results_click_animate_marker !== 'none') {
      map_marker.setAnimation(google.maps.Animation.DROP)
    }

    // Label
    //
    if (slplus.options.results_click_label_marker !== 'no_label') {
      if (not_first_marker) {
        that.last_active_marker.setLabel(that.last_marker_meta.label)
      }
      var label_text = jQuery('<div/>').html(event.data.info[slplus.options.results_click_label_marker]).text()
      that.shade_listener = google.maps.event.addDomListener(cslmap.gmap,
        'idle', function () {
          var target_div = jQuery('div').filter(function () {
            var decoded = this.innerHTML.replace(/&amp;/g, '&')
            return decoded === label_text
          })
          jQuery(target_div).css('margin-top', '5em')
          jQuery(target_div).css('background', 'rgba(255,255,255,0.7)')
          jQuery(target_div).css('padding', '0.2em')
          jQuery(target_div).css('border', 'solid 1px black')
          jQuery(target_div).css('border-radius', '4px')
          google.maps.event.removeListener(that.shade_listener)
        }
      )
      map_marker.setLabel(label_text)
    }

    // ZIndex
    //
    if (not_first_marker) {
      that.last_active_marker.setZIndex(that.last_marker_meta.zindex)
    }
    map_marker.setZIndex(500)

    // Scroll To Map
    if (obj.target.className.indexOf('scroll_to_map') >= 0) {
      jQuery('html, body').animate({
        scrollTop: jQuery('#map').offset().top -
        jQuery('#wpadminbar').height() - 5
      })
    }

    that.last_active_marker = map_marker
    that.last_marker_meta = marker_meta
  }

  /**
   * Public Methods and Such...
   */
  return {
    initialize: initialize
  }
})()

/**
 * Premier Search Manager
 */
SLPPREMIER.search = (function () {

  /**
   * Initialize
   */
  this.initialize = function () {
    jQuery('.search_item.category.button_bar li').on('click', search_by_category)
  }

  /**
   * Search By Category Button Bar
   * This allows us to control the order for fine control over the UX.
   *
   * event.currentTarget = the li the fired
   * event.target will fire once for the label and once for input, we skip the label
   * so event.target then is the checkbox input
   */
  this.search_by_category = function (event) {
    if (event.target.tagName !== 'INPUT') {
      return
    }

    // Uncheck all and remove selected class
    var all_buttons = jQuery(event.currentTarget).siblings()
    all_buttons.removeClass('selected')
    all_buttons.find('input:checkbox').prop('checked', false)

    // Check this one and add selected class
    //
    var clicked_button = jQuery(event.currentTarget)
    var clicked_button_checkbox = jQuery(event.target).first('input:checkbox')
    var the_clicked_button_is_active = clicked_button_checkbox.prop('checked')

    clicked_button_checkbox.prop('checked', the_clicked_button_is_active)
    if (the_clicked_button_is_active) {
      clicked_button.addClass('selected')
    } else {
      clicked_button.removeClass('selected')
    }

    cslmap.load_markers(cslmap.lastCenter, cslmap.lastRadius)
  }

  /**
   * Public Methods and Such...
   */
  return {
    initialize: initialize
  }

})()

/**
 * Helper Utilities.
 */
SLPPREMIER.utils = (function () {

  // Are location list features in play?
  //
  this.wants_to_extend_results = function () {
    return (
      (jQuery('tr.scroll_to_map').length > 0) ||
      (slplus.options.results_click_map_movement === 'center') ||
      (slplus.options.results_click_animate_marker !== 'none') ||
      (slplus.options.results_click_marker_icon_behavior !== 'as_is') ||
      (slplus.options.results_click_label_marker !== 'no_label') ||
      (slplus.options.results_no_wrapper === '1') ||
      (slplus.options.results_header) ||
      (slplus.options.pagination_enabled === '1')
    )
  }

  /**
   * Return true if our settings want results interaction.
   */
  this.wants_results_interaction = function () {

  }

  // Are search features in play?
  //
  this.wants_to_extend_search = function () {
    return (
      (slplus.options.boundaries_influence_type !== 'none') ||
      (slppremier_settings.show_address_guess === '1') ||
      (slppremier_settings.dropdown_autosubmit === '1')
    )
  }

  /**
   * Do we need to the goecoding subscriptions?
   *
   * @return {boolean}
   */
  this.needs_geocoding_subscription = function () {
    return (
      (slplus.options.boundaries_influence_type !== 'none') ||
      (slppremier_settings.show_address_guess === '1')
    )
  }

  /**
   * Swap SLP options with Premier Options for shortcode replace.
   *
   * @param options
   */
  this.use_our_options = function (options) {
    jQuery.extend(options, slppremier_settings)
  }

  /**
   * Public methods and vars.
   */
  return {
    wants_to_extend_results: wants_to_extend_results,
    wants_to_extend_search: wants_to_extend_search,
    needs_geocoding_subscription: needs_geocoding_subscription,
    use_our_options: use_our_options
  }
})()

/**
 * URL Control
 */
SLPPREMIER.URL_Control = (function () {
  var location_from_url = 0

  /**
   * Initialize the URL Controls
   */
  this.initialize = function () {
    if (this.enabled()) {
      jQuery('#map').on('markers_dropped', show_location_from_url)
    }
  }

  /**
   * Are we enabled?
   * @return {boolean}
   */
  this.enabled = function () {
    set_location_id()
    return (location_from_url > 0)
  }

  /**
   * Disable the bubble on location load.
   */
  this.disable = function () {
    delete slplus.options.active_locations         // the "seed" for the
    // cslmap options on
    // initial load
    if (cslmap.search_options !== null) {
      delete cslmap.search_options.active_locations  // sent to AJAX query
      // after initial load
    }
    jQuery('#map').off('markers_dropped', show_location_from_url)
  }

  /**
   * Return our private location from URL.
   *
   * @return {number}
   */
  this.get_location = function () {
    return location_from_url
  }

  /*
   * Set the location id passed from the URL.
   */
  this.set_location_id = function () {
    location_from_url = (typeof slplus.options.active_location ===
      'undefined') ? 0 : slplus.options.active_location
  }

  /*
   * Show the location on the map as passed from the URL.
   */
  this.show_location_from_url = function () {
    cslmap.set_active_marker_to_location(location_from_url)

    if ( SLPPREMIER.marker_cluster && SLPPREMIER.marker_cluster.enabled ) {
      SLPPREMIER.marker_cluster.initialize()
    }

    disable()
    google.maps.event.trigger(cslmap.active_marker.__gmarker, 'click')
  }

  /**
   * Public methods and vars.
   */
  return {
    enabled: enabled,
    initialize: initialize,
    show_location_from_url: show_location_from_url
  }
})()

// Document Ready
jQuery(document).ready(
  function () {

    SLPPREMIER.loading_indicator.initialize();

    // SelectMenu Drop Down Styler
    //
    if (slplus.options.dropdown_style !== 'none') {
      jQuery('#sl_div select').selectmenu()
    }

    // Address Search Management
    //
    SLPPREMIER.search.initialize()
    if (SLPPREMIER.utils.wants_to_extend_search()) {
      if (SLPPREMIER.utils.needs_geocoding_subscription()) {
        SLPPREMIER.location_search.setup_geocoding_subscriptions()
      }

      if (slppremier_settings.dropdown_autosubmit === '1') {
        if (slplus.options.dropdown_style !== 'none') {
          jQuery('.store_locator_plus select').on('selectmenuchange',
            SLPPREMIER.location_search.autosubmit_dropdowns)
        }
        else {
          jQuery('.store_locator_plus select').on('change', SLPPREMIER.location_search.autosubmit_dropdowns)
        }
      }
    }

    // Location List Management
    //
    if (SLPPREMIER.utils.wants_to_extend_results()) {
      SLPPREMIER.location_list.setup_location_list_subscriptions()
    }

    SLPPREMIER.URL_Control.initialize()

    // Only initialize clusters if URL control is not in play
    if ( SLPPREMIER.marker_cluster && ! SLPPREMIER.URL_Control.enabled() ) {
      SLPPREMIER.marker_cluster.initialize()
    }

    // Results Interactions
    //
    if (SLPPREMIER.utils.wants_to_extend_results()) {
      SLPPREMIER.results.initialize()
    }

    // Google Map Modifier
    //
    SLPPREMIER.map.setup_map_subscriptions()

  }
)
