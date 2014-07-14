<?php

namespace core\autoLoad;

class BasicAutoLoader extends AutoLoader
{
	static protected function loadRoutine($className)
	{
		return dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
	}
}