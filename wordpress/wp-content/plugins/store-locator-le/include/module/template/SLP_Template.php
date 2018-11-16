<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Template
 *
 * @used-by \SLP_Template_Vue
 */
class SLP_Template extends SLP_Base_Object {
	public   $plugin_dir = SLPLUS_PLUGINDIR;
	protected $fq_file;
	protected $fq_files = array();
	protected $src_dir = '';
	protected $ext = '';

	/**
	 * Display the contents.
	 *
	 * @param string $file
	 */
	public function display( $file ) {
		if ( $this->valid_file( $file ) ) {
			/** @noinspection PhpIncludeInspection */
			include( $this->fq_file );
		}
	}

	/**
	 * Return the contents.
	 *
	 * @param string $file
	 *
	 * @return bool|string
	 */
	public function get_content( $file ) {
		if ( $this->valid_file( $file ) ) {
			/** @noinspection PhpIncludeInspection */
			return file_get_contents( $this->fq_file );
		}
		return '';
	}

	/**
	 * The the fully qualified filename.
	 *
	 * @param string $file
	 */
	private function set_fq_file( $file ) {
		$file_key = sanitize_key( $file );
		if ( ! isset( $this->fq_files[ $file_key ] ) ) {
			$this->fq_files[ $file_key ] = $this->plugin_dir . 'src/' . $this->src_dir . '/'. $file . $this->ext;
		}
		$this->fq_file = $this->fq_files[ $file_key ];
	}

	/**
	 * Check if file is valid (exists and is readable).
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	private function valid_file( $file ) {
		if ( empty( $this->src_dir ) ) return false;
		$this->set_fq_file( $file );
		return  is_readable( $this->fq_file );
	}

}