<?php

// always include this, loads all phpcli modules.
include realpath(dirname(__FILE__) . '/../phpcli.inc');

# app container
use PhpCli\App;
# linux friendly printer
use PhpCli\LinuxShellPrinter;
# handy logger
use PhpCli\DefaultLogger;
/*
* Example on how to use phpcli loaded modules
*  - logger
*  - printer
* This simple program prints and log 'hello world'.
*/

// By default, PhpCli\DefaultPrinter is loaded by phpcli.
$default_printer = App::getInstance()->getPrinter();
$default_printer->echo_msg("Hello World");

// You can change the printer that is loaded byb phpcli by editing phpcli/config/config.ini,
// and replace it by an implementation of your choice,  
// here we'll switch to LinuxShellPrinter, and will do it programatically for this example:
App::getInstance()->setPrinter(new LinuxShellPrinter());
$new_printer = App::getInstance()->getPrinter();
$new_printer->echo_success("Hello World");
// Feel free to code your own printer implementation, by extending DefaultPrinter.


// Logger works basically the same way. 
// the default logger loaded by phpcli is PhpCli\DefaultLogger.
// it provides most of the basic functionality of a logger, but if you ever wanted to exntend it, 
$default_logger = App::getInstance()->getLogger();
$default_logger->log("Ooops!");
$default_logger->debug("debug message");
$default_logger->info("info message");
$default_logger->warning("warning message");
$default_logger->error("error message");
// if you find DefaultLogger not to be to your liking, you can implement your own. 
// Extend DefaultLogger and edit config.ini to load your class instead.
