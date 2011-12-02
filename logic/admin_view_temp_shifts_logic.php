<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_view_temp_shifts_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	$sql = sprintf("SELECT * 
					FROM tempshifts
					ORDER BY year, month, day, start_time");
	$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
	
	if($result){
		if(mysql_num_rows($result) > 0){
			echo '<table border="1" align="center">';
				echo '<tr>';
					echo '<td>Date:</td>';
					echo '<td>Time:</td>';
					echo '<td>Location:</td>';
					echo '<td>Regular Consultant:</td>';
					echo '<td>Temp Consultant:</td>';
					echo '<td>Edit:</td>';
					echo '<td>Details:</td>';
				echo '</tr>';
			
				while($row = mysql_fetch_array($result)){
					echo '<tr>';
						echo '<td align="center">' . $row['month'] . '-' . $row['day'] . '-' . $row['year'] . '</td>';
						echo '<td align="center">' . convertTime($row['start_time']) . '-' . convertTime($row['end_time']) . '</td>';
						echo '<td align="center">' . getLabDisplayName($row['lab_id']) . '</td>';
						echo '<td align="center">' . getNameByID($row['regular_consultant']) . '</td>';
						if($row['taken'] == 1){
							echo '<td align="center">' . getNameByID($row['temp_consultant']) . '</td>';
						}
						else {
							echo '<td align="center">Not Filled!</td>';
						}
						echo '<td align="center">';
							$url = 'admin_utilities_temp_shifts.php?action=edit&shiftid=' . $row['tempshift_id'];
							echo '<button onclick="window.location=\'' . $url . '\'">Edit</button>';
						echo '</td>';
						echo '<td align="center">';
							$url = 'admin_utilities_temp_shifts.php?action=details&shiftid=' . $row['tempshift_id'];
							echo '<button onclick="window.location=\'' . $url . '\'">Details</button>';
						echo '</td>';
					echo '</tr>';
				}		
			echo '</table>';
		}
		else {
			echo '<br><br>';
			echo '<h2>There are no open Temp Shifts</h2>';
		}
	}
	else {
		generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
	}
?>

