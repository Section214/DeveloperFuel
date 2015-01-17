<?php
/**
 * Scripts
 *
 * @package     DeveloperFuel\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Load scripts
 *
 * @since       1.0.0
 * @param       string $hook The hook for this page
 * @return      void
 */
function developerfuel_load_scripts( $hook ) {
    // Only load on the dashboard
    if( $hook != 'index.php' ) {
        return;
    }

    $ui_style   = ( get_user_option( 'admin_color' ) == 'classic' ) ? 'classic' : 'fresh';
    wp_enqueue_style( 'jquery-ui-css', DEVELOPERFUEL_URL . 'assets/css/jquery-ui-' . $ui_style . '.min.css' );

    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'developerfuel-timepicker', DEVELOPERFUEL_URL . 'assets/js/jquery-ui-timepicker-addon.min.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ) );

    wp_enqueue_style( 'developerfuel', DEVELOPERFUEL_URL . 'assets/css/admin.css' );
    wp_enqueue_script( 'developerfuel', DEVELOPERFUEL_URL . 'assets/js/admin.js' );
}
add_action( 'admin_enqueue_scripts', 'developerfuel_load_scripts', 100 );
