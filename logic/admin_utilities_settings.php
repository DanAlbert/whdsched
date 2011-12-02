<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_utilities_settings")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_POST['settings_submit_button'])){
		generatePageBreaks(2);
		
		$sql =  sprintf("UPDATE settings SET value = '%s' WHERE name = 'test_mode' LIMIT 1", verifySQL($_POST['test_mode']));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if(!$result){
			echo "Error Updating Test Mode!";
		}
		
		$sql = sprintf("UPDATE settings SET value = '%s' WHERE name = 'email_to' LIMIT 1", verifySQL($_POST['email_to']));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if(!$result){
			echo "Error Updating E-mail To!";
		}
		
		$sql = sprintf("UPDATE settings SET value = '%s' WHERE name = 'email_from' LIMIT 1", verifySQL($_POST['email_from']));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if(!$result){
			echo "Error Updating E-mail From!";
		}
		
		$sql = sprintf("UPDATE settings SET value = '%s' WHERE name = 'admin_email' LIMIT 1", verifySQL($_POST['admin_email']));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if(!$result){
			echo "Error Updating Admin E-mail!";
		}
		
		$sql = sprintf("UPDATE settings SET value = '%s' WHERE name = 'developer_email' LIMIT 1", verifySQL($_POST['developer_email']));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if(!$result){
			echo "Error Updating Developer E-Mail!";
		}
		
		loadApplicationVariables();
		echo "<h2>Settings Updated!</h2>";
		generatePageBreaks(5);
		echo generateButton("admin_utilities.php?action=settings", "Back");
	}
	else {
		loadApplicationVariables();
		generatePageBreaks(2);
		
		echo '<form action="admin_utilities.php?action=settings" method="post">';
			echo '<table align="center">';
				echo '<tr>';
					echo '<td>Test Mode:</td>';
					echo '<td>&nbsp;&nbsp;&nbsp;</td>';
					echo '<td>';
						echo '<select name="test_mode">';
							if($_SESSION['test_mode'] == 'Yes'){
								echo '<option value="Yes" selected>Yes</option>';
								echo '<option value="No">No</option>';
							}
							else {
								echo '<option value="Yes">Yes</option>';
								echo '<option value="No" selected>No</option>';
							}
						echo '</select>';
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>E-Mail To:</td>';
					echo '<td>&nbsp;&nbsp;&nbsp;</td>';
					echo '<td><input name="email_to" type="text" size="45" value="' . $_SESSION['email_to'] . '" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>E-Mail From:</td>';
					echo '<td>&nbsp;&nbsp;&nbsp;</td>';
					echo '<td><input name="email_from" type="text" size="45" value="' . $_SESSION['email_from'] . '" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Admin E-Mail:</td>';
					echo '<td>&nbsp;&nbsp;&nbsp;</td>';
					echo '<td><input name="admin_email" type="text" size="45" value="' . $_SESSION['admin_email'] . '" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Developer E-Mail:</td>';
					echo '<td>&nbsp;&nbsp;&nbsp;</td>';
					echo '<td><input name="developer_email" type="text" size="45" value="' . $_SESSION['developer_email'] . '" /></td>';
				echo '</tr>';
			echo '</table>';
			generatePageBreaks(2);
			echo '<input type="submit" name="settings_submit_button" value="Save"/>';
			echo '&nbsp;&nbsp;';
			echo generateButton("admin.php", "Cancel");
		echo '</form>';
	}
?>