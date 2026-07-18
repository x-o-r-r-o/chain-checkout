<?php
/**
 * Frontend payment instructions — Cryptoniq-inspired paybox layout.
 *
 * @package ChainCheckout
 *
 * @var WC_Order $order
 * @var array    $coin
 * @var string   $address
 * @var string   $amount
 * @var int      $expires
 * @var string   $status
 * @var string   $uri
 * @var string   $coin_id
 */

defined( 'ABSPATH' ) || exit;

$icons = Chain_Checkout_Coins::icon_meta( $coin_id );
?>
<div class="chain-checkout-paybox-layer" id="chain-checkout-box" data-status="<?php echo esc_attr( $status ); ?>">
	<div class="chain-checkout-paybox">
		<div class="chain-checkout-paybox__topbar">
			<div class="chain-checkout-paybox__heading">
				<?php if ( ! empty( $icons['icon'] ) ) : ?>
					<span class="chain-checkout-paybox__coin-icon" style="background-image:url('<?php echo esc_url( $icons['icon'] ); ?>')" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="chain-checkout-paybox__heading-pre"><?php esc_html_e( 'Payment', 'chain-checkout' ); ?></span>
				<span class="chain-checkout-paybox__heading-sep">:</span>
				<span class="chain-checkout-paybox__heading-coin"><?php echo esc_html( $coin['name'] ); ?></span>
			</div>
		</div>

		<div class="chain-checkout-paybox__body">
			<?php if ( 'paid' === $status ) : ?>
				<p class="chain-checkout-paybox__message chain-checkout-paybox__message--success"><?php esc_html_e( 'Payment confirmed. Thank you!', 'chain-checkout' ); ?></p>
			<?php elseif ( 'expired' === $status ) : ?>
				<p class="chain-checkout-paybox__message chain-checkout-paybox__message--error"><?php esc_html_e( 'Payment window expired. Please place a new order.', 'chain-checkout' ); ?></p>
			<?php else : ?>
				<div class="chain-checkout-paybox__section-title">
					<span><?php esc_html_e( 'How to pay', 'chain-checkout' ); ?></span>
					<button type="button" class="chain-checkout-paybox__help" id="chain-checkout-help-toggle" aria-expanded="false" aria-controls="chain-checkout-instructions" title="<?php esc_attr_e( 'Instructions', 'chain-checkout' ); ?>">?</button>
				</div>

				<div class="chain-checkout-paybox__data">
					<ul class="chain-checkout-paybox__details">
						<li>
							<div class="chain-checkout-paybox__label">
								<?php esc_html_e( 'Please, send', 'chain-checkout' ); ?>
								<button type="button" class="chain-checkout-copy chain-checkout-paybox__copy" data-copy="#chain-checkout-amount" aria-label="<?php esc_attr_e( 'Copy amount', 'chain-checkout' ); ?>"></button>
							</div>
							<div class="chain-checkout-paybox__value-row">
								<span class="chain-checkout-paybox__mask">
									<?php echo esc_html( $coin['symbol'] ); ?>:
									<span id="chain-checkout-amount"><?php echo esc_html( $amount ); ?></span>
								</span>
								<span class="chain-checkout-paybox__fiat"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
							</div>
						</li>
						<li>
							<div class="chain-checkout-paybox__label">
								<?php esc_html_e( 'To this address', 'chain-checkout' ); ?>
								<button type="button" class="chain-checkout-copy chain-checkout-paybox__copy" data-copy="#chain-checkout-address" aria-label="<?php esc_attr_e( 'Copy address', 'chain-checkout' ); ?>"></button>
							</div>
							<div class="chain-checkout-paybox__value-row chain-checkout-paybox__value-row--address">
								<span id="chain-checkout-address" class="chain-checkout-paybox__mask chain-checkout-paybox__mask--address"><?php echo esc_html( $address ); ?></span>
							</div>
						</li>
						<li>
							<div class="chain-checkout-paybox__label"><?php esc_html_e( 'Network', 'chain-checkout' ); ?></div>
							<div class="chain-checkout-paybox__value-row">
								<span class="chain-checkout-paybox__mask"><?php echo esc_html( $coin['network'] . ' · ' . $coin['type'] ); ?></span>
							</div>
						</li>
						<li>
							<div class="chain-checkout-paybox__label"><?php esc_html_e( 'Scan QR code', 'chain-checkout' ); ?></div>
							<div id="chain-checkout-qrcode" class="chain-checkout-paybox__qrcode" aria-hidden="true"></div>
							<?php if ( ! empty( $uri ) && $uri !== $address ) : ?>
								<p class="chain-checkout-paybox__uri">
									<button type="button" class="chain-checkout-copy chain-checkout-paybox__link" data-copy-text="<?php echo esc_attr( $uri ); ?>">
										<?php esc_html_e( 'Copy payment link', 'chain-checkout' ); ?>
									</button>
								</p>
							<?php endif; ?>
						</li>
					</ul>

					<div class="chain-checkout-paybox__instructions" id="chain-checkout-instructions" hidden>
						<div class="chain-checkout-paybox__instructions-inner">
							<div class="chain-checkout-paybox__instructions-top">
								<strong><?php esc_html_e( 'Instruction', 'chain-checkout' ); ?></strong>
								<button type="button" class="chain-checkout-paybox__instructions-close" id="chain-checkout-help-close" aria-label="<?php esc_attr_e( 'Close', 'chain-checkout' ); ?>"></button>
							</div>
							<p><?php esc_html_e( 'Send exactly the required amount in one transaction (network fees are extra). Use the matching network shown above.', 'chain-checkout' ); ?></p>
							<p><?php esc_html_e( 'This page checks the blockchain automatically. If coins are not received before the timer ends, the payment window expires and you will need to place a new order.', 'chain-checkout' ); ?></p>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( 'paid' !== $status && 'expired' !== $status ) : ?>
			<div class="chain-checkout-paybox__bottombar" id="chain-checkout-status-bar">
				<span class="chain-checkout-paybox__loader" id="chain-checkout-loader" aria-hidden="true"></span>
				<span class="chain-checkout-paybox__alert" id="chain-checkout-status-text"><?php esc_html_e( 'Checking…', 'chain-checkout' ); ?></span>
				<span class="chain-checkout-paybox__time-label"><?php esc_html_e( 'Time:', 'chain-checkout' ); ?></span>
				<span class="chain-checkout-paybox__timer" id="chain-checkout-timer">00:00:00</span>
			</div>
			<input type="hidden" id="chain-checkout-order-key" value="<?php echo esc_attr( $order->get_order_key() ); ?>" />
		<?php elseif ( 'paid' === $status ) : ?>
			<div class="chain-checkout-paybox__bottombar chain-checkout-paybox__bottombar--success">
				<span class="chain-checkout-paybox__alert"><?php esc_html_e( 'Payment confirmed!', 'chain-checkout' ); ?></span>
			</div>
		<?php else : ?>
			<div class="chain-checkout-paybox__bottombar chain-checkout-paybox__bottombar--failed">
				<span class="chain-checkout-paybox__alert"><?php esc_html_e( 'Payment expired', 'chain-checkout' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>
