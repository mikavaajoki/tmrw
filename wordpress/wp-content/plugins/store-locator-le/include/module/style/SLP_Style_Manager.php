<?php
defined( 'ABSPATH' ) || die();

/**
 * Class SLP_Style_Manager
 *
 * Cached styles are cached by the post ID.
 *
 * @property-read   array   $current_style  the meta for the current style being applied/in use
 */
class SLP_Style_Manager extends SLPlus_BaseClass_Object {
	const TRANSIENT_BASE = 'slp_style_';
	const REST_ENDPOINT = 'wp-json/wp/v2/slp_style_gallery';

	private $current_style;
	public $page_size = 9;

	/**
	 * Apply the new style.
	 *
	 * @param $style_id
	 */
	public function apply_style( $style_id ) {
		$this->set_active_style( $style_id );

		if ( empty( $this->current_style ) ) {
			return;
		}
		foreach ( $this->current_style->custom_fields  as $option => $data_array ) {
			if ( $this->slplus->SmartOptions->exists( $option ) ) {
				if ( $data_array[0] === '&nbsp;') {
					$data_array[0] = '';
				}
				$this->slplus->SmartOptions->set( $option , $data_array[0] );
			}
		}

		if ( property_exists( $this->current_style , 'css' ) ) {
			if ( property_exists( $this->current_style->css , 'content' ) ) {
				$this->slplus->SmartOptions->set( 'active_style_css', $this->current_style->css->content );
			} else {
				$this->slplus->SmartOptions->set( 'active_style_css', $this->slplus->SmartOptions->active_style_css->default );
			}
			if ( property_exists( $this->current_style->css , 'date' ) ) {
				$this->slplus->SmartOptions->set( 'active_style_date', $this->current_style->css->date );
			} else {
				$this->slplus->SmartOptions->set( 'active_style_date', $this->slplus->SmartOptions->active_style_date->default );
			}
		}
	}

	/**
	 * Write the locator style to the cache (WP transient)
	 *
	 * @param $post
	 */
	public function cache_style( $post ) {
		set_transient( SLP_Style_Manager::TRANSIENT_BASE . $post->id , $post , WEEK_IN_SECONDS );
	}

	/**
	 * Change the active style.
	 *
	 * @param int $old_id
	 * @param int $new_id
	 */
	public function change_style( $old_id , $new_id ) {
		// TODO: add a 'save style' to allow users to revert
		$this->apply_style( $new_id );
	}

	/**
	 * Fetch the style (or styles) from the style server.
	 *
	 * @param int|null $style_id
	 * @return WP_Error|string
	 */
	public function fetch_style( $style_id = null ) {
		$style_selector = is_null( $style_id ) ? '' : sprintf( '/%d' , $style_id );
		$request_params = '?orderby=title&order=asc&per_page=' . $this->page_size;
		return SLP_Service::get_instance()->get_styles( $style_selector , $request_params );
	}

	/**
	 * Set the active style including its meta as $this->current_style
	 *
	 * @param int $style_id
	 */
	private function set_active_style( $style_id ) {
		if ( ! empty( $this->current_style ) && ( $this->current_style->id === (int) $style_id ) ) {
			return;
		}

		// first check transient
		$this->current_style = get_transient( SLP_Style_Manager::TRANSIENT_BASE . $style_id );

		// if not valid get from style server
		if ( empty( $this->current_style ) || ! $this->style_matches_slug( $this->current_style->slug ) ) {
			$post = $this->fetch_style( $style_id );
			if ( is_wp_error( $post ) ) {
				$this->current_style = null;
			} else {
				$this->current_style = $post;
			}
		}
	}

	/**
	 * Does the style slug match our active setting?
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	private function style_matches_slug( $slug ) {
		return ( $slug === $this->slplus->SmartOptions->style->value );
	}
}