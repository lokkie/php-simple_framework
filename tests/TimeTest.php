<?php

class TimeTest extends PHPUnit_Framework_TestCase
{
	public function testtimeFormat() {
		$cases = [
			'2m30s' => [150,0],
			'2h458mks' => [7200, 458000]
		];
		foreach ($cases as $case => $expected) {
			$nanoTime = \core\utils\Time::formatNanoInterval($case);
			$this->assertEquals(count($expected), count($nanoTime));
			$this->assertEquals($expected[0], $nanoTime[0]);
			$this->assertEquals($expected[1], $nanoTime[1]);
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
			$this->assertLessThanOrEqual($allowdedTimeShift, abs(microtime(true) - $s - $expect));
		}
	}

}