(function ($) {
	'use strict';

	function selectedCoin() {
		return $('input[name="chain_checkout_coin"]:checked').val() || '';
	}

	function fetchQuote() {
		var coin = selectedCoin();
		var $quote = $('#chain-checkout-quote');
		if (!coin || typeof chainCheckout === 'undefined') {
			return;
		}
		$quote.text('…');
		$.post(chainCheckout.ajaxUrl, {
			action: 'chain_checkout_quote',
			nonce: chainCheckout.nonce,
			coin: coin
		}).done(function (res) {
			if (res && res.success && res.data) {
				$quote.text('≈ ' + res.data.amount + ' ' + res.data.symbol);
			} else {
				$quote.text('');
			}
		}).fail(function () {
			$quote.text('');
		});
	}

	$(document.body).on('change', 'input[name="chain_checkout_coin"]', fetchQuote);
	$(document.body).on('updated_checkout payment_method_selected', function () {
		if ($('input[name="payment_method"]:checked').val() === (chainCheckout && chainCheckout.gateway)) {
			fetchQuote();
		}
	});

	$(function () {
		if ($('input[name="chain_checkout_coin"]:checked').length) {
			fetchQuote();
		}
	});
})(jQuery);
