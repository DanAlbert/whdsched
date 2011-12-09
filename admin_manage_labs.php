<?php
	require('includes/application.php');

	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = "display";
	}
?>

<!---------------------------------------- 
	Author: 		Mike Harrold
    E-Mail:			harroldm@engr.orst.edu
	Date: 			July 15, 2008
	Project name: 	Hovland TempShift v2.0

	INCLUDES:	
        -header.php
        	-Conn2DB.php
            -Config.php
            -CommonFunctions.php
        -footer.php
        -styles.css
        -navigationBar.php
----------------------------------------->

<!--Body-->
<body>
    <!----------- HEADER BAR ----------->
		<?php require('includes/header_bar.php'); ?>
    	<br>
    
    <!----------- NAVIGATION BAR ----------->
    	<?php require('includes/admin_navigation_bar.php'); ?>
    	<br>
    
    <!-------------- BEGIN CONTENT --------------->
        <div id="content">
            <?php	
				if(isset($_POST['view_lab_button']) && isset($_POST['lab_id'])){
					$lab_id = $_POST['lab_id'];
					echo "<h2>View Lab:</h2>";
					echo '<br>';
					$sql = sprintf("SELECT * FROM labs WHERE lab_id = %d", verifySQL($lab_id));
					$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					$row = mysql_fetch_array($result);
					echo '<table align="center" border="1">';
						echo '<tr>';
							echo '<td>Lab ID:</td>';
							echo '<td>' . $row['lab_id'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td>Name:</td>';
							echo '<td>' . $row['display_name'] . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td>Consultants:</td>';
							echo '<td>' . $row['number_consultants'] . '</td>';
						echo '</tr>';
					echo '</table>';
					generatePageBreaks(3);
					echo generateButton("admin_manage_labs.php", "Back");
				}
				elseif(isset($_POST['delete_lab_button']) && isset($_POST['lab_id'])){
					$lab_id = $_POST['lab_id'];
					$display_name = getLabDisplayName($lab_id);
					echo "<h2>Delete Lab:</h2>";
					echo '<br>';
					echo "<h2>Are you sure you want to delete $display_name?</h2>";
					generatePageBreaks(3);
					echo '<form action="admin_manage_labs.php" method="post">';
						echo '<input type="submit" name="confirm_delete_lab_button" value="Yes">';
						echo '&nbsp;&nbsp;';
						echo generateButton("admin_manage_labs.php", "No");
						echo '<input type="hidden" name="lab_id" value="' . $lab_id . '">';
						echo '<input type="hidden" name="display_name" value="' . $display_name . '">';
					echo '</form>';
				}
				elseif(isset($_POST['confirm_delete_lab_button']) && isset($_POST['lab_id']) && isset($_POST['display_name'])){
					$lab_id = $_POST['lab_id'];
					$display_name = $_POST['display_name'];
					$sql = sprintf("DELETE FROM labs WHERE lab_id = %d LIMIT 1", verifySQL($lab_id));
					$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					if($result){
						echo "<h2>$display_name has been deleted</h2>";
						generatePageBreaks(5);
						echo generateButton('admin_manage_labs.php', "Back");
					}
					else {
						generate_error("Error Deleting Lab", NULL, $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				elseif(isset($_POST['add_lab_button'])){
					if(isset($_POST['display_name']) && isset($_POST['number_consultants'])){
						$display_name = $_POST['display_name'];
						$number_consultants = $_POST['number_consultants'];
						$sql = "SELECT MAX(display_order) as max FROM labs";
						$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
						$row = mysql_fetch_array($result);
						$display_order = $row['max'];
						$display_order++;
						
						$sql = sprintf("INSERT INTO labs (display_name, number_consultants, display_order)
								VALUES ('%s', %d, %d)", verifySQL($display_name), verifySQL($number_consultants), verifySQL($display_order));
						$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
						if($result){
							echo "<h2>$display_name was added successfully!</h2>";
							generatePageBreaks(5);
							echo generateButton('admin_manage_labs.php', "Back");
						}
						else {
							generate_error("Error adding lab, please try again.", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
						}
					}
					else {
						generate_error("Error adding lab, please try again.", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				else {
					//View Labs
					echo '<h2>Manage Labs:</h2>';
					$sql = "SELECT * FROM labs";
					$result = mysql_query($sql, $conn) or generate_error("Error Getting list of labs", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					generatePageBreaks(1);
					echo '<table align="center" border="1">';
						echo '<tr>';
							echo '<td>Lab ID:</td>';
							echo '<td>Name:</td>';
							echo '<td>View:</td>';
							echo '<td>Delete:</td>';
						echo '</tr>';
						while($row = mysql_fetch_array($result)){
							echo '<tr>';
								echo '<td>' . $row['lab_id'] . '</td>';
								echo '<td>' . $row['display_name'] . '</td>';
								//View Details
								echo '<form action="admin_manage_labs.php" method="post">';
									echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] . '">';
									echo '<td><input type="submit" name="view_lab_button" value="View"></td>';
								echo '</form>';
								//Delete
								echo '<form action="admin_manage_labs.php" method="post">';
									echo '<input type="hidden" name="lab_id" value="' . $row['lab_id'] . '">';
									echo '<td><input type="submit" name="delete_lab_button" value="Delete"></td>';
								echo '</form>';
							echo '</tr>';
						}
					echo '</table>';
					generatePageBreaks(3);
					echo '<h2>Add A Lab:</h2>';
					generatePageBreaks(1);
					echo '<form action="admin_manage_labs.php" method="post">';
						echo '<table align="center" border="1">';
							echo '<tr>';
								echo '<td>Display Name:</td>';
								echo '<td><input type="text" name="display_name" width="20" value=""></td>';
							echo '</tr>';
							echo '<tr>';
								echo '<td>Number Consultants:</td>';
								echo '<td>';
									echo '<select name="number_consultants">';
										echo '<option value="1">1</option>';
										echo '<option value="2">2</option>';
									echo '</select>';
								echo '</td>';
							echo '</tr>';
						echo '</table>';
						echo '<input type="submit" name="add_lab_button" value="Add Lab">';
					echo '</form>';
				}
				generatePageBreaks(8);
				include('includes/html_footer.php');
			?>
        </div>
</body>

<!------- INCLUDE FOOTER ---------->
<?php
	include('includes/footer.php');
?>