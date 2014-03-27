<?php
namespace PhpCli;

class LinuxShellPrinter extends DefaultPrinter {
	public  function echo_success($msg){
		$cmd = ". /etc/init.d/functions; echo -n ".escapeshellarg($msg)."; success; echo; ";
		passthru($cmd, $ret);
		return $ret;
	}

	public function __construct(){
		if (! file_exists('/etc/init.d/functions')){
			throw new PrinterException("/etc/init.d/functions is needed but could not be found.");
		}
	}
	public function echo_err($msg){
		return file_put_contents("php://stderr", "Error: " . $msg . "\n");
	}

	public function echo_failure($msg){
		$cmd = ". /etc/init.d/functions; echo -n ".escapeshellarg($msg)."; failure; echo; ";
		passthru($cmd, $ret);
		return $ret;
	}

	public function echo_warning($msg){
		$cmd = ". /etc/init.d/functions; echo -n ".escapeshellarg($msg)."; warning; echo; ";
		passthru($cmd, $ret);
		return $ret;
	}

	public function echo_right_bottom($msg){
		if (is_array($msg)){
			$anew = implode("\n", $msg);
		}else{
			$anew = $msg;
		}
		$row = 60;
		$data = 'echo -en "\\033['.$row.'G'.$anew;
		echo $data;
		passthru($data,$ret);
		return $ret;
	}

	public function echo_banner($msg){
		printf("\n\n****************************************************************\n");
		printf("%s\n\n", $msg);
	}
	public function echo_warning_banner($msg){
		$this->echo_banner("Read carefully. " . $msg);
	}

	public function echo_bold_msg($msg){
		$cmd = 'echo $(tput smso)' . escapeshellarg($msg) . '$(tput rmso);';
		passthru($cmd, $ret);
		return $ret;
	}

	public function echo_msg($msg){
		printf ("%s\n", $msg);
		return 0;
	}

	public function echo_short_msg($msg){
		printf ("%s", $msg);
	}

	public static function decorateBold($msg){
		return '$(tput smso)' . escapeshellarg($msg) . '$(tput rmso);';
	}

	public function echo_clear($msg){
		$cmd = 'echo $(tput clear)' . escapeshellarg($msg);
		echo $cmd;
		passthru($cmd, $ret);
		return $ret;
	}
	public function read_input($question){
		$f = fopen("php://stdin", 'r');
		$ans = 1;
		while ($ans){
			$this->echo_msg($question . ' [Y\n]');
			$ans = trim(fgets($f, 256));
			if ($ans === "Y"){
				return true;
			}elseif ($ans === 'n'){
				return false;
			}else{
				$this->echo_msg("Don't understand $ans");
				$ans = 1;
			}
		}
	}
}


