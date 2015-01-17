<?php
/**
 * Plugin Name:     Developer Fuel
 * Plugin URI:      http://section214.com
 * Description:     Power up!
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     developerfuel
 *
 * @package         DeveloperFuel
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'DeveloperFuel' ) ) {


    /**
     * Main DeveloperFuel class
     *
     * @since       1.0.0
     */
    class DeveloperFuel {


        /**
         * @access      private
         * @since       1.0.0
         * @var         DeveloperFuel $instance The one true DeveloperFuel
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true DeveloperFuel
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new DeveloperFuel();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin path
            define( 'DEVELOPERFUEL_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'DEVELOPERFUEL_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include required files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            if( is_admin() ) {
                require_once DEVELOPERFUEL_DIR . 'includes/scripts.php';
                require_once DEVELOPERFUEL_DIR . 'includes/functions.php';
                require_once DEVELOPERFUEL_DIR . 'includes/dashboard-widgets.php';
                require_once DEVELOPERFUEL_DIR . 'includes/class.yelp.php';
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directly
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'developerfuel_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'developerfuel', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/developerfuel/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/developerfuel/ folder
                load_textdomain( 'developerfuel', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/developerfuel/languages/ folder
                load_textdomain( 'developerfuel', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'developerfuel', false, $lang_dir );
            }
        }
    }
}


/**
 * The main function responsible for returning the one true DeveloperFuel
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      DeveloperFuel The one true DeveloperFuel
 */
function developerfuel() {
    return DeveloperFuel::instance();
}
add_action( 'plugins_loaded', 'developerfuel' );
