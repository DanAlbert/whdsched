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

$options = $bootstrap->getOption('mail');

try
{
	$consultantMapper = new Application_Model_ConsultantMapper();
	$tempMapper = new Application_Model_TempShiftMapper();
	
	$consultants = $consultantMapper->fetchAll();
	
	$site = $bootstrap->getOption('site');
	$scheme = $site['scheme'];
	$host = $site['host'];
	$root = $site['root'];

	$temps = $tempMapper->fetchAvailable();
	if (count($temps) == 0)
	{
		if ($test === true)
		{
			print 'No temp shifts outstanding' . PHP_EOL;
		}
		
		return true;
	}
	
	foreach ($temps as $temp)
	{
		// If the shift is still open and occurs later today
		if ($temp->getShift()->getDate() == date('Y-m-d'))
		{
			// Warn the consultant responsible for the shift
			$consultant = $temp->getShift()->getConsultant();
			
			// This is a special shift and has not been assigned, skip it
			if ($consultant === null)
			{
				continue;
			}
			
			list($start, $end) = explode(' - ', $temp->getShift()->getTimeString());
			$html = "Your shift today from {$start} to {$end} has not been taken<br />";
			$html .= "Unless someone claims it before {$start}, " .
					"you will still be responsible for this shift.";
			
			$mail = new Zend_Mail();
			$mail->setBodyHtml($html);
			$mail->addTo($consultant->getEmail(), $consultant->getName());
			$mail->setSubject($options['warning']['subject']);
			
			if ($test === null)
			{
				$mail->send();
			}
			else
			{
				print "Sending warning to {$consultant->getName()}" . PHP_EOL;
			}
		}
	}
	
	$temps = organizeTemps($temps);
	
	$html = '<table>';
	foreach ($temps as $heading => $group)
	{
		$links = array();
		$rows = array();
		foreach ($group as $temp)
		{
			$url = "{$scheme}://{$host}{$root}/temp/take/id/{$temp->getId()}";
			$link = '<a href="' . $url . '">Claim this shift</a>';
			
			$date = date('D, M j', $temp->getShift()->getStartTimeStamp());
			
			$start = date('H:i', $temp->getShift()->getStartTimeStamp());
			$end = date('H:i', $temp->getShift()->getEndTimeStamp());
			$time = "{$start} - {$end}";
			
			$location = '@ ' . $temp->getShift()->getLocation();
			
			if ($temp->getShift()->getConsultant() !== null)
			{
				$name = 'for ' . $temp->getShift()->getConsultant()->getName();
				$text = "{$date} {$time} for {$name} @ {$location}";
			}
			else
			{
				$name = '&nbsp;';
				$text = "{$date} {$time} @ {$location}";
			}
			
			$date = "<td>{$date}</td>";
			$time = "<td>{$time}</td>";
			$location = "<td>{$location}</td>";
			$name = "<td>{$name}</td>";
			$rows[] = "<tr>{$date}{$time}{$location}{$name}{$link}</tr>";
			$links[] = "<li>{$text} {$link}</li>";
		}
		
		$html .= '<tr colspan="5"><td><h3>' . $heading . '</h3></td></tr>';
		//$html .= '<ul>' . implode(PHP_EOL, $links) . '</ul>';
		$html .=  implode(PHP_EOL, $rows);
	}
	$html .= '</table>';
	
	$mail = new Zend_Mail();
	$mail->setBodyHtml($html);
		
	if (isset($options['to']))
	{
		$mail->addTo($options['to']['address'], $options['to']['name']);
		$minRecv = 0;
	}
	else
	{
		$mail->addTo('');
		$minRecv = 1;
	}
	
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
		if (count($mail->getRecipients()) > $minRecv)
		{
			print 'Sending to ' . count($mail->getRecipients()) . ' recipients' . PHP_EOL;
			$mail->send();
		}
		else
		{
			print 'No recipients' . PHP_EOL;
		}
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

function organizeTemps(array $temps)
{
	$today = mktime(0, 0, 0);
	$urgent = strtotime('+3 days', $today);
	$week = strtotime('+1 week', $today);
	
	$urgentTemps = array();
	$weekTemps = array();
	$otherTemps = array();
	
	foreach ($temps as $temp)
	{
		if ($temp->getShift()->getStartTimestamp() < $urgent)
		{
			$urgentTemps[] = $temp;
		}
		else if ($temp->getShift()->getStartTimestamp() < $week)
		{
			$weekTemps[] = $temp;
		}
		else
		{
			$otherTemps[] = $temp;
		}
	}
	
	usort($urgentTemps, 'cmpTempShift');
	usort($weekTemps, 'cmpTempShift');
	usort($otherTemps, 'cmpTempShift');
	
	$temps = array();
	if (count($urgentTemps) > 0)
	{
		$temps['URGENT'] = $urgentTemps;
	}
	
	if (count($weekTemps) > 0)
	{
		$temps['Next 7 Days'] = $weekTemps;
	}
	
	if (count($otherTemps) > 0)
	{
		$temps['Future'] = $otherTemps;
	}
	
	return $temps;
}

function cmpTempShift(Application_Model_TempShift $a, Application_Model_TempShift $b)
{
	$atime = $a->getShift()->getStartTimestamp();
	$btime = $b->getShift()->getStartTimestamp();

	if ($atime == $btime)
	{
		return 0;
	}

	return ($atime < $btime) ? -1 : 1;
}
