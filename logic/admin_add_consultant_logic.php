<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_add_consultant_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset( $_POST['add_consultant_submit_button'])){
		$name = $_POST['name'];
		$engr_username = $_POST['engr_username'];
		if(isset($_POST['admin'])){
			$admin = $_POST['admin'];
		}else{
			$admin = 0;
		}
		
		
		$countSQL = sprintf("SELECT engr_username FROM consultants WHERE engr_username = '%s'", verifySQL($engr_username));
		$countResult = mysql_query($countSQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		$count = mysql_num_rows($countResult);
		
		if($count == 0){
			$sql = sprintf("INSERT INTO consultants (name, engr_username, admin, shifts_temped)");
			$sql .= sprintf("VALUES ('%s', '%s', '%d', 0)", verifySQL($name), verifySQL($engr_username), verifySQL($admin));
			mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			
			echo "<h3>" . $name . " has been successfully added to the database!<h3>";
			echo '<br>';
			echo '<br>';
			echo generateButton("admin_manage_consultants.php?action=view", "View Consultants");
		}
		else{
			echo "<h3>The consultant you entered is already in the database!</h3>";
			echo '<br>';
			echo generateButton("admin_manage_consultants.php?action=add", "Back");
		}	
	}
	else {
		echo '<form id="addConsultant" action="admin_manage_consultants.php?action=add" method="post">';
			echo '<table align="center">';
				echo '<tr>';
					echo '<td>Consultants Full Name:</td>';
					echo '<td><input type="text" name="name" width="30" maxlength="30"  /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>ENGR Username:</td>';
					echo '<td><input type="text" name="engr_username" width="20" maxlength="20" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Administrator:</td>';
					echo '<td>';
						echo '<input type="checkbox" name="admin" value="1" />';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			echo '<br>';
			echo '<input type="submit" name="add_consultant_submit_button" value="Add User" />';
			echo '&nbsp;&nbsp;';
			echo '<input type="button" onClick="window.location=' . "'admin.php'" . '" value="Back"/>';
		echo '</form>';
	}
?>