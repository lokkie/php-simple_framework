<?php

namespace core\utils;

/**
 * Time managment utilities
 * 
 * @package core\utils
 * @author Lokkie
 **/
class Time {
	/**
	 * Formats provided param into a pair [seconds, nanosecods].
	 * If provided numeric e.g. 300, 1800, 4.5 it's explained as miliseconds 
	 * (accuracy up to microsekonds - 0.001)
	 * If provided string it could contain next mofifiers:
	 * 	11d  - 11 days
	 * 	5h   - 5 hours
	 * 	3m   - 3 minutes
	 * 	11s	 - 11 seconds
	 *  15ms - 15 miliseconds
	 *  5mks - 5 microseconds
	 *  10ns - 10 nanoseconds
	 * 
	 * @param numeric|string $interval
	 * 
	 * @return array [seconds, nanoseconds]
	 **/
	public static function formatNanoInterval($interval) {
		// default result
		$func_result = [0, 0];
		// Modifiers sets
		$nanoModifiers = ['ns' => 1, 'mks' => 1000, 'ms' => 1000000];
		$secModifiers = ['s' => 1, 'm' => '60', 'h' => 3600, 'd' => 86400];
		
		if (is_numeric($interval)) { // If we got int or float - it's time in ms
			$func_result[0] = (int) ($interval / 1000); // we need seconds firs
			$func_result[1] = (int) ((($interval * 1000) % 1000000) * 1000);
			// to keep float value with microsec we should multiply 1000 first
		} else if (is_string($interval)) {
			$regEx = '/(?P<values>\d+)(?P<modifiers>mks|ns|ms|m|s|h|d)/i'; // Modifiers RegExpr
			
			if (preg_match_all($regEx, $interval, $intervals)) {
				foreach ($intervals['values'] as $index => $value) {
					$modifier = $intervals['modifiers'][$index];
					
					if (isset($nanoModifiers[$modifier])) { // nanoseconds
						$func_result[1] += $value * $nanoModifiers[$modifier];
					} elseif (isset($secModifiers[$modifier])) { // seconds
						$func_result[0] += $value * $secModifiers[$modifier];
					}
				}
			}
		}
		
		return $func_result;
	}
	
	/**
	 * Stops script executing for provided time
	 * $seconds could be:
	 * 	integer - real seconds to sleep
	 *  array - [int seconds, int nanoseconds]
	 * 	array - [int seconds]
	 *  string - string for Time::formatNanoInterval()
	 * 
	 * @param int|array|string $seconds
	 * @param int $nanoSeconds - default 0
	 * 
	 * @see time_nanosleep()
	 * @see \core\utils\Time::formatNanoInterval()
	 **/
	public static function nanoSleep($seconds, $nanoSeconds = 0) {
		if (is_array($seconds)) { 
			if (count($seconds) > 1) { // if we got [seconds, nanoseconds]
				list($seconds, $nanoSeconds) = $seconds;
			} else { 
				$seconds = $seconds[1];
			}
		}
		
		if (is_string($seconds) && !is_numeric($seconds)) { // formated time
			list($seconds, $nanoSeconds) = self::formatNanoInterval($seconds);
		}
		
		time_nanosleep((int)$seconds, (int)$nanoSeconds);
	}
}