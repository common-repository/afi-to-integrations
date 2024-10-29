<?php

function afi_new_order_claim( $order_id ) {

	global $wpdb;
		
	$api = $wpdb->get_var( "select option_value from $wpdb->options where option_name='afi_api_key'" ); 

	if ( empty( $api ) ) {
		exit;
	}

	$cookie_check = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_cookie_check'" );

	if ( '1' == $cookie_check ) {
		if ( empty( $_COOKIE[ 'afi_transaction_id_cookie' ] ) ) {
			exit;
		}
	}

	$order_json = new WC_Order( $order_id );
	$order = json_decode( $order_json, true );

	$points_mode = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_points_mode'" );

	if ( 'order_percent' == $points_mode ) {
		$conversion_rate = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_conversion_rate'" );
		$inc_ship_cost = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_inc_ship_cost'" );
		if ( '1' == $inc_ship_cost ) {
			$points = $order[ 'total' ] * $conversion_rate;
		} else {
			$points = ( $order[ 'total' ] - $order[ 'shipping_total' ]) * $conversion_rate;
		}
	} elseif ( 'fix' == $points_mode ) {
		$points = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_fixed_points'" );
	}

	$email = $order[ 'billing' ][ 'email' ];

	$new_claim = [
		'email' => sanitize_text_field( $email ),
		'points' => sanitize_text_field( $points ),
		'description' => 'order: ' . sanitize_text_field( $order_id )
	];

	if ( !empty( $_COOKIE[ 'afi_transaction_id_cookie' ] ) ) {
		$new_claim[ 'description' ] .= ' (transaction_id = ' . sanitize_text_field( $_COOKIE['afi_transaction_id_cookie'] ) . ')';
	}

	$json_new_claim = json_encode( $new_claim );

	$result = wp_remote_post( 'https://cloud.afidesk.com/public_api/v1/claims', [
		'headers' => [
			'Content-Type' => 'application/json; charset=utf-8',
			'Api-Key' => $api
		],
		'body' => $json_new_claim
	] );

	// $message = "Request:\r\n" . $json_new_claim . "\r\n\r\n" . "Response:\r\n" . wp_remote_retrieve_body($result);

	// $test_file = fopen(__DIR__ . '/test.txt', 'w');
	// fwrite($test_file, $message, strlen($message));
	// fclose($test_file);
}

function afi_check_referal_link () {
	$now_url_parts = parse_url( $_SERVER[ 'REQUEST_URI' ] );
	if ( preg_match( '/^\/shop\/$/', $now_url_parts[ 'path' ] ) ) {
		if ( isset($now_url_parts[ 'query' ]) && preg_match( '/^transaction_id=.+$/', $now_url_parts['query'] ) ) {
			echo parse_str( $now_url_parts[ 'query' ], $transaction_id );
			setcookie( 'afi_transaction_id_cookie', $transaction_id[ 'transaction_id' ], time() + ( 60 * 60 * 24 * 90 ), '/' );
		}
	}
}
?>
