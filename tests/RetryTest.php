<?php

class RetryTest extends PHPUnit_Framework_TestCase { 

	public function testNoDelayRetry() {
		$counter = 0;
		$x = function (&$c) {
			$c++;
			throw new \Exception("Yahoo");
		};
		
		try {
			Retrying::retry($x, [], ['\Exception'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(3, $counter);
		}
	}
}