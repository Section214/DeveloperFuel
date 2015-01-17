<?php
/**
 * Yelp API handler
 *
 * @package     DeveloperFuel\Yelp
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accesed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! defined( 'YelpHandler' ) ) {


    /**
     * Main YelpHandler class
     *
     * @since       1.0.0
     */
    class YelpHandler {
        

        /**
         * @access      private
         * @since       1.0.0
         * @var         string $api The API host URL
         */
        private $api = 'http://api.yelp.com/v2/search/';


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $consumer_key The consumer key
         */
        private $consumer_key;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $consumer_secret The consumer secret
         */
        private $consumer_secret;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $token The API token
         */
        private $token;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $token_secret The API token secret
         */
        private $token_secret;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $category The search category
         */
        private $category;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $coffee_time The 'coffee time'
         */
        private $coffee_time;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $beer_time The 'beer time'
         */
        private $beer_time;


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $quality The quality of coffee shops to return
         */
        private $quality = 'all';


        /**
         * @access      private
         * @since       1.0.0
         * @var         string $loc The user's location
         */
        private $loc;


        /**
         * @access      private
         * @since       1.0.0
         * @var         int $limit The number of places to return
         */
        private $limit = 3;


        /**
         * Get things going
         *
         * @access      public
         * @since       1.0.0
         * @param       array $options Options for the class
         * @return      void
         */
        public function __construct( $options = array() ) {
            $this->consumer_key     = $options['consumer_key'];
            $this->consumer_secret  = $options['consumer_secret'];
            $this->token            = $options['token'];
            $this->token_secret     = $options['token_secret'];
            $this->coffee_time      = $options['coffee_time'];
            $this->beer_time        = $options['beer_time'];
            $this->quality          = $options['quality'];
            $this->category         = $this->maybe_coffee_time();
            $this->limit            = $options['count'];
        }


        /**
         * Perform an API query
         *
         * @access      public
         * @since       1.0.0
         * @return      object $return The JSON respons from the query
         */
        public function fetch() {
            require_once DEVELOPERFUEL_DIR . 'includes/libraries/OAuth.php';
            
            // Build our token object
            $token = new OAuthToken( $this->token, $this->token_secret );

            // Build our consumer object
            $consumer = new OAuthConsumer( $this->consumer_key, $this->consumer_secret );

            $localdata = $this->get_rough_location();
            
            if( is_wp_error( $localdata ) ) {
                $return = array(
                    'error' => array(
                        'text' => __( 'An unknown error occurred.', 'developerfuel' )
                    )
                );
            } else {
                // Build our query URL
                $url = $this->api . '?term=' . $this->category . '&ll=' . $localdata['lat'] . ',' . $localdata['lon'] . '&limit=' . $this->limit . '&sort=1';
                
                $method = new OAuthSignatureMethod_HMAC_SHA1();

                // Build our request
                $request = OAuthRequest::from_consumer_and_token(
                    $consumer,
                    $token,
                    'GET',
                    $url
                );

                // Sign the request
                $request->sign_request( $method, $consumer, $token );

                // Get the signed URL
                $signed_url = $request->to_url();

                // GO!
                $return = wp_remote_retrieve_body( wp_remote_get( $signed_url, array( 'timeout' => 5 ) ) );

                if( is_wp_error( $return ) ) {
                    $return = array(
                        'error' => array(
                            'text' => __( 'An unknown error occurred', 'developerfuel' )
                        )
                    );
                } else {
                    $return = json_decode( $return, true );
                }
            }

            return $return;
        }


        /**
         * Get the rough location for the user
         *
         * @access      public
         * @since       1.0.0
         * @param       bool $rougher False to return full array, true to return city/state
         * @return      mixed array|string $location The location of the user
         */
        public function get_rough_location( $rougher = false ) {
            $localdata = wp_remote_retrieve_body( wp_remote_get( 'http://ip-api.com/json', array( 'timeout' => 5 ) ) );

            if( is_wp_error( $localdata ) ) {
                $location = false;
            } else {
                $localdata = json_decode( $localdata, true );

                if( $rougher ) {
                    $location = $localdata['city'] . ', ' . $localdata['region'];
                } else {
                    $location = $localdata;
                }
            }

            return $location;
        }


        /**
         * Determine whether it is 'coffee time' or 'beer time'
         *
         * @access      public
         * @since       1.0.0
         * @return      string $category The search category for the given time
         */
        public function maybe_coffee_time() {
            $coffee_time    = date( 'Hi', strtotime( $this->coffee_time ) );
            $beer_time      = date( 'Hi', strtotime( $this->beer_time ) );
            $current_time   = date( 'Hi', current_time( 'timestamp', 0 ) );

            if( $coffee_time < $beer_time ) {
                if( $current_time >= $coffee_time && $current_time < $beer_time ) {
                    if( $this->quality == 'all' ) {
                        $category = 'coffee+shop';
                    } else {
                        $category = 'coffeeshop';
                    }
                } else {
                    $category = 'bar';
                }
            } else {
                if( $current_time >= $beer_time && $current_time < $coffee_time ) {
                    $category = 'bar';
                } else {
                    if( $this->quality == 'all' ) {
                        $category = 'coffee+shop';
                    } else {
                        $category = 'coffeeshop';
                    }
                }
            }
            
            return $category;
        }


        /**
         * Get the descriptive title for the current business category
         *
         * @access      public
         * @since       1.0.0
         * @return      string $title The title for the current business category
         */
        public function get_category_title() {
            if( $this->category == 'bar' ) {
                $title = __( 'bars', 'developerfuel' );
            } else {
                $title = __( 'coffee shops', 'developerfuel' );
            }

            return $title;
        }
    }
}
