<?php
namespace PhpCli;

class DefaultLogger {
	private $session_id;
	private $logfile;
	
	public function write($msg){
		self::log($msg, Logger::OUTPUT_WRITE);
	}

	public function __construct($logfile, $time_zone, $level){
		if (!is_dir(dirname($logfile))){
			if(!mkdir(dirname($lgofile), "774", true)){
				throw new LoggerException (dirname($logfile) . " directory doesnt exists and could not be created.");
			}
		
		}
		// see if directory has the correct permission. the user running this process should ahve write access
		if (is_writable(dirname($logfile)) !== true) {
			throw new LoggerException (dirname($logfile) . " directory is not writeable by owner of the process ". get_current_user());
		}
		$this->logfile = $logfile;
		$this->session_id = empty($sessionId) ? rand(10000000, 100000000) : intval($sessionId);
		
		switch(strtoupper($level)){
			case 'DEBUG':
				$this->level = self::DEBUG;
				break;
			case 'INFO':
				$this->level = self::INFO;
				break;
			case 'WARNING':
				$this->level = self::WARNING;
				break;
			case 'ERROR':
				$this->level = self::ERROR;
				break;
		}
		
		if ($time_zone){
			date_default_timezone_set($time_zone);
		}
	}
	
	public  function setSessionId($sessionId){
		$this->session_id = $sessionId;
	}
	
	private $fh = null;
	
	public function getFh($path){
		if (!$this->fh){
			$this->fh = fopen($path, 'a');
		}
		return $this->fh;
	}

	const DEBUG = 30;
	const ERROR = 0;
	const WARNING = 10;
	const INFO = 20;
	
	public function debug($message){
		$this->log($message, self::DEBUG);
	}
	
	public function error($message){
		$this->log($message, self::ERROR);
	}
	
	public function info($message){
		$this->log($message, self::INFO);
	}
	
	public function warning($message){
		$this->log($message, self::WARNING);
	}
	
	public function log( $message, $level = self::DEBUG){
		if ($this->level[1] < $level[0]) return;
		try{
		   $call = debug_backtrace(false);
			(count($call)) > 1 ? $c = $call[1]: $c = $call[0];
			$caller = $c['function'];
			$callerClass = array_key_exists('class', $c) ? $c['class']: '';
			
			$now = new \DateTime('now');
			$out = sprintf("%s [pid:%d] [session:%s] %s:  %s\n",
					$now->format("Y-m-d h:i:s"),
					$this->session_id,
					posix_getpid(),
					$level[0],
					$message );
			$fh = $this->getFh($this->logfile);
			fwrite($fh, $out);
			fflush($fh);
		}
		catch(\Exception $e){
			throw new \Excpetion ("Something went wrong in the logger.\n%s\n", $e->getMessage());
		}
	}
}

class LoggerException extends \Exception {}
