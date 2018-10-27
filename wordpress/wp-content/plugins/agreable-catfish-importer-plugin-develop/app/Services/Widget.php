<?php

namespace AgreableCatfishImporterPlugin\Services;

use AgreableCatfishImporterPlugin\Exception\CatfishException;
use AgreableCatfishImporterPlugin\Services\Widgets\HorizontalRule;
use AgreableCatfishImporterPlugin\Services\Widgets\Html;
use AgreableCatfishImporterPlugin\Services\Widgets\InlineImage;
use AgreableCatfishImporterPlugin\Services\Widgets\Video;
use Croissant\App;
use Croissant\DI\Dependency\CatfishLogger;
use Mesh\Image;
use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class Widget
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
class Widget {

	/**
	 *
	 */
	const WIDGET_GALLERY_ENDPOINT = '/api/in-page-gallery-data';
	/**
	 *
	 */
	const GALLERY_POST_ENDPOINT = '/api/gallery-data';

	/**
	 * @param $widgetName
	 * @param \stdClass $data
	 *
	 * @return \stdClass
	 */
	public static function makeWidget( $widgetName, \stdClass $data ) {
		$widget                = clone $data;
		$widget->acf_fc_layout = $widgetName;

		return $widget;
	}

	/**
	 * @param $widget
	 * @param $widgets
	 */
	public static function addWidgetToWidgets( $widget, $widgets ) {
		$widgets[] = $widget;
	}

	/**
	 * @param $ar1
	 * @param $ar2
	 *
	 * @return array
	 */
	public static function appendArray( $ar1, $ar2 ) {
		foreach ( $ar2 as $index => $item ) {
			$ar1[] = $item;
		}

		return $ar1;
	}

