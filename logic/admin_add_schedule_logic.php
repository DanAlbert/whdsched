<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_add_schedule_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_POST['choose_lab_button'])){
		if(isset($_POST['lab_id']) && isset($_POST['dayofweek'])){
			$lab_id = $_POST['lab_id'];
			$dayofweek = $_POST['dayofweek'];
			
			echo "<h2>" . getLabDisplayName($lab_id) . " - " . $dayofweek . "</h2>";
			
			echo '<form name="add_schedule_form" method="post" action="admin_manage_schedule.php?action=add">';
				$number_consultants = getNumPositions($lab_id);
				$num_hours = 24;
				echo '<table align="center" border="0" cellspacing="8">';
					echo '<tr>';
						echo '<td align="center">Start:</td>';
						echo '<td align="center">End:</td>';
						echo '<td align="center">Consultant 1:</td>';
						if($number_consultants == 2){
							echo '<td align="center">Consultant 2:</td>';
						}
					echo '</tr>';
					for($i = 0; $i < $num_hours; $i++){
						echo '<tr>';
							echo '<td>';
								$startHourName = "startHour" . $i;
								generateHourField($startHourName, $i);
							echo '</td>';  
							echo '<td>';
								$endHourName = "endHour" . $i;
								$j=$i;
								if($j==23){
									$j=0;
								}else{
									$j++;
								}
								generateHourField($endHourName, $j);
							echo '</td>';
							echo '<td>';
								generateUserList(0, "consultantOne" . $i);
							echo '</td>';
							echo '<td>';
								if($number_consultants == 2){
									generateUserList(0, "consultantTwo" . $i);
								}
							echo '</td>';
						echo '</tr>';
					}
				echo '</table>';
				echo '<input name="num_records" type="hidden" value="' . $num_hours . '"/>';
				echo '<input name="number_consultants" type="hidden" value="' . $number_consultants . '"/>';
				echo '<input name="lab_id" type="hidden" value="' . $lab_id . '"/>';
				echo '<input name="dayofweek" type="hidden" value="' . $dayofweek . '"/>';
				generatePageBreaks(1);
				echo '<h2>Effective Dates:</h2>';
				generatePageBreaks(1);
				echo '<table align="center">';
					echo '<tr>';
						echo '<td>Start Date:</td>';
						echo '<td>';
							echo '<select name="start_month">';
								echo '<option value="1">JAN</option>';
								echo '<option value="2">FEB</option>';
								echo '<option value="3">MAR</option>';
								echo '<option value="4">APR</option>';
								echo '<option value="5">MAY</option>';
								echo '<option value="6">JUN</option>';
								echo '<option value="7">JUL</option>';
								echo '<option value="8">AUG</option>';
								echo '<option value="9">SEP</option>';
								echo '<option value="10">OCT</option>';
								echo '<option value="11">NOV</option>';
								echo '<option value="12">DEC</option>';
							echo '</select>';
							echo '&nbsp;';
							echo '<select name="start_day">';
								for($i = 1; $i <= 31; $i++){
									echo '<option value="' . $i . '">' . $i . '</option>';
								}
							echo '</select>';
							echo '&nbsp;';
							echo '<select name="start_year">';
								$yearValue1 = getYear() - 1;
								$yearValue2 = getYear();
								$yearValue3 = getYear() + 1;
								$yearValue4 = getYear() + 2;
								
								echo '<option value="' . $yearValue1 . '">' . $yearValue1 . '</option>';
								echo '<option value="' . $yearValue2 . '" selected="selected">' . $yearValue2 . '</option>';
								echo '<option value="' . $yearValue3 . '">' . $yearValue3 . '</option>';
								echo '<option value="' . $yearValue4 . '">' . $yearValue4 . '</option>';
							echo '</select>';
						echo '</td>';
					echo '</tr>';
					echo '<tr>';
						echo '<td>End Date:</td>';
						echo '<td>';
							echo '<select name="end_month">';
								echo '<option value="1">JAN</option>';
								echo '<option value="2">FEB</option>';
								echo '<option value="3">MAR</option>';
								echo '<option value="4">APR</option>';
								echo '<option value="5">MAY</option>';
								echo '<option value="6">JUN</option>';
								echo '<option value="7">JUL</option>';
								echo '<option value="8">AUG</option>';
								echo '<option value="9">SEP</option>';
								echo '<option value="10">OCT</option>';
								echo '<option value="11">NOV</option>';
								echo '<option value="12">DEC</option>';
							echo '</select>';
							echo '&nbsp;';
							echo '<select name="end_day">';
								for($i = 1; $i <= 31; $i++){
									echo '<option value="' . $i . '">' . $i . '</option>';
								}
							echo '</select>';
							echo '&nbsp;';
							echo '<select name="end_year">';
								$yearValue1 = getYear() - 1;
								$yearValue2 = getYear();
								$yearValue3 = getYear() + 1;
								$yearValue4 = getYear() + 2;
								
								echo '<option value="' . $yearValue1 . '">' . $yearValue1 . '</option>';
								echo '<option value="' . $yearValue2 . '" selected="selected">' . $yearValue2 . '</option>';
								echo '<option value="' . $yearValue3 . '">' . $yearValue3 . '</option>';
								echo '<option value="' . $yearValue4 . '">' . $yearValue4 . '</option>';
							echo '</select>';
						echo '</td>';
					echo '</tr>';
				echo '</table>';

				generatePageBreaks(2);
				echo '<input name="add_schedule_button" type="submit" value="Add Schedule">';
			echo '</form>';
			
		}
		else {
			generate_error("Error choosing lab, Please try again!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 0);
		}
	}
	elseif(isset($_POST['add_schedule_button'])){
		if(isset($_POST['num_records']) && isset($_POST['number_consultants']) && isset($_POST['dayofweek']) && isset($_POST['lab_id'])
			&& isset($_POST['start_month']) && isset($_POST['start_day']) && isset($_POST['start_year'])
			&& isset($_POST['end_month']) && isset($_POST['end_day']) && isset($_POST['end_year'])){
			
			$num_records = $_POST['num_records'];
			$number_consultants = $_POST['number_consultants'];
			$dayofweeklower = strtolower($_POST['dayofweek']);
			$dayOfWeekNumber = dayOfWeekToNumber($_POST['dayofweek']);
			$lab_id = $_POST['lab_id'];
			$insertCount = 0;

			if($_POST['startHour0'] != ""){
				$sql = sprintf("INSERT INTO schedule (consultant1,");
				if($number_consultants == 2){
					$sql .= sprintf(" consultant2,");
				} 
				$sql .= sprintf(" dayofweek, start_time, end_time, lab_id, start_date, end_date) VALUES");
			}	
			
			for($i = 0; $i < $num_records; $i++){
				//Set Current Values:
				$startHour = "startHour" . $i;
				$endHour = "endHour" . $i;
				$consultantOne = "consultantOne" . $i;
				$consultantTwo = "consultantTwo" . $i;
				
				if(  $_POST[$startHour] != "" && $_POST[$endHour] != "" && $_POST[$consultantOne] != ""){
					if($insertCount > 0){
						$sql .= sprintf(", ");
					}
					$sql .= sprintf(" (");
					$sql .= verifySQL($_POST[$consultantOne]);
					if($number_consultants == 2){
						if($_POST[$consultantTwo] != NULL){
							$sql .= sprintf(", %d", verifySQL($_POST[$consultantTwo]));
						}
						else {
							$sql .= sprintf(", NULL");
						}
					}
					$start_date = mktime(0, 0, 0, verifySQL($_POST['start_month']), verifySQL($_POST['start_day']), verifySQL($_POST['start_year']));
					$end_date = mktime(0, 0, 0, verifySQL($_POST['end_month']), verifySQL($_POST['end_day']), verifySQL($_POST['end_year']));
					$start_date = date('Y-m-d H:i:s', $start_date);
					$end_date = date('Y-m-d H:i:s', $end_date);
					$sql .= sprintf(", %d, %d, %d, %d, '%s', '%s')", verifySQL($dayOfWeekNumber), verifySQL($_POST[$startHour]), 
										verifySQL($_POST[$endHour]), verifySQL($lab_id), verifySQL($start_date), verifySQL($end_date));
					$insertCount++;
				}
			}
			
			$result = mysql_query($sql);
			
			if($result){
				$fieldName = $dayofweeklower . "_setup";
				$sql = sprintf("UPDATE labs SET %s = 1 WHERE lab_id = %d LIMIT 1", verifySQL($fieldName), verifySQL($lab_id));
				$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				if($result){
					echo "<h2>Schedule was successfully added!</h2>";
				}
				else {
					generate_error("Error Updating Lab Table", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
				}
			}
			else {
				generate_error("Error Adding Schedule!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			}
		}
		else {
			generate_error("Error passing schedule information!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	else {
		$sql = sprintf("SELECT * FROM labs");
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
			echo '<table align="center" border="1" colspacing="3">';
				echo '<tr>';
					echo '<td align="center">Lab:</td>';
					echo '<td colspan="7" align="center">Day:</td>';
				echo '</tr>';
				while($row = mysql_fetch_array($result)){
					echo '<tr>';
							echo '<td align="center">' . $row['display_name'] . '</td>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Sunday">';
								if($row['sunday_setup'] == 0){
									echo '<td><input type="submit" value="Sunday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Sunday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Monday">';
								if($row['monday_setup'] == 0){
									echo '<td><input type="submit" value="Monday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Monday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Tuesday">';
								if($row['tuesday_setup'] == 0){
									echo '<td><input type="submit" value="Tuesday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Tuesday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Wednesday">';
								if($row['wednesday_setup'] == 0){
									echo '<td><input type="submit" value="Wednesday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Wednesday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Thursday">';
								if($row['thursday_setup'] == 0){
									echo '<td><input type="submit" value="Thursday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Thursday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Friday">';
								if($row['friday_setup'] == 0){
									echo '<td><input type="submit" value="Friday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Friday" name="choose_lab_button" disabled="disabled"></td>';
								}
							echo '</form>';
							echo '<form action="admin_manage_schedule.php?action=add" method="post">';
								echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] .'">';
								echo '<input type="hidden" name="dayofweek" value="Saturday">';
								if($row['saturday_setup'] == 0){
									echo '<td><input type="submit" value="Saturday" name="choose_lab_button"></td>';
								}
								else {
									echo '<td><input type="submit" value="Saturday" name="choose_lab_button" disabled="disabled"></td>';
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