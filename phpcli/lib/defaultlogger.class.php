<?php
namespace PhpCli;

class DefaultLogger {
	private $session_id;
	private $time_zone = 'America/Los_Angeles';
	
	public function write($msg){
		self::log($msg, Logger::OUTPUT_WRITE);
	}

	public function __construct($logfile = '/var/log/phpcli.log'){
		if (!is_dir(dirname($logfile))){
			if(!mkdir(dirname($lgofile), "774", true)){
				throw new LoggerException (dirname($logfile) . " directory doesnt exists and could not be created.");
			}
		
		}
		// see if directory has the correct permission. the user running this process should ahve write access
		if (is_writable(dirname($logfile)) !== true) {
			throw new LoggerException (dirname($logfile) . " directory is not writeable by owner of the process ". get_current_user());
		}
		
		$this->session_id = empty($sessionId) ? rand(10000000, 100000000) : intval($sessionId);
	}
	
	public  function setSessionId($sessionId){
		$this->session_id = $sessionId;
	}
	
	private static $fh = null;
	
	public static function getFh($path){
		if (!self::$fh){
			self::$fh = fopen($path, 'a');
		}
		return self::$fh;
	}

	public function log( $message){
		try{
		   $call = debug_backtrace(false);
			(count($call)) > 1 ? $c = $call[1]: $c = $call[0];
			$caller = $c['function'];
			$callerClass = array_key_exists('class', $c) ? $c['class']: '';
			
			$now = new \DateTime('now', new \DateTimeZone($this->time_zone));
			
			$now = date('y-m-d G:i:s');
			$out = sprintf("%s - [%s] %s::%s: %s ".self::$sep,
					$now->format("Ymd h:i:s"),
					$this->session_id,
					$callerClass,
					$caller,
					$message );
			fwrite(self::$fh, $out);
			fflush(self::$fh);
		}
		catch(\Exception $e){
			throw new \Excpetion ("Something went wrong in the logger.\n%s\n", $e->getMessage());
		}
	}
}

class LoggerException extends \Exception {}
