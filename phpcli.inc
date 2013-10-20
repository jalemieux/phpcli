<?php



# FW init scripts
foreach (glob(dirname(__FILE__) . "/init/*.php") as $filename) {
	include $filename;
}


#  FW runtime libs
spl_autoload_register(function ($class) {
	$parts = explode('\\', str_replace('/', '\\',strtolower($class)));
	$root_dir = array_shift($parts);
	$path = dirname(__FILE__) . '/phpcli/lib/' . strtolower(implode('/', $parts)) . '.class.php'; 
	#echo "loading class $path\n";
	include $path;
});

# User runtime libs
spl_autoload_register(function ($class) {
	include 'usr/' . strtolower($class) . '.php';
});

# init app instance
include 'phpcli/app.php';
	