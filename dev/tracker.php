<?php
date_default_timezone_set('America/New_York');

require_once("lib/presentation.php");
require_once("lib/lib.php");
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
    <title><?php Presentation::outputPageTitle(); ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

	<link rel="stylesheet" href="/Style/bootstrap.min.css"/>
	<link rel="stylesheet" href="/Style/Main.css"/>
	<script type="text/javascript" src="Script/jquery.min.js"></script>
	<script type="text/javascript" src="Script/bootstrap-modal.js"></script>
	<script type="text/javascript" src="Script/bootstrap-twipsy.js"></script>
	<script type="text/javascript" src="Script/bootstrap-popover.js"></script>
	<script type="text/javascript" src="/Script/Tracker.js"></script>
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

	</style>
  </head>

  <body>
    <div id="confirmModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Confirm</h3>
    </div>
    <div class="modal-body">
		<p>
			Are you sure you'd like to save your portfolio?
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="savePortfolio">Save</a>
		<a href="#" class="btn" id="closeConfirm">Cancel</a>
    </div>
    </div>
	
	<div id="confirmTotalOwnershipEmpty" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Total Ownership</h3>
    </div>
    <div class="modal-body">
		<p>
			You have not entered an acceptable value for your total stock ownership. You may proceed, but advanced tracking options will not be available.
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="confirmEmpty">Continue</a>
		<a href="#" class="btn" id="cancelEmpty">Cancel</a>
    </div>
    </div>
	
	<div id="showSaved" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Saved</h3>
    </div>
    <div class="modal-body">
		<p>
			Your portfolio has been saved successfully.
		</p>
	</div>
    <div class="modal-footer">
		<a href="#" class="btn" id="closeSaved">OK</a>
    </div>
    </div>
	
	<div id="portfolioInconsistent" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Portfolio Inconsistent</h3>
    </div>
    <div class="modal-body">
		<p>
			Your portfolio's current type (percentage/amounts) is inconsistent with what you've previously saved.
		</p>
		<p>
			Moving forward with this change will result in previous portfolios being lost.
		</p>
		<p>
			Would you like to proceed?
		</p>
	</div>
    <div class="modal-footer">
		<a href="#" class="btn primary" id="proceedWithInconsistency">Proceed</a>
		<a href="#" class="btn" id="cancelInconsistency">Cancel</a>
    </div>
    </div>

    <div class="container">

      <div class="content">
        <div class="page-header" style="height:100px;">
		<?PHP
		Presentation::outputHeader();
		?>
		<div id="moneyTreeImage" style="position:relative;left:420px;top:-100px;width:200px;z-index:10;">
			<img src="/Style/Images/MoneyTreeSmall.png">
		</div>
        </div>
        <div class="row">
          <div class="span11">
			<input type="search" id="searchTextBox" value="Enter Symbol" data-default-value="Enter Symbol"> <button id="finishButton" class="btn success">Finish</button> <button class="btn primary" id="refreshPage">Refresh Portfolio</button> <button id="resetPortfolioButton" class="btn error" style="z-index:99999">Empty Portfolio</button>
			<div id="dateTrackerDiv" class="span11">
				<table class="span11" id="preferencesTable">
				<thead>
				<tr><th>Date</th><th>Preferred Email Address</th><th>Email Frequency</th></tr>
				</thead>
				<tbody>
					<tr>
					<td><input type="text" class="span3" id="user_date" value="<?php print(date('m/d/Y')); ?>" disabled="disabled"></td>
					<td><input type="text" id="user_email" value="<?php print(isset($_SESSION['User']) ? $_SESSION['User']->user_email : "notlogged") ?>" disabled="disabled"></td>
					<td>
						<label for="daily_input"><input type="radio" name="email_frequency" value="Daily" id="daily_input" <?php $_SESSION['User']->email_frequency == "Daily" || $_SESSION['User']->email_frequency == null ? print("checked='checked'") : print (""); ?>/>Daily</label>
						<label for="weekly_input"><input type="radio" name="email_frequency" value="Weekly" id="weekly_input" <?php $_SESSION['User']->email_frequency == "Weekly" ? print("checked='checked'") : print (""); ?>//>Weekly</label>
						<label for="monthly_input"><input type="radio" name="email_frequency" value="Monthly" id="monthly_input" <?php $_SESSION['User']->email_frequency == "Monthly" ? print("checked='check'") : print (""); ?>//>Monthly</label> 
						<label for="none_input"><input type="radio" name="email_frequency" value="None" id="none_input" <?php $_SESSION['User']->email_frequency == "None" ? print("checked='checked'") : print (""); ?>//>None</label>
					</td>
					</tr>
				</tbody>
				</table>
			</div>
			<div id="tickerTrackerDiv">
				<h3></h3>
				<div id="tickerTrackerTableDiv">
				<?php
				$DB = new Database();
				$Portfolio = $DB->GetMostRecentPortfolio($_SESSION['User']->user_id);
				$LastTotal = $DB->_getLastPortfolioTotal($_SESSION['User']->user_id);
				
				if(count($Portfolio) == 0) {
					print("<center><h4>No existing portfolio found.</h4></center>");
					print("<script>TickerTracker.currentPortfolioType='%';TickerTracker.lastPortfolioType=null;</script>");	
				}else{
					print("<table><thead><tr><th>Ticker</th><th>Company / Mutual Fund Name</th><th>Amount (as of date)</th><th></th></tr></thead><tbody>");
					$Output = "<script type='text/javascript'>TickerTracker.userTrackingSymbols = [";
					$i = 0;
					$TotalVal = 0;
					foreach($Portfolio as $PortfolioItem) {
						print("<tr data-tracker-index='" . $i . "'><td>" . $PortfolioItem->symbol_name . "</td><td>" . $PortfolioItem->symbol_description . "</td><td><div class='input-prepend'><span class='add-on btn primary toggleVal unselectable' style='z-index:0;' data-alt-value='".($PortfolioItem->ownership_type=='$'?'%':'$')."'>".$PortfolioItem->ownership_type."</span><input type='text' class='span2 stockValue' value='".(number_format((float)$PortfolioItem->ownership_value,2,".",""))."'></div></td><td><button class='btn error removeStock'>X</button></td></tr>");
						$Output .= "{symbol_id:".$PortfolioItem->symbol_id.",ownership_value:'".(number_format((float)$PortfolioItem->ownership_value,2,".",""))."',symbol_name:'".$PortfolioItem->symbol_name."',symbol_description:'".$PortfolioItem->symbol_description."', ownership_type:'".$PortfolioItem->ownership_type."'},";
						$i++;
					}

					$Output = substr($Output,0,strlen($Output)-1)."];";
					$Type = isset($Portfolio[0]->ownership_type) ? $Portfolio[0]->ownership_type : "%";
					$Output .= "TickerTracker.currentPortfolioType = '".$Type."';";
					$Output .= "TickerTracker.currentPercentageOwnershipValue = ".$LastTotal.";";
					$Output .= "TickerTracker.totalValue = ".$LastTotal.";";
					$Output .= "TickerTracker.lastPortfolioType = '".$Type."';</script>";
					if($Type == "$") {
						print("<tr><td colspan='2' style='text-align:right;'>Total Value</td><td><div class='input-prepend'><span class='add-on btn primary unselectable disabled' style='z-index:0;'>$</span><input id='totalAmountInput' class='span2' type='text' disabled='disabled' value='".(number_format((float)$LastTotal,2,".",""))."'></div></td></tr>");
					}else{
						print("<tr><td colspan='2' style='text-align:right;'>Total Value</td><td><div class='input-prepend'><span class='add-on btn primary unselectable disabled' style='z-index:0;'>$</span><input id='totalAmountInput' class='span2' type='text' value='".(number_format((float)$LastTotal,2,".",""))."'></div></td></tr>");
					}
					print("</tbody></table>".$Output);
				}
				?>
				</div>
			</div>
          </div>
        </div>
      </div>

	<?php
	Presentation::outputFooter();
	?>

    </div> <!-- /container -->

  </body>
</html>
