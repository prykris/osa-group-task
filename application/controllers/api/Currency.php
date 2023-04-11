<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Currency extends CI_Controller
{

	/**
	 * These properties are here just to provide IDE some hints where to look for references
	 */

	/** @var Currency_Api */
	public $currency_api;

	/** @var CI_Cache */
	public $cache;

	public function __construct()
	{
		parent::__construct();

		$this->load->config('currency');
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('currency_api', array(
			'apiKey' => config_item('api_key')
		));
	}

	public function index()
	{
		if (!$currencies = $this->cache->get('currencies')) {
			$currencies = $this->currency_api->getCurrencies();

			if ($currencies) {
				$this->cache->save('currencies', $currencies, 60 * 60 * 24);
			}
		}

		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($currencies));
	}

	public function latest()
	{
		$baseCurrency = $this->input->get('baseCurrency');
		$cacheKey = 'currency_latest_' . $baseCurrency;

		if (!$response = $this->cache->get($cacheKey)) {
			$currenciesFromApi = $this->currency_api->getLatest($baseCurrency);

			$this->cache->save($cacheKey, $currenciesFromApi, 60 * 60 * 24);

			$response = $currenciesFromApi;
		}

		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));
	}

}
