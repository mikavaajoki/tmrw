<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

/**
 * Class HorizontalRule
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class HorizontalRule {
	/**
	 * @param $widgetDom
	 *
	 * @return stdClass
	 */
	public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'divider';
    return $widgetData;
  }
}
