<?php
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
			echo '<td>' . getNameByID($row['regular_consultant']) . '</td>';
		echo '</tr>';
		if($row['taken'] == 1){
			echo '<td>Temp Consultant:</td>';
			echo '<td>' . getNameByID($row['temp_consultant']) . '</td>';
		}
		else {
			echo '<td>Temp Consultant:</td>';
			echo '<td>Not Filled!</td>';
		}	
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
	echo '<button onclick="history.go(-1)">Back</button>';
	
?>