<?php
	function generate_error($error_message, $mysql_error, $page, $line_number, $email_admin){
		$_SESSION['error_message'] = $error_message;
		$_SESSION['error_email_admin'] = $email_admin;
		
		if($email_admin){
			email_admin($error_message, $mysql_error, $page, $line_number);
		}
		echo '<script type="text/javascript">';
			echo 'window.location = "error.php"';
		echo '</script>';
		
		exit();
	}
	
	function email_admin($error_message, $mysql_error, $page, $line_number){
		$mailTo = $_SESSION['developer_email'];
		$mailHeaders = "From: " . $_SESSION['email_from']; 
		$mailSubject = "Hovland TempShift2 Error";
		
		//Format Mail Body:
		$mailBody = "There has been an error with TempShift2\n\n";
		$mailBody .= "Error Message: " . $error_message . "\n";
		if($mysql_error != NULL){	
			$mailBody .= "MySQL Error Message: " . $mysql_error . "\n";
		}
		$mailBody .= "Page: " . $page . "\n";
		$mailBody .= "Line Number: " . $line_number . "\n";
		$mailBody .= "User: " . $_SESSION['full_name'] . "\n";
		$mailBody .= "Time: " . date("n-j-Y g:iA", mktime(getHour(), getMinute(), 0, getMonth(), getDay(), getYear())) . "\n";
		
		//Send Message
		mail($mailTo, $mailSubject, $mailBody, $mailHeaders);
	}
?>