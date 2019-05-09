<?php
/*
Plugin Name: Tigo Money Woo
Description: Tigo Money Wooo medio de pago simple, solo para Paraguay.
Version: 1.0.1
Author: Saul Morales Pacheco
Author URI: http://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: tigo-money-woo
Domain Path: /languages
WC tested up to: 3.6
WC requires at least: 2.6
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; //Exit if accessed directly
}
if(!defined('TIGO_MONEY_WOO_VERSION')){
	define('TIGO_MONEY_WOO_VERSION', '1.0.1');
}
add_action('plugins_loaded','tigo_money_woo_init',0);

function tigo_money_woo_init()
{
	load_plugin_textdomain('tigo-money-woo', FALSE, dirname(plugin_basename(__FILE__)) . '/languages');
	if (!requeriments_tigo_money_woo()){
		return;
	}

	tigo_money_woo()->tigo_run();

}
add_action('notices_action_tag_tigo_woo', 'tigo_money_woo_notices', 10, 1);
function tigo_money_woo_notices($notice){
	?>
	<div class="error notice">
		<p><?php echo $notice; ?></p>
	</div>
	<?php
}
function requeriments_tigo_money_woo(){
	if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$php = __( 'Tigo Money Woo: Requires php version 5.6.0 or higher.', 'tigo-money-woo' );
			do_action('notices_action_tag_tigo_woo', $php);
		}
		return false;
	}

	if (!function_exists('curl_version')){
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$curl = __( 'Tigo Money Woo: Requires cURL extension to be installed.', 'form-print-pay' );
			do_action('notices_action_tag_tigo_woo', $curl);
		}
		return false;
	}

	// If WooCommerce is active
	if ( !in_array(
		'woocommerce/woocommerce.php',
		apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
		true
	) ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$woo = __( 'Tigo Money Woo: Woocommerce must be installed and active.', 'tigo-money-woo' );
			do_action('notices_action_tag_tigo_woo', $woo);
		}
		return false;
	}

	if (version_compare(WC_VERSION, '3.0', '<')) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$wc_version = __( 'Tigo Money Woo: Version 3.0 or greater of installed woocommerce is required.', 'tigo-money-woo' );
			do_action('notices_action_tag_tigo_woo', $wc_version);
		}
	    return false;
	}

	if (!in_array(get_woocommerce_currency(), array('PYG'))){
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$currency = __('Tigo Money: Requires currency to be PYG ', 'tigo-money-woo' )  . sprintf(__('%s', 'tigo-money-woo' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __('Click here to configure', 'tigo-money-woo') . '</a>' );
			do_action('notices_action_tag_tigo_woo', $currency);
		}
	    return false;
    }

	return true;
}
function tigo_money_woo()
{
	static $plugin;
	if (!isset($plugin)){
		require_once ('includes/class-tigo-money-woo-plugin.php');
		$plugin = new Tigo_Money_Woo_Plugin(__FILE__,TIGO_MONEY_WOO_VERSION);
	}
	return $plugin;
}