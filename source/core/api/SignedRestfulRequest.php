<?php

namespace core\api;

/**
 * Abstract class to provide simple request significvation
 * 
 * @author lokkie
 * @package simpleframe.core.api
 **/
abstract class SignedRestfulRequest extends RestfulRequest {
	/**
	 * @var string
	 **/
	protected $privateKey = '';
	/**
	 * @var string 
	 **/
	protected $publicKey = '';
	/**
	 * @var string
	 **/
	protected $signatureField = '';
	
	/**
	 * Keys initializer
	 * 
	 * @param string|null $publicKey
	 * @param string|null $privateKey
	 * @param string $signatureField
	 * 
	 * @return \core\api\SignedRestfulRequest
	 **/
	public function setKeys($publicKey, $privateKey, $signatureField = 'sig') {
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
		$this->signatureField = $signatureField;
		return $this;
	}
	
	/**
	 * Signes request before sending. Should be implemented in your project
	 * 
	 * @param array $body
	 * @return string
	 **/
	abstract protected function signBody($body);
	
	/**
	 * Sends request to server
	 * @param string $bodyType
	 * @return \core\api\SignedRestfulRequest
	 * @throws \CURLException
	 */
	public function request($bodyType = 'json') {
		$signature = $this->signBody($this->body);
		if ( 
			!empty($this->signatureField) 
			&& !empty($this->body) 
			&& !empty($signature) 
		) {
			parent::request($bodyType);
		}
		
		return $this;
	}
}