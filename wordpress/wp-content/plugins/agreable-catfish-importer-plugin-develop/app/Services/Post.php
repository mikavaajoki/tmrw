<?php

namespace AgreableCatfishImporterPlugin\Services;

use AgreableCatfishImporterPlugin\Services\Widgets\Video;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class Post
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
class Post {

	public static $currentUrl = '';
	/**
	 * $postArrayForWordpress
	 *
	 * Store the post meta as a a constant so that we can access it from an
	 * anonymous function later on.
	 */
	public static $postArrayForWordpress = array();

	/**
	 * Get single post from Clock URL and import into the Pages CMS
	 *
	 * TODO cli output of the full import process
	 */
	/**
	 * @param $postUrl
	 *
	 * @return \TimberPost
	 * @internal param string $onExistAction
	 *
	 */
	public static function getPostFromUrl( $postUrl ) {

		remove_filter( 'save_post', 'yoimg_imgseo_save_post' );

		$postUrl .= '.json';
		// Escape the url path using this handy helper

		$object = Fetch::json( $postUrl, false );


		// Create an empty wordpress post array to build up over the course of the
		// function and to insert or update using wp_insert_post or wp_update_post
		$postArrayForWordpress = array();

		$postObject = $object->article; // The article in object from as retrieved from Clock CMS API
		$postDom    = HtmlDomParser::str_get_html( $object->content ); // A parsed object of the post content to be split into ACF widgets as a later point

		// Check if article exists and handle onExistAction
		$postId = self::getPostsWithSlug( $postObject->slug );


		$displayDate = strtotime( $postObject->displayDate );
		$displayDate = date( 'o\-m\-d G\:i\:s', $displayDate );

		// Set current date in nice format that wordpress likes
		$currentDate = date( 'o\-m\-d G\:i\:s', time() );

		// If no sell exists on this post then create it from the headline
		$sell = empty( $postObject->sell ) ? $postObject->headline : $postObject->sell;

		// Create the base array for the new Wordpress post or merge with existing post if updating


		$postArrayForWordpress = array_merge( [
			'post_name'         => $postObject->slug,
			'post_title'        => $postObject->headline,
			'post_date'         => $displayDate,
			'post_date_gmt'     => $displayDate,
			'post_modified'     => $displayDate,
			'post_modified_gmt' => $displayDate,
			'post_status'       => 'publish',
			'ID'                => $postId
		], $postArrayForWordpress ); // Clock data from api take precedence over local data from Wordpress


		if ( ! isset( $object->article->__author ) ) {
			$object->article->__author = null;
		}
		$postArrayForWordpress['post_author'] = User::findUserFromClockObject( $object->article->__author );


		// If article has a video header, use no-media header and transform to embed widget
		$hero = ( isset( $postObject->videoId ) ) ? "hero-without-media" : "hero";

		// Create meta array for new post (Data that's not in the core post_fields)
		$postACFMetaArrayForWordpress = array(
			'basic_short_headline'                  => $postObject->shortHeadline,
			'basic_sell'                            => $sell,
			'article_header_type'                   => $hero,
			'article_header_display_date'           => true,
			'article_catfish_imported_url'          => preg_replace( '/.json$/', '', $postUrl ),
			'article_catfish_importer_imported'     => true,
			'article_catfish_importer_post_date'    => $displayDate,
			'article_catfish_importer_date_updated' => $currentDate,
			'social_overrides_title'                => self::pickFromArray( $object, 'meta.social.title', "" ),
			'social_overrides_description'          => self::pickFromArray( $object, 'meta.social.description', "" ),
			'social_overrides_share_image'          => false,
			'social_overrides_twitter_text'         => self::pickFromArray( $object, 'meta.social.twitterText', "" ),
			'seo_title'                             => self::pickFromArray( $object, 'meta.title', "" ),
			'seo_description'                       => self::pickFromArray( $object, 'meta.seo.description', "" ),
			'related_show_related_content'          => true,
			'related_limit'                         => "6",
			'related_lists'                         => false,
			'related_posts_manual'                  => false,
			'html_overrides_allow'                  => false
		);


		// Log the created time if this is the first time this post was imported
		if ( $postId === 0 ) {
			$postMetaArrayForWordpress['catfish_importer_date_created'] = $currentDate;

		}


		$wpPostId = WPErrorToException::loud( wp_insert_post( $postArrayForWordpress ) );


		// Save the post meta data (Any field that's not post_)

		self::setACFPostMetadata( $wpPostId, $postACFMetaArrayForWordpress );

		// XXX: Actions to take place __after__ the post is saved and require either the Post ID or TimberPost object


		// Attach Categories to Post
		Category::attachCategories( $object->article->section, $postUrl, $wpPostId );


		// Add tags to post
		$postTags = array();
		foreach ( $object->article->tags as $tag ) {
			if ( $tag->type !== 'System' ) {
				array_push( $postTags, ucwords( $tag->tag ) );
			}
		}

		$ret = wp_set_post_tags( $wpPostId, $postTags );

		update_field( 'basic_tags', $ret, $wpPostId );
		// Catch failure to create TimberPost object
		$post = new \TimberPost( $wpPostId );


		// Create the ACF Widgets from DOM content
		$widgets = Widget::getWidgetsFromDom( $postDom );

		// if there is a video header, convert to embed widget
		if ( $hero == "hero-without-media" ) {
			$headerEmbed = Video::getVideoFromHeader( $postObject->provider, $postObject->videoId );
			array_unshift( $widgets, $headerEmbed );
			/**
			 * @var $_logger \Croissant\DI\Dependency\CatfishLogger
			 */
			$_logger = \Croissant\App::get( \Croissant\DI\Interfaces\CatfishLogger::class );
			$_logger->notice( "provider: " . $postObject->provider . ", video id: " . $postObject->videoId . ", url: " . $postUrl );
		}

		Widget::setPostWidgets( $post, $widgets, $postObject );


		// Store header image
		$show_header = self::setHeroImages( $post, $postDom, $postObject );

		update_field( 'article_header_display_hero_image', $show_header, $wpPostId );
		add_action( 'save_post', 'yoimg_imgseo_save_post' );

		return $post;
	}

