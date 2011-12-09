<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_utilities_log_email_recent")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	$sql = sprintf("SELECT * FROM log WHERE email = 1 ORDER BY year, month, day, hour, minute DESC LIMIT 25");
	$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
	
	$count = mysql_num_rows($result);
	
	if($count > 0){
		echo '<table align="center" border="1" width="650">';
		echo '<tr>';
			echo '<td align="center"><b>ID:</b></td>';
			echo '<td align="center"><b>Timestamp:</b></td>';
			echo '<td align="center"><b>Consultant:</b></td>';
			echo '<td align="center"><b>Action:</b></td>';
			echo '<td align="center"><b>Details:</b></td>';
		echo '</tr>	';
		
		while($row = mysql_fetch_array($result)){
			echo '<tr>';
				echo '<td align="center">';
					echo '<a href="admin_utilities_log_email.php?action=details&logid=' . $row['logid'] . '">' . $row['logid'] . '</a>';
				echo '</td>';
				$logdate = mktime($row['hour'], $row['minute'], 0, $row['month'], $row['day'], $row['year']);
				echo '<td align="center">' . date('n-j-Y h:i A', $logdate) . '</td>';
				echo '<td align="center">' . getNameByID($row['consultant']) . '</td>';
				echo '<td align="center">' . $row['action'] . '</td>';
				echo '<td align="center">';
					$url = 'admin_utilities_log_email.php?action=details&logid=' . $row['logid'];
					echo '<button onclick="window.location=\'' . $url . '\'">Details</button>';
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else {
		echo '<h2>No Logs to display!</h2>';
	}
?>