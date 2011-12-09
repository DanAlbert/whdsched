<?php 
	session_start();
?>
<html>
	<head>
    	<title>Hovland Scheduler</title>
    </head>
</html>

<script type="text/javascript" src="includes/nifty/niftycube.js"></script>

<link rel="stylesheet" type="text/css" href="includes/css/style.css"/>

<style type="text/css">
	a:active {color: #FFF;}
	a:visited {color: #FFF;}
	a:link {color: #FFF;}
	a:hover {color: #FFF;}
</style>

<script type="text/javascript">
	window.onload=function(){
		Nifty("div#errorpage");
	}
</script>

<?php	
	if($_SESSION['error_message'] != NULL){
		$error =  $_SESSION['error_message'];
	}
	else {
		$error = "An Unknown Error Has Occured";
	}
	if($_SESSION['error_email_admin'] != NULL){
		$email_admin = $_SESSION['error_email_admin'];
	}
	else {
		$email_admin = 0;
	}
	
	echo '<br><br><br><br><br>';
	echo '<div id="errorpage">';
		echo "<h1>Hovland Scheduler</h1><br>";	
		echo '<div id="error">';
			echo $error;
			echo '<br><br>';
		echo '</div>';
		if($email_admin == 1){
			echo "The Administrator has been e-mailed";
		}
		echo '<br>';
		echo '<input type=button value="Back" onClick="history.go(-1)">';	
	echo '</div>';
?>