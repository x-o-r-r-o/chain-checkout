<?php
/**
 * WooCommerce Checkout Blocks payment method integration.
 *
 * @package ChainCheckout
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Class Chain_Checkout_Blocks
 */
final class Chain_Checkout_Blocks extends AbstractPaymentMethodType {

	/**
	 * Payment method name matching gateway ID.
	 *
	 * @var string
	 */
	protected $name = CHAIN_CHECKOUT_GATEWAY_ID;

	/**
	 * Gateway instance.
	 *
	 * @var Chain_Checkout_Gateway|null
	 */
	private $gateway;

	/**
	 * Initialize.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . CHAIN_CHECKOUT_GATEWAY_ID . '_settings', array() );
		$gateways       = WC()->payment_gateways()->payment_gateways();
		$this->gateway  = isset( $gateways[ CHAIN_CHECKOUT_GATEWAY_ID ] ) ? $gateways[ CHAIN_CHECKOUT_GATEWAY_ID ] : null;
	}

	/**
	 * Active check.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->gateway && $this->gateway->is_available();
	}

	/**
	 * Script handles.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$handle = 'chain-checkout-blocks';

		wp_enqueue_style(
			'chain-checkout-frontend',
			CHAIN_CHECKOUT_URL . 'assets/css/frontend.css',
			array(),
			CHAIN_CHECKOUT_VERSION
		);

		$branding = Chain_Checkout_Branding::frontend_data();
		wp_add_inline_style(
			'chain-checkout-frontend',
			sprintf(
				'.chain-checkout-blocks-label img.chain-checkout-gateway-icon{width:%1$dpx!important;height:%2$dpx!important;max-width:%1$dpx!important;max-height:%2$dpx!important;object-fit:contain;vertical-align:middle;}',
				(int) $branding['iconWidth'],
				(int) $branding['iconHeight']
			)
		);

		wp_register_script(
			$handle,
			CHAIN_CHECKOUT_URL . 'assets/js/blocks.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
			),
			CHAIN_CHECKOUT_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'xorro-direct-wallet-payments-woocommerce', CHAIN_CHECKOUT_PATH . 'languages' );
		}

		return array( $handle );
	}

	/**
	 * Data passed to frontend script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$coins = array();
		foreach ( Chain_Checkout_Coins::get_payable() as $id => $coin ) {
			$icons   = Chain_Checkout_Coins::icon_meta( $id );
			$coins[] = array(
				'id'     => $id,
				'symbol' => $coin['symbol'],
				'name'   => $coin['name'],
				'icon'   => $icons['icon'],
				'badge'  => $icons['badge'],
			);
		}

		return array_merge(
			array(
				'description' => $this->gateway ? $this->gateway->get_description() : '',
				'supports'    => $this->gateway ? array_filter( $this->gateway->supports ) : array( 'products' ),
				'coins'       => $coins,
			),
			Chain_Checkout_Branding::frontend_data()
		);
	}
}
