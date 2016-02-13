<?php
include("lib/lib.php");
if(!isset($_GET['a'])) {
	die();
}
switch(strtolower($_GET['a'])) {
	case 'emailinuse':
		// USES REQUEST VALUE OF E TO FIND THE EMAIL ADDRESS
		$EmailAddress = $_REQUEST['e'];
		$DB = new Database();
		$InUse = $DB->IsEmailInUse($EmailAddress);
		$IsInUse = ($InUse >0) ? true :false;
		print(JSONResponse::PrepareResponse($IsInUse, "", $InUse));
	break;
	case 'commitnewuser':
		if(isset($_REQUEST['e'])) {
			$EmailAddress = $_REQUEST['e'];
		}else{
			return;	
		}
		if(isset($_REQUEST['p'])) {
			$Password = $_REQUEST['p'];
		}else{
			return;
		}
		if(isset($_REQUEST['s'])) {
			$CreateSession = $_REQUEST['s'] == "true" ? true : false;
		}else{
			return;	
		}
		
		$UM = new UserManagement();
		$UM->CreateUser($EmailAddress,$Password);
		
		print(JSONResponse::PrepareResponse(true, "Creation status enclosed", $Created));
	break;
	case 'validateusercredentials':
		if(isset($_REQUEST['e'])) {
			$EmailAddress = $_REQUEST['e'];
		}else{
			return;	
		}
		if(isset($_REQUEST['p'])) {
			$Password = $_REQUEST['p'];
		}else{
			return;
		}
		if(isset($_REQUEST['s'])) {
			$CreateSession = $_REQUEST['s'] == "true" ? true : false;
		}else{
			return;	
		}
		$DB = new Database();
		$InUse = $DB->ValidateUserCredentials($EmailAddress, $Password);
		
		$ValidCredentials = ($InUse >0) ? true :false;
		if($ValidCredentials && $CreateSession) {
			$Profile = $DB->GetUserProfile($EmailAddress);
			if($Profile->validated != 1) {
				print(JSONResponse::PrepareResponse(false, "Authentication status enclosed", "Email not confirmed"));
				die();
			}else{
				UserManagement::CreateSession($EmailAddress);	
			}
		}
		print(JSONResponse::PrepareResponse($ValidCredentials, "Authentication status enclosed", $InUse));
	break;
	
	case 'stocklookup':
		if(isset($_REQUEST['s'])) {
			$Search = $_REQUEST['s'];
		}else{
			return;	
		}
		$DB = new Database();
		$Results = $DB->StockLookup($Search);
		print(JSONResponse::PrepareResponse(count($Results)>0, "Lookup enclosed", $Results));
	break;
	
	case 'complexstocksearch':
		if(isset($_REQUEST['s'])) {
			$Search = $_REQUEST['s'];
		}else{
			return;	
		}
		$DB = new Database();
		$Results = $DB->StockLookup($Search);
		$RankedResults = $DB->RankStocks($Search,$Results);
		print(JSONResponse::PrepareResponse(count($RankedResults)>0, "Lookup enclosed", $RankedResults));
	break;
	
	case 'insertnewstocks':
		if(isset($_POST['d'])) {
			$RawData = $_POST['d'];
			$RawData = str_replace("\\","",$RawData);
			$Data = json_decode($RawData);
			$DB = new Database();
			UserManagement::InitSession();
			$DB->InsertPortfolioRows($Data,$_SESSION['User']->user_id);
		}else{
			return;	
		}
	break;
	
	case 'getmostrecentportfolio':
		$DB = new Database();
		$Results = $DB->GetMostRecentPortfolio($_SESSION['user']->user_id);
		print(JSONResponse::PrepareResponse(count($Results)>0, "Portfolio enclosed", $Results));
	break;
	
	case 'destroysession':
		UserManagement::DestroySession();
		print(JSONResponse::PrepareResponse(true, "Destroyed", null));
	break;
	
	case 'requestpasswordreset':
		if(isset($_REQUEST['e'])) {
			$EmailAddress = $_REQUEST['e'];
		}else{
			return;	
		}
		$DB = new Database();
		$RecoveryHash = $DB->StoreRecoveryHash($EmailAddress);
		$EmailObject = new Email();
		$EmailObject->SendRecoveryEmail($EmailAddress,$RecoveryHash);
		print(JSONResponse::PrepareResponse(true, "Sent", null));
	break;
	
	case 'updatepassword':
		if(isset($_REQUEST['e']) && isset($_REQUEST['p']) && isset($_REQUEST['h'])) {
			$EmailAddress = $_REQUEST['e'];
			$NewPassword = $_REQUEST['p'];
			$Hash = $_REQUEST['h'];
		}else{
			return;	
		}
		$NewHash = Hash::GenerateHash($NewPassword);
		$DB = new Database();
		$DB->UpdateRecoveryPassword($EmailAddress,$Hash,$NewHash);
	break;
	
	case 'resetportfolio':
		$DB = new Database();
		UserManagement::InitSession();
		$DB->ResetPortfolio($_SESSION['User']->user_id);
		print(JSONResponse::PrepareResponse(true, "Sent", null));
	break;
	
	case 'testmessage':
		$EmailObject = new Email();
		$EmailObject->SendWelcomeEmail("timothyferrell@gmail.com", "test");
	break;
}

?>