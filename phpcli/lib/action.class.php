<?php
namespace PhpCli;

/**
 * Composite Object, useful to store one, or multiple actions. 
 * created to be used with FilterChain
 * @example

$stop_services = new Action("stops web services");
$stop_services->setClassAndMethod(MktoPodFunctions, "podServerCmd", array($podKey, $serverKey, 'stop'));
$deploy_app = new Action("deploys application on server");
$deploy_app->setClassAndMethod(MktoPodFunctions, "podDeployServerApp", array($podKey, $tarName, $serverKey));
$mlm_install_web2 = new Action("stopping server $serverKey, deploying app version $tarName, and restarting services");
$mlm_install_web2->addAction($stop_services);
$mlm_install_web2->addAction($deploy_app);
$mlm_install_web2->execute();

 * @author jacques
 *
 */
class Action {
	protected $actions = array(); //Action
	public $desc = "null";
	
	public $funct = null;
	public $args = null;
	public $clazz = null;
	private $process_user = false;
	private $process_group = false;
	
	/**
	 * @var BaseSignalHandler
	 */
	private $handler;
	
	public function setProcessUser($user){
		$this->process_user = $user;
		return $this;
	}
	
	public function setProcessGroup($group){
		$this->process_group = $group;
		return $this;
	}
	
	public function getProcessUser(){
		return $this->process_user;
	}
	
	public function getProcessGroup(){
		return $this->process_group;
	}
	
	public function __construct($desc = null){
		$this->desc = $desc;
	}
	

	public function setClassAndMethod($clazz, $function, $args = null){
		$this->clazz = $clazz;
		$this->setFunc($function, $args);
		return $this;
	}
	/**
	 * 
	 * 
	 * @param callback $function
	 * @param array $args
	 */	
	public function setFunc($function, $args = array()){
		$this->funct = $function;
		$this->args = $args;
		return $this;
	}
	
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param BaseSignalHandler $handler
	 * @see BaseSignalHandler
	 */
	public function setSystemSignalHandler($handler){
		$this->handler = $handler;
		return $this;
	}
	
	/**
	 * encapsulate all action runtime contesxt initialization
	 */
	private function initRunTimeCtx(){
		if (isset($this->handler)) $this->handler->trap();
	}
	
	public function hasActions(){
		return empty($this->actions) === false; 
	}
	
	public function addAction($action){
		$this->actions[] = $action;
		$this->desc .= "\n\tsub action:" . $action->desc;
		return $this;
	}
	
	public function execute(){
		// init the action run time context
		$this->initRunTimeCtx();
		
		// if the action array is not empty then it is a composite object
		// call execute on all its children then execute himself
		if (empty($this->actions) !== true){
			for($i=0; $i < count($this->actions); $i++){
				
				$ret = $this->actions[$i]->execute() === true;
				if ( $ret !== true){
					
					return $ret;
				}
			}
		}
		if (empty($this->funct) === false){
			// enter action context!
			if (empty($this->clazz) === false){
				$ret = call_user_func_array(array($this->clazz, $this->funct), $this->args);
			}
			else{
		 		$ret = call_user_func_array($this->funct, $this->args);
			}

		}
		return $ret;
	}
	
}
