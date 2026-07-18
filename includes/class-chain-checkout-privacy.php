<?php
/**
 * Privacy Policy suggested content for WordPress Settings → Privacy.
 *
 * @package ChainCheckout
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Chain_Checkout_Privacy
 */
class Chain_Checkout_Privacy {

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_privacy_policy_content' ) );
	}

	/**
	 * Suggest privacy policy text (Guideline: third-party services).
	 */
	public static function register_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = '<p>' . esc_html__( 'Chain Checkout lets customers pay with cryptocurrency directly to store wallets. The plugin does not send data to the plugin author’s servers.', 'chain-checkout' ) . '</p>';

		$content .= '<p><strong>' . esc_html__( 'What is stored locally', 'chain-checkout' ) . '</strong></p>';
		$content .= '<ul>';
		$content .= '<li>' . esc_html__( 'Public cryptocurrency receiving addresses you configure.', 'chain-checkout' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Order metadata needed to match payments (selected coin, quoted amount, assigned address, payment status).', 'chain-checkout' ) . '</li>';
		$content .= '</ul>';

		$content .= '<p><strong>' . esc_html__( 'Third-party services', 'chain-checkout' ) . '</strong></p>';
		$content .= '<p>' . esc_html__( 'When a customer uses this payment method, or when automatic verification is enabled, the store may contact public blockchain and price APIs. Typical data includes coin identifiers, wallet addresses, transaction IDs, and fiat currency codes. Optional API keys you add are sent only to the matching provider.', 'chain-checkout' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li>' . esc_html__( 'CoinGecko — exchange rates for crypto quotes.', 'chain-checkout' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Etherscan API V2 — EVM chain payment detection.', 'chain-checkout' ) . '</li>';
		$content .= '<li>' . esc_html__( 'mempool.space / Blockstream — Bitcoin payment detection.', 'chain-checkout' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Blockchair — Litecoin / Dogecoin payment detection.', 'chain-checkout' ) . '</li>';
		$content .= '<li>' . esc_html__( 'TronGrid, Solana RPC / Helius, and other public explorers/RPCs for supported networks.', 'chain-checkout' ) . '</li>';
		$content .= '</ul>';

		$content .= '<p>' . esc_html__( 'Automatic on-chain verification can be disabled under Chain Checkout → General. Disabling the payment gateway stops these checkout-related requests.', 'chain-checkout' ) . '</p>';

		wp_add_privacy_policy_content( 'Chain Checkout', wp_kses_post( $content ) );
	}
}
