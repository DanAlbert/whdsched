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
	'test|t'  => 'Show script results without sending mail',
	'env|e-s' => 'Application environment to use configuration from (defaults to development)',
	'help|h'  => 'Help -- usage message',
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
$test = $getopt->getOption('t');
$env = $getopt->getOption('e');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// Initialize Zend_Application
$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);

// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('autoloader');
$bootstrap->bootstrap('config');
$bootstrap->bootstrap('db');
$bootstrap->bootstrap('mail');

try
{
	$consultantMapper = new Application_Model_ConsultantMapper();
	$tempMapper = new Application_Model_TempShiftMapper();
	
	$consultants = $consultantMapper->fetchAll();
	
	$temps = array();
	foreach ($tempMapper->fetchAvailable() as $temp)
	{
		$temps[] = "<li>{$temp}</li>";
	}
	
	if (count($temps) == 0)
	{
		if ($test === true)
		{
			print 'No temp shifts outstanding' . PHP_EOL;
		}
		
		return true;
	}
	
	$options = $bootstrap->getOption('mail');
	
	$mail = new Zend_Mail();
	$mail->setBodyHtml('<ul>' . implode(PHP_EOL, $temps) . '</ul>');
	$mail->addTo($options['to']['address'], $options['to']['name']);
	$mail->setSubject($options['nightly']['subject']);
	
	foreach ($consultants as $consultant)
	{
		if ($consultant->getReceiveNightly())
		{
			$mail->addBcc($consultant->getEmail(), $consultant->getName());
		}
	}
	
	if ($test === null)
	{
		print 'Sending to ' . count($mail->getRecipients()) . ' recipients' . PHP_EOL;
		$mail->send();
	}
	else
	{
		print PHP_EOL;
		print 'To: ' . implode(', ', $mail->getRecipients()) . PHP_EOL;
		print "From: {$mail->getFrom()}" . PHP_EOL;
		print "Subject: {$mail->getSubject()}" . PHP_EOL;
		print "Body: {$mail->getBodyHtml(true)}" . PHP_EOL;
	}
}
catch (Exception $e)
{
	echo 'AN ERROR HAS OCCURED:'. PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
	return false;
}

return true;

