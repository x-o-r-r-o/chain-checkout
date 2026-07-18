<?php
/**
 * Fiat ↔ crypto price conversion via CoinGecko.
 *
 * @package ChainCheckout
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Chain_Checkout_Prices
 */
class Chain_Checkout_Prices {

	const TRANSIENT_KEY = 'chain_checkout_price_cache';
	const CACHE_TTL     = 120;

	/**
	 * Convert fiat order total to crypto amount.
	 *
	 * @param float  $fiat_amount   Fiat amount.
	 * @param string $coin_id       Coin ID.
	 * @param string $currency      Fiat currency code.
	 * @param bool   $unique_amount Whether to apply unique dust (order creation only).
	 * @return string Crypto amount string, or empty on failure.
	 */
	public static function fiat_to_crypto( $fiat_amount, $coin_id, $currency = '', $unique_amount = false ) {
		$coin = Chain_Checkout_Coins::get( $coin_id );
		if ( ! $coin ) {
			return '';
		}

		if ( '' === $currency ) {
			$currency = get_woocommerce_currency();
		}

		$rate = self::get_rate( $coin['coingecko_id'], $currency );
		if ( $rate <= 0 ) {
			return '';
		}

		$amount = (float) $fiat_amount / $rate;

		if ( $unique_amount && 'yes' === Chain_Checkout_Settings::get( 'unique_amounts', 'yes' ) ) {
			$amount = self::apply_unique_dust( $amount, $coin_id );
		}

		return Chain_Checkout_Coins::format_amount( $amount, $coin_id );
	}

	/**
	 * Add a tiny unique offset so reused addresses can be matched by amount.
	 *
	 * @param float  $amount  Base amount.
	 * @param string $coin_id Coin ID.
	 * @return float
	 */
	public static function apply_unique_dust( $amount, $coin_id ) {
		$coin     = Chain_Checkout_Coins::get( $coin_id );
		$decimals = $coin ? min( (int) $coin['decimals'], 8 ) : 8;

		// Low-decimal assets cannot safely encode unique dust without large overcharge.
		if ( $decimals <= 4 ) {
			return $amount;
		}

		$counter = (int) get_option( 'chain_checkout_amount_seq', 0 );
		update_option( 'chain_checkout_amount_seq', ( $counter + 1 ) % 9000, false );
		$dust_units = 1000 + ( $counter % 9000 );
		$dust       = $dust_units / pow( 10, $decimals );

		return $amount + $dust;
	}

	/**
	 * Get crypto price in fiat.
	 *
	 * @param string $coingecko_id CoinGecko ID.
	 * @param string $currency     Fiat currency.
	 * @return float
	 */
	public static function get_rate( $coingecko_id, $currency ) {
		$currency = strtolower( $currency );
		$cache    = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		$key = $coingecko_id . '_' . $currency;
		if ( isset( $cache[ $key ] ) && is_numeric( $cache[ $key ] ) ) {
			return (float) $cache[ $key ];
		}

		self::refresh_rates( array( $coingecko_id ), $currency );
		$cache = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cache ) && isset( $cache[ $key ] ) ) {
			return (float) $cache[ $key ];
		}

		return 0.0;
	}

	/**
	 * Refresh rates for given CoinGecko IDs.
	 *
	 * @param array  $ids      CoinGecko IDs.
	 * @param string $currency Fiat currency.
	 */
	public static function refresh_rates( array $ids = array(), $currency = '' ) {
		if ( '' === $currency ) {
			$currency = get_woocommerce_currency();
		}
		$currency = strtolower( $currency );

		if ( empty( $ids ) ) {
			$ids = array();
			foreach ( Chain_Checkout_Coins::all() as $coin ) {
				$ids[] = $coin['coingecko_id'];
			}
			$ids = array_values( array_unique( $ids ) );
		}

		// CoinGecko allows comma-separated ids; chunk to stay under URL limits.
		$chunks = array_chunk( $ids, 50 );
		$cache  = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		$api_key = Chain_Checkout_Settings::get( 'coingecko_api_key', '' );
		$base    = $api_key
			? 'https://pro-api.coingecko.com/api/v3/simple/price'
			: 'https://api.coingecko.com/api/v3/simple/price';

		foreach ( $chunks as $chunk ) {
			$url = add_query_arg(
				array(
					'ids'           => implode( ',', $chunk ),
					'vs_currencies' => $currency,
				),
				$base
			);

			$args = array(
				'timeout' => 15,
				'headers' => array(
					'Accept' => 'application/json',
				),
			);

			if ( $api_key ) {
				$args['headers']['x-cg-pro-api-key'] = $api_key;
			}

			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				continue;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( 200 !== (int) $code || ! is_array( $body ) ) {
				continue;
			}

			foreach ( $body as $id => $prices ) {
				if ( isset( $prices[ $currency ] ) ) {
					$cache[ $id . '_' . $currency ] = (float) $prices[ $currency ];
				}
			}
		}

		set_transient( self::TRANSIENT_KEY, $cache, self::CACHE_TTL );
	}

	/**
	 * Cron callback to warm price cache for enabled coins.
	 */
	public static function cron_refresh() {
		$ids      = array();
		$enabled  = Chain_Checkout_Settings::get( 'enabled_coins', array() );
		if ( ! is_array( $enabled ) ) {
			return;
		}
		foreach ( $enabled as $coin_id ) {
			$coin = Chain_Checkout_Coins::get( $coin_id );
			if ( $coin ) {
				$ids[] = $coin['coingecko_id'];
			}
		}
		if ( ! empty( $ids ) ) {
			self::refresh_rates( array_unique( $ids ) );
		}
	}
}
