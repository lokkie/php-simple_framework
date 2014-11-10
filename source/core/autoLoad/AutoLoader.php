<?php

namespace core\autoLoad;

/**
 * Auto-loadinfg system
 **/
abstract class AutoLoader {
	
	/**
	 * @var array
	 **/
	static private $routes = [];
	
	/**
	 * Loads files for speciffied class
	 * @param string $className
	 **/
	static public function loadClass($className)
	{
		$fileName = static::loadRoutine($className);
		self::includeClass($fileName);
	}
	
	/**
	 * Adds class loading route, by including scpecified class loader
	 * @param string $part
	 * @param string $basePath
	 **/
	static public function addCodeRoute($part, $basePath = null)
	{
		if ($basePath === null) {
			$basePath = dirname(__FILE__);
		}
		if (!in_array($part, self::$routes)) {
			$fileName = implode(DIRECTORY_SEPARATOR, [$basePath, "{$part}AutoLoader"]) . '.php';
			if ( self::includeClass($fileName) ) {
				spl_autoload_register(__NAMESPACE__ . "\\{$part}AutoLoader::loadClass");
				array_push(self::$routes, $part);
			}
		}
	}
	
	/**
	 * Rules to load classes. Should by implemented in auto-loaders
	 * @param string $className - name of class
	 * @return string - path to file with class
	 **/
	static protected function loadRoutine($className) {
		throw new BadMethodCallException('Should be called from child');
	}
	
	/**
	 * Includes specified file if exists. Othewise returns false
	 * @param string $filleName - path to file
	 * @return boolean - false on non-existing file
	 **/
	static function includeClass($fileName) 
	{
		$returnValue = false;
		if ( file_exists($fileName) ) {
			require_once $fileName;
			$returnValue = true;
		}
		
		return $returnValue;
	}
}