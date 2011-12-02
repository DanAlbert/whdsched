<?php
	//COMMON FUNCTIONS
	
	function formatShiftDateForEmail($month, $day, $year){
		$shiftDate = mktime(0, 0, 0, $month, $day, $year);	//Create date from shift date
		$shiftDate = date('l, F j, Y', $shiftDate);  		//Format To: Wednesday, September 3, 2008
		return $shiftDate;
	}
	
	function formatShiftTimeForEmail($start_time, $end_time){
		$shiftStartTime = convertTime($start_time);
		$shiftEndTime = convertTime($end_time);
		$shiftTime = $shiftStartTime . " - " . $shiftEndTime;
		return $shiftTime;
	}
	
	function getEmailTo(){
		loadApplicationVariables();
		if($_SESSION['test_mode'] == "Yes" || $_SESSION['test_mode'] == ""){
			if($_SESSION['developer_email'] != ""){
				$mailTo = $_SESSION['developer_email'];
			}
			else {
				$mailTo = "quicka@engr.orst.edu";
			}
		}
		else {
			if($_SESSION['email_to'] != ""){
				$mailTo = $_SESSION['email_to'];
			}
			else {
				$mailTo = "quicka@engr.orst.edu";
			}
		}
		return $mailTo;
	}
	function getEmailHeaders(){
		loadApplicationVariables();
		if($_SESSION['email_from'] != ""){
			$mailHeaders = "From: " . $_SESSION['email_from'];
		}
		else {
			$mailHeaders = "From: Helpdesk-Staff";
		}
		return $mailHeaders;
	}

	//****************************************************************************
	//********** E-MAIL MESSAGE TEMPLATES FOR MAIL TO CONSULTANT LIST ************
	//****************************************************************************
	
	
	function emailRegularConsultantRescinded($month, $day, $year, $start_time, $end_time, $lab_id){
		$mailTo = getEmailTo();															//Get Address to Send E-Mail To
		$mailHeaders = getEmailHeaders();												//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);						//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);					//Format Shift Time
		$labName = getLabDisplayName($lab_id);											//Format Lab Name
		$mailSubject = "Temp Request Rescinded: " . $shiftDate . "  " . $shiftTime;		//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = $_SESSION['full_name'] . " is no longer looking for a temp and will work during this shift!\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);		
	}
	
	function emailTempConCancelledInLimit($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant){
		$mailTo = getEmailTo();													//Get Address to Send E-Mail To
		$mailHeaders = getEmailHeaders();										//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);				//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);			//Format Shift Time
		$labName = getLabDisplayName($lab_id);									//Format Lab Name
		$mailSubject = "Temp Request: " . $shiftDate . "  " . $shiftTime;		//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = $_SESSION['full_name']. " needs a temp!\n";
		$mailBody .= $_SESSION['full_name']. " was signed up to fill in for " . getNameByID($regular_consultant)  . " BUT has since changed his/her mind.\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);	
	}
	
	function emailTempConCancelledOutsideLimit($month, $day, $year, $start_time, $end_time, $lab_id){
		$mailTo = getEmailTo();													//Get Address to Send E-Mail To
		$mailHeaders = getEmailHeaders();										//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);				//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);			//Format Shift Time
		$labName = getLabDisplayName($lab_id);									//Format Lab Name
		$mailSubject = "Temp Request: " . $shiftDate . "  " . $shiftTime;		//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = $_SESSION['full_name']. " needs a temp!\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
	}
	
