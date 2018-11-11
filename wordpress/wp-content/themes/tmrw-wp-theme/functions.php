<?php

add_theme_support( 'post-thumbnails' ); 
add_action( 'init', 'register_issues' );
add_action( 'init', 'register_legacy_posts' );
add_action( 'admin_init', 'add_theme_caps');

if( function_exists('acf_add_options_page') ) {
  
  acf_add_options_page();
  
}

// Load Composer dependencies
require_once 'vendor/autoload.php';
require_once 'inc/WooCommerce_Theme.php';

// Including the custom fields for projects
include_once('custom-fields/post-fields.php');
include_once('custom-fields/option-fields.php');
include_once('custom-fields/issue-fields.php');


add_filter('timber_context', 'add_to_context');

function add_to_context($data){

  $data['menu'] = new TimberMenu();
  $data['categories'] = Timber::get_terms('category', 'show_count=0&title_li=&hide_empty=0&exclude=1');
  $data['options'] = get_fields('options');

  $issueArgs = array('post_type' => 'issue', 'posts_per_page' => 100);   
  $data['issues'] = Timber::get_posts($issueArgs);


  $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $data['current_url'] = $actual_link;


  // $current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
  
  // $data['page_slug'] = $current_page->post_name;

  return $data;
}


add_action( 'after_setup_theme', 'register_my_menu' );
add_action('after_setup_theme', 'setup_theme');

function setup_theme(  ) {
    // Theme setup code...
    
    // Filters the oEmbed process to run the responsive_embed() function
    add_filter('embed_oembed_html', 'responsive_embed', 10, 3);
}

function register_my_menu() {
  register_nav_menu( 'primary', __( 'Primary Menu', 'theme-slug' ) );
}

/**
 * Adds a responsive embed wrapper around oEmbed content
 * @param  string $html The oEmbed markup
 * @param  string $url  The URL being embedded
 * @param  array  $attr An array of attributes
 * @return string       Updated embed markup
 */
function responsive_embed($html, $url, $attr) {
    return $html!=='' ? '<div class="embed-container">'.$html.'</div>' : '';
}


function register_issues() {
  $labels = array( 
      'name' => _x( 'Issues', 'issue' ),
      'singular_name' => _x( 'Issues', 'issue' ),
      'add_new' => _x( 'Add New', 'issue' ),
      'add_new_item' => _x( 'Add New Issue', 'issue' ),
      'edit_item' => _x( 'Edit Issue', 'issue' ),
      'new_item' => _x( 'New Issue', 'issue' ),
      'view_item' => _x( 'View Issue', 'issue' ),
      'search_items' => _x( 'Search issues', 'issue' ),
      'not_found' => _x( 'No issues found', 'issue' ),
      'not_found_in_trash' => _x( 'No properties found in Trash', 'issue' ),
      'parent_item_colon' => _x( 'Parent issue:', 'issue' ),
      'menu_name' => _x( 'Issues', 'Issue' ),
  );

  $args = array( 
      'labels' => $labels,
      'hierarchical' => true,
      'description' => 'Add new issues',
      'public' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'menu_icon'   => 'dashicons-editor-justify',
      'menu_position'       => 2,
      'show_in_rest'       => true,
      'rest_base'          => 'issues-api',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'show_in_nav_menus' => true,
      'publicly_queryable' => true,
      'exclude_from_search' => false,
      'has_archive' => true,
      'query_var' => true,
      'can_export' => true,
      'rewrite' => true,
      'capabilities' => array(
          'edit_post' => 'edit_issue',
          'edit_posts' => 'edit_issues',
          'edit_others_posts' => 'edit_other_issues',
          'publish_posts' => 'publish_issues',
          'read_post' => 'read_issue',
          'read_private_posts' => 'read_private_issues',
          'delete_post' => 'delete_issue'
      ),
      'map_meta_cap' => true
  );

  register_post_type( 'issue', $args );
}


