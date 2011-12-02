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
				if($action == "settings"){
					echo '<h1>Settings:</h1>';
					require('logic/admin_utilities_settings.php');
				}
				elseif($action =="email"){
					echo '<h1>E-Mail Queue:</h1>';
					require('logic/admin_utilities_email.php');
				}
				else {
					echo '<h1>Please select an option from the Utilities menu.</h1>';
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