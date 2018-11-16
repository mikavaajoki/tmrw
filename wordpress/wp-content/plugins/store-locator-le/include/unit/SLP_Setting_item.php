<?php
if ( ! class_exists( 'SLP_Setting_item' ) ) {
	/**
	 * Class SLP_Setting_item
	 *
	 * The things that go into SLP_Settings when they contain children elements. 
	 * The "vision list" for example, uses this for the child "boxes" attributes for the styles.
	 */
	class SLP_Setting_item extends SLPlus_BaseClass_Object {
		public $clean_title;
		public $classes;
		public $title;
		public $data;
		public $description;
		public $has_actions = true;
		public $uses_slplus = false;

		/**
		 * Things we do at the start.
		 */
		protected function initialize() {
			$this->clean_title = sanitize_title( $this->title );
			$this->data['slug'] = $this->clean_title;
		}
	}
}