<?php

namespace core\exceptions;


interface iErrorCallback {
	/**
	 * 
	 *  
	 **/
	public function OnError(\core\exceptions\BaseError $error);
}