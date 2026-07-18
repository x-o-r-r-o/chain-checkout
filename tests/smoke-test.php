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

function chain_checkout_assert( $cond, $msg ) {
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
	chain_checkout_assert( 0 === $code, 'php -l ' . str_replace( $root . '/', '', $path ) );
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
chain_checkout_assert( count( $all ) >= 48, 'coin catalog size >= 48 (got ' . count( $all ) . ')' );

$required = array(
	'BTC', 'ETH', 'LTC', 'DOGE', 'ARB', 'OP', 'BNB', 'SOL', 'TRX', 'XMR', 'XRP', 'ATA', 'MATIC', 'TUSD', 'USDP', 'GUSD',
	'USDT_ETH', 'USDT_ARB', 'USDT_OP', 'USDT_BNB', 'USDT_SOL', 'USDT_TRX',
	'USDC_ETH', 'USDC_ARB', 'USDC_OP', 'USDC_BNB', 'USDC_SOL',
	'FTT', 'AVAX', 'LINK', 'DOT', 'CAKE', 'ATOM', 'EOS', 'ETC', 'ZIL', 'FIL', 'ALGO', 'HBAR', 'CRO', 'FTM', 'EGLD', 'NEAR', 'AXS', 'MANA', 'SAND', 'UNI', 'XLM',
);
foreach ( $required as $id ) {
	chain_checkout_assert( isset( $all[ $id ] ), "required coin present: {$id}" );
}

chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'BTC' ), 'BTC auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'ETH' ), 'ETH auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'BNB' ), 'BNB auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'USDT_ETH' ), 'USDT_ETH auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'FTM' ), 'FTM auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'CRO' ), 'CRO auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'ETC' ), 'ETC auto-verify' );
chain_checkout_assert( ! Chain_Checkout_Coins::supports_auto_verify( 'XMR' ), 'XMR is manual' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'ALGO' ), 'ALGO auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'HBAR' ), 'HBAR auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'NEAR' ), 'NEAR auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'ATOM' ), 'ATOM auto-verify' );
chain_checkout_assert( Chain_Checkout_Coins::supports_auto_verify( 'DOT' ), 'DOT auto-verify' );

$base = Chain_Checkout_Coins::to_base_units( '1.5', 6 );
chain_checkout_assert( '1500000' === $base, "EIP-681 base units 1.5@6 => {$base}" );

// --- Verifier source checks ---
$verifier = file_get_contents( $root . '/includes/class-chain-checkout-verifier.php' );
chain_checkout_assert( false !== strpos( $verifier, 'api.etherscan.io/v2/api' ), 'Etherscan V2 endpoint present' );
chain_checkout_assert( false !== strpos( $verifier, 'mempool.space' ), 'mempool.space present' );
chain_checkout_assert( false !== strpos( $verifier, 'helius-rpc.com' ), 'Helius RPC present' );
chain_checkout_assert( false !== strpos( $verifier, 'TRON-PRO-API-KEY' ), 'TronGrid key header present' );
chain_checkout_assert( false === strpos( $verifier, 'api.bscscan.com' ), 'legacy BscScan host removed from verifier' );

// --- Headers ---
$main = file_get_contents( $root . '/chain-checkout.php' );
chain_checkout_assert( false !== strpos( $main, 'Version:           1.3.2' ), 'plugin version 1.3.2' );
chain_checkout_assert( false !== strpos( $main, 'Author URI:        https://github.com/x-o-r-r-o' ), 'author URI is GitHub' );
chain_checkout_assert( false === strpos( $main, 'Author URI:        https://wordpress.org/plugins/chain-checkout' ), 'author URI not same as plugin URI' );
chain_checkout_assert( false !== strpos( $main, 'Requires at least: 6.9' ), 'Requires WP 6.9+' );
chain_checkout_assert( false !== strpos( $main, 'WC requires at least: 10.0' ), 'Requires WC 10.0+' );
chain_checkout_assert( false !== strpos( $main, 'WC tested up to:   10.8' ), 'WC tested up to 10.8' );
chain_checkout_assert( false !== strpos( $main, 'custom_order_tables' ), 'HPOS compatibility declared' );
chain_checkout_assert( false !== strpos( $main, 'cart_checkout_blocks' ), 'Blocks compatibility declared' );

$branding = file_get_contents( $root . '/includes/class-chain-checkout-branding.php' );
chain_checkout_assert( false !== strpos( $branding, 'checkout_display' ), 'branding display mode' );
chain_checkout_assert( false !== strpos( $branding, 'checkout_icon_width' ), 'branding icon width' );
chain_checkout_assert( false !== strpos( $branding, 'get_icon_html' ), 'branding icon html' );

$gateway = file_get_contents( $root . '/includes/class-chain-checkout-gateway.php' );
chain_checkout_assert( false !== strpos( $gateway, 'function get_icon' ), 'gateway get_icon override' );
chain_checkout_assert( false !== strpos( $gateway, 'filter_gateway_title' ), 'gateway title filter' );

$frontend_css = file_get_contents( $root . '/assets/css/frontend.css' );
chain_checkout_assert( false !== strpos( $frontend_css, 'chain-checkout-gateway-icon' ), 'frontend icon CSS' );

$blocks_js = file_get_contents( $root . '/assets/js/blocks.js' );
chain_checkout_assert( false !== strpos( $blocks_js, 'iconWidth' ), 'blocks icon width' );
chain_checkout_assert( false !== strpos( $blocks_js, "display === 'text'" ), 'blocks text-only mode' );

$readme_md = file_get_contents( $root . '/README.md' );
chain_checkout_assert( false !== strpos( $readme_md, 'Checkout branding' ), 'README.md branding section' );

$readme = file_get_contents( $root . '/readme.txt' );
chain_checkout_assert( false !== strpos( $readme, 'Tested up to: 7.0' ), 'readme Tested up to WP 7.0' );
chain_checkout_assert( false !== strpos( $readme, 'Stable tag: 1.3.2' ), 'readme stable 1.3.2' );


$readme = file_get_contents( $root . '/readme.txt' );
chain_checkout_assert( false !== strpos( $readme, '== External services ==' ), 'readme external services section' );
chain_checkout_assert( false !== strpos( $readme, 'Stable tag: 1.3.2' ), 'readme stable 1.3.2' );
$privacy = file_get_contents( $root . '/includes/class-chain-checkout-privacy.php' );
chain_checkout_assert( false !== strpos( $privacy, 'wp_add_privacy_policy_content' ), 'privacy policy content registered' );
chain_checkout_assert( is_file( $root . '/assets/js/qrcode.LICENSE.txt' ), 'qrcode license attribution' );
chain_checkout_assert( is_file( $root . '/includes/admin/index.php' ), 'admin index.php silence' );

echo "\n";
if ( $fail > 0 ) {
	echo "FAILED: {$fail} assertion(s)\n";
	exit( 1 );
}
echo "ALL SMOKE TESTS PASSED\n";
exit( 0 );
