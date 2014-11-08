<?php

class RetryTest extends PHPUnit_Framework_TestCase { 

	protected static $exceptionProvider;

	public static function setUpBeforeClass() {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'ExceptionClass.php'; 
		self::$exceptionProvider = new ExceptionClass;
	}

	public function testNoDelayRetry() {
		var_dump('runWithException test');
		self::$exceptionProvider->reset();
		try {
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runWithException'], [1], ['\Exception'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(3, self::$exceptionProvider->getCounter());
		}
/*		var_dump('runWithException incorrect Exception waiting test');
		self::$exceptionProvider->reset();
		try {
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runWithException'], [1], ['\RuntimeException'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(1, self::$exceptionProvider->getCounter());
		}
		var_dump('runWithRuntimeException test');
		self::$exceptionProvider->reset();
		try {
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runWithRuntimeException'], [1], ['\RuntimeException'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(3, self::$exceptionProvider->getCounter());
		}
		var_dump('runWithNoException test');
		self::$exceptionProvider->reset();
		try {
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runWithNoException'], [1], ['\RuntimeException'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(false, true);
		} 
		$this->assertEquals(1, self::$exceptionProvider->getCounter());
		*/
	}
}