<?php
defined( 'ABSPATH' ) || exit;
class SLP_Power_Text_Pages_Tab extends SLPlus_BaseClass_Object {
	public function initialize() {
		/** @var  SLP_Text $the */
		$the = SLP_Text::get_instance();

		$the->text_strings[ 'label'       ][ 'pages_directory_entry_css_class' ] = 'Page List Item Class';
		$the->text_strings[ 'description' ][ 'pages_directory_entry_css_class' ] = 'CSS class used with individual page listing entries.';

		$the->text_strings[ 'label'       ][ 'pages_directory_wrapper_css_class' ] = 'Page List Wrapper Class';
		$the->text_strings[ 'description' ][ 'pages_directory_wrapper_css_class' ] = 'CSS class used with the div that wraps individual page listing entries.';

	}
}