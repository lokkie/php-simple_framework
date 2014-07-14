<?php

namespace core\autoLoad;

/**
 * Basic class routing rule. Looking from document root.
 **/
class BasicAutoLoader extends AutoLoader
{
	/**
	 * Rules to load classes. Should by implemented in auto-loaders
	 * @param string $className - name of class
	 * @return string - path to file with class
	 **/
	static protected function loadRoutine($className)
	{
		return dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
	}
}