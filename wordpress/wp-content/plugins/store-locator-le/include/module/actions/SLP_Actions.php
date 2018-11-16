<?php
/**
 * Store Locator Plus action hooks.
 * The methods in here are normally called from an action hook that is called via the WordPress action stack.
 */
class SLP_Actions extends SLPlus_BaseClass_Object {
	private $scripts_registered = false;

	/**
	 * Things to do at startup.
	 */
	function initialize( ) {
		add_filter( 'cron_request', array( $this, 'modify_cron_request' ) );

		add_action('init'               , array($this,'init'                    ) , 11  );

		add_action( "load-post.php"     , array( $this, 'action_AddToPageHelp'  ) , 20  );
		add_action( "load-post-new.php" , array( $this, 'action_AddToPageHelp'  ) , 20  );

		add_action('wp_head'            , array($this,'wp_head'                 )       ); // UI

		add_action('wp_footer'          , array($this,'wp_footer'               )       ); // UI

		add_action('shutdown'           , array($this,'shutdown'                )       ); // BOTH
	}

	/**
	 * Add SLP setting to the admin bar on the top of the WordPress site.
	 *
	 * @param $admin_bar
	 */
	public function add_slp_to_admin_bar( $admin_bar ) {
        if( ! current_user_can( 'manage_slp_admin' ) ) { return; }

        $args = array(
			'parent' => 'site-name',
			'id'     => 'store-locator-plus',
			'title'  => SLPLUS_NAME,
			'href'   => esc_url( admin_url( 'admin.php?page=slp_manage_locations' ) ),
			'meta'   => false
		);
		$admin_bar->add_node( $args );
	}

