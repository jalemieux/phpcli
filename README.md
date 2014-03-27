# PHPCli

phpcli provides a set of php libs that make php suck less at building production grade CLI based tools. Phpcli was designed with support Linux/*nix support in mind.
Do not try to run this on a windows box :( 
Mac is ok :)

## Installation

There are no pear / pecl module at this time.

## Prerequisites

Works on Php >= 5.3.
Requires pecl Yaml to be installed

## Usage

Start all your script by loading the app container. Assuming you put your files in lib/, have this as the first lines of your script:
    
    include realpath(dirname(__FILE__) . '/../phpcli.inc');
    use PhpCli\App;

This loads the app, and its context. The context configuration is kept in  phpcli/config/config.yml, e.g.:

    modules:
      logger: 
        class: PhpCli\DefaultLogger
        logfile: '/var/log/phpcli.log'
        level: 'DEBUG'
        timezone: 'America/Los_Angeles'
      printer:
        class: PhpCli\DefaultPrinter

Generally, any classes defined in the context can be extended and customized or rewritten as implement the correct interfaces.
Currently, there are two modules defined in the context, logger and printer. We'll go over them on the next sections. 

Once you have loaded the app, its context is available anywhere in your script by calling 
     
     $ctx = App::getInstance();

For example, to access the App logger:

     $logger = App::getInstace()->getLogger();

### Logger

As the name implies, it's used to log stuff. You can use a off the shelf logger, instead of the DefaultLogger implementation provided here. However, make sure the off the self logger implements methods defined in BaseLogger.

If you are using the default logger, you can set the path of the logs it wirtes to in phpcli/config/config.yml, or you can also set it up explicitely:

    App::getInstance()->setLogger(new PhpCli\DefaultLogger($path_to_log, 'America/Los_Angeles'))


#### Custom Logger

If you want to use a different logger than the one provided in PHPCli (noone would blame you), you can either configure it in phpcli/config/config.yml, or set it explicitely in your script, e.g.:
  
    $logger = new MyAwesomeLogger();
    App::getInstance()->setLogger($logger);

##### Accessing the Logger in your script

You can access the logger anywhere in your scripts, by calling:

    $logger = App::getInstance()->getLogger();
    $logger->debug("foo!");

### Printer

As you build php CLI tools for unix users you quickly come to the realization that input and output are not always as good as one would expect. You can go curse berserk, or look for a middle ground. We like middle grounds, so we built a simple linux (xterm) lib which helps displaying stuff on a terminal.

Mainly, 2 printer implementations come with PHPCli, the default printer and the linux printer. Depending on where you run your script, you might chose one or the other. Note that the linux printer (LinuxShellPrinter) extends the default printer (DefaultPrinter), as should any custom printer you decide to use.

A simple example using the printer would be:

    $default_printer = App::getInstance()->getPrinter();
    $default_printer->echo_msg("Hello World");
    
#### Custom Printer

If you want to build your own printer, make sure it extends DefaultPrinter. You can then set it in phpcli/config/config.yml, or set it explicitely in your script:

    $printer = new MyWonderfulPrinter();
    App::getInstance()->setPrinter($printer)

### System Lib

One of the many things you run into when building complex CLI tool, is ensuring utilities are run as the right user. E.g. you might have only a specific user's keys distributed to production systems, and your script needs to ssh to these systems.

Here's an example on how to enforce the right user:

    if (! System::enforceUserAndGroup('kumar', 'serviceaccts' )){
      $printer-> echo_err('Bad user / group');
      exit(1);
    }
    


### Option Menu

Anyone who wrote production grade script can tell you how much of a time drain is handling CLI arguments. Since we hate time drains, and stick to DRY principles, OptionMenu was written. It's meant to be flexible and easy to maintain.


## Examples

Look at scripts leveraging phpcli libs in examples/
* basic.php - shows how to load the app context and basic logger and printer usage
* option_menu.php - shows how to us ethe option menu lib


## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
