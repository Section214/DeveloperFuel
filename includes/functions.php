<?php
/**
 * Helper functions
 *
 * @package     DeveloperFuel\Functions
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get the options for our widget
 *
 * @since       1.0.0
 * @return      mixed array $return The options for the widget | false otherwise
 */
function developerfuel_get_options() {
    // Fetch all options
    $options = get_option( 'dashboard_widget_options' );

    if( isset( $options['developerfuel'] ) ) {
        $return = $options['developerfuel'];
    } else {
        $return = false;
    }

    return $return;
}


/**
 * Update our widget options
 *
 * @since       1.0.0
 * @param       string $id The ID of a given widget
 * @param       array $args The options array
 * @return      void
 */
function developerfuel_update_options( $id, $args = array() ) {
    // Fetch all options
    $options    = get_option( 'dashboard_widget_options' );
    $widget     = ( isset( $options['developerfuel'] ) ? $options['developerfuel'] : array() );

    foreach( $args as $key => $value ) {
        $args[$key] == esc_attr( $value );
    }

    // Update the options array
    $options['developerfuel'] = array_merge( $widget, $args );

    update_option( 'dashboard_widget_options', $options );
}


/**
 * Check to see if all our options are configured
 *
 * @since       1.0.0
 * @return      bool True if options are missing, false otherwise
 */
function developerfuel_has_missing_options() {
    $options    = developerfuel_get_options();
    $required   = apply_filters( 'developerfuel_required_options', array(
        'consumer_key',
        'consumer_secret',
        'token',
        'token_secret',
        'coffee_time',
        'beer_time',
        'count',
        'quality'
    ) );

    foreach( $required as $option ) {
        if( ! array_key_exists( $option, $options ) ) {
            return true;
        } else {
            if( $options[$option] == '' ) {
                return true;
            }
        }
    }

    return false;
}