	/**
	 * Attach widgets to the $post via WP metadata
	 *
	 * @param \TimberPost $post
	 * @param array $widgetsData
	 * @param \stdClass $catfishPostObject
	 *
	 * @throws \Exception
	 */
	public static function setPostWidgets( \TimberPost $post, array $widgetsData, \stdClass $catfishPostObject ) {


		$widgets = [];

		foreach ( $widgetsData as $index => $widgetData ) {
			if ( $widgetData->type !== 'gallery' ) {
				$widgets[] = $widgetData;
				continue;
			}
			$widgets = self::appendArray( $widgets, self::unpackGallery( $catfishPostObject->absoluteUrl, $widgetData->html->attr['data-id'] ) );
		}
		if ( $catfishPostObject->type === 'gallery' ) {
			$widgets = self::appendArray( $widgets, self::unpackGallery( $catfishPostObject->absoluteUrl, false ) );
		}

		$widgets = self::mergeAdjecentWidgets( $widgets );

		/**
		 * Delete all current widgets
		 */

		update_field( $post->post_type . '_widgets', null, $post->id );
		$widgetsInput = [];
		foreach ( $widgets as $key => $widget ) {

			switch ( $widget->acf_fc_layout ) {
				case 'embed':
					$widgetsInput[] = array(
						'embed'         => $widget->embed,
						'width'         => 'medium',
						'acf_fc_layout' => $widget->acf_fc_layout
					);

					break;
				case 'heading':

					$widgetsInput[] = array(
						'text'          => $widget->text,
						'aligment'      => $widget->alignment,
						'font'          => $widget->font,
						'acf_fc_layout' => $widget->acf_fc_layout
					);
					break;
				case 'html':
					$widgetsInput[] = array( 'html' => $widget->html, 'acf_fc_layout' => $widget->acf_fc_layout );

					break;
				case 'paragraph':
					$widgetsInput[] = array(
						'paragraph'     => $widget->paragraph,
						'acf_fc_layout' => $widget->acf_fc_layout
					);
					break;
				case 'image':

					try {
						$image = new Image( str_replace( '.darkroom.', '.origin.darkroom.', $widget->image->src ) );
					} catch ( \Exception $e ) {
						$image     = new \stdClass();
						$image->id = null;
						App::get( CatfishLogger::class )->error( 'Error while importing image: ' . $widget->image->src, [ $widget ] );
					}
					if ( isset( $image->id ) && isset( $widget->image->alt ) ) {
						update_post_meta( $image->id, '_wp_attachment_image_alt', $widget->image->alt );
					}
					$widgetsInput[] = array(
						'image'         => $image->id,
						'border'        => 0,
						'width'         => $widget->image->width,
						'position'      => $widget->image->position,
						'crop'          => 'original',
						'link'          => $widget->url,
						'caption'       => isset( $widget->image->caption ) ? $widget->image->caption : null,
						'acf_fc_layout' => $widget->acf_fc_layout
					);

					break;
				case 'video':
					$widgetsInput[] = array(
						'url'           => $widget->video->url,
						'width'         => $widget->video->width,
						'position'      => $widget->video->position,
						'acf_fc_layout' => $widget->acf_fc_layout
					);

					break;
				case 'divider':
					$widgetsInput[] = array( 'acf_fc_layout' => $widget->acf_fc_layout );
					break;
				case 'gallery':

					$imageIds = [];

					foreach ( $widget->data->images as $image ) {

						$title = $image->title;

						if ( $title == "." ) {

							$title = $post->title;
						}
						$imageUrl = str_replace( '.darkroom.', '.origin.darkroom.', array_pop( $image->__mainImageUrls ) );

						// Sideload the image
						$post_data = array(
							'post_title'   => $title,
							'post_content' => $image->description,
							'post_excerpt' => $image->description
						);
						echo $imageUrl . PHP_EOL;
						$post_attachment_id = WPErrorToException::loud( self::simple_image_sideload( $imageUrl . '.jpg', $post->ID, $title, $post_data ) );
						wp_update_post( array_merge( $post_data, [ 'ID' => $post_attachment_id ] ) );
						$imageIds[] = $post_attachment_id;
					}

					$widgetsInput[] = array( 'acf_fc_layout' => $widget->acf_fc_layout, 'gallery_items' => $imageIds );
					//	self::setGalleryWidget( $post, $widgetNames, $widget->data );

					break;
				case 'promo':
					// Throw exception if promo widget found
					// To help decide if we need Promo widgets in the new pages CMS, throw an exception if a promo widget is found
					throw new \Exception( "Importer found a promo widget. Someone call Elliot.", 30 );
					break;
			}

		}
		$success = update_field( $post->post_type . '_widgets', $widgetsInput, $post->id );
		if ( ! $success ) {
			throw new CatfishException( 'There was problem while saving grid array' );
		}
		// This is an array of widget names for ACF
		/*	update_post_meta( $post->id, 'widgets', serialize( $widgetNames ) );
			update_post_meta( $post->id, '_widgets', 'post_widgets' );*/
	}

	/**
	 * @param $widgets
	 *
	 * @return array
	 */
	public static function mergeAdjecentWidgets( $widgets ) {

		$widgets = array_values( array_filter( $widgets ) );

		foreach ( $widgets as $index => $widget ) {
			if ( $index == 0 ) {
				continue;
			}
			$prev = $widgets[ $index - 1 ];
			if ( ( $prev->type == 'html' ) && ( $widget->type == 'html' ) ) {
				$widget->html = $prev->html . $widget->html;
				unset( $widgets[ $index - 1 ] );
			}
			if ( ( $prev->type == 'paragraph' ) && ( $widget->type == 'paragraph' ) ) {
				$widget->paragraph = $prev->paragraph . $widget->paragraph;
				unset( $widgets[ $index - 1 ] );
			}

		}

		return array_values( array_filter( $widgets ) );
	}

