<?php
/*
 * Simple example on how to use common phpcli functions. 
 * This program prints a simple greeting, based on the arguments. 
 */


// always include this, loads all phpcli modules.
include realpath(dirname(__FILE__) . '/../phpcli.inc');

use PhpCli\System;
use PhpCli\LinuxShellPrinter;
use PhpCli\App;
use PhpCli\OptionMenu;
use PhpCli\DisplayHelpException;

// you could use App::getInstance()->* throught out the program , but for simplicity, 
// we'll be assigning printer and menu to local variables.
$printer = App::getInstance()->getPrinter();
$menu = App::getInstance()->getMenu();

// one example of the system module function, detecting and enforcing the running user and group
// useful when dealing with permissions, and ssh keys.
if (! System::enforceUserAndGroup('jacques', 'staff' )){
	$printer-> echo_err('Bad user / group');
	exit(1);
}

// here we set 3 options, firstname, lastname and reverse. 
// for each option, we can specify the key ( --key )
// the description, which will appear in help
// whether it is a mandatory option
// whether the option should have a value or not, e.g. --key vs. --key=value
// finally, we can pass a lambda for validation. Validation failure should throw an exception
try{
	$menu
	->setOption('firstname',
			'user first name.',
			OptionMenu::OPTION_MANDATORY,
			OptionMenu::OPTION_CANNOT_BE_EMPTY,
			function($value){ 
				if (preg_match('/^[A-Za-z]+$/', $value) < 1){
					throw new Exception("Sorry, simple firstname only.");
				}
			}
	)
	->setOption('lastname',
			'user\'s last name',
			OptionMenu::OPTION_NOT_MANDATORY,
			OptionMenu::OPTION_CANNOT_BE_EMPTY)
	->setOption('reverse',
			 'prints the greeting in reverse', 
			OptionMenu::OPTION_NOT_MANDATORY,
			OptionMenu::OPTION_CAN_BE_EMPTY)
	->setDesc("Fancy Greeting CLI Program")
	->setArgv($argv);
// if --help is passed an an option, DisplayHelpException will be caught 
// and the generated help text printed out using getUsageOut	
}catch(DisplayHelpException $e){
	$printer->echo_msg($menu->getUsageOut());
	exit(1);
}catch(Exception $e){
	// catching validation errors, and displaying the 'usage' along with the error message
	$printer->echo_err($e->getMessage() . ' Try --help.');
	exit(1);
}
// calling get(<key>) on menu gives us the value of the option
$out = "Hello " . $menu->get('firstname');
// calling isOptionCalled on menu tells us if the option was set at all
if ($menu->isOptionCalled('lastname')) $out .= ' ' . $menu->get('lastname');
if ($menu->isOptionCalled('reverse')) $out = strrev($out);

$printer-> echo_banner($out);