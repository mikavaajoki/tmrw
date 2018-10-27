<?php

namespace AgreableCatfishImporterPlugin\Services\Widgets;

use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class Html
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class Html {
	const TAG_ALLOW_NESTED = [
		'main',
		'div',
		'article',
		'p',

	];
	const MAX_PARSE_DEPTH = 3;

	/**
	 * @param $html_string
	 *
	 * @return bool
	 */
	public static function checkIfValidParagraph( $html_string ) {
		$allowable_tags  = '<a><b><i><br><em><sup><sub><strong><p><h3><ul><ol><li><span><center>';
		$stripped_string = strip_tags( $html_string, $allowable_tags );
		$test            = ( $html_string == $stripped_string );
		if ( ctype_space( strip_tags( html_entity_decode( $html_string, ENT_HTML5, 'iso-8859-1' ) ) ) ) {
			return false;
		}

		return $test;
	}

	/**
	 * @param $widgetDom
	 *
	 * @return array|mixed|\stdClass
	 */
	public static function getFromWidgetDom( \simplehtmldom_1_5\simple_html_dom_node $widgetDom ) {
		// Remove the <div class="legacy-custom-html"/> that Clock wrap around the content
		if ( isset( $widgetDom->find( '.legacy-custom-html' )[0] ) ) {
			$widgetDom = $widgetDom->find( '.legacy-custom-html' )[0];
		}

		if ( self::checkIfValidParagraph( $widgetDom->innertext ) ) {
			// Return paragraph only posts as a single paragraph widget
			return Paragraph::getFromWidgetDom( $widgetDom );
		} else {

			return array_filter( self::breakIntoWidgets( $widgetDom ) );
		}
	}

	/**
	 * @param $widgetDom
	 *
	 * @return array|bool
	 * @throws \Exception
	 */
	public static function breakIntoWidgets( \simplehtmldom_1_5\simple_html_dom_node $widgetDom, $depth = 0 ) {

		if ( $depth > self::MAX_PARSE_DEPTH ) {
			return [];
		}

		$widgets = [];
		// Loop through all DOM nodes to create widgets from them
		foreach ( $widgetDom->children() as $index => $node ) {

			if ( $node->getAttribute( 'id' ) === 'fb-root' || ( $node->tag === 'script' && self::checkIfEmbedScriptTag( $node->src ) ) ) {
				echo 'bails on ' . $node->tag . PHP_EOL;

				continue;
			}
			/**
			 * @var $node \simplehtmldom_1_5\simple_html_dom_node
			 */
			// Check if this DOM node is a valid paragraph widget
			if ( self::checkIfValidParagraph( $node->outertext ) ) {
				// Remove blank <p>&nbsp;</p> paragraph
				$clean_paragraph = str_replace( "<p>&nbsp;</p>", "", $node->outertext );
				$paragraphDom    = HtmlDomParser::str_get_html( $clean_paragraph );
				array_push( $widgets, Paragraph::getFromWidgetDom( $paragraphDom ) );

			} elseif ( $node->tag === 'h2' ) {

				array_push( $widgets, Heading::getFromWidgetDom( $node ) );

			} elseif ( ( $embedWidgets = Embed::getWidgetsFromDom( $node ) ) ) {
				foreach ( $embedWidgets as $child_widget ) {
					array_push( $widgets, $child_widget );
				}

			} elseif ( $node->tag === 'img' ) {

				array_push( $widgets, InlineImage::createImageFromTag( $node ) );

			} else {

				/**
				 * As in some of the cases people put embeds wrapped in for example div we need to parse it recursively
				 */
				if ( in_array( $node->tag, self::TAG_ALLOW_NESTED ) ) {
					$childWidgets = self::breakIntoWidgets( $node, $depth + 1 );
					if ( count( array_filter( $childWidgets, function ( $i ) {
						return $i->type !== 'html';
					} ) ) ) {
						foreach ( $childWidgets as $index => $child_widget ) {
							array_push( $widgets, $child_widget );
						}
						//	$widgets = call_user_func_array( 'array_push', $childWidgets );
						continue;
					}
				}

				$html       = new \stdClass();
				$html->type = 'html';
				$html->html = $node->outertext;
				array_push( $widgets, $html );

			}
		}

		return array_values( array_filter( $widgets ) );

	}


	/**
	 * Check if the DOM element is a excess script tag from a social embed string
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public static function checkIfEmbedScriptTag( $string ) {
		return preg_match( "/(platform\.twitter\.com|platform\.instagram\.com|connect\.facebook\.com)/", $string );
	}
}
