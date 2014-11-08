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
		var_dump('runWithException incorrect Exception waiting test');
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
			\core\utils\Retrying::retry([self::$exceptionProvider, 'runNoException'], [1], ['\RuntimeException'], 3, 0, 0);
		} catch (\Exception $error) {
			$this->assertEquals(false, true);
		} 
		$this->assertEquals(1, self::$exceptionProvider->getCounter());
	}
	
	public function testDalayRetry() {
		$time = new ExecutionTime;
		$cases = [
			[
				'method'=> 'runWithException', 
				'movement' => 1, 
				'correctExceptions' => ['\Exception'],
				'tries' => 3, 
				'interval' => 100,
				'backoff' => 50,
				'counter' => 3,
				'expectedTime' => 250000,
				'executionShift' => 5*1000 
			],
			[
				'method'=> 'runWithRuntimeException', 
				'movement' => 2, 
				'correctExceptions' => ['\RuntimeException'],
				'tries' => 5, 
				'interval' => 150,
				'backoff' => 0,
				'counter' => 10,
				'expectedTime' => 600000,
				'executionShift' => 6*1000 
			]
		];
		foreach ($cases as $caseSettings) {
			self::$exceptionProvider->reset();
			try {
				$time->startWatching();
				\core\utils\Retrying::retry(
					[self::$exceptionProvider, $caseSettings['method']], 
					[$caseSettings['movement']], 
					$caseSettings['correctExceptions'], 
					$caseSettings['tries'], 
					$caseSettings['interval'], 
					$caseSettings['backoff']
				);
			} catch (\Exception $error) {
				$time->stopWatching();
				var_dump("Running {$caseSettings['method']} in {$caseSettings['tries']} tries. "
					."Expecting time {$caseSettings['expectedTime']}, shift {$caseSettings['executionShift']}. "
					."Real time: " . $time->getTimeMks() 
					. ". Real shift: " . $time->getDeltaMks($caseSettings['expectedTime']));
				$this->assertEquals(3, self::$exceptionProvider->getCounter());
				$this->assertLessThanOrEqual($caseSettings['executionShift'], $time->getDeltaMks($caseSettings['expectedTime']));
			}
		}
	}
}