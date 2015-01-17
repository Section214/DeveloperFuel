<?php
/**
 * Dashboard widgets
 *
 * @package     DeveloperFuel\Dashboard\Widgets
 * @since       1.0.0
 * @author      Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add our new dashboard widget
 *
 * @since       1.0.0
 * @return      void
 */
function developerfuel_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'developerfuel',
        __( 'Developer Fuel', 'developerfuel' ),
        'developerfuel_render_dashboard_widget',
        'developerfuel_configure_dashboard_widget'
    );
}
add_action( 'wp_dashboard_setup', 'developerfuel_add_dashboard_widget' );


/**
 * Render our dashboard widget
 *
 * @since       1.0.0
 * @return      void
 */
function developerfuel_render_dashboard_widget() {
    $options = developerfuel_get_options();

    if( ! $options || developerfuel_has_missing_options() ) {
        echo '<div class="developerfuel-unconfigured dashicons dashicons-flag"></div>';
        printf( __( 'Developer Fuel must be configured to work properly. Please <a href="%s">click here</a> to configure it now!', 'developerfuel' ), add_query_arg( array( 'edit' => 'developerfuel' ) ) . '#developerfuel' );
    } else {
        $yelp       = new YelpHandler( $options );
        $localdata  = $yelp->fetch();
        
        if( count( $localdata['businesses'] ) == 0 ) {
            echo '<div class="developerfuel-unconfigured dashicons dashicons-flag"></div>';
            printf( __( 'Unfortunately, we were unable to find any %s in your area.', 'developerfuel' ), $yelp->get_category_title() );
        } else {
            foreach( $localdata['businesses'] as $business ) {
                echo '<div class="developerfuel-business">';
                echo '<h4><a href="' . $business['url'] . '" target="_blank">' . $business['name'] . '</a></h4>';
                echo '<div class="developerfuel-rating"><img src="' . $business['rating_img_url'] . '" title="' . sprintf( __( '%s of 5 stars', 'developerfuel' ), $business['rating'] ) . '" /></div>';
                if( ! empty( $business['location']['display_address'][0] ) && ! empty( $business['location']['display_address'][1] ) ) {
                    echo '<div class="developerfuel-address"><strong>' . __( 'Address:', 'developerfuel' ) . ' </strong>' . $business['location']['display_address'][0] . ', ' . $business['location']['display_address'][1] . '</div>';
                }
                if( ! empty( $business['display_phone'] ) ) {
                    echo '<div class="developerfuel-address"><strong>' . __( 'Phone:', 'developerfuel' ) . ' </strong>' . $business['display_phone'] . '</div>';
                }
                if( ! empty( $business['snippet_text'] ) ) {
                    echo '<div class="developerfuel-review">&ldquo;' . $business['snippet_text'] . '&rdquo;</div>';
                }
                echo '</div>';
            }

            $more_url = 'http://www.yelp.com/search?find_desc=' . $yelp->maybe_coffee_time() . '&find_loc=' . $yelp->get_rough_location( true );
            echo '<div class="developerfuel-more">' . sprintf( __( 'Want more options? <a href="%1$s" target="_blank">Click here</a> to see more local %2$s!' ), $more_url, $yelp->get_category_title() ) . '</div>';
        }
    }
}


/**
 * Configure our dashboard widget
 *
 * @since       1.0.0
 * @return      void
 */
function developerfuel_configure_dashboard_widget() {
    // Maybe save?
    if( isset( $_POST['developerfuel_save'] ) ) {
        developerfuel_update_options( 'developerfuel', $_POST['developerfuel'] );
    }

    // Get our options
    $options    = developerfuel_get_options();
    $defaults   = apply_filters( 'developerfuel_fields_defaults', array(
        'consumer_key'      => '',
        'consumer_secret'   => '',
        'token'             => '',
        'token_secret'      => '',
        'coffee_time'       => '',
        'beer_time'         => '',
        'count'             => 3,
        'quality'           => 'all'
    ) );

    $options = wp_parse_args( $options, $defaults );

    echo '<p class="description">' . sprintf( __( 'Please enter your Yelp API credentials below. If you don\'t have them yet, they can be generated <a href="%s" target="_blank">here</a>.', 'developerfuel' ), 'http://www.yelp.com/developers/manage_api_keys' ) . '</p><br />';

    echo '<p>';
    echo '<label for="consumer_key"><strong>' . __( 'Consumer Key', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="widefat" name="developerfuel[consumer_key]" id="consumer_key" value="' . $options['consumer_key'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="consumer_secret"><strong>' . __( 'Consumer Secret', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="widefat" name="developerfuel[consumer_secret]" id="consumer_secret" value="' . $options['consumer_secret'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="token"><strong>' . __( 'Token', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="widefat" name="developerfuel[token]" id="token" value="' . $options['token'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="token_secret"><strong>' . __( 'Token Secret', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="widefat" name="developerfuel[token_secret]" id="token_secret" value="' . $options['token_secret'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="coffee_time"><strong>' . __( 'Coffee Time', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="developerfuel-time" name="developerfuel[coffee_time]" id="coffee_time" value="' . $options['coffee_time'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="quality"><strong>' . __( 'Coffee Shop Quality', 'developerfuel' ) . '</strong></label><br />';
    echo '<select name="developerfuel[quality]" id="quality">';
    echo '<option value="all"' . selected( 'all', $options['quality'], false ) . '">' . __( 'All Coffee Shops', 'developerfuel' ) . '</option>';
    echo '<option value="other"' . selected( 'other', $options['quality'], false ) . '">' . __( 'Non-Chain Coffee Shops', 'developerfuel' ) . '</option>';
    echo '</select>';
    echo '</p>';

    echo '<p>';
    echo '<label for="beer_time"><strong>' . __( 'Beer Time', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="text" class="developerfuel-time" name="developerfuel[beer_time]" id="beer_time" value="' . $options['beer_time'] . '" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="count"><strong>' . __( 'Number to Display', 'developerfuel' ) . '</strong></label><br />';
    echo '<input type="number" class="small-text" min="1" max="10" name="developerfuel[count]" id="count" value="' . $options['count'] . '" />';
    echo '</p>';

    do_action( 'developerfuel_fields' );

    echo '<input type="hidden" name="developerfuel_save" id="developerfuel_save" value="true" />';
    
    echo '<hr /><br />';
}
