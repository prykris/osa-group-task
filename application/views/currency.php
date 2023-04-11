<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!--jquery-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

	<!-- BS5.1.1 CSS/JS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>

	<!-- Latest BS-Select compiled and minified CSS/JS -->
	<link rel="stylesheet"
		  href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

	<title>Currencies of the World</title>
</head>
<body>
<div class="container">
	<div class="row d-flex justify-content-center">
		<div class="card col mt-5">
			<div class="card-body">
				<form action="#" class="row g-3">
					<div class="col">
						<div class="input-group w-100">
							<span class="input-group-text d-none" id="amount-from-currency"></span>
							<input type="text" name="amount-from" class="form-control" value="1" aria-label="Username"
								   aria-describedby="amount-from-currency">
						</div>
					</div>
					<div class="col">
						<label class="w-100">
							<select id="currency-selector-from" name="currency"
									class="form-control currency-selector" data-live-search="true"></select>
						</label>
					</div>
					<div class="col-auto d-flex justify-content-center align-items-center h4">
						>
					</div>
					<div class="col">
						<div class="input-group w-100">
							<span class="input-group-text d-none" id="amount-to-currency"></span>
							<input type="text" name="amount-to" class="form-control" value="1" aria-label="Username"
								   aria-describedby="amount-to-currency">
						</div>
					</div>
					<div class="col">
						<label class="w-100">
							<select id="currency-selector-to" name="amount-to"
									class="form-control currency-selector" data-live-search="true"></select>
						</label>
					</div>
				</form>
				<div class="row mt-1">
					<small>Rate: <span id="rate-text"></span></small>
				</div>
			</div>
		</div>
	</div>
	<div class="row d-flex justify-content-center">
		<div class="card col mt-1">
			<div class="card-body">
				<table class="table table-striped table-small">
					<thead>
					<tr>
						<th>Currency</th>
						<th>Value</th>
					</tr>
					</thead>
					<tbody>
					<?php if (isset($currencyData)) {
						foreach ($currencyData['data'] as $currency) { ?>
							<tr>
								<td><?= $currency['code'] ?></td>
								<td><?= $currency['value'] ?></td>
							</tr>
						<?php }
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	const DEFAULT_CURRENCY_FROM = 'EUR';
	const DEFAULT_CURRENCY_TO = 'USD';

	let currentConversionRate;

	let rates = [];
	let currencies = [];

	$(function () {
		fetchCurrencyRates(DEFAULT_CURRENCY_FROM).then((response) => {
			initCurrencySelectors(response.data);
			initAmountInputs();

			fetchCurrencyInformation().then((response) => {
				currencies = response.data;

				updateCurrencySymbols();
			});
		}).catch((error) => {
			alert(error);
		});
	});

	function updateCurrencySymbols() {
		if (currencies.length === 0) {
			return;
		}

		const symbolSpanFrom = $('#amount-from-currency');
		const symbolSpanTo = $('#amount-to-currency');
		const fromSelector = $('#currency-selector-from');
		const toSelector = $('#currency-selector-to');

		let symbolFrom = currencies[fromSelector.val()].symbol_native;
		let symbolTo = currencies[toSelector.val()].symbol_native;

		symbolSpanFrom.html(symbolFrom);
		symbolSpanTo.html(symbolTo);

		symbolSpanFrom.removeClass('d-none');
		symbolSpanTo.removeClass('d-none');
	}

	function setConversionRate(rate) {
		currentConversionRate = rate;

		$('#rate-text').html(rate);

		updateCurrencySymbols();
	}

	function performConversion(triggerElement) {
		const amountFromEl = $('input[name="amount-from"]');
		const amountToEl = $('input[name="amount-to"]');

		const amountFrom = parseFloat(amountFromEl.val());
		let amountTo = (amountFrom * currentConversionRate).toFixed(2);

		amountToEl.val(amountTo);
	}

	function initAmountInputs() {
		const amountInputs = $('input[name="amount-from"], input[name="amount-to"]');

		amountInputs.on('keypress', function (e) {
			let keyCode = e.which;

			if (keyCode !== 46 && keyCode > 31 && (keyCode < 48 || keyCode > 57)) {
				e.preventDefault();
				return;
			}

			if (keyCode === 46 && $(this).val().indexOf('.') !== -1) {
				e.preventDefault();
				return;
			}

			performConversion($(this));
		});
	}

	function populateTable(data) {
		$('table > tbody > tr').remove();

		Object.values(data).forEach((currency) => {
			$('table > tbody').append(
				$(`
					<tr id="table-row-${currency.code.toLowerCase()}">
						<td>${currency.code}</td>
						<td>${currency.value}</td>
					</tr>`)
			);
		})
	}

	function initCurrencySelectors(currencies) {
		const fromSelector = $('#currency-selector-from');
		const toSelector = $('#currency-selector-to');

		Object.values(currencies).forEach((currency) => {
			$('.currency-selector').append(`<option value="${currency.code}">${currency.code}</option>`);
		});

		fromSelector.val(DEFAULT_CURRENCY_FROM);
		toSelector.val(DEFAULT_CURRENCY_TO);

		fromSelector.change(function () {
			fetchCurrencyRates($(this).val()).then((response) => {
				setConversionRate(response.data[toSelector.val()].value);
				populateTable(response.data);


				performConversion();
			});
		}).trigger('change');

		toSelector.change(function () {
			setConversionRate(currencies[$(this).val()].value);
			populateTable(response.data);

			performConversion();
		});

		fromSelector.selectpicker();
		toSelector.selectpicker();
	}

	function fetchCurrencyRates(baseCurrency) {
		return new Promise((resolve, reject) => $.ajax('/api/currency/latest', {
			dataType: 'json',
			data: {
				baseCurrency
			},
			success: (response) => {
				rates = response.data;

				resolve(response);
			},
			error: (error) => {
				console.error('There was an error with API request');

				reject(error);
			},
		}));
	}

	function fetchCurrencyInformation() {
		return new Promise((resolve, reject) => $.ajax('/api/currency/index', {
			dataType: 'json',
			success: resolve,
			error: reject
		}));
	}
</script>
</html>
