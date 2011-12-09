<?php
	session_start();
	
	header("Location: error.php");
	
	header('WWW-Authenticate: Basic realm="COE Wireless Helpdesk Staff"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are now logged out.';
	
	$_SESSION['loggedin'] = 0;
	$_SESSION['first_name'] = "";
	$_SESSION['last_name'] = "";
	$_SESSION['full_name'] = "";
	$_SESSION['engr_username'] = "";
	$_SESSION['account_type'] = "";
	$_SESSION['imersonate_mode'] = 0;
	$_SESSION['imersonate_user'] = ""; 
	
	exit;
?>


