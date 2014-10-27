<?php

namespace core\data\cache;

abstract class Cache 
{
	
	/**
	 * @var object 
	 **/
	protected $driver = null;
	
	
	public function __call($method, $arguments) 
	{
		if ($this->driver === null) {
			$this->connect();
		}
		
		if (method_exists($this->dirver, $method)) {
			return call_user_func_array([$this->driver, $method], $arguments);
		}
	}
	
	/**
	 * Creates driver instance
	 **/
	abstract public function connect();
}