<?php

namespace PhpCli;

interface Printer {
	public  function echo_success($msg);
	public function echo_failure($msg);
	public function echo_warning($msg);
	public function echo_banner($msg);
	public function echo_warning_banner($msg);
	public function echo_bold_msg($msg);
	public function echo_msg($msg);
	public function echo_short_msg($msg);
	public function echo_err($msg);

	/**
	 * @return true if acknowleged, false on non-acknowlegement
	 * @param string $question
	*/
	public function read_input($question);
}
