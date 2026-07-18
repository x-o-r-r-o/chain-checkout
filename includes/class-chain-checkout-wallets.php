<?php
/**
 * Wallet address storage and rotation.
 *
 * @package ChainCheckout
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Chain_Checkout_Wallets
 */
class Chain_Checkout_Wallets {

	/**
	 * Sanitize wallets map: coin_id => array of addresses.
	 *
	 * @param array<string, mixed> $wallets Raw wallets.
	 * @return array<string, array<int, string>>
	 */
	public static function sanitize_wallets( array $wallets ) {
		$clean = array();
		$valid = array_keys( Chain_Checkout_Coins::all() );

		foreach ( $wallets as $coin_id => $addresses ) {
			$coin_id = sanitize_text_field( $coin_id );
			if ( ! in_array( $coin_id, $valid, true ) ) {
				continue;
			}

			if ( is_string( $addresses ) ) {
				$addresses = preg_split( '/[\r\n,]+/', $addresses );
			}

			if ( ! is_array( $addresses ) ) {
				continue;
			}

			$list = array();
			foreach ( $addresses as $address ) {
				$address = trim( sanitize_text_field( wp_unslash( (string) $address ) ) );
				if ( '' === $address ) {
					continue;
				}
				if ( ! self::is_plausible_address( $coin_id, $address ) ) {
					continue;
				}
				$list[] = $address;
			}

			$list = array_values( array_unique( $list ) );
			if ( ! empty( $list ) ) {
				$clean[ $coin_id ] = $list;
			}
		}

		return $clean;
	}

	/**
	 * Basic address shape validation (not cryptographic proof).
	 *
	 * @param string $coin_id Coin ID.
	 * @param string $address Address.
	 * @return bool
	 */
	public static function is_plausible_address( $coin_id, $address ) {
		$coin = Chain_Checkout_Coins::get( $coin_id );
		if ( ! $coin || '' === $address ) {
			return false;
		}

		$verifier = $coin['verifier'];
		$len      = strlen( $address );

		switch ( $verifier ) {
			case 'btc':
				return (bool) preg_match( '/^(bc1|[13])[a-zA-HJ-NP-Z0-9]{25,62}$/', $address );
			case 'ltc':
				return (bool) preg_match( '/^(ltc1|[LM3])[a-zA-HJ-NP-Z0-9]{25,62}$/', $address );
			case 'doge':
				return (bool) preg_match( '/^D[5-9A-HJ-NP-U][1-9A-HJ-NP-Za-km-z]{32}$/', $address );
			case 'eth':
			case 'arbitrum':
			case 'optimism':
			case 'bsc':
			case 'bnb':
			case 'matic':
			case 'avax':
			case 'ftm':
			case 'cro':
			case 'etc':
				return (bool) preg_match( '/^0x[a-fA-F0-9]{40}$/', $address );
			case 'sol':
			case 'solana':
				return (bool) preg_match( '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address );
			case 'trx':
			case 'tron':
				return (bool) preg_match( '/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $address );
			case 'xrp':
				return (bool) preg_match( '/^r[1-9A-HJ-NP-Za-km-z]{24,34}$/', $address );
			case 'xlm':
				return (bool) preg_match( '/^G[A-Z2-7]{55}$/', $address );
			case 'xmr':
				return $len >= 95 && $len <= 110;
			case 'dot':
			case 'atom':
			case 'algo':
			case 'near':
			case 'fil':
			case 'hbar':
			case 'egld':
			case 'zil':
			case 'eos':
				return $len >= 8 && $len <= 128;
			default:
				return $len >= 10 && $len <= 128;
		}
	}

	/**
	 * Get configured addresses for a coin.
	 *
	 * @param string $coin_id Coin ID.
	 * @return array<int, string>
	 */
	public static function get_addresses( $coin_id ) {
		$wallets = Chain_Checkout_Settings::get( 'wallets', array() );
		if ( ! is_array( $wallets ) || empty( $wallets[ $coin_id ] ) || ! is_array( $wallets[ $coin_id ] ) ) {
			return array();
		}
		return array_values( $wallets[ $coin_id ] );
	}

	/**
	 * Pick a receiving address (rotation or first).
	 *
	 * @param string $coin_id Coin ID.
	 * @return string
	 */
	public static function pick_address( $coin_id ) {
		$addresses = self::get_addresses( $coin_id );
		if ( empty( $addresses ) ) {
			return '';
		}

		if ( 'yes' !== Chain_Checkout_Settings::get( 'wallet_rotation', 'yes' ) || count( $addresses ) === 1 ) {
			return $addresses[0];
		}

		$index_key = 'chain_checkout_wallet_idx_' . sanitize_key( $coin_id );
		$index     = (int) get_option( $index_key, 0 );
		$address   = $addresses[ $index % count( $addresses ) ];
		update_option( $index_key, ( $index + 1 ) % count( $addresses ), false );

		return $address;
	}
}
