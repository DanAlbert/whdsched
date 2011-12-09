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
                if($lab_id > 0){
                    echo "<h2>" . getLabDisplayName($lab_id) . " Open Shifts:</h2>";
					$num_rows = getTempShiftCount($lab_id);
					
					if($num_rows > 0){
						 generatePageBreaks(1);
						 getOpenShifts($lab_id);
						 generatePageBreaks(1);
					}
					else {
						generatePageBreaks(1);
						echo '<h3>No Open Shifts!</h3>';
						generatePageBreaks(1);
					}
                }
                elseif($lab_id == 0) {
                    echo "<h2>All Open Shifts:</h2>";
                    generatePageBreaks(1);
					
					$sql = sprintf("SELECT * FROM labs");
					$result = mysql_query($sql) or generate_error("Error Executing Query", mysql_error(), $_SERVER['PHP_SELF'], __LINE__, 1);
					while($row = mysql_fetch_array($result)){
						echo "<h2>" . getLabDisplayName($row[$lab_id]) . " Open Shifts:</h2>";
						$num_rows = getTempShiftCount($row[$lab_id]);
						
						if($num_rows > 0){
							generatePageBreaks(1);
							getOpenShifts($row[$lab_id]);
							generatePageBreaks(1);
						}
						else {
							echo '<h3>No Open Shifts!</h3>';
							generatePageBreaks(1);;
						}
					}
                }
				else {
					echo "<h2>Please select a location</h2>";
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