<?php


abstract class BasicApplication implements iApplication 
{
	
	public __construct()
	{
		// create routine
		$this->OnCreate();
	}
	
	public __destruct()
	{
		$this->OnDestroy();
		// destructor routine
	}
	
	abstract protected function loopRoutine();
	abstract protected function OnCreate();
	abstract protected function OnDestroy();
}