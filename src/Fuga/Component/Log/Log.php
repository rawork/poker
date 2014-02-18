<?php

namespace Fuga\Component\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log 
{
	
	private $path;
	private $log;
	
	public function __construct() 
	{
		$this->path = PRJ_DIR.'/app/logs/error.log';

		$this->log = new Logger('name');
		$this->log->pushHandler(new StreamHandler(
				$this->path, 
				PRJ_ENV == 'development' ? Logger::DEBUG : Logger::ERROR
			));
	}
	
	public function write($message)
	{
		if ( 'development' == PRJ_ENV ) {
			$this->log->addDebug($message);
		} else {
			$this->log->addError($message);
		}
	}
	
}