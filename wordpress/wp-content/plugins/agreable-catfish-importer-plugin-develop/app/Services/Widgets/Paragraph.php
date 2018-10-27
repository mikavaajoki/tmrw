<?php

namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

/**
 * Class Paragraph
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class Paragraph {
	/**
	 * @param $widgetDom
	 *
	 * @return mixed|stdClass
	 */
	public static function getFromWidgetDom( $widgetDom ) {
		$widgetData       = new stdClass();
		$widgetData->type = 'paragraph';
		$widgetDom        = self::filterBadTags( $widgetDom );
		// Catch $widgetDom == false
		if ( is_object( $widgetDom ) && $widgetDom->innertext ) {
			$widgetData->paragraph = $widgetDom->innertext;

			return $widgetData;
		}

		return $widgetDom;
	}

	/**
	 * @param $html
	 *
	 * @return mixed
	 */
	public static function filterBadTags( $html ) {
		$badTags = 'span, center';
		// Catch if $html does not have the find() member function
		if ( $html ) {
			if ( count( $html->find( $badTags ) ) ) {
				foreach ( $html->find( $badTags ) as $index => $element ) {
					$html->find( $badTags, $index )->outertext = $element->innertext;
				}
			}
		}

		return $html;
	}
}
