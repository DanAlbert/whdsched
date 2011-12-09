<?php
	require('includes/application.php');
	
	if(isset($_GET['month'])){ $month = $_GET['month']; }
	if(isset($_GET['day'])){ $day = $_GET['day']; }
	if(isset($_GET['year'])){ $year = $_GET['year']; }
	if(isset($_GET['start_time'])){ $start_time = $_GET['start_time']; }
	if(isset($_GET['end_time'])){ $end_time = $_GET['end_time']; }
	if(isset($_GET['position'])){ $position = $_GET['position']; }
	if(isset($_GET['regular_consultant'])){ $regular_consultant = $_GET['regular_consultant']; }
	if(isset($_GET['lab_id'])){ $lab_id = $_GET['lab_id']; }
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
                echo "<h2>Create Temp Shift Request</h2>";
                echo "<br>";
                
                if( isset($_POST['create_request_button']) ){
                    if( isset($_POST['month']) ){ $month = $_POST['month']; }
                    if( isset($_POST['day']) ){ $day = $_POST['day']; }
                    if( isset($_POST['year']) ){ $year = $_POST['year']; }
                    if( isset($_POST['regular_consultant']) ){ $regular_consultant = $_POST['regular_consultant']; }
                    if( isset($_POST['temp_consultant']) ){ 
						$temp_consultant = $_POST['temp_consultant']; 
					}
					else {
						$temp_consultant = "";
					}
                    if( isset($_POST['position']) ){ $position = $_POST['position']; }
                    if( isset($_POST['start_time']) ){ $start_time = $_POST['start_time']; }
                    if( isset($_POST['end_time']) ){ $end_time = $_POST['end_time']; }
                    if( isset($_POST['lab_id']) ){ $lab_id = $_POST['lab_id']; }
                    if( isset($_POST['reason']) ){ $reason = $_POST['reason']; }
                    
                    if($temp_consultant != ""){
                        $taken = 1;
                    }
                    else {
                        $taken = 0;
                    }
					
                    $reason = stripslashes($reason);
                    $reason = mysql_real_escape_string($reason);
                    
                    $SQL = "INSERT INTO tempshifts (month, day, year, regular_consultant,";
                    if($temp_consultant != ""){
                        $SQL .= " temp_consultant,"; 
                    }
                    $SQL .= " taken, position, start_time, end_time, lab_id, reason)
                    		  VALUES ($month, $day, $year, $regular_consultant,";
                    if($temp_consultant != ""){
                        $SQL .= sprintf(" %s,", $temp_consultant); 
                    }
                    $SQL .= sprintf(" %d, %d, %d, %d, %d, '%s')", verifySQL($taken), verifySQL($position), 
										verifySQL($start_time), verifySQL($end_time), verifySQL($lab_id), verifySQL($reason));
                    $result = mysql_query($SQL) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
                    
                    if($result){
                        createLogEntry("Create Temp Request", $regular_consultant, $month, $day, $year, $start_time, $end_time, $lab_id, 0, NULL, NULL, NULL, NULL);
						echo '<br><h2>Temp Shift Request has been created!</h2><br><br><br>';
						
						//Send e-mail to consultant list
						emailTempRequestCreated($month, $day, $year, $start_time, $end_time, $lab_id, $reason);
						
						//Increment the shift count of shifts temped by this consultant
						addToShiftCount($regular_consultant);
						
						//Generate Back URL and Button
						$url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
                        $url .= '&month=' . $month;
                        $url .= '&day=' . $day;
                        $url .= '&year=' . $year;
                        echo generateButton($url, "Back");
                    }
                    else {
                        generate_error("Error Creating Temp Shift Request.  Please try again.", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
                    }
                }
                else {
                    $query = sprintf("SELECT * 
                              FROM tempshifts 
                              WHERE month = %d
                              AND day = %d
                              AND year = %d
                              AND start_time = %d
                              AND end_time = %d 
                              AND position = %d
                              AND lab_id = %d", verifySQL($month), verifySQL($day), verifySQL($year), verifySQL($start_time), 
							  					verifySQL($end_time), verifySQL($position), verifySQL($lab_id));
                    $result = mysql_query($query) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
                    $numRows = mysql_num_rows($result);
                    
                    if($numRows == 0){
                        if($regular_consultant == $_SESSION['engr_username'] || 
                            ($regular_consultant == $_SESSION['imersonate_user'] && $_SESSION['imersonate_mode'] == 1)){
                            
                            $fullDate = date("l F j, Y", mktime(0, 0, 0, $month, $day, $year));
                            
                            echo '<form action="consultant_create_temp_request.php" name="create_temp_request_form" method="post">';
                                echo '<br>';
                                echo '<h2>Hello ' . getNameByENGRUsername($regular_consultant) . ',</h2>';
                                echo '<br>';
                                echo '<h2>You are about to create a temp request for your shift on:</h2><br>';
                                echo '<h1>' . $fullDate . '<br>from ' . convertTime($start_time) . ' - ' . convertTime($end_time) . '</h1>';
                                echo '<h1>at ' . getLabDisplayName($lab_id) . '</h1>';
                                echo '<br><h2>Why do you need time off? (optional)</h2><br>';
                                
                                echo '<textarea name="reason" cols="40" rows="5"></textarea>';
                                
                                //echo '<br><br><h2>Do you know who is going to fill in for you?</h2><br>';
                                
                                //generateUserListTempShift("temp_consultant");
                                
                                echo '<br><br><br><br>';
                                echo '<input name="month" type="hidden" value="' . $month .'">';
                                echo '<input name="day" type="hidden" value="' . $day .'">';
                                echo '<input name="year" type="hidden" value="' . $year .'">';
                                echo '<input name="start_time" type="hidden" value="' . $start_time .'">';
                                echo '<input name="end_time" type="hidden" value="' . $end_time .'">';
                                echo '<input name="position" type="hidden" value="' . $position .'">';
                                echo '<input name="lab_id" type="hidden" value="' . $lab_id .'">';
                                echo '<input name="regular_consultant" type="hidden" value="' . getIDByENGRUsername($regular_consultant) .'">';
                                echo '<input type="submit" name="create_request_button" value="Create Request">';
                            echo '</form>';
                            
                            echo '&nbsp;&nbsp;';
                            $url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
                            $url .= '&month=' . $month;
                            $url .= '&day=' . $day;
                            $url .= '&year=' . $year;
                            echo generateButton($url, "Back");
                            
                        }
                        else {
							echo '<br><h2>You are not authorized to create a<br>temp request for this shift</h2>';
                            echo '<br><br><br>';
                            $url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
                            $url .= '&month=' . $month;
                            $url .= '&day=' . $day;
                            $url .= '&year=' . $year;
                            echo generateButton($url, "Back");
                        }
                    }
                    else {
                        echo "<h2>Error: Temp Shift Request already exists!</h2>";
                        echo '<br><br><br>';
                        $url = 'consultant_view_schedule.php?lab_id=' . $lab_id;
                        $url .= '&month=' . $month;
                        $url .= '&day=' . $day;
                        $url .= '&year=' . $year;
                        echo generateButton($url, "Back");
                    }
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