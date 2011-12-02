<?php
	//Start Session
	session_start();
	
	//Unregister Global Session Variables so local variables will not overwrite session variables
	ini_set('register_globals', 'Off');
	
	//error_reporting(E_ALL);
   	ini_set('display_errors', '1');
	
	if($_SESSION['loggedin'] == 1){
		if(strpos($_SERVER['PHP_SELF'], 'admin') == true && $_SESSION['admin'] != 1){
			$_SESSION['error_message'] = "You Are Not Authorized To View The Admin Interface";
			header("Location: error.php");
			die();
		}	
		else {
			//DO NOTHING
		}		
	}
	else {
		if(strpos($_SERVER['PHP_SELF'], 'login.php') == false){
			$redirect = $_SERVER['PHP_SELF'];
			header("Location: login.php");
			echo 'You are being redirected to the login page!<br>';
			echo "(If your browser does not support this, " .
			 	'<a href="login.php">Click Here</a>)';
			die();
		}
	}
	
	//-------------- Include Database Connection Information -----------------------
	require 'Conn2DB.php';

	//--------------------- Include the Date/Time Functions ------------------------
	require 'datetime_functions.php';	

	//-------------------- Include the E-Mail Subsystem ----------------------------
	require 'email_system.php';
	
	//--------------------- Include the Error Subsystem ----------------------------
	require 'error_system.php';
	
	//------------- Load Application Variables if they have not been loaded --------
	if(isset($_SESSION['application_variables_set'])){
		loadApplicationVariables(); 
	}
	
	//------------------------- Reset Error Message Variables ----------------------
	$_SESSION['error_message'] = NULL;
	$_SESSION['error_email_admin'] = NULL;
	
	//*********************** Common Functions ************************************
	//  INDEX OF FUNCTIONS:
	//  
	//  function generateButton($url, $buttonText)
	//  function adminDisplaySchedule($lab_id, $day)
	//  function getNameByID($conid)
	//  function getIDByName($name)
	//  function convertTime($hour)
	//  function dayOfWeekToString($dayofweek)
	//  function generateDayMenu($action, $lab_id)
	//  function adminEditSchedule($lab_id, $day)
	//  function generateUserList($selectedUser, $fieldName)
	//
	//*****************************************************************************
	
	function loadApplicationVariables(){
		$sql = sprintf("SELECT * FROM settings");
		$result = mysql_query($sql) or generate_error("Error Loading Application Variables", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		while($row = mysql_fetch_array($result)){
			eval("\$_SESSION['" . $row['name'] . "'] = " . "'" . $row['value'] . "';");
		}
		$_SESSION['application_variables_set'] = 1;
	}
	
	//Function:  generateButton
	//Arguments:  $url, $buttonText
	//Description:  Creates a new button that points to location $url and has text $buttonText.
	function generateButton($url, $buttonText){
		return '<input type="button" onClick="window.location=' . "'" . $url . "'" . '" value="' . $buttonText . '"/>';
	}
	
	//Function:  adminDisplaySchedule
	//Arguments:  $lab_id, $day
	//Description:  Displays the schedules for a specific location on a specific day.
	function adminDisplaySchedule($lab_id, $day){
		echo '<h1><b>' . getLabDisplayName($lab_id) . ':</b></h1>';
		$query = sprintf("SELECT * 
				  FROM schedule 
				  WHERE lab_id = %d 
				  AND dayofweek = %d 
				  ORDER BY dayofweek, start_time", verifySQL($lab_id), verifySQL($day));
		$result = mysql_query($query) or generate_error("Error Fetching Schedule", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		$numConsQuery = sprintf("SELECT * FROM labs WHERE lab_id = %d", verifySQL($lab_id));
		$numConsQueryResult = mysql_query($numConsQuery) or generate_error("Error Fetching Number of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if($numConsQueryResult){
			$numConsQueryRow = mysql_fetch_array($numConsQueryResult);
			$number_consultants = $numConsQueryRow['number_consultants'];
			generatePageBreaks(1);
		
			echo '<table border="1" align="center">';
				echo '<tr><td colspan="3" align="center"><h2><b>' . dayOfWeekToString($day) . ':</b></h2></td></tr>';
				echo '<tr>';
					echo '<th align="center">Time:</th>';
					echo '<th align="center">Helpdesk/KEC:</th>';
					if($number_consultants == 2){
						echo '<th align="center">Lab Check/Owen:</th>';
					}
					
				echo '</tr>';
			while($row = mysql_fetch_array($result)){
				echo '<tr>';
					echo '<td align="center">' . convertTime($row['start_time']) . " - " . convertTime($row['end_time']) . '</td>';
					echo '<td align="center">' . getNameByID($row['consultant1']) . '</td>';
					if($number_consultants == 2){
						echo '<td align="center">' . getNameByID($row['consultant2']) . '</td>';
					}
				echo '</tr>';
			}
			echo '</table>';
		}
		else {
			generate_error("Error Fetching Number of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
		
	}
	
	//Function:  getNameByID
	//Arguments:  $conid
	//Description:  Returns the name of the person with the given Student ID Number.
	function getNameByID($conid){
		$query = sprintf("SELECT name 
				  FROM consultants
				  WHERE conid = %d", verifySQL($conid));
		
		$result = mysql_query($query) or generate_error("Error Getting Name", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
			$row = mysql_fetch_array($result);
			$consultantName = $row['name'];
			return $consultantName;
		}
		else {
			return $conid;
		}
	}	
	
	function getNameByENGRUsername($engr_username){
		$query = sprintf("SELECT name 
				  FROM consultants
				  WHERE engr_username = '%s'", verifySQL($engr_username));
		
		$result = mysql_query($query) or generate_error("Error Fetching Consultant Name", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);
		$consultantName = $row['name'];
		return $consultantName;
	}	
	
	//Function:  getENGRUsernameByID
	//Arguments:  $conid
	//Description:  Returns the ENGR username of the person with the given Student ID Number.
	function getENGRUsernameByID($conid){
		$query = sprintf("SELECT engr_username 
				  FROM consultants
				  WHERE conid = %d", verifySQL($conid));
		
		$result = mysql_query($query) or generate_error("Error Fetching ENGR Username", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);
		$ENGRUsername = $row['engr_username'];
		return $ENGRUsername;
	}	
	
	//Function:  getIDByName
	//Arguments:  $name
	//Description:  Displays the Student ID Number of a person with the given name.
	function getIDByName($name){
		$query = sprintf("SELECT conid 
				  FROM consultants
				  WHERE name = '%s'", verifySQL($name));
		
		$result = mysql_query($query) or generate_error("Error Fetching Student ID", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);
		$conid = $row['conid'];
		return $conid;
	}
	
	function getIDByENGRUsername($engr_username){
		$query = sprintf("SELECT conid 
				  FROM consultants
				  WHERE engr_username = '%s'", verifySQL($engr_username));
		
		$result = mysql_query($query) or generate_error("Error Fetching Student ID", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);
		$conid = $row['conid'];
		return $conid;
	}
	
	//Function:  dayOfWeekToString
	//Arguments:  $dayofweek
	//Description:  Converts the Integer day of week to the string name of day.
	function dayOfWeekToString($dayofweek){
		switch($dayofweek){
			case 0: return "Sunday"; break;
			case 1: return "Monday"; break;
			case 2: return "Tuesday"; break;
			case 3: return "Wednesday"; break;
			case 4: return "Thursday"; break;
			case 5: return "Friday"; break;
			case 6: return "Saturday"; break;
			default: return -1; break;
		}
	}
	
	function dayOfWeekToNumber($dayofweek){
		switch($dayofweek){
			case "Sunday": return 0; break;
			case "Monday": return 1; break;
			case "Tuesday": return 2; break;
			case "Wednesday": return 3; break;
			case "Thursday": return 4; break;
			case "Friday": return 5; break;
			case "Saturday": return 6; break;
			default: return -1; break;
		}
	}
	
	//Function:  generateDayMenu
	//Arguments:  $action, $lab_id
	//Description:  Generates a menu with all days of the week 
	// with links to a location and the action to perform.
	function generateDayMenu($action, $lab_id){
		$dayArray[0] = "Sunday";
		$dayArray[1] = "Monday";
		$dayArray[2] = "Tuesday";
		$dayArray[3] = "Wednesday";
		$dayArray[4] = "Thursday";
		$dayArray[5] = "Friday";
		$dayArray[6] = "Saturday";
		
		generatePageBreaks(1);
		echo "<h2>Please Select A Day:</h2>";
		generatePageBreaks(2);
		
		echo '<table align="center">';	
			for($i = 0; $i < 7; $i++){
				echo '<tr><td><ul id="sddm"><li>';
				echo '<a href="admin_manage_schedule.php?action=' . $action . '&lab_id=';
				echo $lab_id .'&day=' . $i . '">' . $dayArray[$i] . '</a></dt>';
			}
		echo '</table>';
	}
	
	//Function:  generateUserList
	//Arguments:  $selectedUser, $fieldName
	//Description:  Generates a drop down userlist with the specified name  
	// and the specified user selected.
	function generateUserList($selectedUser, $fieldName){
		$getConsultantsSQL = sprintf("SELECT conid, name FROM consultants ORDER BY name");
		$result = mysql_query($getConsultantsSQL) or generate_error("Error Fetching List of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$html = '<select name="' . $fieldName . '">';
		if($selectedUser == 0){
			$html .= '<option value=""></option>';
		}
		while($row = mysql_fetch_array($result)){
			$html .= '<option value="' . $row['conid'] . '"';
			if($row['conid'] == $selectedUser){
				$html .= " selected";
			}
			$html .= '>' . $row['name'] . '</option>';
		}
		$html .= '</select>';
		echo $html;
	}
	
	function generateUserListWithNULL($selectedUser, $fieldName){
		$getConsultantsSQL = sprintf("SELECT conid, name FROM consultants ORDER BY name");
		$result = mysql_query($getConsultantsSQL) or generate_error("Error Fetching List of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$html = '<select name="' . $fieldName . '">';
		$html .= '<option value=""></option>';
		while($row = mysql_fetch_array($result)){
			$html .= '<option value="' . $row['conid'] . '"';
			if($row['conid'] == $selectedUser){
				$html .= " selected";
			}
			$html .= '>' . $row['name'] . '</option>';
		}
		$html .= '</select>';
		echo $html;
	}
	
	function generateUserListTempShift($fieldName){
		$getConsultantsSQL = sprintf("SELECT conid, name FROM consultants ORDER BY name");
		$result = mysql_query($getConsultantsSQL) or generate_error("Error Fetching List of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$html = '<td><select name="' . $fieldName . '">';
			$html .= '<option value="" selected>No</option>';
			while($row = mysql_fetch_array($result)){
				$html = $html . '<option value="' . $row['conid'] . '"';
				$html = $html . '>' . $row['name'] . '</option>';
			}
		$html = $html . '</td></select>';
		echo $html;
	}
	
	function generateSchedule($lab_id, $month, $day, $year, $numConsultants){
		$dayofweekNumber = date("w", mktime(0, 0, 0, $month, $day, $year));
		$fullDate = date("l, F j, Y", mktime(0, 0, 0, $month, $day, $year));
		
		$query = sprintf("SELECT * 
				  FROM schedule 
				  WHERE lab_id = %d 
				  AND dayofweek = %d 
				  ORDER BY dayofweek, start_time", verifySQL($lab_id), verifySQL($dayofweekNumber));
		$result = mysql_query($query) or generate_error("Error Fetching Schedule", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$numRows = mysql_num_rows($result);
		$dateOfShift = mktime(0, 0, 0, $month, $day, $year);
		if(isInEffectiveRange($lab_id, $dayofweekNumber, $dateOfShift)){
			generatePageBreaks(1);
			echo '<table id="schedule" align="center" border="1">';
				echo '<tr><td colspan="3" align="center"><h2><b>' . $fullDate . ':</b></h2></td></tr>';
				echo '<tr>';
					echo '<th id="time" align="center">Time:</th>';
					echo '<th id="consultant1">Helpdesk/KEC:</th>';
					if($numConsultants == 2){
						echo '<th id="consultant2">Lab Check/Owen:</th>';
					}
				echo '</tr>';
				$i = 0;
				while($row = mysql_fetch_array($result)){
					$start_time = $row['start_time'];
					$end_time = $row['end_time'];
					$start_date = $row['start_date'];
					$end_date = $row['end_date'];
					$consultant1 = $row['consultant1'];
					$consultant2 = $row['consultant2'];
					echo '<tr>';
						echo '<input type="hidden" name="schedule_id' . $i . '" value="' . $row['schedule_id'] . '" />';
						//Print out Time:
						echo '<td align="center">' . convertTime($start_time) . " - " . convertTime($end_time) . '</td>';
						//Print out Consultant1
						isTemped($month, $day, $year, $start_time, $end_time, $lab_id, 1, $consultant1);
						if($numConsultants == 2){
							if($consultant2 == NULL){
								echo '<td style="color:white;">&nbsp;</td>';
							}
							else {
								isTemped($month, $day, $year, $start_time, $end_time, $lab_id, 2, $consultant2);
							}
						}
					echo '</tr>';
					$i++;
				}
			echo '</table>';	
		}
		else {
			generatePageBreaks(1);
			echo '<h2>A schedule for this lab does not exist for the date specified.</h2>';
			echo '<h2>Please contact the system administrator to create a schedule.</h2>';
		}
		generatePageBreaks(3);
		echo generateButton('consultant_view_schedule.php?lab_id=' . $lab_id, "Back");
		
	}
	
	function isTemped($month, $day, $year, $start_time, $end_time, $lab_id, $position, $regular_consultant){
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
				  
		$result = mysql_query($query) or generate_error("Error Fetching Schedule", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$numRows = mysql_num_rows($result);
		
		//A temp shift exists
		if($numRows == 1){
			$row = mysql_fetch_array($result);
			$taken = $row['taken'];
			$tempshift_id = $row['tempshift_id'];
			$regular_consultant = $row['regular_consultant'];
			$temp_consultant = $row['temp_consultant'];
			
			//The Person is looking for a temp
			if($taken == 0){
				if(isEnded($month, $day, $year, $end_time)){
					$value = '<td id="notTemped">';
					$value .= getNameByID($regular_consultant);
					$value .= '</td>';
				}
				else {
/*					if(){
						$value = '<td id="lookingForTemp">';
						$value .= '<a href="consultant_manage_temp_request.php?';
						$value .= 'tempshift_id=' . $tempshift_id . '">';
						$value .= getNameByID($regular_consultant);
						$value .= '<br>is looking for a temp';
						$value .= '</a></td>';
					}*/
/*					elseif($_SESSION['hov_auth'] == 0 && authorizationRequired($lab_id)) {
						$value = '<td id="lookingForTemp">';
						$value .= getNameByID($regular_consultant);
						$value .= '<br>is looking for a temp';
						if(!isEnded($month, $day, $year, $end_time)){
							$value .= '&nbsp;<a href="not_authorized.php"><img src="includes/images/exclamation.gif" border="0"></a>';
						}
						$value .= '</td>';
					}*/
//					else {
						$value = '<td id="lookingForTemp">';
						$value .= '<a href="consultant_manage_temp_request.php?';
						$value .= 'tempshift_id=' . $tempshift_id . '">';
						$value .= getNameByID($regular_consultant);
						$value .= '<br>is looking for a temp';
						$value .= '</a></td>';
//					}
				}
				echo $value;
			}
			//The shift is already taken
			else{
				if(isEnded($month, $day, $year, $end_time)){
					$value = '<td id="notTemped">';
					$value .= getNameByID($temp_consultant);
					$value .= '</td>';
				}	
				else {
/*					if($_SESSION['hov_auth'] == 1 && authorizationRequired($lab_id)){
						$value = '<td id="temped">';
						$value .= '<a href="consultant_manage_temp_request.php?';
						$value .= 'tempshift_id=' . $tempshift_id . '">'; 
						$value .= getNameByID($temp_consultant);
						$value .= '<br>is filling in for<br>';
						$value .= getNameByID($regular_consultant);
						$value .= '</a></td>';
					}
					elseif($_SESSION['hov_auth'] == 0 && authorizationRequired($lab_id)) {
						$value = '<td id="temped">';
						$value .= getNameByID($temp_consultant);
						$value .= '<br>is filling in for<br>';
						$value .= getNameByID($regular_consultant);
						if(!isEnded($month, $day, $year, $end_time)){
							$value .= '&nbsp;<a href="not_authorized.php"><img src="includes/images/exclamation.gif" border="0"></a>';	
						}
						$value .= '</td>';
					}*/
//					else {
						$value = '<td id="temped">';
						$value .= '<a href="consultant_manage_temp_request.php?';
						$value .= 'tempshift_id=' . $tempshift_id . '">'; 
						$value .= getNameByID($temp_consultant);
						$value .= '<br>is filling in for<br>';
						$value .= getNameByID($regular_consultant);
						$value .= '</a></td>';
//					}
				}
				echo $value;
			}
		}
		//A temp shift does not exist
		else {
/*			if($_SESSION['hov_auth'] == 1 && authorizationRequired($lab_id)){
				$value = '<td id="notTemped">';
				if(!isEnded($month, $day, $year, $end_time)){
					$value .= '<a href="consultant_create_temp_request.php?';
					$value .= 'month=' . $month;
					$value .= '&day=' . $day;
					$value .= '&year=' . $year;
					$value .= '&start_time=' . $start_time;
					$value .= '&end_time=' . $end_time;
					$value .= '&position=' . $position;
					$value .= '&regular_consultant=' . getENGRUsernameByID($regular_consultant);
					$value .= '&lab_id=' . $lab_id . '">';
				}
				$value .= getNameByID($regular_consultant);
				if(!isEnded($month, $day, $year, $end_time)){
					$value .= '</a>';
				}	
				$value .= '</td>';
				echo $value;
			
			}
			elseif($_SESSION['hov_auth'] == 0 && authorizationRequired($lab_id)){
				$value = '<td id="notTemped">';
				$value .= getNameByID($regular_consultant);	
				if(!isEnded($month, $day, $year, $end_time)){
					$value .= '&nbsp;<a href="not_authorized.php"><img src="includes/images/exclamation.gif" border="0"></a>';
				}
				$value .= '</td>';
				echo $value;
			}*/
//			else {
				$value = '<td id="notTemped">';
				if(!isEnded($month, $day, $year, $end_time)){
					$value .= '<a href="consultant_create_temp_request.php?';
					$value .= 'month=' . $month;
					$value .= '&day=' . $day;
					$value .= '&year=' . $year;
					$value .= '&start_time=' . $start_time;
					$value .= '&end_time=' . $end_time;
					$value .= '&position=' . $position;
					$value .= '&regular_consultant=' . getENGRUsernameByID($regular_consultant);
					$value .= '&lab_id=' . $lab_id . '">';
				}
				$value .= getNameByID($regular_consultant);
				if(!isEnded($month, $day, $year, $end_time)){
					$value .= '</a>';
				}	
				$value .= '</td>';
				echo $value;
//			}
		}	
	}
	
	function generateCalendar($month, $year, $lab_id){
		$output = '';
		if($month == '' && $year == '') { 
			$time = time();
			$month = date('n',$time);
			$year = date('Y',$time);
		}
		
		$date = getdate(mktime(0,0,0,$month,1,$year));
		$today = getdate();
		
		$days=date("t",mktime(0,0,0,$month,1,$year));
		$start = $date['wday']+1;
		$name = $date['month'];
		$year2 = $date['year'];
		$offset = $days + $start - 1;
		 
		if($month==12) { 
			$next=1; 
			$nexty=$year + 1; 
		} 
		else { 
			$next=$month + 1; 
			$nexty=$year; 
		}
		
		if($month==1) { 
			$prev=12; 
			$prevy=$year - 1; 
		} 
		else { 
			$prev=$month - 1; 
			$prevy=$year; 
		}
		
		$back = $_SERVER['SCRIPT_NAME'] . "?lab_id=$lab_id&month=$prev&year=$prevy";
		$forward = $_SERVER['SCRIPT_NAME'] . "?lab_id=$lab_id&month=$next&year=$nexty";
		
		//$back = "consultant_view_schedule.php?lab_id=$lab_id&month=$prev&year=$prevy";
		//$forward = "consultant_view_schedule.php?lab_id=$lab_id&month=$next&year=$nexty";
		
		if($offset <= 28) $weeks=28; 
		elseif($offset > 35) $weeks = 42; 
		else $weeks = 35; 
		
		$output .= "
		<table class='cal' cellspacing='1' align='center'>
		<tr>
			<td colspan='1'>
				<div class='dayofweek'>
					<a class='arrow' href='$back'><<</a>
				</div>
			</td>
			<td colspan='5'>
				<div class='dayofweek'>
					$name $year2
				</div>
			</td>
			<td colspan='1'>
				<div class='dayofweek'>
					<a class='arrow' href='$forward'>>></a>
				</div>
			</td>
		</tr>
		<tr class='dayhead'>
			<td><div class='dayofweek'>Sun</div></td>
			<td><div class='dayofweek'>Mon</div></td>
			<td><div class='dayofweek'>Tue</div></td>
			<td><div class='dayofweek'>Wed</div></td>
			<td><div class='dayofweek'>Thu</div></td>
			<td><div class='dayofweek'>Fri</div></td>
			<td><div class='dayofweek'>Sat</div></td>
		</tr>";
		
		$col=1;
		$cur=1;
		$next=0;
		
		for($i=1;$i<=$weeks;$i++) { 
			if($next==3) $next=0;
			if($col==1) $output.="<tr class='dayrow'>"; 
			
			$output.="<td>";
		
			if($i <= ($days+($start-1)) && $i >= $start) {
				$output.="<div class='day'><b";
				
				$linkMonth = $month;
				$linkDay = $cur;
				$linkYear = $year;
		
				$output.="><a href='?lab_id=$lab_id&month=$linkMonth&day=$linkDay&year=$linkYear'>";
				if($cur < 10){ $output.= "&nbsp;"; }
				$output.="&nbsp;" . $cur . "&nbsp;";
				if($cur < 10){ $output.= "&nbsp;"; }	
				$output.="</a></b></div></td>";
				$cur++; 
				$col++;
			} 
			else { 
				$output.="&nbsp;</td>"; 
				$col++; 
			}  
				
			if($col==8) { 
				$output.="</tr>"; 
				$col=1; 
			}
		}
		$output.="</table>";
		  
		echo $output;
	}
	
	function generatePageBreaks($num){
		for($i = 0; $i < $num; $i++){
			echo "<br>";
		}
	}

//	NO LONGER NEEDED!	
//	function getDisplayNameByURL($lab_id){
//		$sql = "SELECT display_name FROM labs WHERE lab_id = '$lab_id'";
//		$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
//		if($result){
//			$row = mysql_fetch_array($result);
//			return $row['display_name'];
//		}
//		else {
//			generate_error("Error Fetching Lab Display Name", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
//		}
//	}
	
	function getLabConsultantCount($lab_id){
		$sql = sprintf("SELECT number_consultants FROM labs WHERE lab_id = %d", verifySQL($lab_id));
		$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
			$row = mysql_fetch_array($result);
			return $row['number_consultants'];
		}
		else {
			generate_error("Error Fetching Lab Consultant Number", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	
	function getLabDisplayName($lab_id){
		$sql = sprintf("SELECT display_name FROM labs WHERE lab_id = %d", verifySQL($lab_id));
		$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		if($result){
			$row = mysql_fetch_array($result);
			return $row['display_name'];
		}
		else {
			echo "ERROR Fetching Lab Display Name!";
			generate_error("Error Fetching Lab Display Name", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	
	function generateHourField($name, $value){
		$value = '<input type="text" input style="background-color:#595D66, text-color:#000000"  readonly="readonly" name='. $name . ' value='.formatMilitaryTime($value).' </>';
		echo $value;
	}
	
	function generateHourDropDownMenu($name, $time){
		$num_hours = 24;
		$value = "";
		$value .= '<select name="' . $name . '">';
		$value .= '<option value="" selected></option>';
			for($i = 0; $i < $num_hours; $i++){
					$value .= '<option value="' . $i . '">' . convertTime($i) .'</option>';
			}
		$value .= '</select>';
		echo $value;
	}

	function getNumPositions($lab_id){
		$sql = sprintf("SELECT * FROM labs WHERE lab_id = %d", verifySQL($lab_id));
		$result = mysql_query($sql) or generate_error("Error Fetching Number of Consultants", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$positionArray = mysql_fetch_array($result);
		$positions = $positionArray['number_consultants'];
		return $positions;
	}
	
	function getOpenShifts($lab_id){
		$sql = sprintf("SELECT * 
						FROM tempshifts 
						WHERE lab_id = %d
						ORDER BY year, month, day, start_time", verifySQL($lab_id));
		$result = mysql_query($sql) or generate_error("Error Fetching Temp Shifts", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		echo '<table border="1" align="center">';
			echo '<tr>';
				echo '<td align="center">Date:</td>';
				echo '<td align="center">Time:</td>';	
				echo '<td align="center">Regular Consultant:</td>';	
				echo '<td align="center">Temp Consultant:</td>';
			echo '</tr>';
			
			while($row = mysql_fetch_array($result)){
				$currentDate = mktime(0, 0, 0, getMonth(), getDay(), getYear());
				$shiftDate = mktime(0, 0, 0, $row['month'], $row['day'], $row['year']);
				if($shiftDate >= $currentDate){
					echo '<tr>';
						echo '<td align="center">' . $row['month'] . '-' . $row['day'] . '-' . $row['year'] . '</td>';
						echo '<td align="center">' . convertTime($row['start_time']) . '-' . convertTime($row['end_time']) . '</td>';
						echo '<td align="center">';
						
/*						if($_SESSION['hov_auth'] == 1 && authorizationRequired($lab_id)){
							echo '<a href="consultant_manage_temp_request.php?tempshift_id=' . $row['tempshift_id'] . '">';
							echo getNameByID($row['regular_consultant']) . '</a></td>';
						}
						elseif($_SESSION['hov_auth'] == 0 && authorizationRequired($lab_id)){
							$_SESSION['error_message'] = "You are not authorized to work in this lab.  
								Contact the lab administrator if you feel you have received this message in error.";
							$_SESSION['error_email_admin'] = 0;
							echo getNameByID($row['regular_consultant']) . '&nbsp;<a href="error.php">';
							echo '<img src="includes/images/exclamation.gif" border="0"></a></td>';
						}
						else { */
							echo '<a href="consultant_manage_temp_request.php?tempshift_id=' . $row['tempshift_id'] . '">';
							echo getNameByID($row['regular_consultant']) . '</a></td>';
//						}
						
						if($row['taken'] == 1){
							echo '<td align="center"><a href="consultant_manage_temp_request.php?';
							echo 'tempshift_id=' . $row['tempshift_id'] . '">';
							echo getNameByID($row['temp_consultant']) . '</a></td>';
						}
						else {
							echo '<td align="center">';
/*							if($_SESSION['hov_auth'] == 1 && authorizationRequired($lab_id)){
								echo '<a class="notfilled" href="consultant_manage_temp_request.php?tempshift_id='.$row['tempshift_id'].'">';
								echo 'Not Filled!</a></td>';
							}
							elseif($_SESSION['hov_auth'] == 0 && authorizationRequired($lab_id)){
								$_SESSION['error_message'] = "You are not authorized to work in this lab.  
									Contact the lab administrator if you feel you have received this message in error.";
								$_SESSION['error_email_admin'] = 0;
								echo 'Not Filled!&nbsp;<a href="error.php"><img src="includes/images/exclamation.gif" border="0"></a></td>';
							}
							else { */
								echo '<a class="notfilled" href="consultant_manage_temp_request.php?tempshift_id='.$row['tempshift_id'].'">';
								echo 'Not Filled!</a></td>';
//							}
						}
					echo '</tr>';
				}
			}
		echo '</table>';
	}
	
	function getTempShiftCount($lab_id){
		$sql = sprintf("SELECT * 
						FROM tempshifts 
						WHERE lab_id = %d", verifySQL($lab_id), getYear(), getMonth());
		$result = mysql_query($sql) or generate_error("Error Fetching Number of Temp Shifts", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row_count = mysql_num_rows($result);
		return $row_count;
	}
	
	function createLogEntry($action, $consultant, $shift_month, $shift_day, $shift_year, $start_time, $end_time, $lab_id, 
							$email, $mailTo, $mailHeaders, $mailSubject, $mailBody){
		$current_month = getMonth();
		$current_day = getDay();
		$current_year = getYear();
		$current_hour = getHour();
		$current_minute = getMinute();

		if($email == 1){
			$sql = sprintf("INSERT INTO log ( action, month, day, year, hour, minute, consultant, shift_start_time, shift_end_time,"); 
			$sql .= sprintf(" shift_month, shift_day, shift_year, labid, email, emailTo, emailHeaders, emailSubject, emailBody)");
			$sql .= sprintf(" VALUES ('%s', %d, %d, %d, ", verifySQL($action), verifySQL($current_month), verifySQL($current_day), verifySQL($current_year));
			$sql .= sprintf(" %d, %d, %d,", verifySQL($current_hour), verifySQL($current_minute), verifySQL($consultant));
			$sql .= sprintf(" %d, %d, %d, %d, %d, %d,", verifySQL($start_time), verifySQL($end_time), verifySQL($shift_month), verifySQL($shift_day), verifySQL($shift_year), verifySQL($lab_id)); 
			$sql .= sprintf(" 1, '%s', '%s', '%s', ", verifySQL($mailTo), verifySQL($mailHeaders), verifySQL($mailSubject));
			$mailBodyTemp = str_replace('\r\n', '<br>', $mailBody);
			$mailBodyTemp = str_replace('\n', '<br>', $mailBody);
			$mailBodyTemp = str_replace('\r', '<br>', $mailBody);
			$sql .= sprintf("'%s')", verifySQL($mailBodyTemp));		
		}
		else {
			$sql = "INSERT INTO log ( action, month, day, year, hour, minute, consultant, shift_start_time, shift_end_time, ";
			$sql .= "shift_month, shift_day, shift_year, labid, email, emailTo, emailHeaders, emailSubject, emailBody) ";
			$sql .= "VALUES (";
			$sql .= sprintf("'%s', %d, %d, ", verifySQL($action), verifySQL($current_month), verifySQL($current_day));
			$sql .= sprintf("%d, %d, %d, %d, ", verifySQL($current_year), verifySQL($current_hour), verifySQL($current_minute), verifySQL($consultant));
			$sql .= sprintf("%d, %d, %d, %d, %d, ", verifySQL($start_time), verifySQL($end_time), verifySQL($shift_month), 
													verifySQL($shift_day), verifySQL($shift_year));
			$sql .= sprintf("%d, 0, NULL, NULL, NULL, NULL)", $lab_id);
		}

		$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if(!$result){
			generate_error("Error Createing Log Entry", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	
	function cancelTakeTempShift($tempshift_id){
		$sql = sprintf("UPDATE tempshifts
				SET temp_consultant = NULL,
					time_taken = NULL,
					taken = 0
				WHERE tempshift_id = %d
				LIMIT 1", verifySQL($tempshift_id));
		$result = mysql_query($sql) or generate_error("Error Canceling Temp Shift", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if($result){
			return 1;
		}
		else {
			return 0;
		}
	}
	
	function cancelTakeTempShiftOutsideLimit($tempshift_id, $conid){
		$sql = sprintf("UPDATE tempshifts
				SET regular_consultant = $conid,
					temp_consultant = NULL,
					time_taken = NULL,
					taken = 0
				WHERE tempshift_id = %d
				LIMIT 1", verifySQL($tempshift_id));
		$result = mysql_query($sql) or generate_error("Error Canceling Temp Shift", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		
		if($result){
			return 1;
		}
		else {
			return 0;
		}
	}
	
	function addToShiftCount($conid){
		$sql = sprintf("SELECT shifts_temped FROM consultants WHERE conid = %d", verifySQL($conid));
		$result = mysql_query($sql) or generate_error("Error Running MySQL Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$details = mysql_fetch_array($result);
		$currentCount = $details['shifts_temped'];
		$currentCount++;
		$sql = sprintf("UPDATE consultants SET shifts_temped = %d WHERE conid = %d LIMIT 1", verifySQL($currentCount), verifySQL($conid));
		mysql_query($sql) or generate_error("Error Running MySQL Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
	}
	
	function subtractFromShiftCount($conid){
		$sql = sprintf("SELECT shifts_temped FROM consultants WHERE conid = %d", verifySQL($conid));
		$result = mysql_query($sql) or generate_error("Error Running MySQL Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$details = mysql_fetch_array($result);
		$currentCount = $details['shifts_temped'];
		if($currentCount != 0){
			$currentCount--;
			$sql = sprintf("UPDATE consultants SET shifts_temped = %d WHERE conid = %d LIMIT 1", verifySQL($currentCount), verifySQL($conid));
			mysql_query($sql) or generate_error("Error Running MySQL Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		}
	}
	
	function verifySQL($value){
		if(is_int($value)){
			return $value;
		}
		elseif(is_string($value)){
			$string = mysql_real_escape_string($value);
			return $string;
		}
		elseif(is_null($value)){
			return $value;
		}
		else {
			generate_error("A value entered was invalid.  Please try again.", NULL, $_SERVER['PHP_SELF'], __LINE__, 0);
			exit();
		}
	}
	
	function authorizationRequired($lab_id){
		$sql = sprintf("SELECT auth_required FROM labs WHERE lab_id = %d", $lab_id);
		$result = mysql_query($sql) or generate_error("Error Running MySQL Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);
		
		if($row['auth_required'] == 1){
			return 1;
		}
		else {
			return 0;
		}
	}
		
	//----------------------- Header ------------------------------------
?>
	<html>
		<head>
			<!--Title-->
			<title>Wireless Helpdesk Scheduler</title>
			
			<!--Stylesheets-->
			<link rel='stylesheet' type='text/css' href='includes/css/style.css'/>
            <link rel='stylesheet' type='text/css' href='includes/nifty/niftyCorners.css'/>

			<!--JavaScripts-->
			<script type="text/javascript" src="includes/nifty/niftycube.js"></script>
            <script type="text/javascript" src="includes/common_functions.js"></script>
			<script type="text/javascript">
				<!--Onload function to setup NiftyCorners and Nav Menu-->
				window.onload=function(){
					Nifty("div#content,div#nav,div#header");
					Nifty("div#footer");
					Nifty("li#button1,li#button2,li#button3,li#button4,li#button5");
				}
				// *** Navigation Script ***   Copyright 2006-2007 javascript-array.com
				
				var timeout	= 500;
				var closetimer	= 0;
				var ddmenuitem	= 0;
				
				// Open Hidden Layer
				function mopen(id)
				{	
					// cancel close timer
					mcancelclosetime();
				
					// close old layer
					if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
				
					// get new layer and show it
					ddmenuitem = document.getElementById(id);
					ddmenuitem.style.visibility = 'visible';
				
				}
				// close showed layer
				function mclose()
				{
					if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
				}
				
				// go close timer
				function mclosetime()
				{
					closetimer = window.setTimeout(mclose, timeout);
				}
				
				// cancel close timer
				function mcancelclosetime()
				{
					if(closetimer)
					{
						window.clearTimeout(closetimer);
						closetimer = null;
					}
				}
				// close layer when click-out
				document.onclick = mclose;
			</script>
		</head>