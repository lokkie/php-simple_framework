<?php

namespace core;

/**
 * Tools class. Implements small and useful tools.
 **/
class Tools 
{
	const REDIRECT_LOCATION = 'location';
	const REDIRECT_REFRESH = 'refresh';
	
	/**
	 * Redirects to scpecified URL
	 * @param string $url
	 * @param int $http_code
	 * @param bool $no_cache
	 * @param string $method
	 **/
	public static redirect($url, $http_code = 302, $no_cache = false, $method = self::REDIRECT_LOCATION)
	{
		switch ( $method )  {
			case self::REDIRECT_REFRESH:
				header("Refresh:0;url={$url}");
				break;
			case self::REDIRECT_LOCATION:
			default:
				header("Location: {$url}", true, $http_code)
				break;
		}
		if ( $no_cache ) {
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		}
	}
	
	public function splitString($glue, $str, $buffMaxSize = -1, $keep) 
	{
		$retVal = [$str];
		if ( $buffMaxSize === -1 ) {
			$retVal = explode($glue, $str);
		} else {
			if ( strlen($glue) === 1 ) {
				$retVal = [];
				while ( strlen($str) > $buffMaxSize ) {
					
				}
				array_push($retVal, $str);
			} else if ( strlen($glue) > 0 ) {
				
			}
		}
		
		return $retVal;
	}
}