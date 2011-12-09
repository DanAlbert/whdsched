<?php    
    if(strpos($_SERVER['PHP_SELF'], "admin_utilities_log_details")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_GET['logid'])){
		$logid = $_GET['logid'];
	}
	else {
		$logid = "";
	}
	
	if($logid != ""){
		generatePageBreaks(2);
		
		$sql = sprintf("SELECT * FROM log WHERE logid = %d", verifySQL($logid));
		$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$count = mysql_num_rows($result);
		
		if($count > 0){
			$row = mysql_fetch_array($result);
			echo '<table align="center" border="1" width="650">';
				echo '<tr>';
					echo '<td>Log ID:</td>';
					echo '<td>' . $row['logid'] . '</td>';
				echo '</tr>	';
				echo '<tr>';
					echo '<td>Timestamp:</td>';
					$logdate = mktime($row['hour'], $row['minute'], 0, $row['month'], $row['day'], $row['year']);
					echo '<td>' . date('n-j-Y h:i A', $logdate) . '</td>';
				echo '</tr>	';
				echo '<tr>';
					echo '<td>Consultant:</td>';
					echo '<td>' . getNameByID($row['consultant']) . '</td>';
				echo '</tr>	';
				echo '<tr>';
					echo '<td>Action:</td>';
					echo '<td>' . $row['action'] . '</td>';
				echo '</tr>	';
			echo '</table>';
		}
		else {
			generate_error("Invalid Log ID!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 0);
		}
	}
	else {
		generate_error("Invalid Log ID!", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 0);
	}
?>