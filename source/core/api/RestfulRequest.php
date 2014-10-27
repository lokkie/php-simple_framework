<?php

namespace core\api;

/**
 * Generic Restful API Request implementation
 *
 * @category Network
 * @author Lokkie (A.Rusakevich)
 */
class RestfulRequest {

	/**
	 * @var string
	 */
	private $scheme = 'http';
	/**
	 * @var string
	 */
	private $host;
	/**
	 * @var int
	 */
	private $port = 80;
	/**
	 * @var string
	 */
	private $user;
	/**
	 * @var string
	 */
	private $pass;
	/**
	 * @var string
	 */
	private $path;
	/**
	 * @var string
	 */
	private $query;
	/**
	 * @var string
	 */
	private $fragment;

	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var boolean
	 */
	private $isSSL;
	/**
	 * @var array|object|string
	 */
	protected $body;
	/**
	 * @var string
	 */
	private $encoding;
	/**
	 * @var array
	 */
	private $headers = [];
	/**
	 * @var bool
	 */
	private $sslVerifyHost = false;
	/**
	 * @var bool
	 */
	private $sslVerifyPeer = false;
	/**
	 * @var int
	 */
	private $timeout = 10;
	/**
	 * @var array
	 */
	private $arrayResponse = [];
	/**
	 * @var \SimpleXMLElement|null
	 */
	private $objectResponse = null;
	/**
	 * @var string
	 */
	private $rawResponse = '';
	/**
	 * @var string
	 */
	private $error = '';
	/**
	 * @var int
	 */
	private $errorNumber = 0;
	/**
	 * @var int
	 */
	private $responseCode = 200;


	function __construct($host = 'localhost', $path = null, $method = 'GET', $isSSL = null) {
		$host = rtrim('/', $host);
		$host_parts = parse_url($host);

		if ( $isSSL === null && isset($host_parts['scheme']) ) {
			$isSSL = $host_parts['scheme'] == 'https';
		}

		foreach ( $host_parts as $part => $value ) {
			if ( !isset($$part) && property_exists($this, $part) ) {
				$this->{$part} = $$part = $value;
			}
		}

		$this->host = $host;
		$this->path = $path;
		$this->method = $method;
		$this->isSSL = $isSSL !== null && $isSSL;
	}

