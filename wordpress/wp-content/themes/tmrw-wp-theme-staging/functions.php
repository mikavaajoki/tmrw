<?php

add_theme_support( 'post-thumbnails' ); 
add_action( 'init', 'register_issues' );
add_action( 'admin_init', 'add_theme_caps');


if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page();
	
}

// Including the custom fields for projects
include_once('custom-fields/post-fields.php');
include_once('custom-fields/option-fields.php');
include_once('custom-fields/issue-fields.php');

add_filter('timber_context', 'add_to_context');

function add_to_context($data){

  $data['menu'] = new TimberMenu();
  $data['categories'] = Timber::get_terms('category', 'show_count=0&title_li=&hide_empty=0&exclude=1');
  $data['options'] = get_fields('options');

  return $data;
}


add_action( 'after_setup_theme', 'register_my_menu' );
function register_my_menu() {
  register_nav_menu( 'primary', __( 'Primary Menu', 'theme-slug' ) );
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
  }
}


?>