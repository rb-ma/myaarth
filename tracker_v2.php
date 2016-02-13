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
	<script type="text/javascript" src="/Script/Tracker_v2.js"></script>
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
		
		
	</style>
    <?php
	UserManagement::SetInactiveLogoutTime(1000);
	?>
  </head>

  <body>
  	<div id="stockInfoModal" class="modal hide">
    <div class="modal-header">
    	<a href="#" class="close">&times;</a>
        <h3>Stock/Fund Information</h3>
    </div>
    <div class="modal-body">
        <p id="stockInfoDiv">
        </p>
    </div>
    <div class="modal-footer">
    	<a href="#" class="btn primary" id="closeStockInfo">OK</a>
    </div>
    </div>
    
  	<div id="portfolioInfoModal" class="modal hide">
    <div class="modal-header">
    	<a href="#" class="close">&times;</a>
        <h3>Fund Information</h3>
    </div>
    <div class="modal-body">
        <p id="portfolioInfoDiv">
        </p>
    </div>
    <div class="modal-footer">
    	<a href="#" class="btn primary" id="closePortfolioInfo">OK</a>
    </div>
    </div>
  
  	<div id="portfolioErrorModal" class="modal hide">
    <div class="modal-header">
    	<a href="#" class="close">&times;</a>
        <h3>Portfolio Error</h3>
    </div>
    <div class="modal-body">
    	<p>
        There is some issue with your portfolio.
        </p>
        <p>
        Please ensure that you have at least one stock or fund and have entered only positive, numeric values.
        </p>
    </div>
    <div class="modal-footer">
    	<a href="#" class="btn primary" id="closePortolioError">Go Back</a>
    </div>
    </div>
	
    <div id="emptyModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Empty Portfolio?</h3>
    </div>
    <div class="modal-body">
        <p>Emptying your portfolio will delete all prior portfolio history</p>
        <p>Are you sure you'd like to empty your portfolio?</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="confirmResetPortfolioButton">Empty</a>
		<a href="#" class="btn" id="cancelResetPortfolioButton">Cancel</a>
    </div>
    </div>
    
    <div id="refreshModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Get Last Portfolio?</h3>
    </div>
    <div class="modal-body">
        <p>Are you sure you'd like to get your last saved portfolio?</p>
        <p>You will lose any changes you have made prior to saving.</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="confirmRefreshPortfolioButton">Refresh</a>
		<a href="#" class="btn" id="cancelRefreshPortfolioButton">Cancel</a>
    </div>
    </div>
    
    <div id="trackModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Portfolio Performance</h3>
    </div>
    <div class="modal-body">
		<p>
        TODO: put a graph here
        </p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="exitTrackModal">Exit</a>
    </div>
    </div>
    
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
	
    <div id="confirmLogout" class="modal hide">
    <div class="modal-header">
    	<a href="#" class="close">&times;</a>
        <h3>Are you sure you want to Log Out?</h3>
    </div>
    <div class="modal-body">
    <p>You are about to log out.</p>
    <p>Are you sure you've saved all your changes?</p>
    </div>
    <div class="modal-footer">
		<a href="/User/logout.php" class="btn primary" id="confirmLogout">Log Out</a>
		<a href="#" class="btn" id="cancelLogout">Cancel</a>
    </div>
    </div>
    
    <div class="container">

      <div class="content">
        <div class="page-header" style="height:100px;">
			<?PHP
            Presentation::outputHeader();
            ?>
            <div id="moneyTreeImage" style="position:relative;left:520px;top:-100px;width:200px;z-index:10;">
                <img src="/Style/Images/MoneyTreeSmall.png">
            </div>
        </div>
        <div class="row">
          <div class="span11"><input type="search" id="searchTextBox" value="Enter Symbol" data-default-value="Enter Symbol"> <button id="finishButton" class="btn success">Save Portfolio</button> <button class="btn primary" id="refreshPage">Get Last Portfolio</button> <button id="resetPortfolioButton" class="btn error">Start New Portfolio</button> <button id="groupButton" class="btn">Toggle Portfolio View</button>
            
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
			<div id="tickerTrackerDiv">
				<h3></h3>
				<div id="tickerTrackerTableDiv">
				<?php
				$DB = new Database();
				//$Portfolio = $DB->GetMostRecentPortfolio_v2($_SESSION['User']->user_id);
				$Portfolio = $DB->GetMostRecentPortfolio_v3($_SESSION['User']->user_id);
				$LastTotal = $DB->_getLastPortfolioTotal($_SESSION['User']->user_id);
				
				if(count($Portfolio) == 0) {
					print("<center><h4>No existing portfolio found.</h4></center>");
					print("<script>TickerTracker.currentPortfolioType='$';TickerTracker.lastPortfolioType='$';</script>");	
				}else{
					//print("<p>". count($Portfolio,0) ."</p>");
					print("<table><thead><tr><th>Ticker</th><th>Description</th><th>Amount</th><th>Portfolio Type</th></tr></thead><tbody>");
					$Output = "<script type='text/javascript'>TickerTracker.userTrackingSymbols = [";
					$i = 0;
					$TotalVal = 0;
					$port_type_arr = array(	0=>'',
											1=>'Unknown', 
											2=>'401(k)', 
											3=>'Traditional IRA', 
											4=>'Roth IRA', 
											5=>'SIMPLE IRA', 
											6=>'SEP-IRA', 
											7=>'Solo 401(k)', 
											8=>'Roth 401(k)', 
											9=>'403(b)', 
											10=>'Other');
									
					foreach($Portfolio as $PortfolioItem) {
						print("<p>". $PortfolioItem->portfolio_id ."</p>");
						$prep = "<r2>"; 
						$app = "<r2>";
						// resize font if necessary
						if(strlen($PortfolioItem->symbol_description) > 35){
							$prep = "<r>"; 
							$app = "</r>";
						}
						
						$port_type = array_search($PortfolioItem->portfolio_type, $port_type_arr);
						
						print("<tr data-tracker-index='" . $i . "'><td><r2>".$PortfolioItem->symbol_name."</r2></td><td>".$prep.$PortfolioItem->symbol_description.$app."</td><td><div class='input-prepend'><span class='add-on primary toggleVal unselectable' style='z-index:0;'>$</span><input type='text' class='span2 stockValue' style='min-width:100px;max-width:100px;' value='".(number_format((float)$PortfolioItem->ownership_value,2,".",""))."'></div></td><td><select style='min-width:120px; max-width:120px; font-size:13px;' class='stockType'>");
						
						for($j = 0; $j < 10; $j++){
							if($j == $port_type){
								print("<option selected>");	
							}
							else {
								print("<option>");	
							}
							if($j != 0){
								print($port_type_arr[$j]);	
							}
							print("</option>");
						}
						
						
						print("</select></td><td><button class='btn primary stockInfo' style='font-size:11px;height:25px; width:25px; padding:0;'>?</button> <button class='btn error removeStock' style='font-size:11px;height:25px; width:25px; padding:0;'>X</button></td></tr>");
						
						$last_change = number_format((float)100.0*(exp($PortfolioItem->log_last_change)-1.0), 2, ".", "");
						$sofyr_return = number_format((float)100.0*(exp($PortfolioItem->log_sofyr_return)-1.0), 2, ".", "");
						$first_return = number_format((float)100.0*(exp($PortfolioItem->log_first_return)-1.0), 2, ".", "");
						
						$Output .= "{symbol_id:".$PortfolioItem->symbol_id.",ownership_value:'".(number_format((float)$PortfolioItem->ownership_value,2,".",""))."',symbol_name:'".$PortfolioItem->symbol_name."',symbol_description:'".$PortfolioItem->symbol_description."', ownership_type:'".$PortfolioItem->ownership_type."',portfolio_type:'".$PortfolioItem->portfolio_type."',last_change:'".$last_change."',sofyr_return:'".$sofyr_return."',first_return:'".$first_return."'},";
						
						$i++;
					}
					
					//print("<p>$Output</p>");

					$Output = substr($Output,0,strlen($Output)-1)."];";
					//$Type = isset($Portfolio[0]->ownership_type) ? $Portfolio[0]->ownership_type : "%";
					$Output .= "TickerTracker.currentPortfolioType = '$';";
					$Output .= "TickerTracker.totalValue = ".$LastTotal.";";
					$Output .= "TickerTracker.lastPortfolioType = '$';</script>";
					print("<tr><td colspan='2' style='text-align:right;'><r5>Total </r5></td><td><div class='input-prepend'><span class='add-on btn primary unselectable disabled' style='z-index:0;'>$</span><input id='totalAmountInput' style='min-width:120px; max-width:120px;' class='span2' type='text' disabled='disabled' value='".(number_format((float)$LastTotal,2,".",""))."'></td><td></td><td></td></div></td></tr>");
					
					print("</tbody></table>".$Output);
				}
				
				?>
				</div>
			</div>
          </div>
        </div>
      </div>
	
	<footer style="padding-bottom:5%;"><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com' style="color:blue;">contact@myaarth.com</a> | <button id="logout" style="background-color:white; color:blue;">Log Out</button></center></footer>

    </div> <!-- /container -->

  </body>
</html>