//	function emailTempShiftTaken($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant){
//		$mailTo = getEmailTo();													//Get Address to Send E-Mail To
//		$mailHeaders = getEmailHeaders();										//Get Mail Headers
//		$shiftDate = formatShiftDateForEmail($month, $day, $year);				//Format Shift Date
//		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);			//Format Shift Time
//		$labName = getLabDisplayName($lab_id);									//Format Lab Name
//		$mailSubject = "Temp Shift Taken: " . $shiftDate . "  " . $shiftTime;	//Format Mail Subject
//		
//		//Format Mail Body:
//		$mailBody = $_SESSION['full_name'] . " has agreed to fill in for " . getNameByID($regular_consultant) . "!\n\n";
//		$mailBody .= "LOCATION:   " . $labName . "\n";
//		$mailBody .= "DATE:       " . $shiftDate . "\n";
//		$mailBody .= "TIME:       " . $shiftTime . "\n\n\n";
//		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
//		
//		//Send Message
//		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
//		//Create Record of Sent E-mail
//		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
//					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
//	}
	
	function emailTempRequestCreated($month, $day, $year, $start_time, $end_time, $lab_id, $reason){
		$mailTo = getEmailTo();												//Get Address to Send E-Mail To
		$mailHeaders = getEmailHeaders();									//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);			//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);		//Format Shift Time
		$labName = getLabDisplayName($lab_id);								//Format Lab Name
		$mailSubject = "Temp Request: " . $shiftDate . "  " . $shiftTime;	//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = $_SESSION['full_name'] . " needs a temp!\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n\n";
		$reason = str_replace('\r\n', ' ', $reason);						//Strip New Lines from Reason
		$reason = str_replace('\r', ' ', $reason);							//Strip New Lines from Reason
		$reason = str_replace('\n', ' ', $reason);							//Strip New Lines from Reason
		$mailBody .= "Reason for Replacement: " . $reason . "\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
	}
	
	//****************************************************************************
	//*********** E-MAIL MESSAGE TEMPLATES FOR MAIL TO CONSULTANTS ***************
	//****************************************************************************
	
	function emailNotifyTempConCancelledInLimit($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant){
		$mailTo = getENGRUsernameByID($regular_consultant) . ", " . $_SESSION['admin_email'];	//Send E-mail to Regular Consultant's ENGR account
		$mailHeaders = getEmailHeaders();														//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);								//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);							//Format Shift Time
		$labName = getLabDisplayName($lab_id);													//Format Lab Name
		$mailSubject = "IMPORTANT NOTICE FROM WIRELESS HELPDESK!";									//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = "Hello " . getNameByID($regular_consultant) . ",\n\n";
		$mailBody .= "This e-mail is to inform you that " . $_SESSION['full_name'] . " has changed his/her mind and no longer wishes to work your shift.\n\n";
		$mailBody .= "Since they changed their mind within the 30 minute grace period, the shift has been re-temped.\n\n";
		$mailBody .= "If no one takes the shift, YOU are responsible for working it.\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n";
		$mailBody .= "If you have any questions, or cannot work this shift, please contact quicka@engr.orst.edu immediately to make alternate arrangements.\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
	}
	
	function emailNotifyTempConCancelledOutsideLimit($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant){
		$mailTo = getENGRUsernameByID($regular_consultant) . ", " . $_SESSION['admin_email'];		//Send E-mail to Regular Consultant's ENGR account
		$mailHeaders = getEmailHeaders();															//Get Mail Headers
		$shiftDate = formatShiftDateForEmail($month, $day, $year);									//Format Shift Date
		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);								//Format Shift Time
		$labName = getLabDisplayName($lab_id);														//Format Lab Name
		$mailSubject = "IMPORTANT NOTICE FROM WIRELESS HELPDESK!";										//Format Mail Subject
		
		//Format Mail Body:
		$mailBody = "Hello " . getNameByID($regular_consultant) . ",\n\n";
		$mailBody .= "This e-mail is to inform you that " . $_SESSION['full_name'] . " has changed his/her mind and no longer wishes to work your shift.";
		$mailBody .= "  However, they changed their mind outside the 30 minute grace period.\n\n";
		$mailBody .= "Since it is outside the grace period, the shift has been re-temped in their name.\n\n";
		$mailBody .= "You are NOT responsible for working this shift.\n\n";
		$mailBody .= "LOCATION:   " . $labName . "\n";
		$mailBody .= "DATE:       " . $shiftDate . "\n";
		$mailBody .= "TIME:       " . $shiftTime . "\n\n";
		$mailBody .= "If you have any questions, or cannot work this shift, please contact quicka@engr.orst.edu immediately.\n\n";
		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
		
		//Create Record of Sent E-mail
		createLogEntry("E-Mail", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
	}
	
	function emailNotifyShiftReminder($month, $day, $year, $start_time, $end_time, $lab_id, $regular_consultant){
//		$mailTo = getENGRUsernameByID($regular_consultant) . "@engr.orst.edu, " . $_SESSION['admin_email'];	//Send E-mail to Regular Consultant's ENGR account
//		$mailHeaders = getEmailHeaders();																	//Get Mail Headers
//		$shiftDate = formatShiftDateForEmail($month, $day, $year);											//Format Shift Date
//		$shiftTime = formatShiftTimeForEmail($start_time, $end_time);										//Format Shift Time
//		$labName = getLabDisplayName($lab_id);																//Format Lab Name
//		$mailSubject = "Temp Shift Reminder: " . $shiftDate . "  " . $shiftTime;							//Format Mail Subject
//		
//		//Format Mail Body:
//		$mailBody = "Hello " . getNameByID($regular_consultant) . ",\n\n";
//		$mailBody .= "This e-mail is to remind you that you have a temp shift coming up.\n\n";
//		$mailBody .= "LOCATION:   " . $labName . "\n";
//		$mailBody .= "DATE:       " . $shiftDate . "\n";
//		$mailBody .= "TIME:       " . $shiftTime . "\n\n";
//		$mailBody .= "If you have any questions, or cannot work this shift, please contact quicka@engr.orst.edu immediately.\n\n";
//		$mailBody .= "https://secure.engr.oregonstate.edu/helpdesk-staff/TempShift/";
//		
//		$SQL = sprintf("INSERT INTO email_queue (to, header, subject, body, sent) VALUES ('%s', '%s', '%s', '%s', 0)", 
//							verifySQL($mailTo), verifySQL($mailHeaders), verifySQL($mailSubject), verifySQL($mailBody));
//		$result = mysql_query($SQL);
//		
//		if(!$result){
//			//generate_error("Error Adding Consultant Shift Notification to E-mail Queue", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
//		}
//		else {
//			echo "Working?";
//		}
//		
//		//Create Record of Sent E-mail
//		createLogEntry("E-Mail Added To Queue", $_SESSION['conid'], $month, $day, $year, $start_time, $end_time, $lab_id, 
//					   1, $mailTo, $mailHeaders, $mailSubject, $mailBody);
	}
?>