	/**
	 * Add content tab help to the post and post-new pages.
	 */
	public function action_AddToPageHelp() {
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'slp_help_tab',
				'title'   => __( 'SLP Hints', 'store-locator-le' ),
				'content' =>
					'<p>' .
					sprintf(
						__( 'Check the <a href="%s" target="slp">%s documentation</a> online.<br/>', 'store-locator-le' ),
						$this->slplus->support_url,
						SLPLUS_NAME						
						) .
					sprintf(
						__( 'View the <a href="%s" target="csa">[slplus] shortcode documentation</a>.', 'store-locator-le' ),
						$this->slplus->support_url . '/blog/slplus-shortcode/'
						) .
					'</p>'

			)
		);
	}

	/**
	 * Add the Store Locator panel to the admin sidebar.
	 */
	function admin_menu() {
		SLP_Admin_UI::get_instance()->create_admin_menu();
	}

	/**
	 * Enable debug logging.
	 */
	private function debug_mode() {
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
		ini_set( 'log_errors', 1 );
		ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );
		if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) || wp_doing_ajax() ) {
			@ini_set( 'display_errors', 0 );
		}
	}

	/**
	 * Save screen options.
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value) {
		switch ( $this->slplus->clean[ 'page' ] ) {
			case 'slp_manage_locations':
				require_once( SLPLUS_PLUGINDIR . 'include/module/admin_tabs/SLP_Admin_Locations.php' );
				return $this->slplus->Admin_Locations->save_screen_options( $status, $option, $value );
				break;
		}
		return $status;
	}

	/**
	 * Called when the WordPress init action is processed.
	 *
	 * Current user is authenticated by this time.
	 */
	public function init() {
		load_plugin_textdomain( 'store-locator-le' );

		// Debug?
		if ( $this->slplus->SmartOptions->enable_wp_debug->is_true ) {
			$this->debug_mode();
		}

		add_filter( 'set-screen-option' , array( $this , 'save_screen_options' ) , 10 , 3 );

		// Fire the SLP init starting trigger
		//
		do_action( 'slp_init_starting', $this );

		// Do not texturize our shortcodes
		//
		add_filter( 'no_texturize_shortcodes', array( 'SLP_UI', 'no_texturize_shortcodes' ) );

		/**
		 * Register the store taxonomy & page type.
		 *
		 * This is used in multiple add-on packs.
		 *
		 */
		if ( ! taxonomy_exists( SLPlus::locationTaxonomy ) ) {
			// Store Page Labels
			//
			$storepage_labels =
				apply_filters(
					'slp_storepage_labels',
					array(
						'name'          => __( 'Store Pages', 'store-locator-le' ),
						'singular_name' => __( 'Store Page', 'store-locator-le' ),
                        'all_items'     => __( 'All Pages' , 'store-locator-le' ),
					)
				);

			$storepage_features =
				apply_filters(
					'slp_storepage_features',
					array(
						'title',
						'editor',
						'author',
						'excerpt',
						'trackback',
						'thumbnail',
						'comments',
						'revisions',
						'custom-fields',
						'page-attributes',
						'post-formats'
					)
				);

			$storepage_attributes = apply_filters( 'slp_storepage_attributes', array(
                'labels'          => $storepage_labels,
                'public'          => false,
                'has_archive'     => true,
                'description'     => sprintf( __( '%s location pages.', 'store-locator-le' ) , SLPLUS_NAME ),
                'menu_position'    => 32,
                'menu_icon'       => SLPlus::menu_icon,
                'show_in_menu'    => current_user_can( 'manage_slp_admin' ),
                'capabilities'     => array(
                    'create_posts' => 'do_not_allow',
                ),
                'map_meta_cap'      => true,
                'supports'        => $storepage_features,
            ) );

			// Register Store Pages Custom Type
			register_post_type( SLPlus::locationPostType, $storepage_attributes );

			register_taxonomy(
				SLPLus::locationTaxonomy,
				SLPLus::locationPostType,
				array(
					'hierarchical' => true,
					'labels'       =>
						array(
							'menu_name' => __( 'Categories', 'store-locator-le' ),
							'name'      => __( 'Store Categories', 'store-locator-le' ),
						),
					'capabilities' =>
						array(
							'manage_terms' => 'manage_slp_admin',
							'edit_terms'   => 'manage_slp_admin',
							'delete_terms' => 'manage_slp_admin',
							'assign_terms' => 'manage_slp_admin',
						)
				)
			);
		}

		// Fire the SLP initialized trigger
		//
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		// HOOK: slp_init_complete
		//
		do_action( 'slp_init_complete' );

		//  If the current user can manage_slp (roles & caps), add these admin hooks.
		//
		if ( current_user_can( 'manage_slp' ) ) {
			add_action('admin_menu'		    , array( $this , 'admin_menu'			)		); 	// ADMIN

			if ( ! defined( 'MYSLP_VERSION' ) || is_main_site() ) {
				add_action( 'admin_bar_menu', array( $this, 'add_slp_to_admin_bar' ), 999 );    // ADMIN
			}

            add_action('network_admin_menu'	, array( $this , 'network_admin_menu'	) , 999 ); 	// Multisite Admin Menu

		}
	}

	/**
	 * Add profiler to cron requests.
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function modify_cron_request( $request ) {
		if ( $this->slplus->SmartOptions->enable_wp_debug->is_false ) {
			return $request;
		}

		$request['url'] .= '&XDEBUG_PROFILE';
		return $request;
	}

	/**
	 * This is called whenever the WordPress wp_enqueue_scripts action is called.
	 */
	public function wp_enqueue_scripts() {

		//------------------------
		// Register our scripts for later enqueue when needed
		//
		if ( ! $this->slplus->is_CheckTrue( $this->slplus->options_nojs['no_google_js'] ) ) {
			$this->slplus->enqueue_google_maps_script();
		}

		$sslURL =
			( is_ssl() ?
				preg_replace( '/http:/', 'https:', SLPLUS_PLUGINURL ) :
				SLPLUS_PLUGINURL
			);


		$js_file[ 'slp_core'   ] = ( ! WP_DEBUG && is_readable(SLPLUS_PLUGINDIR . 'js/slp_core.min.js'  )  )  ?  '/js/slp_core.min.js' :   '/js/slp_core.js';
		$js_file[ 'csl_script' ] = ( ! WP_DEBUG && is_readable( SLPLUS_PLUGINDIR . '/js/wpslp.min.js' ) ) ?  '/js/wpslp.min.js' :  '/js/wpslp.js';


		// Force load?  Enqueue and localize.
		//
		if ( $this->slplus->javascript_is_forced ) {
			wp_enqueue_script( 'slp_core', $sslURL . $js_file[ 'slp_core'   ], array( 'jquery' ), filemtime( SLPLUS_PLUGINDIR . $js_file[ 'slp_core'   ] ), ! $this->slplus->javascript_is_forced );
			wp_enqueue_script( 'csl_script', $sslURL . $js_file[ 'csl_script' ] , array( 'slp_core' ), filemtime( SLPLUS_PLUGINDIR . $js_file[ 'csl_script'   ] ), ! $this->slplus->javascript_is_forced );

			$this->slplus->UI->localize_script();
			$this->slplus->UI->setup_stylesheet_for_slplus();

		// No force load?  Register only.
		// Localize happens when rendering a shortcode.
		//
		} else {
			wp_register_script( 'slp_core', $sslURL .$js_file[ 'slp_core'   ], array( 'jquery' ), filemtime( SLPLUS_PLUGINDIR . $js_file[ 'slp_core'   ] ), ! $this->slplus->javascript_is_forced );
			wp_register_script( 'csl_script', $sslURL .$js_file[ 'csl_script' ] , array( 'slp_core' ), filemtime( SLPLUS_PLUGINDIR . $js_file[ 'csl_script'   ] ), ! $this->slplus->javascript_is_forced );

			$this->scripts_registered = true;
		}
	}


	/**
	 * This is called whenever the WordPress shutdown action is called.
	 */
	function wp_footer() {
		SLP_Actions::ManageTheScripts();
	}


	/**
	 * Called when the <head> tags are rendered.
	 */
	function wp_head() {
		if ( ! isset( $this->slplus ) ) {
			return;
		}


		echo '<!-- SLP Custom CSS -->' . "\n" . '<style type="text/css">' . "\n" .

		     // Map
		     "div#map.slp_map {\n" .
		     "width:{$this->slplus->options_nojs['map_width']}{$this->slplus->options_nojs['map_width_units']};\n" .
		     "height:{$this->slplus->options_nojs['map_height']}{$this->slplus->options_nojs['map_height_units']};\n" .
		     "}\n" .

		     // Tagline
		     "div#slp_tagline {\n" .
		     "width:{$this->slplus->options_nojs['map_width']}{$this->slplus->options_nojs['map_width_units']};\n" .
		     "}\n" .

		     // FILTER: slp_ui_headers
		     //
		     apply_filters( 'slp_ui_headers', '' ) .

		     '</style>' . "\n\n";
	}

    /**
     * Network menu admin.
     */
    public function network_admin_menu() {
        add_menu_page(
            SLPLUS_NAME,
            SLPLUS_NAME,
            'manage_network_options',
            'slp-network-admin',
            array( SLP_Admin_UI::get_instance() , 'renderPage_GeneralSettings' ),
            SLPlus::menu_icon
            );
    }

	/**
	 * This is called whenever the WordPress shutdown action is called.
	 */
	function shutdown() {
		SLP_Actions::ManageTheScripts();
	}

	/**
	 * Unload The SLP Scripts If No Shortcode
	 */
	function ManageTheScripts() {
		if ( ! defined( 'SLPLUS_SCRIPTS_MANAGED' ) || ! SLPLUS_SCRIPTS_MANAGED ) {

			// If no shortcode rendered, remove scripts
			//
			if ( ! defined( 'SLPLUS_SHORTCODE_RENDERED' ) || ! SLPLUS_SHORTCODE_RENDERED ) {
				wp_dequeue_script( 'google_maps' );
				wp_deregister_script( 'google_maps' );
				if ( $this->scripts_registered ) {
					wp_dequeue_script( 'slp_core' );
					wp_deregister_script( 'slp_core' );
					wp_dequeue_script( 'csl_script' );
					wp_deregister_script( 'csl_script' );
					$this->scripts_registered = false;
				}
			}
			define( 'SLPLUS_SCRIPTS_MANAGED', true );
		}
	}
}

// These dogs are loaded up way before this class is instantiated.
//
add_action("load-post",array( 'SLP_Actions' , 'init'));
add_action("load-post-new",array( 'SLP_Actions' , 'init'));

