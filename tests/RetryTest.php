<?php

class RetryTest extends PHPUnit_Framework_TestCase { 

	protected static $exceptionProvider;

	public static function setUpBeforeClass() {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'ExceptionClass.php'; 
		self::$exceptionProvider = new ExceptionClass;
	}

	public function testNoDelayRetry() {
		try {
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runWithException'], [1], ['\Exception'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(3, self::$exceptionProvider->getCounter());
		}
	}
}