<?php

if ( ! class_exists('SLP_Web_Link') ) {

	/**
	 * Class SLP_Web_Link
	 *
	 * @package StoreLocatorPlus\Web_Link
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2016 Charleston Software Associates, LLC
	 *
	 * @property string $slug 				the slug, a sanitized key version of name
	 *
	 * @since 4.6.2
	 *
	 * @var     string      anchor_tag          The formatted HTML
	 * @var     string      link_text           The text that shows as the link. (default to url).
	 * @var     string      sentence            A text string with a hyperlink in it.
	 * @var     string      sentence_with_link  The formatted HTML
	 * @var     string      slug                The slug we refer to this object by.
	 * @var     string      title               The tool tip title (default to link text).
	 * @var     string      url                 The URL to link to.
	 */
	class SLP_Web_Link extends SLPlus_BaseClass_Object {
		private $anchor_tag;
		public $link_text;
		public $sentence;
		private $sentence_with_link;
		public $slug;
		public $title;
		public $url;

		/**
		 * SLP_Web_Link constructor, turn off uses_slplus.
		 *
		 * @param array $options
		 * @return string
		 */
		function __construct( $options = array() ) {
			$this->uses_slplus = false;

			parent::__construct( $options );

			if ( ! isset( $this->link_text ) ) {
				$this->link_text = $this->url;
			}

			if ( ! isset( $this->title ) ) {
				$this->title = $this->link_text;
			}
		}

		/**
		 * @return string
		 */
		private function create_hyperlink() {
			if ( ! isset( $this->anchor_tag ) ) {
				if ( empty( $this->title ) ) {
					return '';
				}
				$this->anchor_tag = sprintf( '<a href="%s" target="slp" title="%s" class="%s" target="store_locator_plus">%s</a>', $this->url, $this->title, $this->slug, $this->link_text );
			}
			return $this->anchor_tag;
		}

		/**
		 * Output the object as an HTML string.
		 *
		 * @return string
		 */
		function __toString() {
			if ( empty( $this->link_text ) && empty( $this->title ) ) {
				return '';
			}
			if ( ! isset( $this->sentence_with_link ) ) {
				$this->sentence_with_link = sprintf( $this->sentence , $this->create_hyperlink() );
			}

			return $this->sentence_with_link;
		}
	}

}