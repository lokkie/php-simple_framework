<?php

class TimeTest extends PHPUnit_Framework_TestCase
{
	public function testtimeFormat() {
		$cases = [
			'2m30s' => [90,0],
			'2h458mks' => [7200, 358000]
		];
		foreach ($cases as $case => $expected) {
			$nanoTime = \core\utils\Time::formatNanoInterval($case);
			$this->assertEquals(count($nanoTime), count($expected));
			$this->assertEquals($nanoTime[0], $expected[0]);
			$this->assertEquals($nanoTime[1], $expected[1]);
		}
	}
	
	public function testnanoSleeping() {
		$cases = [
			'1s' => 1000000,
			'20ms' => 20000,
			'1s300ms' => 1300000
			];
		$allowdedTimeShift = 2000;
		foreach ($cases as $case => $expect) {
			$s = microtime(true);
			\core\utils\Time::nanoSleep($case);
			$this->assertLessThanOrEqual(abs(microtime(true) - $s - $expect), $allowdedTimeShift);
		}
	}

}