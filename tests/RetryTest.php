<?php

class RetryTest extends PHPUnit_Framework_TestCase { 

	public function testNoDelayRetry() {
		$counter = 0;
		$x = function () use ($counter) {
			$counter++;
			throw new \Exception("Yahoo");
		};
		
		try {
			\core\utils\Retrying::retry($x, [], ['\Exception'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(4, $counter);
		}
	}
}