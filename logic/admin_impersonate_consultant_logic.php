<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_impersonate_consultant_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_POST['impersonate_button'])){
		$conid = $_POST['conid'];
		$name = getNameByID($conid);
		
		echo '<h3>You are about to impersonate: ' . $name . '</h3>';
		
		echo '<br><br>';
		echo 'Do you wish to continue?<br><br>';
		
		echo '<form action="admin_manage_consultants.php?action=impersonate" method="post">';
			echo '<input type="hidden" name="consultant_impersonated" value="' . $conid . '" />';
			echo '<input type="submit" name="confirm_impersonate_button" value="Yes" />';
			echo '&nbsp;&nbsp;';
			echo generateButton("admin.php", "No");
		echo '</form>';
	}
	elseif(isset($_POST['confirm_impersonate_button'])){
		if(isset($_POST['consultant_impersonated'])){
			$consultant_impersonated = $_POST['consultant_impersonated'];
			$sql = sprintf("SELECT * FROM consultants WHERE conid = %d", verifySQL($consultant_impersonated));
			$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			$row = mysql_fetch_array($result);
			
			$splitname = explode(" ", $row['name'], 20);
			$_SESSION['imersonate_mode'] = 1;
			$_SESSION['admin_impersonating'] = $_SESSION['conid'];
			$_SESSION['conid'] = $row['conid'];
			$_SESSION['loggedin'] = 1;
			$_SESSION['first_name'] = $splitname[0];
			$_SESSION['last_name'] = $splitname[1];
			$_SESSION['name'] = $row['name'];
			$_SESSION['engr_username'] = $row['engr_username'];
			$_SESSION['admin'] = $row['admin'];
			
			echo '<h2>Impersonation is complete!</h2>';
			echo '<br>';
			echo '<h2><a href="index.php">Click Here</a> to go to the Consultant Interface</h2>';
		}
		else {
			generate_error("Error: Student ID Not Set!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 0);
		}
	}
	else {
		$getConsultantsSQL = sprintf("SELECT conid, name FROM consultants ORDER BY name");
		$result = mysql_query($getConsultantsSQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		echo '<h3>Impersonating a consultant allows an administrator to go into the consultant interface as any user.  This give an administrator the ability to perform actions on behaf of a consultant.</h3>';
		
		echo '<h3>Please select the Consultant you want to impersonate:</h3>';
		echo '<form name="impersonate_form" action="admin_manage_consultants.php?action=impersonate" method="post">';
			echo '<select name="conid">';
				while($row = mysql_fetch_array($result)){
					echo '<option value="' . $row['conid'] . '">' . $row['name'] . '</option>';
				}
			echo '</select>';
			echo '<br>';
			echo '<br>';
			echo '<input type="submit" name="impersonate_button" value="Impersonate"/>';
			echo '&nbsp;&nbsp;';
			echo generateButton("admin.php", "Back");
		echo '</form>';
	}
?>