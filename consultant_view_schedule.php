<?php
	require('includes/application.php');
	
	if(isset($_GET['lab_id'])){
		$lab_id = $_GET['lab_id'];
	}
	else {
		$lab_id = "";
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
				if($lab_id != ""){
                    echo "<h2><b>" . getLabDisplayName($lab_id) . " Schedule</b></h2>";
                    echo "<br>";
                    
                    if(isset($_GET['month']) && isset($_GET['day']) && isset($_GET['year'])){
                        $month = $_GET['month'];
                        $day = $_GET['day'];
                        $year = $_GET['year'];
    					$number_consultants = getLabConsultantCount($lab_id);
                        generateSchedule($lab_id, $month, $day, $year, $number_consultants);
                    }
                    else {
						if(isset($_GET['month']) && isset($_GET['year'])){
							$month = $_GET['month'];
						   	$year = $_GET['year'];
					   	}
					   	else {
						   $month = getMonth();
						   $year = getYear();
					   	}
					   	generateCalendar($month, $year, $lab_id);
                    }
                }
                else {
                    echo "Please select a location";
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