<?php
/*
Plugin Name: Draw From Google Analytics
Version: 1.0
Description: Plugin drawing graphs from google analytics data using graph.js
Author: Subair T C
Author URI:
Plugin URI:
Text Domain: draw-from-ga
Domain Path: /languages
*/


/* Set constant path to the plugin directory. */
define( 'DRAW_FROM_GA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/* Set the constant path to the plugin's includes directory. */
define( 'DRAW_FROM_GA_PLUGIN_INC', DRAW_FROM_GA_PLUGIN_PATH . trailingslashit( 'inc' ), true );

/* Set the constant path to the plugin's template directory. */
define( 'DRAW_FROM_GA_PLUGIN_TEMPLATES', DRAW_FROM_GA_PLUGIN_INC . trailingslashit( 'graphs' ), true );



/*
*	Function to Enqueue required scripts and Style.
*/
function add_draw_from_ga_script() {
	wp_register_script( 'draw-from-ga', plugins_url( '/js/draw-from-ga.js', __FILE__ ), true );
	wp_enqueue_script( 'draw-from-ga' );
	
	wp_register_script( 'Chart.min', plugins_url( '/js/Chart.min.js', __FILE__ ), true );
	wp_enqueue_script( 'Chart.min' );
	
	
	wp_localize_script('draw-from-ga', 'Ajax', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	));
	wp_register_style( 'draw-from-ga', plugins_url( '/css/draw-from-ga.css', __FILE__ ) );
	wp_enqueue_style( 'draw-from-ga' );
}

add_action( 'wp_enqueue_scripts', 'add_draw_from_ga_script' );


/*
	Adding admin styles and scripts
*/
function add_draw_from_ga_admin_style() {
	
	wp_register_style( 'draw-from-ga-css', plugins_url( '/css/custom.css', __FILE__ ) );
	wp_enqueue_style( 'draw-from-css' );
	wp_localize_script('custom-js', 'Ajax', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	));
}

add_action( 'admin_enqueue_scripts', 'add_draw_from_ga_admin_style' );





function draw_from_ga_activate() {
	// need to create tbale here
}
register_activation_hook( __FILE__, 'draw_from_ga_activate' );


function dfga_initializeAnalytics() {
	
	require_once DRAW_FROM_GA_PLUGIN_INC . '/vendor/autoload.php';
	// Creates and returns the Analytics Reporting service object.

	$KEY_FILE_LOCATION = DRAW_FROM_GA_PLUGIN_INC . '/service-account-credentials.json';
	if( !file_exists ( $KEY_FILE_LOCATION )) {
		return -1;
	}

	// Create and configure a new client object.
	$client = new Google_Client();
	$client->setApplicationName("Hello Analytics Reporting");
	$client->setAuthConfig($KEY_FILE_LOCATION);
	$client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
	$analytics = new Google_Service_Analytics($client);

	return $analytics;
}



function dfga_getProfileDetails( $analytics ) {
	// Get the user's view (profile) ID.

	// Get the list of accounts for the authorized user.
	$accounts = $analytics->management_accounts->listManagementAccounts();

	$return = array();
	$profile_items = array();

	if (count($accounts->getItems()) > 0) {
		$items = $accounts->getItems();
		$firstAccountId = $items[0]->getId();

		// Get the list of properties for the authorized user.
		$properties = $analytics->management_webproperties
		->listManagementWebproperties($firstAccountId);

		if (count($properties->getItems()) > 0) {
			$items = $properties->getItems();
			$firstPropertyId = $items[0]->getId();

			// Get the list of views (profiles) for the authorized user.
			$profiles = $analytics->management_profiles
			->listManagementProfiles($firstAccountId, $firstPropertyId);


			if (count($profiles->getItems()) > 0) {
				$items = $profiles->getItems();

				foreach( $items as $item ) {
					$profile_items[$item->getId()]	= $item->getName();
				}
				$return['success'] 	= 1;
				$return['profiles'] = $profile_items;

			} else {
				$return['success'] 	= 0;
				throw new Exception('No views (profiles) found for this user.');
			}
		} else {
			$return['success'] 	= 0;
			throw new Exception('No properties found for this user.');
		}
	} else {
		$return['success'] 	= 0;
		throw new Exception('No accounts found for this user.');
	}
	
	return $return;
}


function dfga_getSavedProfile(  ) {
	
	return 124509026;
}

function dfga_getResults ( $from,$to,$metrics,$options  ) {
	
	$analytics = dfga_initializeAnalytics();
	$profileId = dfga_getSavedProfile( );
	return $analytics->data_ga->get( 'ga:' . $profileId, $from, $to, $metrics, $options );	
}



//including required te,plate filesize

/* including insert functionalities*/
include_once( DRAW_FROM_GA_PLUGIN_TEMPLATES . 'average-time-on-site.php' );


