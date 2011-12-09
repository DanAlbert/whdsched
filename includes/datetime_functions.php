<?php
	
	//Function:  convertTime
	//Arguments:  $hour
	//Description:  Converts the hour from military time to standard time with AM/PM.
	function convertTime($hour){
		if($hour == 0){
			return "0000";
		}
		elseif($hour > 0 && $hour <1000){
			return "0" . $hour;
		}
		elseif($hour >=1000){
			return $hour;
		}
		else {
			$hour = $hour - 12;
			return $hour . "PM";
		}
	}
	
	function formatMilitaryTime($hour){
		if($hour < 10){
			$hour =  0 . $hour;
			return $hour . 0 . 0;
		}
		elseif($hour >= 10 && $hour < 25){
			return $hour . 0 . 0;
		}
	}

	function isEnded($month, $day, $year, $end_time){
		if($end_time == 0){
			$temp_end_time = 23;
			$shift_time = mktime($temp_end_time, 59, 0, $month, $day, $year);
		}
		else {
			$shift_time = mktime($end_time, 0, 0, $month, $day, $year);
		}
		
		
		$currentTime = time();
		
		if($currentTime > $shift_time){
			return 1; //Shift has already ended
		}
		else {
			return 0; //Shift has not ended yet
		}
	}
	
	function isInEffectiveRange($lab_id, $dayofweekNumber, $dateOfShift){
		$query = sprintf("SELECT * FROM schedule WHERE lab_id = %d AND dayofweek = %d", verifySQL($lab_id), verifySQL($dayofweekNumber));
		$result = mysql_query($query) or generate_error("Error Fetching Schedule", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
		$row = mysql_fetch_array($result);

		$start_date = strtotime($row['start_date']);
		$end_date = strtotime($row['end_date']);
		
		if($dateOfShift >= $start_date && $dateOfShift <= $end_date){
			return 1;
		}
		elseif($start_date == NULL || $end_date == NULL){
			return 1;
		}
		else {
			return 0;
		}
	}

	function getMonth(){
		$currentTime = time();
		$month = date('n', $currentTime);
		return $month;
	}
	
	function getDay(){
		$currentTime = time();
		$day = date('j', $currentTime);
		return $day;
	}
	
	function getYear(){
		$currentTime = time();
		$year = date('Y', $currentTime);
		return $year;
	}
	
	function getHour(){
		$currentTime = time();
		$hour = date('G', $currentTime);
		return $hour;
	}
	
	function getMinute(){
		$currentTime = time();
		$minute = date('i', $currentTime);
		return $minute;
	}

	function inGracePeriod($time_taken){
		$time_taken = strtotime($time_taken);
		$currentTime = time();
		$difference = ($currentTime - $time_taken) / 60;
		
		if($difference <= 30){
			return 1;
		}
		else{
			return 0;
		}	
	}
?>