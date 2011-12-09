<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_delete_schedule_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_GET['lab_id'])){
		$lab_id = $_GET['lab_id'];
	}
	else {
		$lab_id = "";
	}
	
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = "";
	}
	
	if(isset($_POST['delete_confirm_button'])){
		if(isset($_POST['lab_id']) && isset($_POST['dayofweek'])){
			$lab_id = $_POST['lab_id'];
			$dayofweek = $_POST['dayofweek'];
			$dayofweekstring = dayOfWeekToString($dayofweek);
			$dayofweeklower = strtolower($dayofweekstring);
			$lab_name = getLabDisplayName($lab_id);
			
			$sql = sprintf("DELETE FROM schedule WHERE dayofweek = %d AND lab_id = %d LIMIT 1", verifySQL($dayofweek), verifySQL($lab_id));
			
			$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			
			if($result){
				$fieldName = $dayofweeklower . "_setup";
				$sql = sprintf("UPDATE labs SET %s = 0 WHERE lab_id = %d LIMIT 1", verifySQL($fieldName), verifySQL($lab_id));
				$result2 = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				if($result2){
					echo "<h2>The $dayofweekstring schedule for $lab_name has been deleted.</h2>";
					generatePageBreaks(5);
					echo generateButton('admin_manage_schedule.php?action=delete', 'Back');
				}
				else {
					generate_error("Error Setting Lab To Inactive!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				}
			}
			else {
				generate_error("Error Deleting Records From Database!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			}
		}
		else {
			generate_error("Error getting Lab ID and Day of Week!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	elseif(isset($_POST['delete_schedule_button'])){
		if(isset($_POST['lab_id']) && isset($_POST['dayofweek'])){
			$lab_id = $_POST['lab_id'];
			$dayofweek = $_POST['dayofweek'];
			$dayofweekstring = dayOfWeekToString($dayofweek);
			$lab_name = getLabDisplayName($lab_id);
			generatePageBreaks(2);
			echo '<h2>Are you sure you want to delete the ' . $dayofweekstring . ' schedule for ' . $lab_name . '?</h2>';
			generatePageBreaks(2);
			echo '<form method="post" action="admin_manage_schedule.php?action=delete">';
				echo '<input type="hidden" name="lab_id" value="' . $lab_id .'">';
				echo '<input type="hidden" name="dayofweek" value="' . $dayofweek .'">';
				echo '<input type="submit" name="delete_confirm_button" value="Yes">';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo generateButton('admin_manage_schedule.php?action=delete', 'No');
			echo '</form>';
		}
		generatePageBreaks(5);
	}
	else {
		$sql = sprintf("SELECT * FROM labs");
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
			echo '<table align="center" border="1" colspacing="3">';
				echo '<tr>';
					echo '<td align="center">&nbsp;</td>';
					echo '<td colspan="7" align="center">Day:</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td align="center">Lab:</td>';
					echo '<td align="center">Sun:</td>';
					echo '<td align="center">Mon:</td>';
					echo '<td align="center">Tue:</td>';
					echo '<td align="center">Wed:</td>';
					echo '<td align="center">Thu:</td>';
					echo '<td align="center">Fri:</td>';
					echo '<td align="center">Sat:</td>';
				echo '</tr>';
				while($row = mysql_fetch_array($result)){
					echo '<tr>';
							echo '<td align="center">' . $row['display_name'] . '</td>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="0">';
								if($row['sunday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="1">';
								if($row['monday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="2">';
								if($row['tuesday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="3">';
								if($row['wednesday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="4">';
								if($row['thursday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="5">';
								if($row['friday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=delete" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="6">';
								if($row['saturday_setup'] == 1){
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Delete" name="delete_schedule_button" disabled="disabled"></td>';
								}
							echo '</form>';
					echo '</tr>';
				}
			echo '</table>';
		}
		else {
			generate_error("Error fetching Location List!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
?>