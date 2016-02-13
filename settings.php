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
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>MyAarth.com</title>
    <meta name="description" content="">
    <meta name="author" content="">
	<link rel="stylesheet" href="/Style/bootstrap.min.css"/>
	<link rel="stylesheet" href="/Style/Main.css"/>
    <script type="text/javascript">
		var modal_showing = false;
	</script>
	 <script type="text/javascript">
        swfobject.embedSWF(
          "open-flash-chart.swf", "my_chart", "700", "400",
          "9.0.0", "expressInstall.swf",
          {"data-file":"returns.php"}
          );
        </script>
	<script type="text/javascript" src="Script/jquery.min.js"></script>
	<script type="text/javascript" src="Script/bootstrap-modal.js"></script>
	<script type="text/javascript" src="Script/bootstrap-twipsy.js"></script>
	<script type="text/javascript" src="Script/bootstrap-popover.js"></script>
	<script type="text/javascript" src="/Script/Tracker_v4.js"></script>
	<style type="text/css">
		label {
			float: none;
			font-weight: 100;
		}
		
		#preferencesTable td {
			border-top: 0 solid #DDDDDD;
			padding: 0
		}
		
		#preferencesTable th {
			font-size: 12pt;
			padding: 0;
			padding-top: 10px;
		}
		
		#preferencesTable {
			padding: 0
		}
		
		*.unselectable {
		   -moz-user-select: -moz-none;
		   -khtml-user-select: none;
		   -webkit-user-select: none;
		   user-select: none;
		}
		
		.autocompleteSuggestion {
			cursor: pointer;
		}
		
		.showEmphasis {
			border-color: rgba(82, 168, 236, 0.8);
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) inset, 0 0 8px #62C462;
			outline: 0 none;
		}
		r {font-size:11px;}
		r2 {font-size:13px;}
		r3 {font-family:Arial; font-size:15px;}
		r4 {font-family:calibri;font-size:20px;font-style:bold}
		r5 {font-family:calibri;font-size:18px;}
		r6 {font-family:calibri;font-size:15px; font-style:bold}
		r6_red {font-family:calibri; font-size:17px; color:#F00;}
		r6_green {font-family:calibri; font-size:17px; color:#090;}
		groupInput {outline:none;}
		
		graphBtn {
    		padding: 5px 10px 5px 25px;
   			font-family: "Trebuchet MS", Arial, Verdana;   
    		background: #e9e9e9 url(https://www.myaarth.com/Style/Images/graph.gif) 5px 5px no-repeat;
    		border-radius: 3px;
    		border: 1px solid #d9d9d9;
    		text-shadow: 1px 1px #fff;
			position:relative;
			top:10px%;
		}

		graphBtn:hover {
			background-color: #f0f0f0;        
		}
		
		graphBtn:active{
			background-color: #e0e0e0;
		}
​
		
	</style>
    <?php
	UserManagement::SetInactiveLogoutTime(30);
	?>
  </head>

  <body>
  <div class="container">
  	<div class="content">
    	<div class="page-header" style="height:100px;">
			<?PHP
            Presentation::outputHeader();
            ?>
            <div id="moneyTreeImage" style="position:relative;left:520px;top:-100px;width:200px;z-index:10;"> <img src="/Style/Images/MoneyTreeSmall.png">
            </div>
        </div>
        <div class="row">
          <div class="span11"><input type="search" id="searchTextBox" value="Enter Symbol" data-default-value="Enter Symbol"> <button id="finishButton" class="btn success">Save Portfolio</button> <button class="btn primary" id="refreshPage">Get Last Portfolio</button>  <button id="resetPortfolioButton" class="btn error">Start New Portfolio</button>
            
			<div id="dateTrackerDiv" class="span10">
				<table id="preferencesTable" class="span10">
				<thead>
				<tr><th>Date</th><th>Preferred Email Address</th><th>Email Frequency</th></tr>
				</thead>
				<tbody>
					<tr>
					<td><input type="text" class="span3" id="user_date" value="<?php print(date('m/d/Y')); ?>" disabled="disabled">  </td>
					<td><input type="text" id="user_email" value="<?php print(isset($_SESSION['User']) ? $_SESSION['User']->user_email : "notlogged") ?>" disabled="disabled">  </td>
					<td>
						<label for="daily_input"><input type="radio" name="email_frequency" value="Daily" id="daily_input" <?php $_SESSION['User']->email_frequency == "Daily" || $_SESSION['User']->email_frequency == null ? print("checked='checked'") : print (""); ?>/><r3>Daily  </r3></label>
						<label for="weekly_input"> <input type="radio" name="email_frequency" value="Weekly" id="weekly_input" <?php $_SESSION['User']->email_frequency == "Weekly" ? print("checked='checked'") : print (""); ?>//><r3> Weekly  </r3></label>
						<label for="monthly_input"> <input type="radio" name="email_frequency" value="Monthly" id="monthly_input" <?php $_SESSION['User']->email_frequency == "Monthly" ? print("checked='check'") : print (""); ?>//><r3> Monthly  </r3></label> 
						<label for="none_input"> <input type="radio" name="email_frequency" value="None" id="none_input" <?php $_SESSION['User']->email_frequency == "None" ? print("checked='checked'") : print (""); ?>//><r3> None</r3></label>
					</td>
					</tr>
				</tbody>
				</table>
				</div>
        	</div>
    	</div>
        <table align="center;">
       	<hr />
        <tr>
        <td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td align="center"><button id="finishButton" class="btn success">Save Portfolio</button></td><td> -- OR -- </td><td><button id="manageAmountsManually" class="btn">Manage Amounts Myself</button></td><td> </td><td> </td><td> </td><td> </td>
        </tr>
        </table>
    </div>
    <footer style="padding-bottom:5%;"><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com' style="color:blue;">contact@myaarth.com</a> | <button id="logout" style="background-color:white; color:blue;">Log Out</button></center></footer>
  </div>
  </body>
  </html>