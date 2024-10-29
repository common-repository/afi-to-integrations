<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}
 
$options = array( 'afi_api_key', 'afi_fixed_points', 'afi_conversion_rate', 'afi_points_mode', 'afi_inc_ship_cost', 'afi_cookie_check' );

foreach ( $options as $option_name ) {
	delete_option( $option_name );
}

?>