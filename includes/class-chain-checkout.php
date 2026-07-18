<?php
/**
 * Main plugin bootstrap.
 *
 * @package ChainCheckout
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Chain_Checkout
 */
final class Chain_Checkout {

	/**
	 * Singleton instance.
	 *
	 * @var Chain_Checkout|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return Chain_Checkout
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load required files.
	 */
	private function includes() {
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-coins.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-settings.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-branding.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-privacy.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-prices.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-wallets.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-verifier.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-order.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-cron.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-ajax.php';
		require_once CHAIN_CHECKOUT_PATH . 'includes/class-chain-checkout-gateway.php';

		if ( is_admin() ) {
			require_once CHAIN_CHECKOUT_PATH . 'includes/admin/class-chain-checkout-admin.php';
		}
	}

	/**
	 * Register hooks.
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_blocks_support' ) );

		Chain_Checkout_Cron::init();
		Chain_Checkout_Ajax::init();
		Chain_Checkout_Order::init();
		Chain_Checkout_Privacy::init();

		if ( is_admin() ) {
			Chain_Checkout_Admin::init();
		}
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'chain-checkout', false, dirname( CHAIN_CHECKOUT_BASENAME ) . '/languages' );
	}

	/**
	 * Custom cron intervals.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public function add_cron_schedules( $schedules ) {
		$schedules['chain_checkout_every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Every Minute (Chain Checkout)', 'chain-checkout' ),
		);
		return $schedules;
	}

	/**
	 * Register payment gateway.
	 *
	 * @param array $gateways Gateways.
	 * @return array
	 */
	public function register_gateway( $gateways ) {
		$gateways[] = 'Chain_Checkout_Gateway';
		return $gateways;
	}

	/**
	 * Register Checkout Blocks integration.
	 */
	public function register_blocks_support() {
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		require_once CHAIN_CHECKOUT_PATH . 'includes/blocks/class-chain-checkout-blocks.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			static function ( $registry ) {
				$registry->register( new Chain_Checkout_Blocks() );
			}
		);
	}
}
