<?php
	if(isset($_POST['delete_temp_shift_confirm'])){
		$tempshift_id =  $_POST['tempshift_id'];
		$sql = sprintf("DELETE FROM tempshifts WHERE tempshift_id = %d LIMIT 1", verifySQL($tempshift_id));
		$result = mysql_query($sql, $conn);
		if($result){
			echo '<h2>Temp Shift was successfully deleted!</h2>';
		}
		else {
			generate_error("Error Deleting Temp Shift", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	elseif(isset($_POST['delete_temp_shift'])){
		echo '<h2>Are you sure you want to delete this temp shift?</h2>';
		echo '<br><br>';
		echo '<form action="admin_utilities_temp_shifts.php?action=edit" method="post">';
			echo '<input type="hidden" name="tempshift_id" value="' . $_POST['tempshift_id'] . '"/>';
			echo '<input type="submit" name="delete_temp_shift_confirm" value="Yes" />';
			echo '&nbsp;&nbsp;';
			echo generateButton("admin_utilities_temp_shifts.php", "No");
		echo '</form>';
	}
	elseif(isset($_POST['update_temp_shift'])){
		$regular_consultant = $_POST['regular_consultant'];
		$temp_consultant = $_POST['temp_consultant']; 
		$tempshift_id =  $_POST['tempshift_id'];
		
		$sql = sprintf("UPDATE tempshifts SET regular_consultant = %d", verifySQL($regular_consultant));
		if($temp_consultant != ""){
			$sql .= sprintf(", temp_consultant = %d, taken = 1", verifySQL($temp_consultant));
		}
		else {
			$sql .= sprintf(", taken = 0");
		}
		$sql .= sprintf(" WHERE tempshift_id = %d", verifySQL($tempshift_id));
		$result = mysql_query($sql, $conn);
		if($result){
			echo '<h2>Temp Shift was successfully updated!</h2>';
		}
		else {
			generate_error("Error Updating Temp Shift", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	else {
		if(isset($_GET['shiftid'])){
			$shiftid = $_GET['shiftid'];
		}
		else {
			$shiftid = NULL;
		}
		
		$sql = sprintf("SELECT * 
						FROM tempshifts
						WHERE tempshift_id = %d
						LIMIT 1", verifySQL($shiftid));
		$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		$row = mysql_fetch_array($result);
		echo '<form action="admin_utilities_temp_shifts.php?action=edit" method="post">';
			echo '<table border="1" align="center">';
				echo '<tr>';
					echo '<td>Shift ID:</td>';
					echo '<td>' . $row['tempshift_id'] . '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Date:</td>';
					echo '<td>' . $row['month'] . '-' . $row['day'] . '-' . $row['year'] . '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Time:</td>';
					echo '<td>' . convertTime($row['start_time']) . '-' . convertTime($row['end_time']) . '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Location:</td>';
					echo '<td>' . getLabDisplayName($row['lab_id']) . '</td>';		
				echo '</tr>';
				echo '<tr>';
					echo '<td>Regular Consultant:</td>';
					echo '<td>';
						generateUserList($row['regular_consultant'], "regular_consultant");
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					if($row['taken'] == 1){
						echo '<td>Temp Consultant:</td>';
						echo '<td>';
							generateUserListWithNULL($row['temp_consultant'], "temp_consultant");
						echo '</td>';
					}
					else {
						echo '<td>Temp Consultant:</td>';
						echo '<td>';
							generateUserListWithNULL(0, "temp_consultant");
						echo '</td>';
					}	
				echo '</tr>';
				if($row['reason'] != NULL){
					echo '<tr>';
						echo '<td>Reason:</td>';
						echo '<td>'. $row['reason'] . '</td>';
					echo '</tr>';
				}
				if($row['taken'] == 1 && $row['time_taken'] != NULL){
					echo '<tr>';
						echo '<td>Time Taken:</td>';
						echo '<td>'. $row['time_taken'] . '</td>';
					echo '</tr>';
				}
			echo '</table>';
			generatePageBreaks(3);
			echo '<input type="hidden" name="tempshift_id" value="' . $row['tempshift_id'] . '"';
			echo '<input type="submit" name="update_temp_shift" value="Update Temp Shift" />';
			echo '&nbsp;&nbsp;';
			echo generateButton("admin_utilities_temp_shifts.php", "Cancel");
			echo '&nbsp;&nbsp;';
			echo '<input type="submit" name="delete_temp_shift" value="Delete Temp Shift" />';
		echo '</form>';
	}

?>