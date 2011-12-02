<?php
	require('includes/application.php');
	
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = "";
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
    	<?php require('includes/admin_navigation_bar.php'); ?>
    	<br>
    
    <!-------------- BEGIN CONTENT --------------->
    	<div id="content" class="postnav">
            <?php
				if($action == "all"){
					echo '<h1>View E-Mail Log:</h1>';
					generatePageBreaks(1);
					require('logic/admin_utilities_log_email_all.php');
				}
				elseif($action =="search"){
					echo '<h1>Search E-Mail Log:</h1>';
					generatePageBreaks(1);
					require('logic/admin_utilities_log_email_search.php');
				}
				elseif($action == "details"){
					echo '<h1>View E-Mail Log Details:</h1>';
					generatePageBreaks(1);
					require('logic/admin_utilities_log_email_details.php');
				}
				else {
					echo '<h1>E-Mail Log:</h1>';
					generatePageBreaks(1);
					echo generateButton('admin_utilities_log_email.php?action=all', "View All Entries");
					echo '&nbsp;&nbsp;';
					echo generateButton('admin_utilities_log_email.php?action=search', "Search Log");
					generatePageBreaks(2);
					echo '<h3>Recent Log Entries:</h3>';
					require('logic/admin_utilities_log_email_recent.php');
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