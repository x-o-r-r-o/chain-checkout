(function () {
	'use strict';

	if (typeof chainCheckoutData === 'undefined') {
		return;
	}

	var data = chainCheckoutData;
	var timerEl = document.getElementById('chain-checkout-timer');
	var statusEl = document.getElementById('chain-checkout-status-text');
	var box = document.getElementById('chain-checkout-box');
	var pollTimer = null;

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function updateTimer() {
		if (!timerEl || !data.expires) {
			return;
		}
		var left = data.expires - Math.floor(Date.now() / 1000);
		if (left <= 0) {
			timerEl.textContent = data.i18n.expired;
			if (statusEl) {
				statusEl.textContent = data.i18n.expired;
			}
			return;
		}
		var m = Math.floor(left / 60);
		var s = left % 60;
		timerEl.textContent = pad(m) + ':' + pad(s);
	}

	function copyText(selector) {
		var el = document.querySelector(selector);
		if (!el) {
			return;
		}
		var text = el.textContent.trim();
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text);
		} else {
			var ta = document.createElement('textarea');
			ta.value = text;
			document.body.appendChild(ta);
			ta.select();
			try {
				document.execCommand('copy');
			} catch (e) {
				/* ignore */
			}
			document.body.removeChild(ta);
		}
	}

	document.querySelectorAll('.chain-checkout-copy').forEach(function (btn) {
		btn.addEventListener('click', function () {
			copyText(btn.getAttribute('data-copy'));
			var original = btn.textContent;
			btn.textContent = data.i18n.copied;
			setTimeout(function () {
				btn.textContent = original;
			}, 1500);
		});
	});

	function renderQr() {
		var host = document.getElementById('chain-checkout-qrcode');
		if (!host || !data.qrValue) {
			return;
		}
		host.innerHTML = '';
		if (typeof QRCode !== 'undefined') {
			new QRCode(host, {
				text: data.qrValue,
				width: 160,
				height: 160,
				correctLevel: QRCode.CorrectLevel.M
			});
		}
	}

	function pollStatus() {
		if (data.status === 'paid' || data.status === 'expired') {
			return;
		}
		if (statusEl) {
			statusEl.textContent = data.i18n.checking;
		}
		var body = new FormData();
		body.append('action', 'chain_checkout_status');
		body.append('nonce', data.nonce);
		body.append('order_id', String(data.orderId));
		var keyEl = document.getElementById('chain-checkout-order-key');
		if (keyEl) {
			body.append('order_key', keyEl.value);
		}

		fetch(data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (res) {
				if (!res || !res.success || !res.data) {
					if (statusEl) {
						statusEl.textContent = 'Waiting for payment…';
					}
					return;
				}
				data.status = res.data.status;
				if (box) {
					box.setAttribute('data-status', data.status);
				}
				if (res.data.paid) {
					if (statusEl) {
						statusEl.textContent = data.i18n.paid;
					}
					if (pollTimer) {
						clearInterval(pollTimer);
					}
					window.setTimeout(function () {
						window.location.reload();
					}, 1200);
					return;
				}
				if (res.data.expired) {
					if (statusEl) {
						statusEl.textContent = data.i18n.expired;
					}
					if (pollTimer) {
						clearInterval(pollTimer);
					}
					return;
				}
				if (statusEl) {
					statusEl.textContent = 'Waiting for payment…';
				}
			})
			.catch(function () {
				if (statusEl) {
					statusEl.textContent = 'Waiting for payment…';
				}
			});
	}

	renderQr();
	updateTimer();
	window.setInterval(updateTimer, 1000);

	if (data.status === 'awaiting') {
		pollTimer = window.setInterval(pollStatus, 20000);
		window.setTimeout(pollStatus, 5000);
	}
})();
