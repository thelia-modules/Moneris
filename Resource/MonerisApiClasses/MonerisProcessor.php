<?php
/**
 * A much less awful way to access the Moneris API.
 * ------------------------------------------------
 * Copyright (C) 2012 Keith Silgard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace Moneris\Resource\MonerisApiClasses;

use Moneris\Resource\MonerisApi;

class MonerisProcessor
{
	/**
	 * API config variables pulled from the terrible Moneris API.
	 * @var array
	 */
	static protected $_config = array(
		'protocol' => 'https',
		'host' => 'esqa.moneris.com',
		'port' => '443',
		'url' => '/gateway2/servlet/MpgRequest',
		'api_version' =>'PHP - 2.5.1',
		'timeout' => '60'
	);

	/**
	 * @var string
	 */
	static protected $_error_response =
        "<?xml version=\"1.0\"?>
        <response>
        <receipt>
        <ReceiptId>Global Error Receipt</ReceiptId>
        <ReferenceNum>null</ReferenceNum>
        <ResponseCode>null</ResponseCode>
        <ISO>null</ISO>
        <AuthCode>null</AuthCode>
        <TransTime>null</TransTime>
        <TransDate>null</TransDate>
        <TransType>null</TransType>
        <Complete>false</Complete>
        <Message>null</Message>
        <TransAmount>null</TransAmount>
        <CardType>null</CardType>
        <TransID>null</TransID>
        <TimedOut>null</TimedOut>
        </receipt>
        </response>";

	/**
	 * Get the API config.
	 *
	 * @param string $environment
	 * @return array
	 */
	static public function config($environment)
	{
		if ($environment != MonerisApi::ENV_LIVE) {
			self::$_config['host'] = 'esqa.moneris.com';
		} else {
			self::$_config['host'] = 'www3.moneris.com';
		}
		return self::$_config;
	}

	/**
	 * Do the necessary magic to process this transaction via the Moneris API.
	 *
	 * @param MonerisTransaction $transaction
	 * @return MonerisResult
	 */
	static public function process(MonerisTransaction $transaction)
	{
		if (! $transaction->is_valid()) {
			$result = new MonerisResult($transaction);
			$result->was_successful(false);
			$result->error_code(MonerisResult::ERROR_INVALID_POST_DATA);
			return $result;
		}

		$response = self::_call_api($transaction);
		return $transaction->validate_response($response);
	}

	/**
	 * Do the curl call to process the API request.
	 *
	 * @param MonerisTransaction $transaction
	 * @return SimpleXMLElement
	 */
	static protected function _call_api(MonerisTransaction $transaction)
	{
		$gateway = $transaction->gateway();
		$config = self::config($gateway->environment());
		$params = $transaction->params();
		// frig... this MPI stuff is leaking gross code everywhere... needs to be refactored
		if (in_array($params['type'], array('txn', 'acs')))
			$config['url'] = '/mpi/servlet/MpiServlet';

		$url = $config['protocol'] . '://' .
			   $config['host'] . ':' .
			   $config['port'] .
			   $config['url'];

		$xml = str_replace(' </', '</', $transaction->to_xml());

		//var_dump($url, $xml);

		// this is pulled directly from mpgClasses.php
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);
		curl_setopt($ch, CURLOPT_USERAGENT, $config['api_version']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/../curl-ca-bundle.crt');

		$response = curl_exec($ch);
		curl_close($ch);

		// if the response fails for any reason, just use some stock XML
		// also taken directly from mpgClasses:
		if (! $response) {
			return simplexml_load_string(self::$_error_response);
		}

		$xml = @simplexml_load_string($response);

		// they sometimes return HTML formatted Apache errors... NICE.
		if ($xml === false) {
			return simplexml_load_string(self::$_error_response);
		}
		// force fail AVS for testing
		//$xml->receipt->AvsResultCode = 'N';

		// force fail CVD for testing
		//$xml->receipt->CvdResultCode = '1N';

		//var_dump($xml);

		return $xml;

	}
}
