<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Template_Vue
 *
 * Base class for fetching Vue components.
 *
 * @used-by \SLP_Power_Admin_Locations::modify_add_form
 */
class SLP_Template_Vue extends SLP_Template {
	protected $src_dir = 'components';
	protected $ext = '.vue';
}