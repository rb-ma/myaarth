<?php

class UserManagement {
	public static function CreateSession($EmailAddress) {
		UserManagement::InitSession();
		$DB = new Database();
		$_SESSION['User'] =	$DB->GetUserProfile($EmailAddress);
		
	}
	
	public static function DestroySession() {
		UserManagement::InitSession();
		session_destroy();
	}
	
	public static function InitSession() {
		//if(session_status() != PHP_SESSION_ACTIVE) {
		@session_start();	
		//}
	}
	
	public static function GenerateRecoveryHash() {
		$RecoveryHash = Hash::GenerateHash(time());
		return $RecoveryHash;
	}
	
	public function CreateUser($Email, $Password) {
		$DB = new Database();
		$Hash = Hash::GenerateHash(time());
		$DB->CommitUserToDatabase($Email,$Password,$Hash);
		$EmailObject = new Email();
		$EmailObject->SendWelcomeEmail($Email, $Hash);
	}
	

}

class Database {
	private $Host = "aarthadmin2.db.8935638.hostedresource.com";
	private $Database  = "aarthadmin2";
	private $Username = "aarthadmin2";
	private $Password = "Aarth!Log1n";
	private $MySQLi;
	
	private function Connect() {
		try{
			$this->MySQLi = new mysqli($this->Host, $this->Username, $this->Password, $this->Database);
		}catch(PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
	}
	
	public function GetUserProfile($EmailAddress) {
		$this->Connect();
		$Output = null;
		if($Statement = $this->MySQLi->prepare("SELECT user_id, user_email, user_first_name, user_last_name, validated, email_frequency, password_sha256_hash, welcome_hash FROM users WHERE user_email = ? and active = 1")) {
			$Statement->bind_param("s", $EmailAddress);
			$Statement->execute();
			$Statement->bind_result($user_id, $user_email, $user_first_name, $user_last_name, $validated, $email_frequency, $password_hash, $welcome_hash);

			/* fetch values */
			while ($Statement->fetch()) {
				$Output = (object) array("user_id"=>$user_id,"user_email"=>$user_email,"user_first_name"=>($user_first_name==null?"":$user_first_name),"user_last_name"=>($user_last_name==null?"":$user_last_name),"validated"=>$validated,"email_frequency"=>$email_frequency,"password_hash"=>$password_hash, "welcome_hash"=>$welcome_hash, "validated"=>$validated);
			}
			$Statement->close();
		}
		$this->Disconnect();
		return $Output;
	}
	
	public function ResetPortfolio($UserId) {
		$this->Connect();
		$Output = null;
		$OldPortfolio = $this->GetMostRecentPortfolio($UserId);
		foreach($OldPortfolio as $PortfolioItem) {
			$this->_decrementStockInUse($PortfolioItem->symbol_id);	
		}
		if($Statement = $this->MySQLi->prepare("UPDATE users SET active = 0 WHERE user_id = ?")) {
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			$Statement->close();
		}
		if($Statement = $this->MySQLi->prepare("INSERT INTO users (user_email, password_sha256_hash, welcome_hash, validated, active) VALUES (?,?,?,?, 1)")) {
			$Time = time();
			$Statement->bind_param("sssi", $_SESSION['User']->user_email, $_SESSION['User']->password_hash, $_SESSION['User']->welcome_hash, $_SESSION['User']->validated);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
		}
		$_SESSION['User'] = $this->GetUserProfile($_SESSION['User']->user_email);
		return $Output;
	}
	
	public function IsEmailInUse($Email) {
		$this->Connect();
		$InUse = null;
		if($Statement = $this->MySQLi->prepare("SELECT COUNT(*) Rows FROM users WHERE users.user_email = ?")) {
			$Statement->bind_param("s", $Email);
			$Statement->execute();
			$InUse = null;
			$Statement->bind_result($InUse);
			$Statement->fetch();
			$Statement->close();
		}
		$this->Disconnect();
		return ($InUse>0 ? true : false);
	}
	
	public function GetExistingPasswordHash($Email) {
		$this->Connect();
		$InUse = null;
		if($Statement = $this->MySQLi->prepare("SELECT password_sha256_hash FROM users WHERE users.user_email = ?")) {
			$Statement->bind_param("s", $Email);
			$Statement->execute();
			$Hash = null;
			$Statement->bind_result($Hash);
			$Statement->fetch();
			$Statement->close();
		}
		$this->Disconnect();
		return $Hash;
	}
	
	public function StoreRecoveryHash($EmailAddress) {
		$this->Connect();
		$Rows = null;
		if($Statement = $this->MySQLi->prepare("UPDATE users SET recovery_hash = ? WHERE users.user_email = ?")) {
			$RecoveryHash = UserManagement::GenerateRecoveryHash();
			$Statement->bind_param("ss",  $RecoveryHash, $EmailAddress);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			if($Rows == 1) {
				
			}
			$Statement->close();
		}
		$this->Disconnect();
		return $RecoveryHash;
	}
	
	public function UpdateRecoveryPassword($EmailAddress,$RecoveryHash,$NewPassword) {
		$this->Connect();
		$Rows = null;
		if($Statement = $this->MySQLi->prepare("UPDATE users SET password_sha256_hash = ? WHERE users.user_email = ? AND recovery_hash = ?")) {
			$Statement->bind_param("sss",  $NewPassword, $EmailAddress,$RecoveryHash);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
		}
		$this->Disconnect();
		return ($Rows==1 ? true : false);
	}
	
	public function ValidateEmailRestoreMatch($Email,$Hash) {
		$this->Connect();
		$InUse = null;
		if($Statement = $this->MySQLi->prepare("SELECT COUNT(*) Rows FROM users WHERE users.user_email = ? AND users.recovery_hash = ?")) {
			$Statement->bind_param("ss", $Email, $Hash);
			$Statement->execute();
			$Statement->bind_result($InUse);
			$Statement->fetch();
			$Statement->close();
		}
		$this->Disconnect();
		return ($InUse>0 ? true : false);
	}
	
	public function ConfirmEmailHashMatch($Email,$Hash) {
		$this->Connect();
		$InUse = null;
		if($Statement = $this->MySQLi->prepare("SELECT COUNT(*) Rows FROM users WHERE users.user_email = ? AND users.welcome_hash = ?")) {
			$Statement->bind_param("ss", $Email, $Hash);
			$Statement->execute();
			$Statement->bind_result($InUse);
			$Statement->fetch();
			$Statement->close();
		}
		$this->Disconnect();
		return ($InUse>0 ? true : false);
	}
	
	public function ValidateUserCredentials($Email, $Password) {
		$this->Connect();
		$InUse = null;
		$HashedPassword = Hash::GenerateHash($Password);
		if($Statement = $this->MySQLi->prepare("SELECT COUNT(*) Rows FROM users WHERE users.user_email = ? AND users.password_sha256_hash = ?")) {
			$Statement->bind_param("ss", $Email, $HashedPassword);
			$Statement->execute();
			$InUse = null;
			$Statement->bind_result($InUse);
			$Statement->fetch();
			$Statement->close();
		}
		$this->Disconnect();
		return ($InUse>0 ? true : false);
	}
	

	public function CommitUserToDatabase($Email, $Password, $WelcomeHash) {
		$this->Connect();
		$InUse = null;
		$HashedPassword = Hash::GenerateHash($Password);
		if($Statement = $this->MySQLi->prepare("INSERT INTO users (user_email, password_sha256_hash, welcome_hash) VALUES (?,?,?)")) {
			$Time = time();
			$Statement->bind_param("sss", $Email, $HashedPassword, $WelcomeHash);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			if($Rows == 1) {
				//UserManagement::CreateSession($Email);
			}
			$Statement->close();
		}
		
		$this->Disconnect();
		return ($Rows==1 ? true : false);
	}
	
	public function StockLookup($Search) {
		$this->Connect();
		$Output = array();
		if(false && strlen($Search)>3) {
			if($Statement = $this->MySQLi->prepare("SELECT symbol_description, symbol_name, symbol_id, stock_exchange.exchange_name FROM stock_symbols LEFT JOIN stock_exchange ON stock_exchange.exchange_id = stock_symbols.symbol_exchange WHERE (Match(symbol_name,symbol_description) AGAINST (?)) AND stock_symbols.active != 0 LIMIT 50")) {
				$Statement->bind_param("s", $Search);
				$Statement->execute();
				$Statement->bind_result($symbol_description, $symbol_name, $symbol_id, $exchange_name);

				/* fetch values */
				while ($Statement->fetch()) {
					array_push($Output, (object) array("symbol_description"=>$symbol_description,"symbol_name"=>$symbol_name,"symbol_id"=>$symbol_id,"exchange_name"=>$exchange_name,"type"=>"ft"));
				}
				$Statement->close();
			}
		}else{
			if($Statement = $this->MySQLi->prepare("SELECT DISTINCT symbol_description, symbol_name, symbol_id, stock_exchange.exchange_name FROM aarthadmin2.stock_symbols LEFT JOIN stock_exchange ON stock_exchange.exchange_id = stock_symbols.symbol_exchange WHERE (symbol_name like CONCAT('%',?,'%') OR symbol_description like CONCAT('%',?,'%')) and active !=0 order by symbol_name asc;")) {
				$Statement->bind_param("ss", $Search, $Search);
				$Statement->execute();
				$Statement->bind_result($symbol_description, $symbol_name, $symbol_id, $exchange_name);

				/* fetch values */
				while ($Statement->fetch()) {
					array_push($Output, (object) array("symbol_description"=>$symbol_description,"symbol_name"=>$symbol_name,"symbol_id"=>$symbol_id,"exchange_name"=>$exchange_name,"type"=>"like"));
				}
				$Statement->close();
			}
		}
		$this->Disconnect();
		return $Output;
	}
	
	public function RankStocks($SearchTerm, $Output) {
		// Score rankings
		$StockNameExactMatch = 10;
		$StockNameStartsWith = 4;
		$StockNameEndsWith = 3;
		$StockNameContains = 2;
		
		$StockDescriptionExactMatch = 10;
		$StockDescriptionStartsWith = 7;
		$StockDescriptionEndsWith = 3;
		$StockDescriptionContains = 2;

		foreach($Output as $StockItem) {
			$StockItem->rank = 0;
			// Exact symbol name match
			if(strtolower($StockItem->symbol_name) == $SearchTerm) {
				$StockItem->rank += $StockNameExactMatch;	
			}
			// Symbol name starts with
			if($this->_startsWith(strtolower($StockItem->symbol_name), $SearchTerm)) {
				$StockItem->rank += $StockNameStartsWith;
			}
			// Symbol name ends with
			if($this->_endsWith(strtolower($StockItem->symbol_name), $SearchTerm)) {
				$StockItem->rank += $StockNameEndsWith;	
			}
			// Symbol name contains
			if(strpos($StockItem->symbol_name,$SearchTerm) !== false) {
				$StockItem->rank += $StockNameContains;
			}
				
			// Exact symbol description match
			if(strtolower($StockItem->symbol_description) == $SearchTerm) {
				$StockItem->rank += $StockDescriptionExactMatch;	
			}
			// Symbol name starts with
			if($this->_startsWith(strtolower($StockItem->symbol_description), $SearchTerm)) {
				$StockItem->rank += $StockDescriptionStartsWith;
			}
			// Symbol name ends with
			if($this->_endsWith(strtolower($StockItem->symbol_description), $SearchTerm)) {
				$StockItem->rank += $StockDescriptionEndsWith;	
			}
			// Symbol name contains
			if(strpos($StockItem->symbol_description,$SearchTerm) !== false) {
				$StockItem->rank += $StockDescriptionContains;
			}
		}
		usort($Output, "_cmp");
		$Top4 = array_slice($Output,0,10);
		return $Top4;
	}
	
	private function _startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	private function _endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		$start  = $length * -1; //negative
		return (substr($haystack, $start) === $needle);
	}
	



	
	
