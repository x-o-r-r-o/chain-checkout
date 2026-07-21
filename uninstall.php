<?php
/**
 * Uninstall Xorro Wallet Payments — remove options and transients only.
 * Order meta is left intact for accounting history.
 *
 * @package ChainCheckout
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'chain_checkout_settings' );
delete_option( 'chain_checkout_version' );
delete_option( 'chain_checkout_amount_seq' );
delete_option( 'woocommerce_chain_checkout_settings' );

wp_clear_scheduled_hook( 'chain_checkout_check_payments' );
wp_clear_scheduled_hook( 'chain_checkout_refresh_prices' );

global $wpdb;

$like_transient = $wpdb->esc_like( '_transient_chain_checkout_' ) . '%';
$like_timeout   = $wpdb->esc_like( '_transient_timeout_chain_checkout_' ) . '%';
$like_wallet    = $wpdb->esc_like( 'chain_checkout_wallet_idx_' ) . '%';
$like_paying    = $wpdb->esc_like( 'chain_checkout_paying_' ) . '%';
$like_txid      = $wpdb->esc_like( 'chain_checkout_txid_claim_' ) . '%';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$like_transient,
		$like_timeout
	)
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
		$like_wallet,
		$like_paying,
		$like_txid
	)
);
