<?php
/**
 * Plugin Name:	   Afi.to - Integration
 * Description:	   Reward your customers for making purchases in your online store!
 * Version:		   1.0.1
 * Requires at least: 5.7.2
 * Requires PHP:	  7.1
 * Author:			Afi.to Limited
 * Author URI:		https://afi.to/
 * Text Domain:	   afi
 * License:	 GPLv2+

Afi.ro - Integrations is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Afi.ro - Integrations is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License 
along with Afi.ro - Integrations. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function afi_is_woocommerce_active() {

	$active_plugins = ( array ) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return in_array( 'woocommerce/woocommerce.php', $active_plugins, false ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
}

if ( !afi_is_woocommerce_active() ) {

	return;
}

require_once ( __DIR__ . '/includes/config.php' );
require_once 'includes/wc_store.php';

function afi_register_assets_is_admin() {
	wp_register_style( 'afi_style', plugins_url( 'admin/css/main.css', __FILE__ ), false, time() );
	wp_register_script( 'afi_script', plugins_url( 'admin/js/points_mode.js', __FILE__ ), false, time() );
}

function afi_enqueue_assets_is_admin() {
	wp_enqueue_style( 'afi_style' );
	wp_enqueue_script( 'afi_script' );
}

function afi_show_new_items() {
	$title = 'Afi configuration';
	if ( current_user_can( 'manage_options' ) ) {
		add_menu_page(
			esc_html__( $title ),
			esc_html__( 'Afi.to' ),
			'manage_options',
			'afi-config',
			'afi_add_config',
			'dashicons-awards',
			3
		);
		add_submenu_page(
			'afi-config',
			esc_html__( $title ),
			esc_html__( 'Configuration', 'Afi' ),
			'manage_options',
			'afi-config',
			'afi_add_config'
		);
	}
}

if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'afi_register_assets_is_admin' );
	add_action( 'admin_enqueue_scripts', 'afi_enqueue_assets_is_admin' );
	add_action( 'admin_menu', 'afi_show_new_items' );
}

if ( !is_admin() ) {
	afi_check_referal_link();
	add_action( 'woocommerce_thankyou', 'afi_new_order_claim', 1, 1 );
}
