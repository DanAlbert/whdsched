<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_view_schedule_logic")){
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
	
	if($lab_id != ""){
		if($day == ""){
			generateDayMenu($action, $lab_id);
			echo '<br><br>';
			echo generateButton('admin_manage_schedule.php?action=view', 'Back');
		}
		else {
			echo '<br>';
			adminDisplaySchedule($lab_id, $day);
			echo '<br><br>';
			echo generateButton('admin_manage_schedule.php?action=view&lab_id=' . $lab_id, 'Back');
		}
	}
	else {
		$sql = sprintf("SELECT * FROM labs");
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
				echo '<table align="center">';
					while($row = mysql_fetch_array($result)){
						$entry = '<tr><td><ul id="sddm"><li><a href="admin_manage_schedule.php?action=view&lab_id=';
						$entry .= $row['lab_id'];
						$entry .= '">' . $row['display_name'] . '</a></li></ul></td></tr>';
						echo $entry;
					}
				echo '</table>';
		}
		else {
			generate_error("Error Fetching Location List", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
?>