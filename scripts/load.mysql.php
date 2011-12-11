<?php

// Initialize the application path and autoloading
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
	APPLICATION_PATH . '/../library',
	get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
	'withdata|w' => 'Load database with sample data',
	'env|e-s'    => 'Application environment for which to create database (defaults to development)',
	'help|h'     => 'Help -- usage message',
));

try
{
	$getopt->parse();
}
catch (Zend_Console_Getopt_Exception $e)
{
	// Bad options passes. Report usage
	echo $e->getUsageMessage();
	return false;
}

// If help requested, report usage message
if ($getopt->getOption('h'))
{
	echo $getopt->getUsageMessage();
	return true;
}

// Initialize values based on presence or absence of CLI options
$withData = $getopt->getOption('w');
$env = $getopt->getOption('e');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// Initialize Zend_Application
$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);

// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');

if ('testing' != APPLICATION_ENV)
{
	echo 'Writing Database in (control-c to cancel): ' . PHP_EOL;
	for ($i = 5; $i > 0; $i--)
	{
		echo $i . "\r";
		sleep(1);
	}
}

// Check to see if database file already exists
$options = $bootstrap->getOption('resources');
/*$dbFile = $options['db']['params']['dbname'];
if (file_exists($dbFile))
{
	unlink($dbFile);
}*/

try
{
	$schemaSql = file_get_contents(dirname(__FILE__) . '/schema.mysql.sql');
	$dbAdapter->getConnection()->exec($schemaSql);
	//chmod($dbFile, 0666);

	if ('testing' != APPLICATION_ENV)
	{
		echo PHP_EOL;
		echo 'Database Created';
		echo PHP_EOL;
	}

	if ($withData)
	{
		$dataSql = file_get_contents(dirname(__FILE__) . '/data.mysql.sql');
		$dbAdapter->getConnection()->exec($dataSql);
		if ('testing' != APPLICATION_ENV)
		{
			echo 'Data Loaded.';
			echo PHP_EOL;
		}
	}
}
catch (Exception $e)
{
	echo 'AN ERROR HAS OCCURED:'. PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
	return false;
}

return true;

