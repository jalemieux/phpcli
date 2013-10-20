<?php
namespace PhpCli;
/**
 * The OptionMenu is used for parsing, validating and accessing agurments.
* It is also useful for usage display.
*
* @author jac <jacques@marketo.com>
*/
class OptionMenu {
	const OPTION_MANDATORY = true;
	const OPTION_NOT_MANDATORY = false;
	const OPTION_CAN_BE_EMPTY = true;
	const OPTION_CANNOT_BE_EMPTY = false;
	const NO_VALIDATION_CALLBACK = null;

	private $options = array();
	/**
	 * Defines a supported option for the menu.
	 *
	 * After instanciating an OptionMenu, one should define options supported
	 * along with their specificity, e.g. mandatory or not, format, value required, etc.
	 *
	 * To define an option, 'verbose', that is not mandatory, that has for description "verbose mode",
	 * that does not require a value (i.e. --verbose, and not --verbose=123) and no validation is required:
	 * <code>
	 * $option_menu->setOption('verbose',  'verbose mode', self::OPTION_NOT_MANDATORY, self::OPTION_CAN_BE_EMPTY, self::NO_VALIDATION_CALLBACK);
	 * </code>
	 * or in a shorter form:
	 * <code>
	 * $option_menu->setOption('verbose', 'verbose mode');
	 * </code>
	 * To define an option, '--output=/path/to/file', that is not mandatory, that has for description "path to file",
	 * that does require a value and no where validation is required:
	 * <code>
	 * $option_menu->setOption('output', 'path to file', self::OPTION_NOT_MANDATORY, self::OPTION_CANNOT_BE_EMPTY, self::NO_VALIDATION_CALLBACK);
	 * </code>
	 * or in a shorter form:
	 * <code>
	 * $option_menu->setOption('output', 'path to file', self::OPTION_NOT_MANDATORY, self::OPTION_CANNOT_BE_EMPTY);
	 * </code>
	 * If '--output' was mandatory, then it should be defined as:
	 * <code>
	 * $option_menu->setOption('output', 'path to file', self::OPTION_MANDATORY, self::OPTION_CANNOT_BE_EMPTY);
	 * </code>
	 * If some kind of validation is needed on the option, a callback can be passed. Say for example that we want to validate that the
	 * path given in --output is a valid one, we can create a function that does that and pass it to setOption.
	 * <code>
	 * function __validatePath($value){
	 * 	return file_exists($value);
	 * }
	 * $option_menu->setOption('output', 'path to file', self::OPTION_MANDATORY, self::OPTION_CANNOT_BE_EMPTY, '__validatePath');
	 * </code>
	 * alternatively, you can define the function inline:
	 * <code>
	 * $option_menu->setOption('output', 'path to file', self::OPTION_MANDATORY, self::OPTION_CANNOT_BE_EMPTY, function ($value){
	 * 	return file_exists($value);
	 * });
	 * </code>
	 * Finally, a few validation function like these have been made available throught the facade class OptionMenuValidators. For example,
	 * if you want to make sure an argument is a valid mlm server, you could do the following:
	 * <code>
	 * $option_menu->setOption('output', 'path to file', self::OPTION_MANDATORY, self::OPTION_CANNOT_BE_EMPTY, array('OptionMenuValidators', 'isValidMlmServer'));
	 * </code>
	 *
	 *
	 * @param string $label The label identifying the option.
	 * @param boolean $mandatory Whether the option is mandatory or not.
	 * @param string $description Description that will be printed when usage is called.
	 * @param boolean $empty_val Whether the option should come along a value or not.
	 * @param callback $validating_callback The callback to call to validate the option value if defined.
	*/
	public function setOption($label,
	$description = null,
	$mandatory = false,
	$empty_val = true,
	$validating_callback = null){
		$option = new \stdClass();
		$option->variable_name = str_replace('-', '_', $label);
		$option->description = $description;
		$option->mandatory = $mandatory;
		$option->validating_callback = $validating_callback;
		$option->empty_val = $empty_val;
		$option->called = false;
		$option->value = null;
		$this->options[$label] = $option;
		return $this;
	}


