<?php

namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Fetch;
use AgreableCatfishImporterPlugin\Services\Post;
use Croissant\DI\Interfaces\CatfishLogger;

/**
 * Class Api
 * Lookout this need to be run
 * @package AgreableCatfishImporterPlugin
 */
class Api {
	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	/**
	 * Api constructor.
	 *
	 * @param CatfishLogger $logger
	 */
	public function __construct( CatfishLogger $logger ) {
		$this->_logger = $logger;
	}

	/**
	 * @return array array of urls
	 */
	public function getSitemaps() {
		$sitemap = Fetch::xml( getenv( 'CATFISH_IMPORTER_TARGET_URL' ) . 'sitemap-index.xml' );
		$urls = [];

		foreach ( $sitemap as $loc ) {
			$urls[] = (string) $loc->loc;
		}

		return $urls;
	}

	/**
	 * @param $url
	 *
	 * @return array associative array $url=>$timestamp
	 */
	public function getPostsFromSitemap( $url, $date_limit = null ) {
		$timezone = date_default_timezone_get();
		date_default_timezone_set( 'Europe/Dublin' );

		$sitemap = Fetch::xml( $url );

		$urls = [];
		foreach ($sitemap as $loc) {
			if ( ! empty( $date_limit ) ) {
				$last_mod = new \DateTime( (string) $loc->lastmod );

				if ($last_mod < $date_limit) {
					continue;
				}
			}

			$urls[(string) $loc->loc] = (string) $loc->lastmod;
		}

		date_default_timezone_set( $timezone );

		return $urls;
	}

	/**
	 * @param $postUrl
	 *
	 * @return \TimberPost
	 */
	public function importPost( $postUrl ) {
		return Post::getPostFromUrl( $postUrl, 'update' );
	}

	/**
	 * @return array
	 */
	public function getAllPosts( $date_limit ) {
		$this->_logger->info( "Fetching sitemaps" );
		$links = $this->getSitemaps();

		$all_posts = [];
		$this->_logger->info( "Success while downloading sitemaps" );
		foreach ( $links as $link ) {
			try {
				$posts     = $this->getPostsFromSitemap( $link, $date_limit );
				$all_posts = array_merge( $all_posts, $posts );
				$this->_logger->info( "Success $link" );
			} catch ( \Exception $e ) {
				$this->_logger->error( "Error while processing sitemap $link", [ (string) $e ] );
			}
		}

		return array_keys($all_posts);
	}
}