	/**
	 *
	 *
	 * @param $array
	 * @param $path
	 * @param null $default
	 *
	 * @return array|null
	 */
	public static function pickFromArray( $array, $path, $default = null ) {
		$delimiter = '.';

		if ( is_array( $path ) ) {
			// The path has already been separated into keys
			$keys = $path;
		} else {
			if ( array_key_exists( $path, $array ) ) {
				// No need to do extra processing
				return $array[ $path ];
			}
			// Remove starting delimiters and spaces
			$path = ltrim( $path, "{$delimiter} " );
			// Remove ending delimiters, spaces, and wildcards
			$path = rtrim( $path, "{$delimiter} *" );
			// Split the keys by delimiter
			$keys = explode( $delimiter, $path );
		}

		do {
			$key = array_shift( $keys );

			if ( ctype_digit( $key ) ) {
				// Make the key an integer
				$key = (int) $key;
			}

			if ( $array instanceof \stdClass ) {
				$array = json_decode( json_encode( $array ), true );
			}

			if ( isset( $array[ $key ] ) ) {
				if ( $keys ) {
					if ( is_array( $array[ $key ] ) ) {
						// Dig down into the next part of the path
						$array = $array[ $key ];
					} else {
						// Unable to dig deeper
						break;
					}
				} else {
					// Found the path requested
					return $array[ $key ];
				}
			} elseif ( $key === '*' ) {
				// Handle wildcards
				$values = array();
				foreach ( $array as $arr ) {
					if ( $value = self::pickFromArray( $arr, implode( '.', $keys ) ) ) {
						$values[] = $value;
					}
				}
				if ( $values ) {
					// Found the values requested
					return $values;
				} else {
					// Unable to dig deeper
					break;
				}
			} else {
				// Unable to dig deeper
				break;
			}
		} while ( $keys );

		// Unable to find the value requested
		return $default;
	}

	/**
	 * Set or update multiple post meta properties at once
	 *
	 * @param $postId
	 * @param $fields
	 */
	protected static function setPostMetadata( $postId, $fields ) {
		foreach ( $fields as $fieldName => $value ) {
			self::setACFPostMetaProperty( $postId, $fieldName, $value );
		}
	}


