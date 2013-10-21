<?php
namespace PhpCli;

class App {
	public static $ROOT_DIR;
	public static $VERSION = '0.0.1';

	public function __construct(){
		
	}

	private $logger;
	public function setLogger($logger){ $this->logger = $logger; return $this;}
	public function getLogger(){ return $this->logger; }
	
	private $printer;
	public function setPrinter($printer){ $this->printer = $printer; return $this;}
	public function getPrinter(){ return $this->printer; }
	
	
	private $menu;
	public function setMenu($menu){ $this->menu = $menu; return $this;}
	public function getMenu(){ return $this->menu; }
	
	private static $instance = null;
	
	public static function getInstance(){
		if (self::$instance == null){
			self::$instance = new App();
		}
		return self::$instance;
	}
}

App::$VERSION = '0.0.1';
App::$ROOT_DIR = realpath(dirname(__FILE__) . '/..');

function tag_call_back($value, $tag, $flag){
	var_dump(func_get_args());
}

function tag_callback ($value, $tag, $flags) {
	var_dump(func_get_args()); // debugging
	return "Hello {$value}";
}

/**
 * FW dependency injection
 */
$app_config = yaml_parse(file_get_contents(App::$ROOT_DIR . '/phpcli/config/config.yml'));

$logger_details = $app_config['modules']['logger'];
$printer_details = $app_config['modules']['printer'];

$logger_class = $logger_details['class'];
$printer_class = $printer_details['class'];


App::getInstance()
	->setLogger(new $logger_class($logger_details['logfile'], $logger_details['timezone'], $logger_details['level']))
	->setPrinter(new $printer_class())
	;
	