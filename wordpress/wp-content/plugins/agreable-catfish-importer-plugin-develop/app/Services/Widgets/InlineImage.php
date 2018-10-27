<?php

namespace AgreableCatfishImporterPlugin\Services\Widgets;

use simplehtmldom_1_5\simple_html_dom_node;
use stdClass;

/**
 * Class InlineImage
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class InlineImage {
	/**
	 * @param $widgetDom
	 *
	 * @return stdClass
	 */
	public static function getFromWidgetDom( $widgetDom ) {

		$widgetData             = new stdClass();
		$widgetData->type       = 'image';
		$widgetData->image      = new stdClass();
		$widgetData->url        = '';
		$image                  = $widgetDom->find( 'img' );
		$widgetData->image->src = $image[0]->src;
		$widgetData->image->alt = isset( $image[0]->alt ) ? $image[0]->alt : '';

		$widgetData->image->filename  = substr( $widgetData->image->src, strrpos( $widgetData->image->src, '/' ) + 1 );
		$widgetData->image->name      = substr( $widgetData->image->filename, 0, strrpos( $widgetData->image->filename, '.' ) );
		$widgetData->image->extension = substr( $widgetData->image->filename, strrpos( $widgetData->image->filename, '.' ) + 1 );

		$imageCaptionElements = $widgetDom->find( '.inline-image__caption' );
		if ( count( $imageCaptionElements ) > 0 ) {
			$widgetData->image->caption = $imageCaptionElements[0]->innertext;
		} else {
			$widgetData->image->caption = '';
		}

		$inlineImageElements = $widgetDom->find( '.inline-image' );

		if ( count( $inlineImageElements ) > 0 ) {
			$classes = $inlineImageElements[0]->class;

			if ( strpos( $classes, 'inline-image--full' ) !== false ) {
				$widgetData->image->width = 'large';
			} else if ( strpos( $classes, 'inline-image--medium' ) !== false ) {
				$widgetData->image->width = 'medium';
			} else {
				$widgetData->image->width = 'small';
			}

			if ( strpos( $classes, 'inline-image--center' ) !== false ) {
				$widgetData->image->position = 'center';
			} else if ( strpos( $classes, 'inline-image--left' ) !== false ) {
				$widgetData->image->position = 'left';
			} else {
				$widgetData->image->position = 'right';
			}
		} else {
			$a = $widgetDom->find( 'a' );
			if ( count( $a ) > 0 ) {
				$widgetData->url = $a[0]->attr['href'];
			}

			$widgetData->image->width    = 'medium';
			$widgetData->image->position = 'center';
		}

		return $widgetData;
	}

	public static function createImageFromTag( simple_html_dom_node $tag ) {

		$widget = new \stdClass();

		$widget->image           = new \stdClass();
		$widget->image->width    = 'medium';
		$widget->image->position = 'center';
		$widget->url             = '';
		$widget->image->caption  = false;
		$widget->type            = 'image';
		$src                     = $tag->getAttribute( 'src' );
		if ( ! $src ) {
			$images = $tag->getAttribute( 'srcset' );
			if ( $images ) {
				$images = explode( ',', $images );
				$src    = array_pop( $images );
			}
		}
		echo $src . PHP_EOL;
		if ( $src ) {

			$widget->image->src = $src;
			$widget->url        = $src;

			return $widget;

		} else {
			return false;
		}
	}
}
