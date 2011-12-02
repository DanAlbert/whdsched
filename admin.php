<?php
	require('includes/application.php');
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
        <div id="content">
            <h1>Welcome <?php echo $_SESSION['full_name'] ?>!</h1>
            <br>
            <h2>Please select an option from the navigation menu.</h2>
            <br>
            <h2>If you would like to temp out a shift for a consultant,</h2>
            <h2>select "Impersonate" from the Consultant's menu</h2>
            <br>
            <h2><a href="index.php">Click Here</a> to go to the Consultant Interface</h2>     
			<?php 
				generatePageBreaks(22); 
				include('includes/html_footer.php');
			?>
        </div>
</body>

<!------- INCLUDE FOOTER ---------->
<?php
	require('includes/footer.php');
?>