	private $desc = "";
	/**
	 * Sets the description that will be displayed when calling usage.
	 * @param string $desc
	 */
	public function setDesc($desc){
		$this->desc = $desc;
		return $this;
	}

	private $program_name;

	/**
	 * when set to tru, the menu will be displayed when no argument is passed.
	 * to turn this off: OptionMenu::doNotshowMenuOnNoArgs();
	 */
	public static $show_help_when_no_args = false;
	public static function showHelpWhenNoOptions(){
		self::$show_help_when_no_args = true;
	}

	public function setArgv($argv){
		$this->program_name = array_shift($argv);
		if (count($argv) < 1 && self::$show_help_when_no_args === true){
			array_unshift($argv, '--help');
		}
		if (in_array('--help', $argv)) throw new DisplayHelpException("Displaying Help.");
		while (null !== $arg = array_shift($argv)){
			if (preg_match('/^--[a-z-]*?=?.*$/', $arg) < 1){
				throw new OptionMenuException ("bad option given [" . $arg . "]");
			}
			$argPair = @explode("=", $arg);
			$argName = @ltrim($argPair[0], "--");
			$argValue = @trim($argPair[1]);
			if (!array_key_exists($argName, $this->options)){
				throw new OptionMenuException('option ' . $argName . 'is not supported.');
			}
			$option =& $this->options[$argName];
			$option->value = !empty($argValue) ? $argValue : null;
			$option->called = true;
		}
		$this->validateArgv();
		return $this;
	}

	public function isOptionCalled($label){
		return isset($this->options[$label]) && $this->options[$label]->called === true;
	}

	private function validateArgv(){
		foreach ($this->options as $label => $option) {
			// mandatory option value should be called
			if ($option->mandatory === true && $option-> called === false){
				throw new OptionMenuException("option " . $label . " should be specified");
			}
			// option with mandatory value should be checked
			if ($option->called === true && $option->empty_val === false && $option->value === null){
				throw new OptionMenuException("option " . $label . " cannot be empty.");
			}
				
			// if there is a value, and callback for validation, call it.
			if (isset($option->value) && isset($option->validating_callback)){
				$ret = call_user_func($option->validating_callback, $option->value);
				if ($ret === false){
					throw new OptionMenuException("validation failed on " . $label);
				}
			}

		}
	}

	public function getUsageOut($msg = null){
		$out = "Usage: " . $this->program_name . " ";
		if ($msg) $out = $msg . "\n". $out;
		$desc = array();
		foreach($this->options as $label => $option){
			$token = "--" . $label . "";
			if ($option->empty_val === false){
				$token .= '=<value>';
			}
			if ($option->mandatory === self::OPTION_NOT_MANDATORY){
				$token = '[' . $token . '] ';
			}
			$out .= ' ' . $token;
			$desc[] = "--" . $label . ": " . $option->description;
		}
		$out .= "\n\nDesc: " . $this->desc . "\n";
		$out .= "\nOptions: \n" . implode("\n", $desc) . "\n";
		return $out;
	}

	public function get($label){
		return $this->options[$label]->value;
	}

}
class DisplayHelpException extends \Exception {}

class OptionMenuException extends \Exception {}



/*
function validation_isValidMlmServer($value){
	list($p, $s, $i) = mktUtils::getNodeName($value);
	$server = MktoPodManager::getServer($p, $s . $i);
}
function validation_isValidMlmPod($value){
	if ( false === MktoPodManager::isValidatePodKey($value) ){
		throw new Exception("invalid pod name [" . $value . "]");
	}
}

function validation_isValidDbUpManifest($value){
	if (false === realpath($value)) throw new Exception ('invalid path given [' . $value . ']');
	exec("php -l " . $value, $out, $ret);
	if ($ret !== 0){
		throw new Exception($value . " contains syntax errors");
	}
	include $value;
	if (!isset($subDBScripts)) throw new Exception ('$subDBScripts is not set in manifest');
	if (!isset($sharedDbScripts)) throw new Exception ('$sharedDbScripts is not set in manifest');
}

*/
