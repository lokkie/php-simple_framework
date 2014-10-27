<?php 

namespace classes;

class testClass {
	public function doAction() 
	{
		\core\Log::e(__CLASS__, 'test data');
		echo "passed!";
	}
}