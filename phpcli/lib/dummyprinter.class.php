<?php
namespace PhpCli;

class TestPrinter extends DefaultPrinter {
	public $msg =array();

	public  function echo_success($msg){
		$this->msg[] = $msg;
	}

	public function echo_failure($msg){
		$this->msg[] = $msg;
	}

	public function echo_warning($msg){
		$this->msg[] = $msg;
	}

	public function echo_banner($msg){
		$this->msg[] = $msg;
	}
	public function echo_warning_banner($msg){
		$this->msg[] = $msg;
	}

	public function echo_bold_msg($msg){
		$this->msg[] = $msg;
	}

	public function echo_msg($msg){
		$this->msg[] = $msg;
	}
	public function echo_short_msg($msg){
		$this->msg[] = $msg;
	}

	public function echo_err($msg){
		$this->msg[] = $msg;
	}

	/**
	 * @return true if acknowleged, false on non-acknowlegement
	 * @param string $question
	 */
	public function read_input($question){
		return true;
	}
}