<?php namespace AgreableCatfishImporterPlugin;

use Croissant\App;

/**
 * Adds re-sync button
 */
add_action( 'wp_ajax_catfish_reimport', function () {
	if ( ! isset( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		throw new \InvalidArgumentException( 'Request requires id and it need to be numeric' );
	}
	$id = (int) $_POST['id'];

	$url = get_post_meta( $id, 'catfish_importer_url', true );

	/**
	 * @var $api Api
	 */
	$api = App::get( Api::class );
	$api->importPost( $url );
	header( 'Content-type: Application/json' );
	echo json_encode( [] );
	exit;
} );

add_filter( 'post_row_actions', function ( $actions, $post ) {
	$actions['re_import'] = "<a title='Re-import from Catfish' class='js-catfish-reimport' href='#' data-id='{$post->ID}'>Reimport</a>";

	return $actions;
}, 10, 2 );


add_action( 'admin_init', function () {
	$user = wp_get_current_user();
	if ( in_array( 'purgatory', (array) $user->roles ) ) {
		exit( 'You are not allowed to see this page' );
	}
}, 100 );

