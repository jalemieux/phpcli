<?php
/**
* Simple script that make uses of logger snad printers.
* This simple program prints and log 'hello world'.
*/

// always include this, loads all phpcli modules.
include realpath(dirname(__FILE__) . '/../phpcli.inc');

# app container
use PhpCli\App;
# linux friendly printer
use PhpCli\LinuxShellPrinter;
# handy logger
use PhpCli\DefaultLogger;

// By default, PhpCli\DefaultPrinter is loaded by phpcli. See phpcli/config/config.yml
$default_printer = App::getInstance()->getPrinter();
$default_printer->echo_msg("Hello World");

// You can change the printer that is loaded byb phpcli by editing phpcli/config/config.yml,
// and replace it by an implementation of your choice,  
// here we switch to LinuxShellPrinter, and will do it programatically for this example:
try {
  App::getInstance()->setPrinter(new LinuxShellPrinter());
  $new_printer = App::getInstance()->getPrinter();
  $new_printer->echo_success("Hello World");
}catch(\Exception $e){
  // an exception will be thrown on system with no init.d
  echo "oops. You aren't running this on a init.d system.\n"; 
}

// Logger works basically the same way. Its configured in phpcli/config/config.yml.
// the default logger loaded by phpcli is PhpCli\DefaultLogger.
// it provides most of the basic functionality of a logger, but if you ever wanted to exntend it, 
$default_logger = App::getInstance()->getLogger();
$default_logger->log("Ooops!");
$default_logger->debug("debug message");
$default_logger->info("info message");
$default_logger->warning("warning message");
$default_logger->error("error message");

exit(0);
