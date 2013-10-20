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

/**
 * FW dependency injection
 */
$app_config = parse_ini_file('config/config.ini', true);

$logger_class = $app_config['modules']['logger_class'];
$printer_class = $app_config['modules']['printer_class'];
$menu_class = $app_config['modules']['menu_class'];

App::getInstance()
	->setLogger(new $logger_class())
	->setPrinter(new $printer_class())
	->setMenu(new $menu_class())
	;