<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_edit_schedule_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_GET['lab_id'])){
		$lab_id = $_GET['lab_id'];
	}
	else {
		$lab_id = "";
	}
	
	if(isset($_GET['day'])){
		$day = $_GET['day'];
	}
	else {
		$day = "";
	}
	
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = "";
	}
	
	if(isset($_POST['update_schedule_button'])){
		if(isset($_POST['num_fields'])){
			$numFields = $_POST['num_fields'];
			for($i = 0; $i < $numFields; $i++){
				$schedule_id = "schedule_id" . $i;
				$consultant1 = "consultant1" . $i;
				$consultant2 = "consultant2" . $i;
				
				if(isset($_POST[$consultant1]) && isset($_POST[$schedule_id])){
					$id = $_POST[$schedule_id];
					$con1 = $_POST[$consultant1];
					if($_POST[$consultant2] != ""){
						$con2 = $_POST[$consultant2];
					}
					
					$updateConSQL = sprintf("UPDATE schedule SET ");
					$updateConSQL .= sprintf("consultant1 = %d", verifySQL($con1));
					if($_POST[$consultant2] != ""){
						$updateConSQL .= sprintf(", consultant2 = %d", verifySQL($con2));
					}
					$updateConSQL .= sprintf(" WHERE schedule_id = %d LIMIT 1", verifySQL($id));
				
					$result = mysql_query($updateConSQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					
					if($result){
						echo '<br>';	
						echo "<h3>Schedule Record " . $_POST[$schedule_id] . " was updated successfully!<h3>";
						echo "<br>";
					}
					else {
						generate_error("Record Update Failed!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				else {
					generate_error("Error Transferring Data For Update!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				}
			}
		}
		else {
			generate_error("Unable to Update Schedule!<br>Please Try Again!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
		echo '<br>';
		echo generateButton('admin_manage_schedule.php?action=edit', 'Back');
	
	}
	else {
		if($lab_id != ""){
			if($day == ""){
				generateDayMenu($action, $lab_id);
				echo '<br><br>';
				echo generateButton('admin_manage_schedule.php?action=edit', 'Back');
			}
			else {
				echo '<br><h1><b>' . getLabDisplayName($lab_id) . ':</b></h1>';
				$number_consultants = getNumPositions($lab_id);
				
				$query = sprintf("SELECT * FROM schedule WHERE lab_id = %d AND dayofweek = %d ORDER BY dayofweek, start_time", verifySQL($lab_id), verifySQL($day));
				$result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				$numRows = mysql_num_rows($result);
				echo '<br>';
				echo '<table border="1" align="center">';
					echo '<tr><td colspan="3" align="center"><h2><b>' . dayOfWeekToString($day) . ':</b></h2></td></tr>';
					echo '<tr>';
						echo '<th align="center">Time:</th>';
						echo '<th align="center">Consultant 1:</th>';
						if($number_consultants == 2){
							echo '<th align="center">Consultant 2:</th>';
						}
					echo '</tr>';
				echo '<form action="admin_manage_schedule.php?action=edit" method="post">';
					$i = 0;
					while($row = mysql_fetch_array($result)){
						echo '<tr>';
							echo '<input type="hidden" name="schedule_id' . $i . '" value="' . $row['schedule_id'] . '" />';
							echo '<td align="center">' . convertTime($row['start_time']) . " - " . convertTime($row['end_time']) . '</td>';
							echo '<td>';
							generateUserList($row['consultant1'], 'consultant1' . $i);
							echo '</td>';
							if($number_consultants == 2){
								echo '<td>';
								generateUserList($row['consultant2'], 'consultant2' . $i);
								echo '</td>';
							}
						echo '</tr>';
						$i++;
					}
				echo '</table>';	
				echo '<br>';
				echo '<input type="hidden" name="num_fields" value="' . $numRows .'" />';
				echo '<input type="submit" name="update_schedule_button" value="Update Schedule" />';
				echo '</form>';
				echo '<br><br>';
				echo generateButton('admin_manage_schedule.php?action=edit&lab_id=' . $lab_id, 'Back');
			}
		}
		else {
			
			$sql = sprintf("SELECT * FROM labs");
			$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			if($result){
					echo '<table align="center">';
						while($row = mysql_fetch_array($result)){
							$entry = '<tr><td><ul id="sddm"><li><a href="admin_manage_schedule.php?action=edit&lab_id=';
							$entry .= $row['lab_id'];
							$entry .= '">' . $row['display_name'] . '</a></li></ul></td></tr>';
							echo $entry;
						}
					echo '</table>';
			}
			else {
				generate_error("Error Fetching Location List!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			}
		}
	}
?>