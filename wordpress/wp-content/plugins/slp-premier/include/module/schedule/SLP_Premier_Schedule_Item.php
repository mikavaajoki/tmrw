<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Premier_Schedule_Item
 *
 * An item for Premier Schedule Manager.
 *
 * NOTE: Remember only private properties are run through the __get(), __set(), and __isset() magic methods.
 *
 * @property        array           $callback        The function to call.
 * @property        string          $slug            The item's slug = WP Cron hook.
 * @property        string          $interval        How often to run it (default: never).
 * @property        string          $next_event      The timestamp for the next time this thing is to run.
 * @property        string          $next_event_text Special nice text format for the next_event timestamp.
 */
class SLP_Premier_Schedule_Item extends SLPlus_BaseClass_Object {
    public $slug;
    public $interval = 'never';
    private $next_event;
    public $callback;
    protected $uses_slplus = false;

    /**
     * Get the value, running it through a filter.
     *
     * @param string $property
     *
     * @return mixed     null if not set or the value
     */
    function __get( $property ) {

        // Standard Properties
	    //
	    if ( property_exists( $this , $property ) ) {

	        if ( ( $property === 'next_event' ) && empty( $this->next_event) ) {
	            $this->next_event = wp_next_scheduled( $this->slug );
		    }
		    return $this->$property;

	    // On the fly formatting.
		//
	    } else {
	        switch ( $property ) {
			    case 'next_event_text':
			        return $this->format_time( $this->__get( 'next_event') );
			    case 'next_event_time_to_text':
				    return $this->time_to_next_event( $this->__get( 'next_event') );
		    }
	    }

	    return null;
    }

    /**
     * Allow isset to be called on private properties.
     *
     * @param $property
     *
     * @return bool
     */
    public function __isset( $property ) {
        $this->__get($property);
	    return isset( $this->$property );
    }

    /**
     * Allow value to be set directly.
     *
     * @param $property
     *
     * @param $value
     * @return SLP_Option
     */
    public function __set( $property, $value ) {
	    if ( property_exists( $this, $property ) ) {
		    switch ( $property ) {
			    case 'next_event':
				    $this->$property = $value;
				    break;
		    }
		    return $this;
	    }
    }

    /**
     * Return nice text for timestamps.
     *
     * @param $timestamp
     *
     * @return string
     */
    private function format_time( $timestamp ) {
	    if ( empty( $timestamp) ) {
		    return __( 'never' , 'slp-premier' );
	    }
	    return date("d F Y H:i:s",$timestamp);
    }

    /**
     * Return nice text for amount of time until the next event occurs.
     *
     * @param $timestamp
     *
     * @return string
     */
    private function time_to_next_event( $timestamp ) {
	    if ( empty( $timestamp) ) {
		    return '';
	    }
        $next_event_date = new DateTime();
        $next_event_date->setTimestamp( $timestamp );
        $now = new DateTime();
        return $now->diff( $next_event_date )->format( '%hh %im %ss' );
    }
}