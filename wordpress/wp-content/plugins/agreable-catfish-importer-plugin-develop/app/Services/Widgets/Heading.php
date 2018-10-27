<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

/**
 * Class Heading
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class Heading {
	/**
	 * @param $widgetDom
	 *
	 * @return stdClass
	 */
	public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'heading';
    $widgetData->text = strip_tags($widgetDom->innertext);
    $widgetData->alignment = 'left';
    $widgetData->font = 'primary';
    return $widgetData;
  }
}
