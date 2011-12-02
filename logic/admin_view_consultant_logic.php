<script type="text/javascript">
	function addCount(){
		document.update_consultant.temp_requests_update.value++;
	}
	
	function subCount(){
		var Count = document.update_consultant.temp_requests_update.value;
		if(Count != 0){
			document.update_consultant.temp_requests_update.value--;
		}
		else {
			document.update_consultant.temp_requests_update.value = 0;
		}
	}
	
	function clearCount(){
		document.update_consultant.temp_requests_update.value = 0;
	}
</script>

<?php
	if(strpos($_SERVER['PHP_SELF'], "admin_view_consultant_logic")){
		echo "<h2>ERROR:  Cannot Load Logic Files Individually!</h2>";
	}
	
	if(isset($_POST['details_button'])){
		$conid = $_POST['conid'];
		$sqlDetails = sprintf("SELECT * FROM consultants WHERE conid = %d", verifySQL($conid));
		$qConDetails = mysql_query($sqlDetails, $conn)
			or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		$row = mysql_fetch_array($qConDetails);
		
		echo '<table border="1" align="center">';
			echo '<tr>';
				echo '<td>Consultant ID:</td>';
					echo '<td>' . $row['conid'] .'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Name:</td>';
				echo '<td>' . $row['name'] .'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>ENGR Username:</td>';
				echo '<td>' . $row['engr_username'] .'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>Admin:</td>';
				if($row['admin'] == 1){
					echo '<td>Yes</td>';
				}
				else {
					echo '<td>No</td>';
				}
			echo '</tr>';
			echo '<tr>';
				echo '<td>Temp Requests Made:</td>';
				echo '<td>' . $row['shifts_temped'] .'</td>';
			echo '</tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="button" onClick="window.location=' . "'admin_manage_consultants.php?action=view'" . '" value="Back"/>';
	}
	elseif(isset($_POST['edit_button'])){
		$conid = $_POST['conid'];
		$sqlDetails = sprintf("SELECT * FROM consultants WHERE conid = %d", verifySQL($conid));
		$qConDetails = mysql_query($sqlDetails, $conn)
			or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		$row = mysql_fetch_array($qConDetails);
		echo '<form name="update_consultant" id="update_consultant" action="admin_manage_consultants.php?action=view" method="post">';
			echo '<table border="1" align="center">';
				echo '<tr>';
					echo '<td>Consultant ID:</td>';
						echo '<td>' . $row['conid'] . '</td>';
					echo '<input type="hidden" name="conid_update" value="' . $row['conid'] . '"/>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Full Name:</td>';
					echo '<td><input type="text" name="name_update" value="' . $row['name'] . '"/></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>ENGR Username:</td>';
					echo '<td><input type="text" name="engr_username_update" value="' . $row['engr_username'] . '"/></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Admin:</td>';
					echo '<td>';
						echo '<input type="checkbox" name="account_type_update" value=1 ';
							if($row['admin'] == 1){
								echo "checked />";
							}else{
								echo " />";
							}
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Temp Requests Made:</td>';
					echo '<td>';
						echo '<input type="text" id="temp_requests_update" name="temp_requests_update" readonly="readonly" size="6" value="' . $row['shifts_temped'] . '"/>';
						echo '<button type="button" onClick=clearCount()>C</button>';
						echo '<button type="button" onClick=addCount()>+</button>';
						echo '<button type="button" onClick=subCount()>-</button>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			echo '<br>';
			echo '<input name="update_consultant_submit_button" type="submit" value="Update" />';
			echo '&nbsp;&nbsp;';
			echo '<input type="button" onClick="window.location=' . "'admin_manage_consultants.php?action=view'" . '" value="Cancel"/>';
		echo '</form>';
		echo '<br>';
		echo '<input type="button" onClick="window.location=' . "'admin_manage_consultants.php?action=view'" . '" value="Back"/>';
	}
	elseif(isset($_POST['update_consultant_submit_button'])){
		$updateConSQL = "UPDATE consultants SET ";
		$updateConSQL = $updateConSQL . sprintf("name = '%s', ", verifySQL($_POST['name_update']));
		$updateConSQL = $updateConSQL . sprintf("engr_username = '%s', ", verifySQL($_POST['engr_username_update']));
		$updateConSQL = $updateConSQL . sprintf("admin = '%s', ", verifySQL(isset($_POST['account_type_update'])));
		$updateConSQL = $updateConSQL . sprintf("shifts_temped = %d ", verifySQL(isset($_POST['temp_requests_update'])));
		$updateConSQL = $updateConSQL . sprintf("WHERE conid = %d LIMIT 1", verifySQL($_POST['conid_update']));
		
		$result = mysql_query($updateConSQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if($result){
			echo "<h3>Consultant " . $_POST['name_update'] . " was updated successfully!<h3>";
			echo "<br>";
		}
		else {
			generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
		echo '<input type="button" onClick="window.location=' . "'admin_manage_consultants.php?action=view'" . '" value="Back"/>';
	}
	elseif(isset($_POST['delete_button'])){
		$conid = $_POST['conid'];
		$deleteInfoQuery = sprintf("SELECT name FROM consultants WHERE conid = %d", verifySQL($conid));
		$qDeleteInfo = mysql_query($deleteInfoQuery, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($qDeleteInfo);
		echo "<h3>Are you sure you want to delete user:<br>";
		echo $row['name'];
		echo "?</h3>";
		
		echo '<form name="delete_user_confirm_form" action="admin_manage_consultants.php?action=view" method="post">';
			echo '<input type="hidden" name="conid" value="' . $_POST['conid'] . '"/>';
			echo '<input type="hidden" name="name" value="' . $row['name'] . '"/>';
			echo '<input type="submit" name="delete_confirm_button" value="Yes"/>';
    		echo '<input type="button" name="delete_decline_button" value="No" ';
			echo 'onclick="window.location=';
			echo "'admin_manage_consultants.php?action=view'";
			echo '"/>';
		echo '</form>';
	}
	elseif(isset($_POST['delete_confirm_button'])){
		$conid = $_POST['conid'];
		$deleteUserSQL = sprintf("DELETE FROM consultants WHERE conid = %d LIMIT 1", verifySQL($conid));
		$result = mysql_query($deleteUserSQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if($result){
			echo "<h3>Consultant " . $_POST['name'] . " has been deleted from the database!<h3>";
			echo "<br>";
		}
		else {
			generate_error("Error Deleting Record.  Please Try Again.", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
		echo '<input type="button" onClick="window.location=' . "'admin_manage_consultants.php?action=view'" . '" value="Back"/>';
	}
	else {
		$sqlConsultants = sprintf("SELECT * FROM consultants ORDER BY conid ASC");
		$qConsultants = mysql_query($sqlConsultants, $conn) 
			or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
			
		echo '<table border="1" align="center">';
			echo '<tr>';
				if($_SESSION['engr_username'] == "buckley"){  // ???
					echo '<td>Consultant ID:</td>';
				}
				echo '<td>Name:</td>';
				echo '<td>Details:</td>';
				echo '<td>Edit:</td>';
				echo '<td>Delete:</td>';
			echo '</tr>';
		$i = 0;
		while($row = mysql_fetch_array($qConsultants)){
			echo "<tr>";
				echo '<form name="view_form' . $i . '" action="admin_manage_consultants.php?action=view" method="post">';
					if($_SESSION['engr_username'] == "buckley"){   // ????
						echo "<td>{$row['conid']}</td>";
					}
					echo "<td>{$row['name']}</td>";
					echo '<input type="hidden" name="conid" value="' . $row['conid'] . '" />';
					echo '<td><input type="submit" name="details_button" value="Details"/></td>';
					echo '<td><input type="submit" name="edit_button" value="Edit"/></td>';
					echo '<td><input type="submit" name="delete_button" value="Delete"/></td>';
				echo '</form>';
			echo "</tr>";
			$i++;
		}
		echo '</table>';
	}
?>