	/**
	 * ACF Set or update multiple post meta properties at once
	 *
	 * @param $postId
	 * @param $fields
	 */
	protected static function setACFPostMetadata( $postId, $fields ) {
		foreach ( $fields as $fieldName => $value ) {
			self::setACFPostMetaProperty( $postId, $fieldName, $value );
		}
	}

	/**
	 * ACF Create or update a post meta field
	 *
	 * @param $postId
	 * @param $fieldName
	 * @param string $value
	 */
	protected static function setACFPostMetaProperty( $postId, $fieldName, $value = '' ) {
		update_field( $fieldName, $value, $postId );
	}

	/**
	 * @param $authorObject
	 *
	 * @return bool|int|\WP_Error
	 */
	protected static function setAuthor( $authorObject ) {
		$user_id = User::checkUserByEmail( $authorObject->emailAddress );
		if ( $user_id == false ) {
			$user_id = User::insertCatfishUser( $authorObject );
		}

		return $user_id;
	}

	/**
	 * @param $articleObject
	 *
	 * @return string
	 */
	protected static function setArticleType( $articleObject ) {
		if ( isset( $articleObject->analyticsPageTypeDimension ) ) {
			return strtolower( $articleObject->analyticsPageTypeDimension );
		}

		return 'article';
	}

	/**
	 * @param \TimberPost $post
	 * @param $postDom
	 * @param $postObject
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected static function setHeroImages( \TimberPost $post, $postDom, $postObject ) {
		$show_header   = true;
		$heroImagesDom = $postDom->find( '.slideshow__slide img,.gallery-overview__main-image img' );

		$heroImageIds = [];
		foreach ( $heroImagesDom as $index => $heroImageDom ) {
			$heroImage            = new \stdClass();
			$heroImage->src       = $heroImageDom->src;
			$heroImage->filename  = substr( $heroImage->src, strrpos( $heroImage->src, '/' ) + 1 );
			$heroImage->name      = substr( $heroImage->filename, 0, strrpos( $heroImage->filename, '.' ) );
			$heroImage->extension = substr( $heroImage->filename, strrpos( $heroImage->filename, '.' ) + 1 );
			$meshImage            = new \Mesh\Image( $heroImage->src );
			$heroImage->id        = $meshImage->id;
			$heroImageIds[]       = (string) $heroImage->id;
		}
		if ( ! count( $heroImageIds ) ) {
			$show_header = false;
		}

		if ( ( ! count( $heroImageIds ) ) && ( isset( $postObject->images->widgets[0]->imageUrl ) ) ) {
			$url                  = $postObject->images->widgets[0]->imageUrl;
			$heroImage            = new \stdClass();
			$heroImage->src       = $url;
			$heroImage->filename  = substr( $url, strrpos( $url, '/' ) + 1 );
			$heroImage->name      = substr( $heroImage->filename, 0, strrpos( $heroImage->filename, '.' ) );
			$heroImage->extension = substr( $heroImage->filename, strrpos( $heroImage->filename, '.' ) + 1 );
			$meshImage            = new \Mesh\Image( $heroImage->src );
			$heroImage->id        = $meshImage->id;
			$heroImageIds[]       = (string) $heroImage->id;
		}

		if ( array_key_exists( 0, $heroImageIds ) ) {
			update_field( 'basic_hero_images', $heroImageIds, $post->id );
			//update_post_meta( $post->id, '_hero_images', 'article_basic_hero_images' );
			set_post_thumbnail( $post->id, $heroImageIds[0] );
		} else {
			$message = "$post->title has no hero images";
			throw new \Exception( $message );
		}

		return $show_header;
	}

	/**
	 * @param \TimberPost $post
	 *
	 * @return array|null|object|\WP_Error
	 */
	public static function getCategory( \TimberPost $post ) {
		$postCategories = wp_get_post_categories( $post->id );

		return get_category( $postCategories[0] );
	}

	/**
	 * Get and return posts with matching slug
	 *
	 * @param $slug
	 *
	 * @return array
	 */
	public static function getPostsWithSlug( $slug ) {
		global $wpdb;


		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s ", $slug ) );
	}


}
