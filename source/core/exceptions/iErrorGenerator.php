<?php

namespace core\exceptions;

interface iErrorGenerator {
	public function setErrorCallback(\core\exceptions\iErrorCallback $callback);
}