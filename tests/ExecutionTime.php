<?php

class ExecutionTime {
	
	protected $started;
	
	public function __construct() {
		$this->stopped = $this->started = 0;
	}
	
	public function startWatching() {
		$this->stopped = $this->started = microtime(true);
	}
	
	public function stopWatching() {
		$this->stopped = microtime(true);
	}
	
	public function getTimeMks() {
		return (int) (1000000 * ($this->stopped - $this->started));
	}
	
	public function getTimeMs() {
		return (int) (1000 * ($this->stopped - $this->started));
	}
	
	public function getDeltaMs($expected = 0) {
		return abs((1000 * ($this->stopped - $this->started)) - $expected);
	}

	public function getDeltaMks($expected = 0) {
		return abs((1000000 * ($this->stopped - $this->started)) - $expected);
	}
	
}