<?php
defined( 'ABSPATH' ) || exit;
class SLP_Settings_import_stats extends SLP_Setting {
	private $text;

	/**
	 * Things we do at the start.
	 */
	protected final function at_startup() {
		$text_slugs = array(
            'auto_refresh'        ,
			'download'            ,
			'geocoding'           ,
			'geocoding_location'  ,
			'geocode_after_import',
			'geocode_in_progress' ,
			'imported'            ,
			'importing'           ,
			'imports'             ,
			'import_in_progress'  ,
			'no_active_geocoding' ,
			'no_active_imports'   ,
			'processing'          ,
			'reading_line'        ,
		);
		$text = SLP_Text::get_instance();
		foreach ( $text_slugs as $slug ) {
			$this->text[ $slug ] = $text->get_text_string( $slug );
		}
	}

	/**
	 * Return a card HTML string for a file.
	 *
	 * @param int $id
	 * @param array $meta
	 *
	 * @return string
	 */
	private function file_card( $id , $meta ) {
		$meta = stripslashes_deep( $meta );
		$meta[ 'card_class' ] = ! empty( $meta[ 'card_class' ] ) ? $meta[ 'card_class' ] : '';

		$media_link    = sprintf( '<a href="%s%s" target="store_locator_plus" class="header_link">%s</a>' , admin_url( 'upload.php?item=' ) , $id, $meta['original_name'] );
		$progress      = sprintf( "%.2f" , ($meta[ 'offset' ]/$meta[ 'size' ])*100 );
		$progress_text = $progress . '%';

		return <<<HTML
			<div data-attachment_id="{$id}" class="import_card {$meta['card_class']} large-auto cell">
				<div class="card">
					<div class="card-divider text-center">
						<h3>{$this->text['importing']} {$media_link}</h3>
					</div>
					<div class="card-section">					 
                        <div class="progress" role="progressbar" tabindex="0" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuetext="{$progress_text}" aria-valuemax="100">
                          <span class="progress-meter" style="width: {$progress_text}">
                            <p class="progress-meter-text">{$progress_text}</p>
                          </span>
                        </div>               
                        <div class="grid-x">
                            <div class="small-6 cell text-center"> 
                                {$this->text['geocode_after_import']}
                            </div>
                            <div class="small-3 cell text-center"> 
                                <h4 class="subheader text-center">{$this->text['reading_line']}</h4>
                                <div class="current_record stat text-center">{$meta['record']}</div>
                            </div>
                            <div class="small-3 cell text-center"> 
                                <h4 class="subheader text-center">{$this->text['download']}</h4>
                                <a href="{$meta['url']}" title="{$this->text['download']}"><span  class="dashicons dashicons-download stat"></span></a>
                            </div>
                        </div>
					</div>
				</div>
			</div>
HTML;
	}

