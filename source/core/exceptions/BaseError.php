<?php

namespace core\exceptions;


class BaseError {
	
	protected $reason;
	protected $code;
	protected $backTrace;
	
	public function __construct($resonn, $code) {
		
	}
}