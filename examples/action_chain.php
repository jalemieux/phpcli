<?php
/*
 * Example on how to use the actions in phpcli.
 * The action class provides a way to execute functions/scripts in a sequentiual way.
 * For this example, let's say we want to ....
 */


// always include this, loads all phpcli modules.
include realpath(dirname(__FILE__) . '/../phpcli.inc');

/** actions **/
// the call getJobs gets all Jobs prep'ed up
$main_action = ToolsHelper::getAction('mlm database upgrade')
// register signal handler, so crt-c does not terminate the program
->setSystemSignalHandler(new BasicSignalHandler('prevent_exit'));

// split the upgrade action and the reporting action
$upgrade_action = ToolsHelper::getAction('execute db upgrade labeled [' . $menu-> get('label') . ']')
->setFunc("executeReleaseDbUpgrade", array(
		$menu-> get('label'),
		$menu-> get('manifest'),
		$menu->get('max-threads')));

$report_action = ToolsHelper::getAction('reporting on upgrade status')
->setFunc('displayDbUpgradeStatus', array(ToolsHelper::getPrinter(), $menu-> get('label'), $menu-> get('manifest')));

$main_action
-> addAction($upgrade_action)
-> addAction($report_action);

// dry run is useful to test changelog tabek population without running scripts.
if ($menu->isOptionCalled('dryrun')){
	// TODO: make these choices as options
	// if we're in test mode, we want to make sure we dont run the scripts,
	// there are 3 alternatives,
	// (1) use a dummy impl for the script run class. that class will only return 0.
	// (2) set mktMLMUtils in dry run mode, this will excersive bigger part of the code, and is recommended.
	// (3) RnadFailRun provides a way to simulate random failures durring execution
	//DbUpJob::setScriptExecutionImpl( 'DummyScriptRunImpl' ); // for all success
	mktMlmUtils::setDryRunMode(true);
	//DbUpJob::setScriptExecutionImpl('RandFailRun');
}

if ( $menu->isOptionCalled('perform-dryrun')) {
	// the operator can enable / disable dry runs on php scripts
	// note that this option will be overwritten by the following one
	DbUpJob::setScriptExecutionImpl('RunScriptWithDryRunImpl');
}

if ($menu->isOptionCalled('script-dryrun')){
	// switch script execution implementations that runs script in dry run mode
	// yml and sql do not yet support dry run, scripts of that type declared in the manifest,
	// will be ignored.
	DbUpJob::setScriptExecutionImpl('DryRunScriptImpl');
}
// test is usefull to run store the changelog data in a test db / table. SHould only be used on dev env.
if ($menu->isOptionCalled('test')){
	// we don't want ot actually run the script but  we want to store changelog data so we try things
	// like resume session and other operational scenarios. The following sets the changelog db and db name to
	// test friendly values. It requires that you have the correct db setup on your environment, see mktDbChangelogTest.php
	mktDbChangelog::setTestMode();
}





// filters throw exceptions on failure. some will force an exit. So we make sure there are not stateful information until the
// point wehre we excute the main action
try{
	$ret = ToolsHelper::getFilterChain()
	// we validate that connectivity to changelog db host is ok
	-> addFilter(new FilterCustom(
			function(){
				// checks connectivty to changelog db, @see mktDbChangelog more details
				try{
					ToolsHelper::checkDbChangelogAccess();
				}catch(mktDataSourceException $e){
					ToolsHelper::getPrinter()-> echo_err("Cannot connect to the changelog DB [". mktDbChangelog::$db_host . ']');
					exit (1);
				}
			}
	))
	// validate connectivity needed to app datasource
	-> addFilter(new FilterCustom(
			function (){
				list($host, $port, $user, $pass, $db) = ToolsHelper::getDataSource()->explode();
				// get db settings
				// check db connectivity to mmc
				try{
					ToolsHelper::checkMysqlConnectivity($host, $port, $user, $pass);
				}catch(Exception $e){
					ToolsHelper::getPrinter()-> echo_err("Cannot connect to the DB to be upgraded [". $host . ":" . $port . ']');
					exit (1);
				}

			}
	) )
	// validate that call to web service for DSN is working and endpoint is up
	// only when multi db support is not disabled
	-> addFilter(new FilterCustom(
			function ( $printer, $multi_db){
				if ($multi_db != "off" && !is_file($multi_db)){
					try{
						ToolsHelper::checkSocketConn('localhost', '8099');
					}catch(Exception $e){
						$printer->echo_err("Could not connect to the API needed for multi db customer support, with msg: " . $e->getMessage());
						exit(1);
					}
				}elseif ($multi_db != "off" && is_file($multi_db)){
					$printer->echo_msg("You are using a multi-db pre-generated DSN list file.");

				}else{
					$printer->echo_msg("You have turned multi db support off!");
				}
			}, array( ToolsHelper::getPrinter(), $menu->get('multi-db'))
	))
	// notify the user of the db to be upgraded
	-> addFilter(new FilterCustom(
			function ($label, Printer $printer){
				// notice on the db to be upgraded
				list($host, $port, $user, $pass, $db) =  ToolsHelper::getDataSource()->explode();
				$ret = mktDbChangelog::findLabel($label);
				$msg = "You are about to perform a database upgrade on [" . $host . "]";
				if ($ret !== false){
					$msg .= "\n" . "And the label [" . $label . "] already exists, you're about to RESUME a previous run. ";
				}
				$printer->echo_warning_banner($msg);
				if ( $printer-> read_input("Are you sure you want to proceed?") !== true){
					exit(1);
				}
			}, array($menu->get('label'), ToolsHelper::getPrinter())
	))

	// log debug statements
	-> addFilter(new FilterLogging(Logger::DEBUG))
	// authenticate the user executing th escript
	-> addFilter(new FilterAuthenticate($user))
	-> doChain($main_action);
}catch(Exception $e){
	ToolsHelper::getPrinter()-> echo_err("chain execution threw an exception [" . get_class($e) . "]\n\n w/ msg [". $e->getMessage() . '] trace ['. $e->getTraceAsString() . ']');
	exit (1);
}
// most function only return a boolean
if (is_int($ret)) {
	exit ($ret);
}elseif ($ret == true){
	exit (0);
}else{
	exit(1);
}