	/**
	 * @param $catfishUrl
	 * @param $widgetId
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function unpackGallery( $catfishUrl, $widgetId ) {

		$galleryData = self::getGalleryData( $catfishUrl, $widgetId );
		if ( ! isset( $galleryData->images ) || ! is_array( $galleryData->images ) ) {
			throw new \Exception( 'Was expecting an array of images in gallery data' );
		}
		/**
		 * if no html elements bail
		 */
		if ( count( array_filter( $galleryData->images, function ( $i ) {
				return $i->type !== 'image';
			} ) ) === 0 ) {
			$widget       = new \stdClass();
			$widget->type = 'gallery';
			$widget->data = $galleryData;

			return [ self::makeWidget( 'gallery', $widget ) ];

		}
		/**
		 * Unpack widget
		 */
		$ret = [];
		foreach ( $galleryData->images as $index => $item ) {

			if ( $item->type === 'image' ) {

				if ( trim( $item->title ) ) {
					$widgetData                = new \stdClass();
					$widgetData->type          = 'heading';
					$widgetData->text          = strip_tags( trim( $item->title ) );
					$widgetData->alignment     = 'left';
					$widgetData->font          = 'primary';
					$widgetData->acf_fc_layout = 'heading';
					$ret[]                     = $widgetData;
				}

				$widget                  = new \stdClass();
				$widget->id              = $item->id;
				$widget->image           = new \stdClass();
				$widget->image->width    = 'medium';
				$widget->image->position = 'center';
				$widget->url             = '';
				$widget->image->caption  = ( $item->description ? $item->description : false );
				$widget->type            = 'image';
				$widget->image->alt      = isset( $item->__caption ) ? $item->__caption : '';
				$widget->image->src      = array_pop( $item->__mainImageUrls );
				$ret[]                   = $widget;

			} elseif ( $item->type === 'html' ) {

				$widgets = [];

				if ( trim( $item->title ) ) {
					$widgetData                = new \stdClass();
					$widgetData->type          = 'heading';
					$widgetData->text          = strip_tags( trim( $item->title ) );
					$widgetData->alignment     = 'left';
					$widgetData->font          = 'primary';
					$widgetData->acf_fc_layout = 'heading';
					$widgets[]                 = $widgetData;
				}

				$html = HtmlDomParser::str_get_html( ( $item->description ? $item->description : '' ) . ( $item->html ? $item->html : '' ) );

				$textWidgets = Html::breakIntoWidgets( $html->root );

				if ( is_array( $textWidgets ) ) {
					$widgets = self::appendArray( $widgets, $textWidgets );
				}


				if ( ! empty( $widgets ) ) {
					$ret = self::appendArray( $ret, $widgets );
				}

			}
		}