	/**
	 * @param string $encoding
	 *
	 * @return \API\RestfulRequest
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * @param string $fragment
	 *
	 * @return \API\RestfulRequest
	 */
	public function setFragment($fragment)
	{
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * @param string $host
	 *
	 * @return \API\RestfulRequest
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @param boolean $isSSL
	 *
	 * @return \API\RestfulRequest
	 */
	public function setIsSSL($isSSL)
	{
		$this->isSSL = $isSSL;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsSSL()
	{
		return $this->isSSL;
	}

	/**
	 * @param string $method
	 *
	 * @return \API\RestfulRequest
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param string $pass
	 *
	 * @return \API\RestfulRequest
	 */
	public function setPass($pass)
	{
		$this->pass = $pass;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * @param string $path
	 *
	 * @return \API\RestfulRequest
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param int $port
	 *
	 * @return \API\RestfulRequest
	 */
	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @param string $query
	 *
	 * @return \API\RestfulRequest
	 */
	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @param string $scheme
	 *
	 * @return \API\RestfulRequest
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * @param string $user
	 *
	 * @return \API\RestfulRequest
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $body
	 *
	 * @return \API\RestfulRequest
	 */
	public function setBody($body)
	{
		if ( is_object($body) ) {
			$body = json_encode($body);
		}

		if ( is_string($body) ) {
			if ( false !== ($b = json_decode($body, true)) ) {
				$body = $b;
			} else {
				if ( strpos($body, '=') !== false ) {
					parse_str($body, $body);
				} else {
					$body = [];
				}
			}
		}
		$this->body = $body;
		return $this;
	}

	/**
	 * @param string $headerKey
	 * @param string|null $headerValue
	 *
	 * @return \API\RestfulRequest
	 */
	public function setHeader($headerKey, $headerValue = null) {
		if ( $headerValue === null ) {
			if ( isset($this->headers[$headerKey]) ) {
				unset($this->headers[$headerKey]);
			}
		} else {
			$this->headers[$headerKey] = $headerValue;
		}
		return $this;
	}

	/**
	 * @param boolean $sslVerifyHost
	 *
	 * @return \API\RestfulRequest
	 */
	public function setSslVerifyHost($sslVerifyHost)
	{
		$this->sslVerifyHost = $sslVerifyHost;
		return $this;
	}

	/**
	 * @param boolean $sslVerifyPeer
	 *
	 * @return \API\RestfulRequest
	 */
	public function setSslVerifyPeer($sslVerifyPeer)
	{
		$this->sslVerifyPeer = $sslVerifyPeer;
		return $this;
	}

	/**
	 * @param int $timeout
	 *
	 * @return \API\RestfulRequest
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getURI() {
		$uri = '';
		if ( isset($this->scheme) ) {
			$uri .= "{$this->scheme}://";
		}

		if ( isset($this->user) ) {
			$uri .= $this->user . (isset($this->pass) ? ":{$this->pass}" : '') . '@';
		}

		$uri .= $this->host;

		if ( isset($this->port) ) {
			$uri .= ":{$this->port}";
		}
		$uri .= $this->path;
		if ( isset($this->query) ) {
			$uri .= "?{$this->query}";
			if ( $this->method == 'GET' && !empty($this->body) ) {
				$uri .= (empty($this->query) ? '' :  '&') . $this->getUrlEncodedBody();
			}
		}
		if ( isset($this->fragment) ) {
			$uri .= "#{$this->fragment}";
		}

		return $uri;
	}

	/**
	 * Sends request to server
	 * @param string $bodyType
	 * @return \API\RestfulRequest
	 * @throws \CURLException
	 */
	public function request($bodyType = 'json') {
		if ( !method_exists($this, 'get' . ucfirst($bodyType) . 'EncodedBody') ) {
			throw new \CURLException('Unknown body type');
		} else {
			$body = $this->{'get' . ucfirst($bodyType) . 'EncodedBody'}();
		}

		$connection = curl_init($this->getURI());
		curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $this->method);
		curl_setopt($connection, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($connection, CURLOPT_ENCODING, $this->encoding);
		if ( $this->method == 'POST' || $this->method == 'PUT' ) {
			curl_setopt($connection, CURLOPT_POSTFIELDS, $body);
		}
		$headers = $this->headers;
		array_walk($headers, function(&$v, $k) {$v = $k.': '.$v;});
		curl_setopt($connection, CURLOPT_HTTPHEADER, array_values($headers));
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, $this->$sslVerifyHost);

		$result = curl_exec($connection);
		$this->error = curl_error($connection);
		$this->errorNumber = curl_errno($connection);
		$this->responseCode = curl_getinfo($connection, CURLINFO_HTTP_CODE);
		curl_close($connection);

		$this->acceptResponse($result);
		if (!empty($curlError)) throw new \CURLException($this->error, $this->errorNumber);

		return $this;
	}

	/**
	 * Returns text response
	 * @return string
	 */
	public function getRawResponse()
	{
		return $this->rawResponse;
	}

	/**
	 * Returns response in object representation
	 * @return null|\SimpleXMLElement
	 */
	public function getObjectResponse()
	{
		return $this->objectResponse;
	}

	/**
	 * Return response in array representation
	 * @return array
	 */
	public function getArrayResponse()
	{
		return $this->arrayResponse;
	}

	/**
	 * Returns last connection error
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns last connection error code
	 *
	 * @return int
	 */
	public function getErrorNumber()
	{
		return $this->errorNumber;
	}

	/**
	 * Returns HTTP response code
	 *
	 * @return int
	 */
	public function getResponseCode()
	{
		return $this->responseCode;
	}

	/**
	 * Applies and parses response
	 *
	 * @param string $response
	 */
	private function acceptResponse($response) {
		$this->arrayResponse = [];
		$this->objectResponse = null;
		$this->rawResponse = $response;
		if ( $response !== null || $response !== false ) {
			if (null !== ($responseArray = json_decode($response, true)) ) { // JSON
				$this->arrayResponse = $responseArray;
				$this->objectResponse = json_decode($response);
			} else { // XML
				try {
					$this->objectResponse = new \SimpleXMLElement($response);
					$this->arrayResponse = json_decode(json_encode($this->objectResponse), true);
				} catch (\Exception $error ) {
					$arr = [];
					parse_str($response, $arr);
					if ( !empty($arr) ) {
						$this->arrayResponse = $arr;
						$this->objectResponse = json_decode(json_encode($arr));
					}
				}
			}
		} else {
			$this->rawResponse = '';
		}
	}

	/**
	 * @return string
	 */
	private function getUrlEncodedBody() {
		$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		return http_build_query($this->body);
	}

	/**
	 * @return string
	 */
	private function getJsonEncodedBody() {
		$this->setHeader('Content-Type', 'application/json');
		return json_encode($this->body);
	}

	/**
	 * @return string
	 */
	private function getXMLBody() {
		$this->setHeader('Content-Type', 'text/xml');
		$xmlBody = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><root></root>');
		$this->arrayIntoXml($xmlBody, $this->body);
		$body = $xmlBody->asXML();
		return $body === false ? "" : $body;
	}

	/**
	 * Converts array into SimpleXML structure
	 * @param \SimpleXMLElement $xml
	 * @param array $arr
	 * @param string $rootKey
	 */
	private function arrayIntoXml(\SimpleXMLElement &$xml, $arr, $rootKey = 'root') {
		foreach ($arr as $key => $value) {
			if ( is_array($value) ) {
				if ( is_numeric($key) ) {
					$node = $xml->addChild(rtrim($rootKey, 's'));
					$this->arrayIntoXml($node, $value, $rootKey);
				} else {
					$node = $xml->addChild($key);
					$this->arrayIntoXml($node, $value, $key);
				}
			} else {
				$xml->addChild(is_numeric($key) ? $rootKey : $key, htmlspecialchars($value));
			}
		}
	}
}