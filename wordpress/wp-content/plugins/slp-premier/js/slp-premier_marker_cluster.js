var SLPPREMIER = SLPPREMIER || {};

/**
 * Marker Cluster Class.
 *
 * @class Manages the cluster marker interface.
 *
 * @property {MarkerClusterer}              all_markers         an array of the
 *     map markers
 * @property {object}                       cluster_options     the marker
 *     options
 *
 * @type {{create, all_markers}}
 */
SLPPREMIER.marker_cluster = (function() {
	var all_markers, cluster_options;

	/**
	 * Initialize the class.
	 */
	this.initialize = function() {
		if (this.enabled()) {
			cluster_options = {
				gridSize: slplus.options.cluster_gridsize ? parseInt(slplus.options.cluster_gridsize) : 60,
				minimumClusterSize: slplus.options.cluster_minimum ? parseInt(slplus.options.cluster_minimum) : 3
			};
			jQuery('#map').on('markers_dropped', SLPPREMIER.marker_cluster.create);
			slp_Filter('location_search_responded').subscribe(reset);
		}
	};

	/**
	 * Create the clusters.
	 * @function
	 */
	this.create = function() {
		var gmlist = [];
		for (var idx = 0; idx < cslmap.markers.length; idx++) {
			gmlist.push(cslmap.markers[idx].__gmarker);
		}
		all_markers = new MarkerClusterer(cslmap.gmap, gmlist, cluster_options);
	};

	/**
	 * Are cluster markers enabled?
	 * @return {boolean}
	 */
	this.enabled = function() {
		return (slplus.options.clusters_enabled === '1');
	};

	/**
	 * Get all the clustered markers.
	 * @function
	 * @return {*}
	 */
	this.get_clustered_markers = function() {
		return all_markers;
	};

	/**
	 * Reset on response.
	 */
	this.reset = function() {
		if (all_markers) {
			all_markers.clearMarkers();
		}
	};

	/**
	 * Public methods.
	 */
	return {
		create: create,
		enabled: enabled,
		get_clustered_markers: get_clustered_markers,
		initialize: initialize
	};
})();