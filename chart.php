<?php
ob_start();
date_default_timezone_set('America/New_York');
require_once("lib/presentation.php");
require_once("lib/lib_v2.php");
UserManagement::InitSession();
if(!isset($_SESSION['User'])) {
	header("Location: /");
	die();	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!DOCTYPE html>
<html lang="en">
      <head>
        <meta charset="utf-8">
        <title>MyAarth.com</title>
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="stylesheet" href="/Style/bootstrap.min.css"/>
        <link rel="stylesheet" href="/Style/Main.css"/>
        <script type="text/javascript" src="Script/jquery.min.js"></script>
        <script type="text/javascript" src="Script/bootstrap-modal.js"></script>
        <script type="text/javascript" src="Script/bootstrap-twipsy.js"></script>
        <script type="text/javascript" src="Script/bootstrap-popover.js"></script>
        <script type="text/javascript" src="/Script/Tracker_v3.js"></script>
        <script type="text/javascript" src="lib/open-flash-chart-2-Lug-Wyrm-Charmer/js/swfobject.js"></script>
         <script type="text/javascript">
        swfobject.embedSWF(
          "open-flash-chart.swf", "my_chart", "700", "400",
          "9.0.0", "expressInstall.swf",
          {"data-file":"returns.php"}
          );
		  
		  swfobject.embedSWF(
          "open-flash-chart.swf", "all", "700", "400",
          "9.0.0", "expressInstall.swf",
          {"data-file":"all.php"}
          );
        </script>
        <?php
		UserManagement::SetInactiveLogoutTime(0);
		?>
      </head>
	<body>
    <div class="container">
     <div class="page-header" style="height:100px;">
			<?PHP
            Presentation::outputHeader();
            ?>
            <div id="moneyTreeImage" style="position:relative;left:520px;top:-100px;width:200px;z-index:10;">
                <img src="/Style/Images/MoneyTreeSmall.png">
            </div>
      </div>
      <?php
	  $DB = new Database();
	  $data = $DB->trackTotal_getData($_SESSION['User']->user_id);
	  if(count($data) <= 1){
	  	print("<center><h4>Not enough data to track performance.</h4></center>");
	  } else {
		print('<center><div id="my_chart" style="border:0.5px; padding:10px;"></div></center><br/><br/>');
		//print('<center><div id="all" style="border:0.5px; padding:10px;"></div></center>');  
	  }
	  ?>
      <footer style="padding-bottom:5%;"><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com' style="color:blue;">contact@myaarth.com</a> | <button id="logout" style="background-color:white; color:blue;">Log Out</button></center></footer>
	</div>
	</body>
    
</html>