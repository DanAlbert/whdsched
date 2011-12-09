<?php
	require('includes/application.php');
	
	if(isset($_GET['tempshift_id'])){ 
		$tempshift_id = $_GET['tempshift_id']; 
	}
	else {
		$tempshift_id = "";
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
    	<?php require('includes/navigation_bar.php'); ?>
    	<br>
	
    <!-------------- BEGIN CONTENT --------------->
        <div id="content" class="postnav">
            <?php
                if(isset($_POST['take_temp_shift_button']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					generatePageBreaks(1);
					echo '<h2>If you take this temp shift you agree that you are responsible for working it.</h2>';
					generatePageBreaks(1);
					echo '<h2>If you later realize that you cannot work this shift, you may cancel</h2>';
					echo '<h2>and the shift will then be temped out again.</h2>';
					generatePageBreaks(1);
					echo '<h2>If you cancel within 30 minutes, <br>you will not be responsible for working this shift.</h2>';
					generatePageBreaks(1);
					echo '<h2>However, if you cancel after 30 minutes, <br>you will be responsible for working this shift.</h2>';
					generatePageBreaks(2);
					echo '<h2>Do you agree?</h2>';
					generatePageBreaks(1);
					echo '<form action="consultant_manage_temp_request.php" method="post">';
						echo '<input type="submit" name="confirm_take_temp_shift_button" value="Yes">';
						echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
						echo '&nbsp;&nbsp;';
						echo generateButton("index.php", "No");
					echo '</form>';
				}
				elseif(isset($_POST['confirm_take_temp_shift_button']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					$conid = $_SESSION['conid'];
					$currentDateTime = date('Y-m-d H:i:s', time());
					$query = sprintf("UPDATE tempshifts 
							  SET taken = 1,
							  temp_consultant = %d,
							  time_taken = '%s'
							  WHERE tempshift_id = %d LIMIT 1", verifySQL($conid), $currentDateTime, verifySQL($tempshift_id));
					$result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					if($result){
						$query = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
						$result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
						$shift = mysql_fetch_array($result);
						$fullDate = date("l F j, Y", mktime(0, 0, 0, $shift['month'], $shift['day'], $shift['year']));
						echo '<h1>' . getNameByID($shift['temp_consultant']) . '</h1>';
						echo '<h2>you are now scheduled to work at</h2>';
						echo '<h1>' . getLabDisplayName($shift['lab_id']) . '</h1>';
						echo '<h2>on</h2>';
						echo '<h1>' . $fullDate . '</h1>';
						echo '<h2>from</h2>';
						echo '<h1>' . convertTime($shift['start_time']) . " - " . convertTime($shift['end_time']) . '</h1>';
						generatePageBreaks(4);
						echo generateButton("index.php", "Home");
						
						//SEND E-MAIL OUT
						emailTempShiftTaken($shift['month'], $shift['day'], $shift['year'], $shift['start_time'], $shift['end_time'], $shift['lab_id'], $shift['regular_consultant']);
					}
					else {
						generate_error("Error Updating Temp Shift", NULL, $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				elseif(isset($_POST['regular_consultant_cancel']) && isset($_POST['tempshift_id'])){
					generatePageBreaks(1);
					echo '<h2>' . $_SESSION['full_name'] . ', are you sure you want to cancel this shift?</h2>';
					generatePageBreaks(2);
					echo '<form action="consultant_manage_temp_request.php?tempshift_id=';
					echo $tempshift_id . '" method="post">';
						echo '<input type="hidden" name="tempshift_id" value="' . $_POST['tempshift_id'] . '">';
						echo '<input type="submit" name="regular_consultant_cancel_confirm" value="Yes">';
						echo '&nbsp;&nbsp;';
						echo generateButton("index.php", "No");
					echo '</form>';
				}
				elseif(isset($_POST['regular_consultant_cancel_confirm']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					$shiftSQL = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
					$findShiftResult = mysql_query($shiftSQL) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					
					if($findShiftResult){
						$shiftInfo = mysql_fetch_array($findShiftResult);
						$start_time = $shiftInfo['start_time'];
						$end_time = $shiftInfo['end_time'];
						$shift_month = $shiftInfo['month'];
						$shift_day = $shiftInfo['day'];
						$shift_year = $shiftInfo['year'];
						$lab_id = $shiftInfo['lab_id'];
						
						$sql = sprintf("DELETE FROM tempshifts
								WHERE tempshift_id = %d LIMIT 1", verifySQL($tempshift_id));
						$result = mysql_query($sql, $conn);
						if($result){
							generatePageBreaks(2);
							
							//Increment the shift count of shifts temped by this consultant
							subtractFromShiftCount($_SESSION['conid']);
							
							createLogEntry("Cancel Temp Request", $_SESSION['conid'], $shift_month, $shift_day, $shift_year, $start_time, $end_time, 
											$lab_id, 0, NULL, NULL, NULL, NULL);
							echo '<h2>Your Temp Shift Request has been deleted</h2>';
							echo '<h2>You are now responsbile for working this shift!</h2>';
							
							//SEND E-MAIL OUT
							emailRegularConsultantRescinded($shift_month, $shift_day, $shift_year, $start_time, $end_time, $lab_id);
							
							generatePageBreaks(2);
							echo generateButton("index.php", "Home");
						}
						else {
							generate_error("Error Deleting Temp Shift", NULL, $_SERVER['PHP_SELF'], __LINE__, 1);
						}
					}
					else {
						generate_error("Error Fetching Shift Info", NULL, $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				elseif(isset($_POST['temp_consultant_cancel']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					$query = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
					$result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					if($result){
						if(mysql_num_rows($result) == 1){
							$shift = mysql_fetch_array($result);
							$time_taken = $shift['time_taken'];
							if(inGracePeriod($time_taken)){
								generatePageBreaks(2);
								echo '<h2>You are still within the 30 minute grace period.</h2>'; 
								echo '<h2>You may cancel this shift without penalty.</h2>'; 
								echo '<h2>Would you like to cancel?</h2>'; 
								generatePageBreaks(2);
								echo '<form action="consultant_manage_temp_request.php?tempshift_id=';
								echo $tempshift_id . '" method="post">';
									echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
									echo '<input type="submit" name="temp_consultant_cancel_confirm" value="Yes">';
									echo '&nbsp;&nbsp;';
									echo generateButton("index.php", "No");
								echo '</form>';
							}
							else {
								generatePageBreaks(2);
								echo '<h2>You are outside the 30 minute grace period.</h2>';
								echo '<h2>You may cancel this shift, but it will be re-temped in your name.</h2>';
								echo '<h2>If no one takes the shift, you are responsible for working it.</h2>';
								generatePageBreaks(2);
								echo '<h2>Would you still like to cancel?</h2>'; 
								generatePageBreaks(2);
								echo '<form action="consultant_manage_temp_request.php?tempshift_id=';
								echo $tempshift_id . '" method="post">';
									echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
									echo '<input type="submit" name="temp_consultant_cancel_confirm_outside_limit" value="Yes">';
									echo '&nbsp;&nbsp;';
									echo generateButton("index.php", "No");
								echo '</form>';
							}
						}
						else {
							generate_error("Temp Shift Not Found", NULL, $_SERVER['PHP_SELF'], __LINE__, 1);
						}
					}
					else {
						generate_error("Error Fetching Temp Shift", NULL, $_SERVER['PHP_SELF'], __LINE__, 1); 
					}
					//$shift = mysql_fetch_array($result);
				}
				elseif(isset($_POST['temp_consultant_cancel_confirm']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					$sql = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
					$result = mysql_query($sql, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					$shift = mysql_fetch_array($result);
					
					$month = $shift['month'];
					$day = $shift['day'];
					$year = $shift['year'];
					$startTime = $shift['start_time'];
					$endTime = $shift['end_time'];
					$lab_id = $shift['lab_id'];
					$regular_consultant = $shift['regular_consultant'];
					
					if(cancelTakeTempShift($tempshift_id)){
						generatePageBreaks(2);
						echo '<h2>You are no longer responsible for this temp shift.</h2>'; 
						
						//Create Shift Log Entry:
						createLogEntry("Cancel Temp Shift - Inside Limit", $regular_consultant, $month, $day, $year, $startTime, $endTime, 
										$lab_id, 0, NULL, NULL, NULL, NULL);
						
						//SEND EMAIL TO CONSULTANT LIST:
						emailTempConCancelledInLimit($month, $day, $year, $startTime, $endTime, $lab_id, $regular_consultant);
						
						//SEND EMAIL TO REGULAR CONSULTANT
						emailNotifyTempConCancelledInLimit($month, $day, $year, $startTime, $endTime, $lab_id, $regular_consultant);
						
						generatePageBreaks(2);
						echo generateButton("index.php", "Home");
					}
					else {
						generate_error("Error Canceling Temp Shift", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);

					}
				}
				elseif(isset($_POST['temp_consultant_cancel_confirm_outside_limit']) && isset($_POST['tempshift_id'])){
					$tempshift_id = $_POST['tempshift_id'];
					if(cancelTakeTempShiftOutsideLimit($tempshift_id, $_SESSION['conid'])){
						$SQL = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
						$result = mysql_query($SQL, $conn) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
						$shift = mysql_fetch_array($result);
						
						$month = $shift['month'];
						$day = $shift['day'];
						$year = $shift['year'];
						$start_time = $shift['start_time'];
						$end_time = $shift['end_time'];
						$lab_id = $shift['lab_id'];
						$regular_consultant = $shift['regular_consultant'];
						
						//SEND EMAIL TO CONSULTANT LIST:
						emailTempConCancelledOutsideLimit($month, $day, $year, $start_time, $end_time, $lab_id);
						
						//SEND EMAIL TO REGULAR CONSULTANT:
						emailNotifyTempConCancelledOutsideLimit($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant);
						
						createLogEntry("Cancel Temp Shift - Outside Limit", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, 
							$lab_id, 0, NULL, NULL, NULL, NULL);
						
						echo '<h2>This shift has been re-temped in your name.</h2>';
						echo '<h2>If no one takes the shift, you are responsible for working it.</h2>';
						
						generatePageBreaks(2);
						generatePageBreaks(2);
						echo generateButton("index.php", "Home");
					}
					else {
						generate_error("Error Canceling Temp Shift.  Please try again.", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					}
				}
				elseif($tempshift_id != "") {
					$query = sprintf("SELECT * FROM tempshifts WHERE tempshift_id = %d", verifySQL($tempshift_id));
					$result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					$numRows = mysql_num_rows($result);
					$shift = mysql_fetch_array($result);
                
					//Check and see if the temp actually exists and has not been tampered with
					if($numRows == 1){
						$tempshift_id = $shift['tempshift_id'];
						$regular_consultant = $shift['regular_consultant'];
						$temp_consultant = $shift['temp_consultant'];
						$month = $shift['month'];
						$day = $shift['day'];
						$year = $shift['year'];
						$taken = $shift['taken'];
						$start_time = $shift['start_time'];
						$end_time = $shift['end_time'];
						$lab_id = $shift['lab_id'];
                    
						if($regular_consultant == $_SESSION['conid']){
							//The user that created the temp request, or an admin impersonating them
							echo "<h2>Manage Temp Shift Request</h2>";
							$fullDate = date("l F j, Y", mktime(0, 0, 0, $month, $day, $year));
							echo '<br><br>';
							
							//The shift has been taken already
							if($taken == 1){
								echo '<h1>' . getNameByID($temp_consultant) . '</h1>';
								echo '<h2>has agreed to fill in for</h2>';
								echo '<h1>' . getNameByID($regular_consultant) . '</h1>';
								echo '<h2>on</h2>';
								echo '<h1>' . $fullDate . '</h1>';
								echo '<h2>from</h2>';
								echo '<h1>' . convertTime($start_time) . " - " . convertTime($end_time) . '</h1>';
								generatePageBreaks(2);
							}
							//The shift has not been taken yet
							else {
								echo '<h1>' . getNameByID($regular_consultant) . ',</h1>';
								echo '<h2>no one has taken your shift on:</h2>';
								echo '<h1>' . $fullDate . '</h1>';
								echo '<h2>from</h2>';
								echo '<h1>' . convertTime($start_time) . " - " . convertTime($end_time) . '</h1>';
								
								echo '<h2>Would you like to cancel this temp request?</h2>';
								generatePageBreaks(2);
								
								$url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
								$url .= '&month=' . $month;
								$url .= '&day=' . $day;
								$url .= '&year=' . $year;
								
								echo '<form action="consultant_manage_temp_request.php" method="post">';
									echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
									echo '<input type="submit" name="regular_consultant_cancel" value="Yes">';
									echo '&nbsp;&nbsp;';
									echo generateButton($url, "No");
								echo '</form>';
							}
						}
						//The user who took the shift is viewing it or an admin impersonating them
						elseif($temp_consultant == $_SESSION['conid']){
							$fullDate = date("l F j, Y", mktime(0, 0, 0, $month, $day, $year));
							echo '<br><br>';
							echo '<h1>' . getNameByID($temp_consultant) . '</h1>';
							echo '<h2>you agreed to fill in for</h2>';
							echo '<h1>' . getNameByID($regular_consultant) . '</h1>';
							echo '<h2>on</h2>';
							echo '<h1>' . $fullDate . '</h1>';
							echo '<h2>from</h2>';
							echo '<h1>' . convertTime($start_time) . " - " . convertTime($end_time) . '</h1>';
							
							generatePageBreaks(2);
							
							$url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
							$url .= '&month=' . $month;
							$url .= '&day=' . $day;
							$url .= '&year=' . $year;
							
							echo '<h2>Do you want to cancel this temp shift?</h2>';
							generatePageBreaks(2);
							echo '<form action="consultant_manage_temp_request.php" method="post">';
								echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
								echo '<input type="submit" name="temp_consultant_cancel" value="Yes">';
								echo '&nbsp;&nbsp;';
								echo generateButton($url, "No");
							echo '</form>';
						}
                    	//Give a consultant the opportunity to accept or view the shift
						else {
							echo "<h2>View Temp Shift Request</h2>";
							$fullDate = date("l F j, Y", mktime(0, 0, 0, $month, $day, $year));
							if($shift['taken'] == 1){
								echo '<br><br>';
								echo '<h1>' . getNameByID($temp_consultant) . '</h1>';
								echo '<h2>has agreed to fill in for</h2>';
								echo '<h1>' . getNameByID($regular_consultant) . '</h1>';
								echo '<h2>on</h2>';
								echo '<h1>' . $fullDate . '</h1>';
								echo '<h2>from</h2>';
								echo '<h1>' . convertTime($start_time) . " - " . convertTime($end_time) . '</h1>';
								generatePageBreaks(4);
								$url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
								$url .= '&month=' . $month;
								$url .= '&day=' . $day;
								$url .= '&year=' . $year;
								echo generateButton($url, "Back");
							}
							else {
								generatePageBreaks(1);
								echo '<h1>' . getNameByID($regular_consultant) . '</h1>';
								echo '<h2>is looking for a temp</h2>';
								echo '<h2>on</h2>';
								echo '<h1>' . $fullDate . '</h1>';
								echo '<h2>from</h2>';
								echo '<h1>' . convertTime($start_time) . " - " . convertTime($end_time) . '</h1>';
								generatePageBreaks(2);
								echo '<h2>Would you like to work this shift?</h2>';
								generatePageBreaks(2);
								echo '<form action="consultant_manage_temp_request.php" method="post">';
									echo '<input type="submit" name="take_temp_shift_button" value="Yes">';
									echo '<input type="hidden" name="tempshift_id" value="' . $tempshift_id . '">';
									echo '&nbsp;&nbsp;';
									echo generateButton("index.php", "No");
								echo '</form>';
							}
                    	}
                	}
					else {
						generatePageBreaks(1);
						echo '<h2>This shift is not available</h2>';
						generatePageBreaks(2);
						echo generateButton("index.php", "Back");
					}
				}
				else {
					generatePageBreaks(1);
					echo '<h2>You did not select a shift.</h2>';
					generatePageBreaks(2);
					echo generateButton("index.php", "Back");
				}
            	generatePageBreaks(5);
				include('includes/html_footer.php');
			?>
        </div>
</body>

<!------- INCLUDE FOOTER ---------->
<?php
	include('includes/footer.php');
?>