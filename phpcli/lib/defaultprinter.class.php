<?php
namespace PhpCli;

class DefaultPrinter implements Printer {
	public  function echo_success($msg){
		printf("%s\n", $msg);
	}

	public function echo_err($msg){
		return file_put_contents("php://stderr", "Error: " . $msg . "\n");
	}

	public function echo_failure($msg){
		printf("ERROR: %s\n", $msg);
	}

	public function echo_warning($msg){
		printf("WARNING: %s\n", $msg);
	}

	public function echo_right_bottom($msg){
		throw new \Exception("method not implemented");
	}

	public function echo_banner($msg){
		printf("****************************************************************\n");
		printf("%s\n", $msg);
		printf("****************************************************************\n");
	}
	public function echo_warning_banner($msg){
		$this->echo_banner("Read carefully. " . $msg);
	}

	public function echo_bold_msg($msg){
		throw new \Exception("method not implemented");
	}

	public function echo_msg($msg){
		printf ("%s\n", $msg);
		return 0;
	}

	public function echo_short_msg($msg){
		printf ("%s", $msg);
	}

	public static function decorateBold($msg){
		throw new \Exception("method not implemented");
	}

	public function echo_clear($msg){
		throw new \Exception("method not implemented");
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


