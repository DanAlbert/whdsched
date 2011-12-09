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
        <div id="content" class="postnav">
            <?php
				if($action == "details"){
					echo "<h2>Open Temp Shift Details:</h2>";
					generatePageBreaks(1);
					include('logic/admin_view_temp_shifts_details_logic.php');
				}
				elseif($action == "edit"){
					echo "<h2>Edit Temp Shift Details:</h2>";
					generatePageBreaks(1);
					include('logic/admin_view_temp_shifts_edit_logic.php');
				}
				else {
					echo "<h2>Open Temp Shifts:</h2>";
					generatePageBreaks(1);
					include('logic/admin_view_temp_shifts_logic.php');
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