<?php

namespace core\utils;

/**
 * Exceution retry utility (like retrying module in python)
 * 
 * @package core\utils
 * @author Lokkie
 **/
class Retrying {
	
	/**
	 * Basic retrying method
	 * 
	 * @param callable $callable
	 * @param array $args
	 * @param array $watchExceptions - allowded Exceptions list
	 * @param int $tries - number of tries
	 * @param numeric|string $interval - interval before tries
	 * @param numeric|string $backoff - interval changing rule e.g. each try
	 * 									should wait more time
	 * 
	 * @return mixed
	 * 
	 * @see \core\utils\Time
	 **/
	public static function retry(
			$callable, $args = [], $watchExceptions = ["\Exception"], 
			$tries = 5, $interval = 300, $backoff = 0
	) {
		$func_result = null;
		// normalize time
		$interval = \core\utils\Time::formatInterval($interval);
		$backoff = \core\utils\Time::formatInterval($backoff);
		
		// Preparing interval 
		$interval = self::formatInterval($interval);
		
		// prevent incorrect executing
		if (!is_callable($callable)) {
			throw new \BadFunctionCallException('Incorrect callable');
		}
		
		$success = false;
		// until tries end or success call
		while ($tries > 0 && !$success) { 
			try {
				$func_result = call_user_func_array($callable, $args);
				$success = true;
			} catch (\Exception $anyError) {
				// search error in allowded list
				foreach ($watchExceptions as $exceptionStructure) {
					if ($anyError instanceof $exceptionStructure) {
						$tries--;
						if ($tries == 0) { // if it was last try
										// bouble Exception up
							throw $anyError;
						}
						// Wait $interval
						\core\utils\Time::nanoSleep($interval);
						if ( true
							&& is_array($backoff) 
							&& count($backoff) == 2 
							&& $backoff !== [0,0]
						) { // Rise up interval if backoff provided
							$interval[0] += $backoff[0];
							$interval[1] += $backoff[1];
						}
						break 1;
					}
				}
			}
		}
		return $func_result;
	}
	
}