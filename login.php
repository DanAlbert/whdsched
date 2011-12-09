<?php
	session_start();
	
	$_SESSION['loggedin'] = 0;
	
	require('Conn2DB.php');							//Connection Information
	require('includes/datetime_functions.php');		//Date / Time Functions
	require('includes/email_system.php');			//Email System Functions
	require('includes/error_system.php');			//Error System Functions
	
	if(isset($_SERVER['REMOTE_USER'])){
		$username = $_SERVER['REMOTE_USER'];
		
		
		
		
		$username = stripslashes($username);
		$username = mysql_real_escape_string($username);

		
		$sql = sprintf("SELECT * FROM consultants 
						WHERE engr_username = '%s'", verifySQL($username));
		
		$result = mysql_query($sql, $conn);
		if(!$result){
			generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
		
		$count = mysql_num_rows($result);
		
		if($count == 1){
			$user = mysql_fetch_array($result)
				or generate_error("Error processing results", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			
			$conid = $user['conid'];
			//$first_name = $user['name'];
			//$last_name = $user['name'];
			$full_name = $user['name'];
			$engr_username = $user['engr_username'];
			$admin = $user['admin'];
			
			$name = explode(" ", $full_name, 20);
			$_SESSION['loggedin'] = 1;
			$_SESSION['conid'] = $conid;
			$_SESSION['first_name'] = $name[0];
			$_SESSION['last_name'] = $name[1];
			$_SESSION['full_name'] = $full_name;
			$_SESSION['engr_username'] = $engr_username;
			$_SESSION['admin'] = $admin;
			$_SESSION['imersonate_mode'] = 0;
			$_SESSION['imersonate_user'] = NULL;	
			
			
			header("Location: index.php");
			exit();	
		}
		else {
			generate_error("You are not authorized to view this!", NULL, $_SERVER['PHP_SELF'], __LINE__, 0);
			header("Location: error.php");
			exit();
			echo '<br>';
			echo 'You are being redirected back to the login page!<br>';
			echo "(If your browser does not support this, " .
				'<a href="error.php">Click Here</a>)';
			die();
		}
	}
	else {
		echo '<script type="text/javascript" src="includes/nifty/niftycube.js"></script>';
		echo '<link rel="stylesheet" type="text/css" href="includes/css/style.css"/>';
		
		echo '<title>Wireless Helpdesk Scheduler</title>';
		
		echo '<script type="text/javascript">';
			echo 'window.onload=function(){';
			echo 'Nifty("div#loginpage,div#error");';
			echo 'show();';
			echo '}';
		echo '</script>';
	
		echo '<br><br><br><br><br>';
		echo '<div id="logincontainer" align="center">';
			echo '<div id="loginpage" align="center">';
				
				echo "<h1>Wireless Helpdesk Scheduler</h1><br>";
				echo '<form name="login" action="login.php" method="post">';
					echo '<table align="center">';
						echo '<tr>';
							echo '<td>Username:</td>';
							echo '<td><input name="username" type="text" /></td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td>Password:</td>';
							echo '<td><input name="password" type="password"  /></td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td colspan="2" align="center"><input name="submit_button" type="submit" value="Login" /></td>';
						echo '</tr>';
					echo '</table>';
				echo '</form>';
			echo '</div>';
		echo '</div>';
	}
	
	echo '<script type="text/javascript">';
		echo 'document.login.username.focus();';
	echo '</script>';
	
	function verifySQL($value){
		if(is_int($value)){
			return $value;
		}
		elseif(is_string($value)){
			$string = mysql_real_escape_string($value);
			return $string;
		}
		else {
			generate_error("A value entered was invalid.  Please try again.", NULL, $_SERVER['PHP_SELF'], __LINE__, 0);
			exit();
		}
	}
?>