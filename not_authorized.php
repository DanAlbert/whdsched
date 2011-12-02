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
	echo '<br><br><br><br><br>';
	echo '<div id="errorpage">';
		echo "<h1>Hovland Scheduler</h1><br>";	
		echo '<div id="error">';
				echo "You are not authorized to work in this lab.  
					  Contact the lab administrator if you feel you have received this message in error.";
			echo '<br><br>';
		echo '</div>';
		echo '<br>';
		echo '<input type=button value="Back" onClick="history.go(-1)">';	
	echo '</div>';
?>