		return array_map( function ( $i ) {
			return self::makeWidget( $i->type, $i );
		}, $ret );
	}

	/**
	 * @param $catfishUrl
	 * @param $widgetId
	 *
	 * @return \stdClass
	 */
	public static function getGalleryData( $catfishUrl, $widgetId ) {

		$endpoint = ! $widgetId ? self::GALLERY_POST_ENDPOINT : self::WIDGET_GALLERY_ENDPOINT;

		$catfishPath = '/' . trim( parse_url( $catfishUrl )['path'], '/' );

		$widgetQueryString = $widgetId ? '?widgetId=' . $widgetId : '';

		$galleryApi = str_replace( $catfishPath, $endpoint . $catfishPath . $widgetQueryString, $catfishUrl );

		return Fetch::json( $galleryApi, false );

	}


	/**
	 * @param $post
	 * @param $widgetNames
	 * @param $galleryData
	 */
	protected static function setGalleryWidget( $post, $widgetNames, $galleryData ) {


	}

	/**
	 * Function to side load image from Clock to Wordpress
	 *
	 * Adapted from Mark Wilkinson's function:
	 * https://markwilkinson.me/2015/07/using-the-media-handle-sideload-function/
	 *
	 * @param $url
	 * @param $post_id
	 * @param $desc
	 * @param $post_data
	 *
	 * @return int|mixed|object
	 */
	public static function simple_image_sideload( $url, $post_id, $desc, $post_data ) {

		/**
		 * download the url into wordpress
		 * saved temporarily for now
		 */
		$tmp = download_url( $url );

		/**
		 * build an array of file information about the url
		 * getting the files name using basename()
		 */
		$file_array = array(
			'name'     => basename( $url ),
			'tmp_name' => $tmp
		);
		/**
		 * Check for download errors
		 * if there are error unlink the temp file name
		 */
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );

			return $tmp;
		}
		/**
		 * now we can use the sideload function
		 * we pass it the file array of the file to handle
		 * and the post id of the post to attach it too
		 * it returns the attachment id if the file
		 */
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}
		$id = media_handle_sideload( $file_array, $post_id, $desc, $post_data );
		/**
		 * check for handle sideload errors
		 * if errors again unlink the file
		 */
		if ( is_wp_error( $id ) ) {


			@unlink( $file_array['tmp_name'] );

			return $id;
		}

		/**
		 * get the url from the newly upload file
		 * $value now contains the file url in WordPress
		 * $id is the attachment id
		 */


		return $id;
	}

	/**
	 * @param \TimberPost $post
	 *
	 * @return mixed|null
	 */
	public static function getPostWidgets( \TimberPost $post ) {
		return get_field( 'widgets', $post->id );
	}

	/**
	 * Get widgets from a post. If provided a widget name, only these are returned
	 * If an index is provided only return the widget at that index
	 *
	 * @param \TimberPost $post
	 * @param null $name
	 * @param null $index
	 *
	 * @return array|mixed|null
	 */
	public static function getPostWidgetsFiltered( \TimberPost $post, $name = null, $index = null ) {
		$widgets = self::getPostWidgets( $post );
		if ( $name ) {
			$filteredWidgets = [];
			foreach ( $widgets as $key => $widget ) {
				if ( $widget['acf_fc_layout'] === $name ) {
					$filteredWidgets[] = $widget;
				}
			}
			$widgets = $filteredWidgets;
		}

		if ( $index !== null ) {
			if ( isset( $widgets[ $index ] ) ) {
				return $widgets[ $index ];
			}

			return null;
		}

		return $widgets;
	}

	/**
	 * Given a URL to an post, identify the widgets within HTML
	 * and then build up an array of widget objects
	 *
	 * @param $postDom simple_html_dom_node
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getWidgetsFromDom( $postDom ) {

		if ( ! $postDom ) {
			throw new \Exception( 'Could not retrieve widgets from ' . $postDom );
		}

		$widgets = array();

		foreach ( $postDom->find( '.article__content .widget__wrapper' ) as $widgetWrapper ) {
			$widgetData = [];
			// Handle most core widgets that have the .widget class
			if ( isset( $widgetWrapper->find( '.widget' )[0] ) ) {
				$widget = $widgetWrapper->find( '.widget' )[0];

				// Get class name
				$matches = [];
				preg_match( '/widget--([a-z-0-9]*)/', $widget->class, $matches );
				if ( count( $matches ) !== 2 ) {
					throw new \Exception( 'Expected to retrieve widget name from class name' );
				}

				$widgetData = null;
				$widgetName = $matches[1];

				switch ( $widgetName ) {
					case 'html':
						$widgetData = Html::getFromWidgetDom( $widget );
						break;
					case 'inline-image':
						$widgetData = InlineImage::getFromWidgetDom( $widget );
						break;
					case 'image-promo':
						$widgetData = InlineImage::getFromWidgetDom( $widget );
						break;
					case 'video':
						$widgetData = Video::getFromWidgetDom( $widget );
						break;
				}

				// Catch <hr>
			} else if ( isset( $widgetWrapper->find( 'hr' )[0] ) ) {
				$widget     = $widgetWrapper->find( 'hr' )[0];
				$widgetData = HorizontalRule::getFromWidgetDom( $widget );

				// Catch .js-in-page-gallery
			} else if ( isset( $widgetWrapper->find( '.js-in-page-gallery' )[0] ) ) {

				$widgetData       = new \stdClass();
				$widgetData->type = 'gallery';
				$widgetData->html = $widgetWrapper->find( '.js-in-page-gallery' )[0];

			}

			if ( is_array( $widgetData ) ) {
				foreach ( $widgetData as $widget ) {
					$widgets[] = self::makeWidget( $widget->type, $widget );
				}
			} elseif ( $widgetData ) {
				$widgets[] = self::makeWidget( $widgetData->type, $widgetData );
			}

		}

		return $widgets;
	}

	/**
	 * A small helper for setting post metadata
	 *
	 * @param \TimberPost $post
	 * @param $acfKey
	 * @param $widgetProperty
	 * @param $value
	 */
	protected static function setPostMetaProperty( \TimberPost $post, $acfKey, $widgetProperty, $value ) {
		update_sub_field( $acfKey, $value, $post->id );
	}
}
