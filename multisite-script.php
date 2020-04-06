<?php
/**
* Plugin Name: Network Wide Custom Code
* Plugin URI: https://www.brainstormforce.com/
* Description: This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.
* Version: 1.0.0
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
* Text Domain: nwcc
*
* @package NWCC.
*/

//Block direct access to plugin files.
defined( 'ABSPATH' ) or die();

if ( ! defined( 'NWCC_ROOT' ) ) {
	define( 'NWCC_ROOT', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'NWCC_DIR' ) ) {
	define( 'NWCC_DIR', plugin_dir_path( __FILE__ ) );
}

if( function_exists( 'switch_to_blog' ) ) {

	if ( ! version_compare( PHP_VERSION, '5.6', '>=' ) ) {
		add_action( 'admin_notices', 'nwcc_fail_php_version' );
	} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {
		add_action( 'admin_notices', 'nwcc_fail_wp_version' );
	} else {
		require_once 'classes/class-nwcc-loader.php';
	}
} else {
	add_action( 'admin_notices', array( $this, 'need_network_error_notice' ) );
}

/**
 * Network Wide Custom Code admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 * @return void
 */
function nwcc_fail_php_version() {
	/* translators: %s: PHP version */
	$message      = sprintf( esc_html__( 'Network Wide Custom Code requires PHP version %s+, plugin is currently NOT RUNNING.', 'nwcc' ), '5.6' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * Network Wide Custom Code admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 * @return void
 */
function nwcc_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'Network Wide Custom Code requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'nwcc' ), '4.6' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * Function Name: need_network_error_notice
 * Function Description: Network setup need to be there, admin notice
 *
 * @since  1.0.0
 * @return void
 */
function need_network_error_notice() {
	$msg = __( '<strong>Network Wide Custom Code</strong> works for WordPress Multisite setup only.', 'nwcc' );
	echo "<div class=\"error\"> <p>" . $msg . "</p> </div>"; 
}
