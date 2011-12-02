<?php
	require 'includes/application.php';
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
	Modified By:    Andy Quick for CoE Helpdesk

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
        <div id="content">
            <br>
            <?php
				if($_SESSION['imersonate_mode'] == 1){
					$admin_impersonating = getNameByID($_SESSION['admin_impersonating']);
					echo '<h1>Welcome ' . $admin_impersonating . '!</h1>';
					echo '<h2>You are currently impersonating</h2>';
					echo '<h1>' . $_SESSION['name'] . '</h1>';
					generatePageBreaks(2);
					echo '<h2>To restore your credentials, you will need to logout and then log back in.</h2>';
				}
				else {
					echo '<h1>Welcome ' . $_SESSION['full_name'] . '!</h1>';
				}
				generatePageBreaks(2);
				echo '<h2>If you would like to view open shifts, click Open Shifts and select a lab.</h2>';
				generatePageBreaks(2);
				echo '<h2>If you would like to view the schedule, click Schedule and select a lab.</h2>';
				echo '<br><br>';
				if($_SESSION['admin'] == 1){
                    echo '<h1>Administrator:</h1>';
					echo '<h2><a href="admin.php">Click Here</a> to go to the Administrator Interface</h2>';
                    echo '<h2></h2>';
                }
				
				generatePageBreaks(18);
				include('includes/html_footer.php');
            ?>
        </div>
</body>

<!------- INCLUDE FOOTER ---------->
<?php
	require 'includes/footer.php';
?>