	/**
	 * Get file cards.
	$file_cards = array(
	'2949' => array(
	'data_type' => 'location_csv',
	'processed' => false,
	'record' => 190,
	'offset' => 37068,
	'original_name' => 'ben_test_geocode_680.csv',
	'size' => 86016,
	'url' => "http:\/\/wpslp.test\/wp-content\/uploads\/2017\/11\/bennett_locations_2017_oct_31-1-13.csv",
	'filename' => "\/2017\/11\/bennett_locations_2017_oct_31-1-13.csv"
	'local_file' => "\/srv\/www\/wpslp\/public_html\/wp-content\/uploads\/2017\/11\/bennett_locations_2017_oct_31-1-13.csv",
	),
	);
	 *
	 */
	private function get_file_cards() {
		/**
		 * @var SLP_Power_Locations_Import $obj
		 */
		$obj = SLP_Power_Locations_Import::get_instance();
		$list = $obj->get_active_list();
		$file_cards = $list['data'];

/*
 * A test card...
$file_cards[ '9999' ][ 'meta' ] = array(
			'data_type' => 'location_csv',
			'processed' => false,
			'record' => 1237,
			'offset' => 80018,
			'original_name' => 'ben_test_geocode_680.csv',
			'size' => 86016,
			'url' => "http:\/\/wpslp.test\/wp-content\/uploads\/2017\/11\/bennett_locations_2017_oct_31-1-13.csv",
			'filename' => "\/2017\/11\/bennett_locations_2017_oct_31-1-13.csv"
        );
*/

        // The hidden empty card
		$file_cards[ '0' ][ 'meta' ] = array(
		    'card_class' => 'hidden',
			'data_type' => 'location_csv',
			'processed' => false,
			'record' => 0,
			'offset' => 0,
			'original_name' => 'new import',
			'size' => 100,
			'url' => '.',
			'filename' => ''
		);

		// Build a status card for each active import.
		//
        $cards = array();
        foreach ( $file_cards as $id => $data ) {

            // Empty meta size, file likely was deleted before finished processing.
            //
	        if ( empty( $data[ 'meta' ][ 'size' ] ) || ! $data[ 'meta' ][ 'url' ] ) {
	            $importer = SLP_Power_Locations_Import::get_instance();
	            $importer->stop_processing( $id );
		        continue;
	        }

            $cards[] = $this->file_card( $id, $data['meta'] );
        }

        return join( '', $cards );
	}

	/**
	 * Get geocode card
	 * @return string
	 */
	private function get_geocode_card() {
	    global $slplus;
		$obj = SLP_Power_Locations_Geocode::get_instance();
		$list = $obj->get_active_list();

		$progress_bars = '';
		$display_class = empty( $list['data']['jobs'] ) ? 'hidden' : '';

		$list['data']['jobs'][] = array( 'max' => 0 , 'start_uncoded' => 0 );

		// Build a status card for each active import.
		//
        $description = $this->text['geocode_in_progress'];
        $cards = array();
        foreach ( $list['data']['jobs'] as $job ) {
            $pct_complete      = empty( $job['max'] ) ? '0' : sprintf( "%.2f" , (($job['start_uncoded'] - $list['data']['current_uncoded'])/$job['start_uncoded'])*100 );
            $bar_display = empty( $pct_complete ) ? 'hidden' : '';
            $progress_bars .= <<<PROGRESS_BAR
                <div id="geocode_{$job['max']}" class="progress {$bar_display}" role="progressbar" tabindex="0" aria-valuenow="{$pct_complete}" aria-valuemin="0" aria-valuetext="{$pct_complete} %" aria-valuemax="100">
                  <span class="progress-meter" style="width: {$pct_complete}%">
                    <p class="progress-meter-text">{$pct_complete}%</p>
                  </span>
                </div>
PROGRESS_BAR;
        }

		return <<<HTML
			<div class="geocode_card {$display_class} large-auto cell">
				<div class="card">
					<div class="card-divider">
						<h3>{$this->text['geocoding']}</h3>
						<span title="{$this->text['auto_refresh']}" class="reload_icon dashicons dashicons-image-rotate"></span>
					</div>
					<div class="card-section">
				        {$progress_bars}
                        <div class="grid-x">
                            <div class="small-6 cell text-center"> 
                                <h4 class="subheader text-center">{$this->text['geocoding_location']}</h4>
                                <div class="current_record stat text-center">{$list['data']['current_location']}</div>
                            </div>
                            <div class="small-6 cell text-center"> 
                                {$this->text['geocode_in_progress']}
                            </div>
                        </div>
					</div>
				</div>
			</div>
HTML;
	}

	/**
	 * Just the content - no standard Settings wrappers here.
	 */
	protected function wrap_in_default_html() {
		?>
		<div id="slp_import_stats" class="grid-x grid-margin-x">
			<?= $this->get_file_cards() ?>
			<?= $this->get_geocode_card() ?>
		</div>
		<?php
	}

}