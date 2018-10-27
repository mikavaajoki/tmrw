<?php

namespace AgreableCatfishImporterPlugin\Services\Widgets;

use simplehtmldom_1_5\simple_html_dom;
use stdClass;

/**
 * Class Embed
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class Embed {
	/**
	 * @param $widgetDom
	 *
	 * @return array|bool|stdClass
	 */

	public static function getWidgetsFromDom( $widgetDom ) {

		return array_merge( self::handleBlock( $widgetDom ), self::handleFrame( $widgetDom ) );
	}

	/**
	 * @param $widgetDom
	 *
	 * @return bool|stdClass
	 */
	public static function handleFrame( $widgetDom ) {
		$widgets = [];
		$frames  = $widgetDom->find( 'iframe' );
		foreach ( $frames as $index => $frame ) {
			$url   = $frame->src;
			$parts = parse_url( $url );
			if ( isset( $parts['query'] ) ) {
				parse_str( $parts['query'], $query );
				if ( isset( $query['href'] ) ) {
					$href = $query['href'];
					$url  = $href;
				}
			}
			$check = wp_oembed_get( $url );
			if ( $check ) {
				$widgetData        = new stdClass();
				$widgetData->type  = 'embed';
				$widgetData->embed = $url;

				array_push( $widgets, $widgetData );
			}
		}

		return array_filter( $widgets );

	}

	/**
	 * @param $widgetDom simple_html_dom
	 *
	 * @return array|bool
	 */
	public static function handleBlock( $widgetDom ) {

		// Separate multiple embeds in a row
		$widgets = [];

		$blockquotes = $widgetDom->find( 'blockquote' );
		if ( isset( $widgetDom->tag ) && $widgetDom->tag == 'blockquote' ) {
			$blockquotes = [ $widgetDom ];
		}
		foreach ( $blockquotes as $blockquote ) {

			$links = $blockquote->find( 'a' );

			// Take the last link in each blockquote which should be the link to the tweet
			foreach ( array_reverse( $links ) as $link ) {

				$href  = $link->href;
				$check = wp_oembed_get( $href );
				if ( $check ) {
					$widgetData        = new stdClass();
					$widgetData->type  = 'embed';
					$widgetData->embed = $href;

					array_push( $widgets, $widgetData );
					// Break because we only want the link to the embed not all the other links in the tweet.
					break;
				}
			}
		}

		$widgets = array_values( $widgets );

		return array_filter( $widgets );
	}
}
