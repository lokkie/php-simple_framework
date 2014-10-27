<?php 

namespace core\autoLoad;

/**
 * Autoloader for example project
 **/
class ExampleAutoLoader extends AutoLoader
{
	/**
	 * Rules to load classes. Should by implemented in auto-loaders
	 * @param string $className - name of class
	 * @return string - path to file with class
	 **/
	static protected function loadRoutine($className)
	{
		return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
	}	
}