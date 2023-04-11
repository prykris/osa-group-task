<?php


class Currency_Api
{

	/**
	 * @var string
	 */
	public $apiKey;

	/**
	 * @var string
	 */
	private $baseUrl;

	public function __construct(array $config)
	{
		assert(!is_string($config['apiKey']), 'Please give me a string for api key parameter');

		$this->apiKey = $config['apiKey'];
		$this->baseUrl = 'https://api.currencyapi.com/v3/';
	}

	/**
	 * @param string $endpoint
	 * @param array $parameters
	 * @return mixed
	 */
	private function request($endpoint, array $parameters = array())
	{
		$url = $this->baseUrl . $endpoint . '?' . http_build_query(array_merge(array(
				'apikey' => $this->apiKey
			), $parameters));

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);  // URL to send request to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as string
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
		curl_setopt($ch, CURLOPT_HTTPGET, true); // Use HTTP GET method
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			$error_message = curl_error($ch);
			exit($error_message);
			// Handle error
		} else {
			$decoded_response = json_decode($response, true);

			if ($decoded_response === null) {
				// JSON parsing error
			} else {
				return $decoded_response;
			}
		}

		curl_close($ch);

		return null;
	}

	/**
	 * @param string $baseCurrency
	 * @param array $currencies = []
	 * @return mixed
	 */
	public function getLatest($baseCurrency = 'USD', array $currencies = null)
	{
		$params = array(
			'base_currency' => $baseCurrency
		);

		if ($currencies) {
			$params['currencies'] = implode(',', $currencies);
		}

		return $this->request('latest', $params);
	}

	/**
	 * @param int $value The value you want to convert
	 * @param $baseCurrency
	 * @param string $date Date to retrieve historical rates from (format: 2021-12-31)
	 * @param array $currencies
	 *
	 * @return array|null
	 */
	public function convertExchangeRates($value, $baseCurrency, $date, array $currencies = array())
	{
		return $this->request('convert', array_filter(array(
			'base_currency' => $baseCurrency,
			'value' => $value,
			'date' => $date,
			'currencies' => implode(',', $currencies)
		)));
	}

	/**
	 * @return array|null
	 */
	public function getCurrencies()
	{
		return $this->request('currencies');
	}

}
