<?php

class ExceptionClass {
	protected $counter;
	
	public function __construct() {
		$this->counter = 0;
	}
	
	
	public function runWithException($movement = 1) {
		$this->counter += $movement;
		throw new \Exception('Let\'s roll');
	}
	
	public function runNoException($movement = 1) {
		$this->counter += $movement;
	}
	
	public function runWithRuntimeException($movement = 1) {
		$this->counter += $movement;
		throw new \RuntimeException('Yahoo');
	}
	
	public function getCounter() {
		return $this->counter;
	}
	
	public function reset() {
		$this->counter = 0;
	}
}