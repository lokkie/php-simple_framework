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
			'1s300ms' => 1300000,
			'2ms300mks'=> 2300
			];
		$allowdedTimeShiftMks = 2000;
		$time = new ExecutionTime;
		foreach ($cases as $case => $expect) {
			$time->startWatching();
			\core\utils\Time::nanoSleep($case);
			$time->stopWatching();
			var_dump("Test on {$expect} mks, delta: " . $time->getDeltaMks($expect));
			$this->assertLessThanOrEqual($allowdedTimeShiftMks, $time->getDeltaMks($expect));
		}
	}

}