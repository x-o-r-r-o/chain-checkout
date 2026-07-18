<?php
/**
 * Admin settings UI.
 *
 * @package ChainCheckout
 *
 * @var string $tab
 * @var array  $settings
 * @var array  $groups
 */

defined( 'ABSPATH' ) || exit;

$tabs = array(
	'general' => array(
		'label' => __( 'General', 'chain-checkout' ),
		'url'   => admin_url( 'admin.php?page=chain-checkout' ),
	),
	'coins'   => array(
		'label' => __( 'Coins', 'chain-checkout' ),
		'url'   => admin_url( 'admin.php?page=chain-checkout-coins' ),
	),
	'wallets' => array(
		'label' => __( 'Wallets', 'chain-checkout' ),
		'url'   => admin_url( 'admin.php?page=chain-checkout-wallets' ),
	),
	'prices'  => array(
		'label' => __( 'Prices & APIs', 'chain-checkout' ),
		'url'   => admin_url( 'admin.php?page=chain-checkout-prices' ),
	),
);

$enabled = isset( $settings['enabled_coins'] ) && is_array( $settings['enabled_coins'] ) ? $settings['enabled_coins'] : array();
$wallets = isset( $settings['wallets'] ) && is_array( $settings['wallets'] ) ? $settings['wallets'] : array();
?>
<div class="wrap chain-checkout-admin">
	<h1><?php echo esc_html__( 'Chain Checkout', 'chain-checkout' ); ?></h1>
	<p class="description"><?php echo esc_html__( 'Accept crypto payments directly to your wallets — no third-party payment processor.', 'chain-checkout' ); ?></p>

	<nav class="nav-tab-wrapper wp-clearfix">
		<?php foreach ( $tabs as $key => $item ) : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="">
		<?php wp_nonce_field( 'chain_checkout_save_settings', 'chain_checkout_nonce' ); ?>

		<?php if ( 'general' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Payment window (minutes)', 'chain-checkout' ); ?></th>
					<td>
						<input type="number" min="5" max="1440" name="chain_checkout[payment_window]" value="<?php echo esc_attr( (string) ( $settings['payment_window'] ?? 60 ) ); ?>" class="small-text" />
						<p class="description"><?php esc_html_e( 'Quoted crypto amount is valid for this duration. Default: 60.', 'chain-checkout' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Order status after payment', 'chain-checkout' ); ?></th>
					<td>
						<select name="chain_checkout[order_status]">
							<?php
							$statuses = array(
								'processing' => __( 'Processing', 'chain-checkout' ),
								'completed'  => __( 'Completed', 'chain-checkout' ),
								'on-hold'    => __( 'On Hold', 'chain-checkout' ),
							);
							$current = $settings['order_status'] ?? 'processing';
							foreach ( $statuses as $value => $label ) {
								printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Underpayment tolerance (%)', 'chain-checkout' ); ?></th>
					<td>
						<input type="number" step="0.1" min="0" max="10" name="chain_checkout[underpayment_percent]" value="<?php echo esc_attr( (string) ( $settings['underpayment_percent'] ?? 1 ) ); ?>" class="small-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Unique payment amounts', 'chain-checkout' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="chain_checkout[unique_amounts]" value="yes" <?php checked( ( $settings['unique_amounts'] ?? 'yes' ), 'yes' ); ?> />
							<?php esc_html_e( 'Add a tiny unique dust amount so payments to reused addresses can be matched reliably.', 'chain-checkout' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Wallet rotation', 'chain-checkout' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="chain_checkout[wallet_rotation]" value="yes" <?php checked( ( $settings['wallet_rotation'] ?? 'yes' ), 'yes' ); ?> />
							<?php esc_html_e( 'Rotate through multiple addresses per coin when available.', 'chain-checkout' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Automatic verification', 'chain-checkout' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="chain_checkout[auto_verify]" value="yes" <?php checked( ( $settings['auto_verify'] ?? 'yes' ), 'yes' ); ?> />
							<?php esc_html_e( 'Poll public block explorers / RPCs and mark orders paid when payment is detected.', 'chain-checkout' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="chain-checkout-title"><?php esc_html_e( 'Checkout title', 'chain-checkout' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="chain-checkout-title" name="chain_checkout[title]" value="<?php echo esc_attr( (string) ( $settings['title'] ?? __( 'Pay with Cryptocurrency', 'chain-checkout' ) ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Payment method name shown at checkout (e.g. “Pay with Cryptocurrency”).', 'chain-checkout' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="chain-checkout-description"><?php esc_html_e( 'Checkout description', 'chain-checkout' ); ?></label></th>
					<td>
						<textarea class="large-text" rows="3" id="chain-checkout-description" name="chain_checkout[description]"><?php echo esc_textarea( (string) ( $settings['description'] ?? '' ) ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Checkout label style', 'chain-checkout' ); ?></th>
					<td>
						<?php
						$display = $settings['checkout_display'] ?? 'both';
						foreach ( Chain_Checkout_Branding::display_modes() as $mode => $label ) :
							?>
							<label style="display:inline-block;margin-right:1rem;">
								<input type="radio" name="chain_checkout[checkout_display]" value="<?php echo esc_attr( $mode ); ?>" <?php checked( $display, $mode ); ?> />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Choose how the payment method is identified on the checkout page.', 'chain-checkout' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Checkout icon', 'chain-checkout' ); ?></th>
					<td>
						<?php
						$icon_id  = absint( $settings['checkout_icon_id'] ?? 0 );
						$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : Chain_Checkout_Branding::default_icon_url();
						$iw       = absint( $settings['checkout_icon_width'] ?? 32 );
						$ih       = absint( $settings['checkout_icon_height'] ?? 32 );
						?>
						<div class="chain-checkout-icon-picker" id="chain-checkout-icon-picker">
							<input type="hidden" name="chain_checkout[checkout_icon_id]" id="chain-checkout-icon-id" value="<?php echo esc_attr( (string) $icon_id ); ?>" />
							<div class="chain-checkout-icon-picker__preview">
								<img src="<?php echo esc_url( $icon_url ? $icon_url : Chain_Checkout_Branding::default_icon_url() ); ?>" alt="" id="chain-checkout-icon-preview" width="48" height="48" style="width:48px;height:48px;object-fit:contain;border:1px solid #c3c4c7;border-radius:4px;background:#fff;padding:4px;" />
							</div>
							<p>
								<button type="button" class="button" id="chain-checkout-icon-upload"><?php esc_html_e( 'Upload / replace icon', 'chain-checkout' ); ?></button>
								<button type="button" class="button" id="chain-checkout-icon-reset"><?php esc_html_e( 'Use default icon', 'chain-checkout' ); ?></button>
							</p>
							<p class="description"><?php esc_html_e( 'PNG, JPG, GIF, WebP, or SVG. Default plugin icon is used when none is selected.', 'chain-checkout' ); ?></p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Icon size (px)', 'chain-checkout' ); ?></th>
					<td>
						<label>
							<?php esc_html_e( 'Width', 'chain-checkout' ); ?>
							<input type="number" class="small-text" min="16" max="128" name="chain_checkout[checkout_icon_width]" value="<?php echo esc_attr( (string) $iw ); ?>" />
						</label>
						&nbsp;
						<label>
							<?php esc_html_e( 'Height', 'chain-checkout' ); ?>
							<input type="number" class="small-text" min="16" max="128" name="chain_checkout[checkout_icon_height]" value="<?php echo esc_attr( (string) $ih ); ?>" />
						</label>
						<p class="description"><?php esc_html_e( 'Recommended: 24–40px. Allowed range: 16–128.', 'chain-checkout' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WooCommerce gateway', 'chain-checkout' ); ?></th>
					<td>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=chain_checkout' ) ); ?>">
							<?php esc_html_e( 'Enable gateway in Payments settings', 'chain-checkout' ); ?>
						</a>
					</td>
				</tr>
			</table>

		<?php elseif ( 'coins' === $tab ) : ?>
			<p><?php esc_html_e( 'Enable the coins and networks you want to accept. You must also add at least one wallet address for each enabled coin.', 'chain-checkout' ); ?></p>
			<p class="description"><?php esc_html_e( 'Auto-verify uses public blockchain APIs. Monero (XMR) remains Manual — use “Mark payment received” on the order (privacy coin; needs a view key for automated detection).', 'chain-checkout' ); ?></p>
			<?php
			$sections = array(
				'coins'  => __( 'Coins', 'chain-checkout' ),
				'usdt'   => __( 'USDT (multi-network)', 'chain-checkout' ),
				'usdc'   => __( 'USDC (multi-network)', 'chain-checkout' ),
				'tokens' => __( 'Tokens', 'chain-checkout' ),
			);
			foreach ( $sections as $section_key => $section_label ) :
				if ( empty( $groups[ $section_key ] ) ) {
					continue;
				}
				?>
				<h2><?php echo esc_html( $section_label ); ?></h2>
				<table class="widefat striped chain-checkout-coins-table">
					<thead>
						<tr>
							<th style="width:40px;"><?php esc_html_e( 'On', 'chain-checkout' ); ?></th>
							<th><?php esc_html_e( 'Coin', 'chain-checkout' ); ?></th>
							<th><?php esc_html_e( 'Network', 'chain-checkout' ); ?></th>
							<th><?php esc_html_e( 'Type', 'chain-checkout' ); ?></th>
							<th><?php esc_html_e( 'Auto-verify', 'chain-checkout' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $groups[ $section_key ] as $id => $coin ) : ?>
							<tr>
								<td>
									<input type="checkbox" name="chain_checkout[enabled_coins][]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( $id, $enabled, true ) ); ?> />
								</td>
								<td><strong><?php echo esc_html( $coin['symbol'] ); ?></strong> — <?php echo esc_html( $coin['name'] ); ?></td>
								<td><code><?php echo esc_html( $coin['network'] ); ?></code></td>
								<td><?php echo esc_html( $coin['type'] ); ?></td>
								<td>
									<?php
									echo Chain_Checkout_Coins::supports_auto_verify( $id )
										? esc_html__( 'Yes', 'chain-checkout' )
										: esc_html__( 'Manual', 'chain-checkout' );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

		<?php elseif ( 'wallets' === $tab ) : ?>
			<?php
			include CHAIN_CHECKOUT_PATH . 'includes/admin/views/wallets-ui.php';
			?>

		<?php elseif ( 'prices' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'CoinGecko API key (optional)', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[coingecko_api_key]" value="<?php echo esc_attr( $settings['coingecko_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'Fiat↔crypto rates. Free without a key. Get a key at %s', 'chain-checkout' ),
									'<a href="https://www.coingecko.com/en/api" target="_blank" rel="noopener noreferrer">CoinGecko</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Etherscan API V2 key', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[etherscan_api_key]" value="<?php echo esc_attr( $settings['etherscan_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'One free key covers ETH, BNB, Polygon, Arbitrum, Optimism, Avalanche, Fantom, Cronos, ETC and 50+ EVM chains. Get it at %s', 'chain-checkout' ),
									'<a href="https://etherscan.io/apis" target="_blank" rel="noopener noreferrer">etherscan.io/apis</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'TronGrid API key (optional)', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[trongrid_api_key]" value="<?php echo esc_attr( $settings['trongrid_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'Recommended for TRX / USDT-TRC20 stability. Free at %s', 'chain-checkout' ),
									'<a href="https://www.trongrid.io/" target="_blank" rel="noopener noreferrer">TronGrid</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Helius API key (optional)', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[helius_api_key]" value="<?php echo esc_attr( $settings['helius_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'More stable Solana RPC than the public endpoint. Free tier at %s', 'chain-checkout' ),
									'<a href="https://www.helius.dev/" target="_blank" rel="noopener noreferrer">Helius</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Subscan API key (optional)', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[subscan_api_key]" value="<?php echo esc_attr( $settings['subscan_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'Improves Polkadot (DOT) auto-verify rate limits. Free at %s', 'chain-checkout' ),
									'<a href="https://www.subscan.io/" target="_blank" rel="noopener noreferrer">Subscan</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'ViewBlock API key (optional)', 'chain-checkout' ); ?></th>
					<td>
						<input type="password" class="regular-text" name="chain_checkout[viewblock_api_key]" value="<?php echo esc_attr( $settings['viewblock_api_key'] ?? '' ); ?>" autocomplete="new-password" />
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: URL */
									__( 'Improves Zilliqa (ZIL) auto-verify reliability. Free at %s', 'chain-checkout' ),
									'<a href="https://viewblock.io/api" target="_blank" rel="noopener noreferrer">ViewBlock</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show crypto price on products', 'chain-checkout' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="chain_checkout[price_coin_show]" value="yes" <?php checked( ( $settings['price_coin_show'] ?? 'no' ), 'yes' ); ?> />
							<?php esc_html_e( 'Display an approximate crypto equivalent near product prices.', 'chain-checkout' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Product price coin', 'chain-checkout' ); ?></th>
					<td>
						<select name="chain_checkout[price_coin_ticker]">
							<?php
							$ticker = $settings['price_coin_ticker'] ?? 'BTC';
							foreach ( array( 'BTC', 'ETH', 'USDT_ETH', 'USDC_ETH' ) as $opt ) {
								$c = Chain_Checkout_Coins::get( $opt );
								if ( ! $c ) {
									continue;
								}
								printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $ticker, $opt, false ), esc_html( $c['name'] ) );
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<p class="description">
				<?php esc_html_e( 'Bitcoin uses mempool.space with Blockstream fallback (no key needed). LTC/DOGE use Blockchair. XRP/XLM and most alt chains use public APIs. Monero (XMR) stays manual because inbound detection requires a private view key.', 'chain-checkout' ); ?>
			</p>
		<?php endif; ?>

		<p class="submit">
			<button type="submit" name="chain_checkout_save" class="button button-primary" value="1">
				<?php esc_html_e( 'Save changes', 'chain-checkout' ); ?>
			</button>
		</p>
	</form>
</div>
