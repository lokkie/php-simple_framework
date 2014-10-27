<?php

namespace core\data\cache;

class ChainedCache extends Cache
{

	protected $chain = [];

	/**
	 *
	 * @throws \core\data\cache\CacheException
	 **/
	public function connect()
	{
		throw new CacheException('Inconnecteble type');
	}
	
	public function __call() 
	{
		
	}
	
}