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
                if($action == "add"){
                    echo "<h2>Add Schedule:</h2>";
                    echo "<br>";
                    include('logic/admin_add_schedule_logic.php');
                }
				elseif($action == "edit"){
                    echo "<h2>Edit Schedule:</h2>";
                    echo "<br>";
                    include('logic/admin_edit_schedule_logic.php');
                }
				elseif($action == "delete"){
                    echo "<h2>Delete Schedule:</h2>";
					echo "<br>";
                    include('logic/admin_delete_schedule_logic.php');
                }
                elseif($action == "view"){
                    echo "<h2>View Schedule:</h2>";
					echo "<br>";
                    include('logic/admin_view_schedule_logic.php');
				}
                else {
                    echo "<h2>Please select an option from the navigation bar.</h2>";
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