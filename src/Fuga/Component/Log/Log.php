<?php

namespace Fuga\Component\Log;

class Log 
{
	
	private $path;
	
	public function __construct() 
	{
		$this->path = PRJ_DIR.'/app/logs/error.log';
	}
	
	public function write($message)
	{
		$filePointer = fopen($this->path, 'a');
		fwrite($filePointer, date('Y-m-d H:i:s')."\t".$message."\n");
		fclose($filePointer);
	}
	
	public function clear() 
	{
		$filePointer = fopen($this->path, 'w+');
		fclose($filePointer);
	} 
	
}