function register_legacy_posts() {
  $labels = array( 
      'name' => _x( 'Legacy', 'legacy' ),
      'singular_name' => _x( 'Legacy', 'legacy' ),
      'add_new' => _x( 'Add New', 'legacy' ),
      'add_new_item' => _x( 'Add New Legacy', 'Legacy' ),
      'edit_item' => _x( 'Edit Legacy', 'Legacy' ),
      'new_item' => _x( 'New Legacy', 'Legacy' ),
      'view_item' => _x( 'View Legacy', 'Legacy' ),
      'search_items' => _x( 'Search Legacy', 'Legacy' ),
      'not_found' => _x( 'No Legacy found', 'Legacy' ),
      'not_found_in_trash' => _x( 'No properties found in Trash', 'Legacy' ),
      'parent_item_colon' => _x( 'Parent Legacy:', 'Legacy' ),
      'menu_name' => _x( 'Legacy', 'Legacy' ),
  );

  $args = array( 
      'labels' => $labels,
      'hierarchical' => true,
      'description' => 'Add new legacy',
      'public' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'menu_position'       => 8,
      'show_in_rest'       => true,
      'rest_base'          => 'legacy-api',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'show_in_nav_menus' => true,
      'publicly_queryable' => true,
      'exclude_from_search' => false,
      'has_archive' => true,
      'query_var' => true,
      'can_export' => true,
      'taxonomies'  => array( 'category' ),
      'supports' => array('thumbnail', 'editor', 'title'),
      'capabilities' => array(
          'edit_post' => 'edit_legacy',
          'edit_posts' => 'edit_legacies',
          'edit_others_posts' => 'edit_other_legacies',
          'publish_posts' => 'publish_legacies',
          'read_post' => 'read_legacy',
          'read_private_posts' => 'read_private_legacies',
          'delete_post' => 'delete_legacy'
      ),
      'map_meta_cap' => true
  );

  register_post_type( 'legacy', $args );
}

function add_theme_caps() {
  $roles = array('editor','administrator');
  // Loop through each role and assign capabilities
  foreach($roles as $the_role) {
    $role = get_role($the_role);
    $role->add_cap( 'edit_issue' ); 
    $role->add_cap( 'edit_issues' ); 
    $role->add_cap( 'edit_other_issues' ); 
    $role->add_cap( 'publish_issues' ); 
    $role->add_cap( 'read_issue' ); 
    $role->add_cap( 'read_private_issues' ); 
    $role->add_cap( 'delete_issue');  
    $role->add_cap( 'edit_legacy' ); 
    $role->add_cap( 'edit_legacies' ); 
    $role->add_cap( 'edit_other_legacies' ); 
    $role->add_cap( 'publish_legacies' ); 
    $role->add_cap( 'read_legacy' ); 
    $role->add_cap( 'read_private_legacies' ); 
    $role->add_cap( 'delete_legacy');  
  }
}





add_action( 'after_setup_theme', 'remove_woocommerce_single_product', 1 );

function remove_woocommerce_single_product() {
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
}


add_action( 'after_setup_theme', 'add_product_desciption', 2 );

function add_product_desciption() {
   add_action( 'product_description', 'woocommerce_template_single_excerpt', 5 );
 
}


// Single Product
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_single_add_to_cart_text' );
function custom_single_add_to_cart_text() {
  return __('Buy Now', 'woocommerce'); // Change this to change the text on the Single Product Add to cart button.
}

// Add class to checkout fields
add_filter('woocommerce_checkout_fields', 'addBootstrapToCheckoutFields' );
function addBootstrapToCheckoutFields($fields) {
    foreach ($fields as &$fieldset) {
        foreach ($fieldset as &$field) {
            // if you want to add the form-group class around the label and the input
            $field['class'][] = 'form-field'; 

        }
    }
    return $fields;
}

// Reorder Checkout Fields
add_filter('woocommerce_checkout_fields','reorder_woo_fields');

function reorder_woo_fields($fields) {
$fields['billing']['billing_first_name']['priority'] = 5;
$fields['billing']['billing_last_name']['priority'] = 10;
$fields['billing']['billing_company']['priority'] = 20;
$fields['billing']['billing_address_1']['priority'] = 30;
$fields['billing']['billing_address_2']['priority'] = 40;
$fields['billing']['billing_city']['priority'] = 50;
$fields['billing']['billing_state']['priority'] = 60;
$fields['billing']['billing_postcode']['priority'] = 70;
$fields['billing']['billing_country']['priority'] = 80;
$fields['billing']['billing_email']['priority'] = 90;
$fields['billing']['billing_phone']['priority'] = 100;

// Adding custom classes
$fields['billing']['billing_address_1'] = array(
'label' => __('STREET ADDRESS', 'woocommerce'),
'required' => false,
'class' => array('form-field street-address'),
'clear' => true
);

$fields['billing']['billing_address_2'] = array(
'label' => __('APARTMENT / SUITE / ETC. ', 'woocommerce'),
'required' => false,
'class' => array('form-field'),
'clear' => true
);

$fields['billing']['billing_country'] = array(
'label' => __('COUNTRY', 'woocommerce'),
'type' => 'country',
'required' => false,
'class' => array('form-field custom-select'),
'clear' => true
);

return $fields;
}


// rewrites custom post type name
global $wp_rewrite;
$legacy_structure = '/%category%/%postname%';
$wp_rewrite->add_rewrite_tag("%legacy%", '([^/]+)', "legacy=");
$wp_rewrite->add_permastruct('legacy', $legacy_structure, false);

?>