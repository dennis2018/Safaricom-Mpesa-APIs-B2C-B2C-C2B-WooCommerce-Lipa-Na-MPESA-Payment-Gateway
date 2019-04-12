<?php
/**
 * Plugin Name: MPesa For WooCommerce
 * Plugin URI: https://dennismburu.tech/
 * Description: This plugin extends WordPress and WooCommerce functionality to integrate MPesa for making and receiving online payments.
 * Author: Dennis Mburu Tech < admin@dennismburu.tech >
 * Version: 1.8.8
 * Author URI: https://wwww.dennismburu.tech/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.5
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ){
	exit;
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
	exit('Please install WooCommerce for this extension to work');
}

define( 'MPESA_DIR', plugin_dir_path( __FILE__ ) );
define( 'MPESA_INC_DIR', MPESA_DIR.'includes/' );
define( 'WC_MPESA_VERSION', '1.7.8' );

// Admin Menus
require_once( MPESA_INC_DIR.'menu.php' );

//Payments Post Type
require_once( MPESA_INC_DIR.'payments.php' );

//Payments Metaboxes
require_once( MPESA_INC_DIR.'metaboxes.php' );

function get_post_id_by_meta_key_and_value($key, $value) {
    global $wpdb;
    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$key."' AND meta_value='".$value."'");
    if (is_array($meta) && !empty($meta) && isset($meta[0])) {
        $meta = $meta[0];
    }

    if (is_object($meta)) {
        return $meta->post_id;
    } else {
        return false;
    }
}

/**
 * Installation hook callback creates plugin settings
 */
register_activation_hook( __FILE__, 'wc_mpesa_install' );
function wc_mpesa_install()
{
	update_option( 'wc_mpesa_version', WC_MPESA_VERSION );
	update_option( 'wc_mpesa_urls_reg', 0 );
}

/**
 * Uninstallation hook callback deletes plugin settings
 */
register_uninstall_hook( __FILE__, 'wc_mpesa_uninstall' );
function wc_mpesa_uninstall()
{
	delete_option( 'wc_mpesa_version' );
	delete_option( 'wc_mpesa_urls_reg' );
}

function register_urls_notice()
{
	if ( get_option( 'wc_mpesa_urls_reg', 0 ) ) {
		echo '<div class="notification">You need to register your confirmation and validation endpoints to work.</div>';
	}
}

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'mpesa_action_links' );
function mpesa_action_links( $links )
{
	return array_merge( $links, [ '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mpesa' ).'">&nbsp;Preferences</a>' ] );
} 

add_filter( 'plugin_row_meta', 'mpesa_row_meta', 10, 2 );
function mpesa_row_meta( $links, $file )
{
	$plugin = plugin_basename( __FILE__ );