	public function GetMostRecentPortfolio($UserId) {
		$this->Connect();
		$Output = array();
		if($Statement = $this->MySQLi->prepare("SELECT stock_id, symbol_name, symbol_description, ownership_value, ownership_type FROM user_stock_symbol_correlation LEFT JOIN stock_symbols ON stock_symbols.symbol_id = user_stock_symbol_correlation.stock_id LEFT JOIN user_portfolios ON user_portfolios.portfolio_id = user_stock_symbol_correlation.portfolio_id WHERE user_portfolios.portfolio_id = (SELECT MAX(portfolio_id) FROM user_portfolios WHERE user_id = ?)")) {
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			$Statement->bind_result($symbol_id, $symbol_name, $symbol_description, $ownership_value, $ownership_type);

			/* fetch values */
			while ($Statement->fetch()) {
				array_push($Output, (object) array("symbol_description"=>$symbol_description,"symbol_name"=>$symbol_name,"symbol_id"=>$symbol_id,"ownership_value"=>$ownership_value,"ownership_type"=>$ownership_type));
			}
			$Statement->close();
		}
		return $Output;
	}
	
	private function _insertPortfolioRow($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType) {
		if($Statement = $this->MySQLi->prepare("INSERT INTO user_stock_symbol_correlation (stock_id, user_id, last_update, ownership_value, portfolio_id, ownership_type) VALUES (?,?,NOW(),?,?,?)")) {
			$Statement->bind_param("iidis", $SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
	}
	
	public function UpdateValidatedFlag($UserId, $EmailAddress, $Validated) {
		$this->Connect();
		if($Statement = $this->MySQLi->prepare("UPDATE `aarthadmin2`.`users` SET `validated`=1 WHERE `user_id`=? ")) {
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
		$this->Disconnect();
	}
	
	public function _getLastPortfolioTotal($UserId) {
		if($Statement = $this->MySQLi->prepare("SELECT current_portfolio_amount FROM users WHERE user_id = ?")) {
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			$Hash = null;
			$Statement->bind_result($CurrentPortfolio);
			$Statement->fetch();
			$Statement->close();
		}
		return $CurrentPortfolio;
	}
	
	
	private function _createNewPortfolio($UserId, $Data) {
		$ID = null;
		$ExistingPortfolio = (count($this->GetMostRecentPortfolio($UserId))>0);
		$this->Connect();
		$_SESSION['User']->email_frequency = $Data->emailFrequency;
		if($Statement = $this->MySQLi->prepare("INSERT INTO user_portfolios (`generated_by_id`, `user_id`, `email_frequency`) VALUES (?, ?, ?);")) {
			$Statement->bind_param("iis", $UserId, $UserId, $Data->emailFrequency);
			$Statement->execute();
			$ID = $this->MySQLi->insert_id;
		}
		if($Statement = $this->MySQLi->prepare("UPDATE users SET no_total_value = ? WHERE user_id = ?")) {
			$NoTotalVal = $Data->noTotalVal==true?1:0;
			$Statement->bind_param("ii", $NoTotalVal, $UserId);
			$Statement->execute();
		}
		if($ExistingPortfolio === false) {
			if($Data->symbols[0]->ownership_type == "$") {
				// User is creating their first amount based portfolio
				$SummedAmount= $Data->totalVal;
				if($Statement = $this->MySQLi->prepare("UPDATE users SET portfolio_type = '$', email_frequency = ?, first_portfolio_amount = ?, startofyear_portfolio_amount = ?, lastchange_portfolio_amount = ?, current_portfolio_amount = ? WHERE user_id = ?")) {
					$Statement->bind_param("sddddi",$Data->emailFrequency,$SummedAmount,$SummedAmount,$SummedAmount,$SummedAmount,$UserId);
					$Statement->execute();
				}
			}else{
				// User is creating their first percentage based portfolio
				$SummedAmount= $Data->totalVal;
				if($Statement = $this->MySQLi->prepare("UPDATE users SET portfolio_type = '%', email_frequency = ?, first_portfolio_amount = ?, startofyear_portfolio_amount = ?, lastchange_portfolio_amount = ?, current_portfolio_amount = ? WHERE user_id = ?")) {
					$Statement->bind_param("sddddi",$Data->emailFrequency,$SummedAmount,$SummedAmount,$SummedAmount,$SummedAmount,$UserId);
					$Statement->execute();
				}
			}
		}else{
			if($Data->symbols[0]->ownership_type == "$") {
				// User is creating their non-first amount based portfolio
				$SummedAmount= $Data->totalVal;
				if($Statement = $this->MySQLi->prepare("UPDATE users SET portfolio_type = '$', email_frequency = ?, lastchange_portfolio_amount = ?, current_portfolio_amount = ? WHERE user_id = ?")) {
					$Statement->bind_param("sddi",$Data->emailFrequency,$SummedAmount,$SummedAmount,$UserId);
					$Statement->execute();
				}
			}else{
				// User is creating their non-first percentage based portfolio
				$SummedAmount= $Data->totalVal;
				if($Statement = $this->MySQLi->prepare("UPDATE users SET portfolio_type = '%', email_frequency = ?, lastchange_portfolio_amount = ?, current_portfolio_amount = ? WHERE user_id = ?")) {
					$Statement->bind_param("sddi",$Data->emailFrequency,$SummedAmount,$SummedAmount,$UserId);
					$Statement->execute();
				}
			}
		}
		return $ID;
	}
	
	public function InsertPortfolioRows($Data, $UserId) {
		$OldPortfolio = $this->GetMostRecentPortfolio($UserId);
		foreach($OldPortfolio as $PortfolioItem) {
			$this->_decrementStockInUse($PortfolioItem->symbol_id);	
		}
		$PortfolioId = $this->_createNewPortfolio($UserId,$Data);
		foreach($Data->symbols as $RowItem) {
			$this->_insertPortfolioRow((int) $RowItem->symbol_id, (int) $UserId, (double) $RowItem->ownership_value, (int) $PortfolioId, $RowItem->ownership_type);
			$this->_incrementStockInUse($RowItem->symbol_id);
		}
		$this->Disconnect();
	}
	
	private function _incrementStockInUse($StockId) {
		if($Statement = $this->MySQLi->prepare("UPDATE stock_symbols SET num_portfolios = num_portfolios+1 WHERE symbol_id = ?")) {
			$Statement->bind_param("i", $StockId);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
	}
	
	private function _decrementStockInUse($StockId) {
		if($Statement = $this->MySQLi->prepare("UPDATE stock_symbols SET num_portfolios = num_portfolios-1 WHERE symbol_id = ?")) {
			$Statement->bind_param("i", $StockId);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
	}
	
	public function _sumStockAmounts($Data) {
		$Sum = (double) 0;
		foreach($Data->symbols as $RowItem) {
			$Sum += (double) $RowItem->ownership_value;
		}
		return $Sum;
	}
	
	private function Disconnect() {
		$this->MySQLi->close();
	}
}

class Hash {
	public static function GenerateHash($IncomingString) {
		return hash('sha256',$IncomingString);
	}
}

class JSONResponse {
	public static function PrepareResponse($Success, $Message, $Data) {
		$StringSuccess = $Success == true ? "Success" : "Failure";
		$EncodedData = json_encode($Data);
		$Output = "{\"response\":{\"result\":\"".$StringSuccess."\", \"message\":\"".$Message."\", \"data\":".$EncodedData."}}";
		return $Output;
	}	
}

class Email {
	public function SendWelcomeEmail($EmailAddress, $WelcomeHash) {
		
		$to = $EmailAddress; 
		$subject = "Welcome to MyAarth";
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'From: MyAarth <noreply@myaarth.com>' . "\r\n";
		
		$body = "<div style='font-family:Verdana;'><h3><img src='http://myaarth.com/Style/Images/AarthLogoHugeTrackWealth.png' alt='MyAarth, Track Your Wealth' title='MyAarth, Track Your Wealth'></h3><br/><h4>Welcome to MyAarth</h4><br/><p>Before you get started, please <a href='http://myaarth.com/authenticate.php?e=".$EmailAddress."&h=".$WelcomeHash."'>click here</a> to validate your account.</p><p>Thanks, we look forward to working with you soon.</p><p>-MyAarth</p></div>";

		mail($to, $subject, $body,$headers);
	
	}
	
	public function SendRecoveryEmail($EmailAddress, $RecoveryHash) {
		
		$to = $EmailAddress; 
		$subject = "Password Reset - MyAarth";
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'From: MyAarth <noreply@myaarth.com>' . "\r\n";
		
		$body = "<div style='font-family:Verdana;'><h3><img src='http://myaarth.com/Style/Images/AarthLogoHugeTrackWealth.png' alt='MyAarth, Track Your Wealth' title='MyAarth, Track Your Wealth'></h3><br/><h4>Password Reset</h4><br/><p>You've recently notified us that you'd forgotten your password.</p><p>To reset your password, please <a href='http://myaarth.com/restore.php?e=".$EmailAddress."&h=".$RecoveryHash."'>click here</a> and follow the steps to change your password.</p><p>.</p><p>-MyAarth</p></div>";

		mail($to, $subject, $body,$headers);
		
	}
}

function _cmp($a, $b)
{
	if ($a->rank == $b->rank) {
		return 0;
	}
	if($a->rank < $b->rank) {
		return 1;
	}else{
		return -1;	
	}
}


?>