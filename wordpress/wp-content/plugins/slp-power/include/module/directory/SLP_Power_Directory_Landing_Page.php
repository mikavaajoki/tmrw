<?php
defined( 'ABSPATH' ) || exit;

/**
 * The slp_directory shortcode landing page processor.
 *
 * [slp_directory style="landing_page"]
 *
 * @property-read   string      $field      The field we are filtering on.
 * @property-read   string      $filter_by  The shorthand name of the field we are filtering on.
 * @property        string      $html       The landing page HTML.
 * @property-read   string      $value      The value we are filtering on.
 */
class SLP_Power_Directory_Landing_Page  extends SLPlus_BaseClass_Object {
    private $field;
    private $filter_by;
    public $html;
    private $value;

    /**
     * Thing we do at the start.
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'Power' );
        add_filter( 'slp_ajaxsql_where' , array( $this , 'set_WhereClause' ) );
        $this->slplus->UI->setup_stylesheet_for_slplus();
    }

    /**
     * Generate the landing page HTML from the SLP results layout.
     */
    public function set_html_FromResultsLayout() {
        $this->html = '';
	    $power_ui = SLP_Power_UI::get_instance();
	    if ( ! $power_ui->is_a_valid_directory_redirect() ) return;

	    /** @var  SLP_UI_Shortcode_slp_location $slp_location */
	    $slp_location = SLP_UI_Shortcode_slp_location::get_instance();
	    SLP_UI_Shortcode_slp_option::get_instance();

        $results_layout = $this->slplus->UI->set_ResultsLayout( true, true );

        $this->set_FilterProperties();

        // Loop through each matching location
        //
        $offset = 0;
        $location_output = '';
        while ( ( $location = $this->slplus->database->get_Record( array('selectall','where_default') , array( ) , $offset++) ) !== null) {
            $this->slplus->currentLocation->set_PropertiesViaArray($location);
            $location_output .= sprintf( '<div class="results_wrapper" id="slp_results_wrapper_%d">%s</div>' , $this->slplus->currentLocation->ID , do_shortcode( $results_layout ) );
            $slp_location->clear_ajax_response();
        }

        // Wrap the HTML in a div so we can style it easier.
        //
        if ( ! empty( $location_output ) ) {
	        $this->html = "<div class='slp_directory landing_page by_{$power_ui->property_shorthand}' ><div class='slp_results_container'>{$location_output}</div></div>";
        }

    }

    /**
     * Set filter properties, the by, field, and value we are filtering on.
     */
    private function set_FilterProperties() {
	    $power_ui = SLP_Power_UI::get_instance();
        if ( !isset( $power_ui->location_property             ) ) { return; }
        $this->field = $power_ui->location_property;
        $this->filter_by = $power_ui->property_shorthand;

        if ( !isset( $_REQUEST[$this->field]  ) ) { return; }
        $this->value = $_REQUEST[$this->field];
    }

    /**
     * Extend the database where clause to filter by city, state, zip, etc.
     *
     * @param string $where
     * @return string
     */
    public function set_WhereClause( $where ) {
        if ( !isset( $this->field             ) ) { return $where; }
        if ( !isset( $_REQUEST[$this->field]  ) ) { return $where; }

        return
            $this->slplus->database->extend_WhereFieldMatches(
                $where ,
                $this->field ,
                $this->value
            );
    }

}
