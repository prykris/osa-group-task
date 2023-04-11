<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
		  integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

	<!-- JQUERY 3.6.4 -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"
			integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ=="
			crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<title>Currencies of the World</title>
</head>
<body>
<div class="container">
	<div class="row d-flex justify-content-center">
		<div class="card col mt-5">
			<div class="card-body">
				<form action="#" class="row g-3">
					<div class="col">
						<label class="w-100">
							<input type="text" name="amount-from" class="form-control" value="1">
						</label>
					</div>
					<div class="col">
						<label class="w-100">
							<select id="currency-selector-from" name="currency"
									class="form-control currency-selector"></select>
						</label>
					</div>
					<div class="col-auto d-flex justify-content-center align-items-center h4">
						>
					</div>
					<div class="col">
						<label class="w-100">
							<input type="text" name="amount-to" class="form-control">
						</label>
					</div>
					<div class="col">
						<label class="w-100">
							<select id="currency-selector-to" name="amount-to"
									class="form-control currency-selector"></select>
						</label>
					</div>
				</form>
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

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
		crossorigin="anonymous"></script>
</body>

<script>
	const DEFAULT_CURRENCY_FROM = 'EUR';
	const DEFAULT_CURRENCY_TO = 'USD';

	let currentConversionRate;

	let currencies = [];

	$(function () {
		fetchCurrencyRates(DEFAULT_CURRENCY_FROM).then((response) => {
			initCurrencySelectors(response.data);
			initAmountInputs();
		}).catch((error) => {
			alert(error);
		});
	});

	function setConversionRate(rate) {
		currentConversionRate = rate;

		console.log({currentConversionRate});
	}

	function performConversion(triggerElement) {
		const amountFromEl = $('input[name="amount-from"]');
		const amountToEl = $('input[name="amount-to"]');

		const amountFrom = parseFloat(amountFromEl.val());
		let amountTo = (amountFrom * currentConversionRate).toFixed(2);

		amountToEl.val(amountTo);

		console.log({amountFrom, amountTo, currentConversionRate})
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

				console.log('One ' + fromSelector.val() + ' is equal to ' + response.data[toSelector.val()].value + ' ' + toSelector.val())

				performConversion();
			});
		}).trigger('change');

		toSelector.change(function () {
			setConversionRate(currencies[$(this).val()].value);

			console.log('One ' + fromSelector.val() + ' is equal to ' + currencies[toSelector.val()].value + ' ' + toSelector.val())


			performConversion();
		});
	}

	function fetchCurrencyRates(baseCurrency) {
		return new Promise((resolve, reject) => $.ajax('/api/currency/latest', {
			dataType: 'json',
			data: {
				baseCurrency
			},
			success: (response) => {
				currencies = response.data;

				resolve(response);
			},
			error: (error) => {
				console.error('There was an error with API request');

				reject(error);
			},
		}));
	}

	function fetchCurrencyInformation() {
		$.ajax('/api/currency/index', {
			dataType: 'json',
			success: (response) => console.log(response),
		});
	}
</script>
</html>
