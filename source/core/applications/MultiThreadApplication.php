<?php


class MultiThreadApplication extends BasicApplication
{
	private $masterSid;
	
	public __construct()
	{
		# Turning output buffering to flush on output mode
		ob_implicit_flush();
		# magic string :)
		declare(ticks = 1); 
		pcntl_signal(SIGTERM, array($this, 'signalHandler'));
		pcntl_signal(SIGINT, array($this, 'signalHandler'));
		# ignoring signal from dying childs to prevent zombies
		pcntl_signal(SIGCHLD, SIG_IGN);
		parent::__construct();
	}
	
	public function handleSignals()
	{
		switch ($sig) {
		    case SIGTERM:
		    case SIGINT:
			exit;
		    break;
		}		
	}
	
	public function demonize()
	{
		if ($this instanceof iConsoleApplication) {
			$pid = pcntl_fork();
			if ($pid == -1) {
			    die('Couldn\'t fork master process');
			} elseif ($pid) {
			    //die('Parent');
			    exit(0);
			} else {
			    # first child become a parent!
			    $this->masterSid = posix_setsid();
			    return TRUE;
			}
		}
	}
	
	/**
	 * 
	 * @param callable $call
	 **/
	public function runInSeparateThread($call)
	{
		
	}
}