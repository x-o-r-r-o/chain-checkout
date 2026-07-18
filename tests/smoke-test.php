#!/usr/bin/env php
<?php
/**
 * Offline smoke tests for Chain Checkout (no WordPress bootstrap required).
 *
 * Run: php tests/smoke-test.php
 *
 * @package ChainCheckout
 */

$root = dirname( __DIR__ );
$fail = 0;

function cc_assert( $cond, $msg ) {
	global $fail;
	if ( $cond ) {
		echo "[PASS] {$msg}\n";
		return;
	}
	echo "[FAIL] {$msg}\n";
	$fail++;
}

// --- PHP syntax of all plugin files ---
$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);
foreach ( $iterator as $file ) {
	if ( 'php' !== $file->getExtension() ) {
		continue;
	}
	$path = $file->getPathname();
	if ( false !== strpos( $path, '/tests/' ) ) {
		continue;
	}
	$output = array();
	$code   = 0;
	exec( 'php -l ' . escapeshellarg( $path ) . ' 2>&1', $output, $code );
	cc_assert( 0 === $code, 'php -l ' . str_replace( $root . '/', '', $path ) );
}

// --- Load coin catalog in isolation ---
define( 'ABSPATH', '/tmp/' );
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) { // phpcs:ignore
		return $value;
	}
}
require_once $root . '/includes/class-chain-checkout-coins.php';

$all = Chain_Checkout_Coins::all();
cc_assert( count( $all ) >= 48, 'coin catalog size >= 48 (got ' . count( $all ) . ')' );

$required = array(
	'BTC', 'ETH', 'LTC', 'DOGE', 'ARB', 'OP', 'BNB', 'SOL', 'TRX', 'XMR', 'XRP', 'ATA', 'MATIC', 'TUSD', 'USDP', 'GUSD',
	'USDT_ETH', 'USDT_ARB', 'USDT_OP', 'USDT_BNB', 'USDT_SOL', 'USDT_TRX',
	'USDC_ETH', 'USDC_ARB', 'USDC_OP', 'USDC_BNB', 'USDC_SOL',
	'FTT', 'AVAX', 'LINK', 'DOT', 'CAKE', 'ATOM', 'EOS', 'ETC', 'ZIL', 'FIL', 'ALGO', 'HBAR', 'CRO', 'FTM', 'EGLD', 'NEAR', 'AXS', 'MANA', 'SAND', 'UNI', 'XLM',
);
foreach ( $required as $id ) {
	cc_assert( isset( $all[ $id ] ), "required coin present: {$id}" );
}

cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'BTC' ), 'BTC auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'ETH' ), 'ETH auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'BNB' ), 'BNB auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'USDT_ETH' ), 'USDT_ETH auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'FTM' ), 'FTM auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'CRO' ), 'CRO auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'ETC' ), 'ETC auto-verify' );
cc_assert( ! Chain_Checkout_Coins::supports_auto_verify( 'XMR' ), 'XMR is manual' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'ALGO' ), 'ALGO auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'HBAR' ), 'HBAR auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'NEAR' ), 'NEAR auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'ATOM' ), 'ATOM auto-verify' );
cc_assert( Chain_Checkout_Coins::supports_auto_verify( 'DOT' ), 'DOT auto-verify' );

$base = Chain_Checkout_Coins::to_base_units( '1.5', 6 );
cc_assert( '1500000' === $base, "EIP-681 base units 1.5@6 => {$base}" );

// --- Verifier source checks ---
$verifier = file_get_contents( $root . '/includes/class-chain-checkout-verifier.php' );
cc_assert( false !== strpos( $verifier, 'api.etherscan.io/v2/api' ), 'Etherscan V2 endpoint present' );
cc_assert( false !== strpos( $verifier, 'mempool.space' ), 'mempool.space present' );
cc_assert( false !== strpos( $verifier, 'helius-rpc.com' ), 'Helius RPC present' );
cc_assert( false !== strpos( $verifier, 'TRON-PRO-API-KEY' ), 'TronGrid key header present' );
cc_assert( false === strpos( $verifier, 'api.bscscan.com' ), 'legacy BscScan host removed from verifier' );

// --- Headers ---
$main = file_get_contents( $root . '/chain-checkout.php' );
cc_assert( false !== strpos( $main, 'Version:           1.3.1' ), 'plugin version 1.3.1' );
cc_assert( false !== strpos( $main, 'Author URI:        https://github.com/x-o-r-r-o' ), 'author URI is GitHub' );
cc_assert( false === strpos( $main, 'Author URI:        https://wordpress.org/plugins/chain-checkout' ), 'author URI not same as plugin URI' );
cc_assert( false !== strpos( $main, 'Requires at least: 6.9' ), 'Requires WP 6.9+' );
cc_assert( false !== strpos( $main, 'WC requires at least: 10.0' ), 'Requires WC 10.0+' );
cc_assert( false !== strpos( $main, 'WC tested up to:   10.8' ), 'WC tested up to 10.8' );
cc_assert( false !== strpos( $main, 'custom_order_tables' ), 'HPOS compatibility declared' );
cc_assert( false !== strpos( $main, 'cart_checkout_blocks' ), 'Blocks compatibility declared' );

$branding = file_get_contents( $root . '/includes/class-chain-checkout-branding.php' );
cc_assert( false !== strpos( $branding, 'checkout_display' ), 'branding display mode' );
cc_assert( false !== strpos( $branding, 'checkout_icon_width' ), 'branding icon width' );
cc_assert( false !== strpos( $branding, 'get_icon_html' ), 'branding icon html' );

$gateway = file_get_contents( $root . '/includes/class-chain-checkout-gateway.php' );
cc_assert( false !== strpos( $gateway, 'function get_icon' ), 'gateway get_icon override' );
cc_assert( false !== strpos( $gateway, 'filter_gateway_title' ), 'gateway title filter' );

$frontend_css = file_get_contents( $root . '/assets/css/frontend.css' );
cc_assert( false !== strpos( $frontend_css, 'chain-checkout-gateway-icon' ), 'frontend icon CSS' );

$blocks_js = file_get_contents( $root . '/assets/js/blocks.js' );
cc_assert( false !== strpos( $blocks_js, 'iconWidth' ), 'blocks icon width' );
cc_assert( false !== strpos( $blocks_js, "display === 'text'" ), 'blocks text-only mode' );

$readme_md = file_get_contents( $root . '/README.md' );
cc_assert( false !== strpos( $readme_md, 'Checkout branding' ), 'README.md branding section' );

$readme = file_get_contents( $root . '/readme.txt' );
cc_assert( false !== strpos( $readme, 'Tested up to: 7.0' ), 'readme Tested up to WP 7.0' );
cc_assert( false !== strpos( $readme, 'Stable tag: 1.3.1' ), 'readme stable 1.3.1' );

echo "\n";
if ( $fail > 0 ) {
	echo "FAILED: {$fail} assertion(s)\n";
	exit( 1 );
}
echo "ALL SMOKE TESTS PASSED\n";
exit( 0 );
