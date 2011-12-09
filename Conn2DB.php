<?php
	
	//include '../../config.php';
	$dbhost = "mysql.gingerhq.net";
	$dbuser = "helpdesk";
	$dbpass = "";
	$dbname = "helpdesk";
	
	
	$conn = mysql_connect($dbhost, $dbuser, $dbpass)
   		or die("Error connecting to database server: ". mysql_error());

	mysql_select_db($dbname, $conn)
    	or die("Error selecting database: $dbname");
    	

?>
