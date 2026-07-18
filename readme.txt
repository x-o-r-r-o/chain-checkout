=== Chain Checkout ===
Contributors: chaincheckout
Tags: woocommerce, cryptocurrency, bitcoin, ethereum, payments, usdt, crypto checkout
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept cryptocurrency payments directly to your own wallets — no third-party payment processor.

== Description ==

Chain Checkout is a WooCommerce payment gateway that lets customers pay with cryptocurrency straight to wallets you control. There is no payment processor holding funds, no license key, and no phone-home licensing.

= Features =

* Direct-to-wallet payments (no payment processor holds your funds)
* BTC, ETH, LTC, DOGE, SOL, TRX, XMR, XRP, BNB, MATIC/POL, ARB, OP, and more
* USDT & USDC on multiple networks with separate wallet fields
* Token support (LINK, UNI, CAKE, AVAX, and others) including multi-chain variants
* Coin picker at checkout + payment page with amount, address, and QR code
* 60-minute payment window (configurable)
* Automatic on-chain verification via Etherscan API V2, mempool.space, TronGrid, Helius, AlgoNode, Hedera Mirror, MultiversX, Subscan, and more
* Wallet rotation across multiple addresses
* Unique payment amounts for reliable matching
* Checkout branding: custom title, upload/replace icon, icon width & height, show icon and/or text
* WooCommerce Checkout Blocks + HPOS compatible
* Compatible with WordPress 7.0 and WooCommerce 10.x
* Dedicated admin menu: General, Coins, Wallets, Prices & APIs
* No license keys or phone-home licensing

= Requirements =

* WordPress 6.9+ (tested up to 7.0)
* WooCommerce 10.0+ (tested up to 10.8)
* PHP 7.4+ (8.3+ recommended)
* HTTPS recommended

== Installation ==

1. Upload the `chain-checkout` folder to `/wp-content/plugins/` or install the ZIP via Plugins → Add New → Upload.
2. Activate **Chain Checkout**.
3. Go to **Chain Checkout → Coins** and enable the assets you accept.
4. Go to **Chain Checkout → Wallets** and add receiving addresses (use **+ Add address** for multiple / rotation).
5. Add API keys under **Prices & APIs** (Etherscan V2 recommended; TronGrid/Helius/Subscan/ViewBlock optional).
6. Under **Chain Checkout → General**, set the checkout title, icon, size, and whether to show icon, text, or both.
7. Enable the gateway under **WooCommerce → Settings → Payments → Chain Checkout**.

== Frequently Asked Questions ==

= Does this use a third-party payment processor? =

No. Customers pay your wallet addresses directly. Public APIs are used only for exchange rates and blockchain verification.

= How do I change the checkout icon or title? =

Go to **Chain Checkout → General**. You can edit the title (e.g. “Pay with Cryptocurrency”), upload or reset the icon, set width/height (16–128px), and choose Icon and text, Icon only, or Text only.

= Which free API keys should I add? =

* **Etherscan API V2** — one key for ETH, BNB, Polygon, Arbitrum, Optimism, Avalanche, and other EVM chains
* **CoinGecko** — optional, for higher rate limits on price conversion
* **TronGrid** — optional, for TRX / USDT-TRC20 reliability
* **Helius** — optional, for more stable Solana verification
* **Subscan** — optional, for Polkadot (DOT) rate limits
* **ViewBlock** — optional, for Zilliqa (ZIL) reliability

Bitcoin uses mempool.space (Blockstream fallback) with no key required. ALGO, HBAR, NEAR, ATOM, EGLD, FIL, EOS use free public endpoints. Monero (XMR) stays manual.

= Are private keys stored? =

Never. Only public receiving addresses are stored.

= Will it work with Checkout Blocks? =

Yes. Chain Checkout registers a Blocks payment method and declares cart/checkout blocks compatibility.

= Will it work with my theme? =

Yes. It uses the WooCommerce payment gateway API and scoped CSS classes.

== Screenshots ==

1. Chain Checkout settings — General (payment window, branding, icon size)
2. Coins — enable coins and networks
3. Wallets — multi-address manager per activated coin
4. Checkout — payment method with sized icon and coin picker
5. Thank-you / payment page — amount, address, QR, status

== Changelog ==

= 1.3.1 =
* Fixed plugin headers: Author URI now points to GitHub (must differ from Plugin URI for wordpress.org)

= 1.3.0 =
* Fixed oversized checkout gateway icon (default 32×32, CSS-constrained)
* Added checkout branding: title, description, icon upload/replace/reset, width & height
* Added display mode: icon and text / icon only / text only (classic + Blocks)
* Improved docs (readme.txt + README.md)

= 1.2.4 =
* Fixed Add address with inline wallets script (works even if admin.js cache fails)
* Document-level click handling via data-cc-action attributes

= 1.2.3 =
* Fixed Wallets “+ Add address” button (vanilla JS; no jQuery data-name parsing bug)
* Wallets page lists only coins activated under Coins
* Mobile-friendly wallets UI with clearer cards, validation, and counters
* Admin assets load more reliably on plugin screens

= 1.2.0 =
* Extended auto-verify to ALGO, HBAR, NEAR, ATOM, EGLD, FIL, EOS, DOT, ZIL via free public APIs
* Optional Subscan + ViewBlock API keys for DOT/ZIL reliability
* Monero (XMR) remains manual (requires private view key)

= 1.1.1 =
* Security and reliability fixes: wallet merge on save, atomic txid claim, shared-address guards, Solana ATA lookup, AJAX verify throttling, BCMath amount matching, quote rate limit
* Cleaner uninstall of wallet index / txid claim options

= 1.1.0 =
* Migrated EVM verification to Etherscan API V2 (single key, multi-chain)
* Added mempool.space Bitcoin primary endpoint with Blockstream fallback
* Added optional TronGrid and Helius API key support
* Extended auto-verify to FTM, CRO, and ETC via Etherscan V2
* Declared compatibility with WordPress 7.0 and WooCommerce 10.x
* Simplified Prices & APIs settings UI

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.3.0 =
Adds checkout icon/title branding controls and fixes the oversized checkout icon. Re-save General settings if you customize branding.
