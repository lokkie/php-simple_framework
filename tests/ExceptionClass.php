<?php

class ExceptionClass {
	protected $counter;
	
	public function __construct() {
		$this->counter = 0;
	}
	
	
	public function runWithException($movement = 1) {
		$this->counter += $movement;
		throw new Exception('Let\'s roll');
	}
	
	public function runNoException($movement = 1) {
		$this->counter += $movement;
	}
	
	public function getCounter() {
		return $this->counter;
	}
}