<?php
	require('includes/application.php');
	
	if(isset($_GET['lab_id'])){
		$lab_id = $_GET['lab_id'];
	}
	else {
		$lab_id = "";
	}
?>

<style type="text/css">
	a:active {color: #FFF;}
	a:visited {color: #FFF;}
	a:link {color: #FFF;}
	a:hover {color: #FFF;}
</style>

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
				echo "<h1>Shifts Temped Out:</h1>";
                generatePageBreaks(1);
				
				$takenSQL = sprintf("SELECT * FROM tempshifts WHERE regular_consultant = %d", $_SESSION['conid']);
				$takenResult = mysql_query($takenSQL, $conn);
				
				$takenRowCount = mysql_num_rows($takenResult);
				
				if($takenRowCount > 0){
					echo '<table border="1" align="center">';
					echo '<tr>';
						echo '<td align="center">Date:</td>';
						echo '<td align="center">Time:</td>';	
						echo '<td align="center">Temp Consultant:</td>';	
						echo '<td align="center">Location:</td>';
					echo '</tr>';
					while($row = mysql_fetch_array($takenResult)){
						echo '<tr>';
							echo '<td align="center">' . $row['month'] . '-' . $row['day'] . '-' . $row['year'] . '</td>';
							echo '<td align="center">' . convertTime($row['start_time']) . '-' . convertTime($row['end_time']) . '</td>';
							if($row['temp_consultant'] != NULL){
								echo '<td align="center">' . getNameByID($row['temp_consultant']) . '</td>';
							}
							else {
								echo '<td align="center">Not Filled!</td>';
							}
							echo '<td align="center">' . getLabDisplayName($row['lab_id']) . '</td>';
						echo '</tr>';
					}
					echo '</table>';
				}
				else {
					echo "<h2>No Shifts Temped Out</h2>";
				}
				
				generatePageBreaks(3);
				
				echo "<h1>Temp Shifts Taken:</h1>";
                generatePageBreaks(1);
				
				$tempedSQL = sprintf("SELECT * FROM tempshifts WHERE temp_consultant = %d", $_SESSION['conid']);
                $tempedResult = mysql_query($tempedSQL, $conn);
				
				$tempedRowCount = mysql_num_rows($tempedResult);
				
				if($tempedRowCount > 0){
					echo '<table border="1" align="center">';
					echo '<tr>';
						echo '<td align="center">Date:</td>';
						echo '<td align="center">Time:</td>';	
						echo '<td align="center">Regular Consultant:</td>';	
						echo '<td align="center">Location:</td>';
					echo '</tr>';
					while($row = mysql_fetch_array($tempedResult)){
						echo '<tr>';
							echo '<td align="center">' . $row['month'] . '-' . $row['day'] . '-' . $row['year'] . '</td>';
							echo '<td align="center">' . convertTime($row['start_time']) . '-' . convertTime($row['end_time']) . '</td>';
							echo '<td align="center">' . getNameByID($row['regular_consultant']) . '</td>';
							echo '<td align="center">' . getLabDisplayName($row['lab_id']) . '</td>';
						echo '</tr>';
					}
					echo '</table>';
				}
				else {
					echo "<h2>No Temp Shifts Taken</h2>";
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