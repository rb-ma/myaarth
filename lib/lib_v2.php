<?php
include 'ChromePhp.php';
set_time_limit(0);
ini_set('memory_limit', '256M');
class UserManagement {
	public static function CreateSession($EmailAddress) {
		UserManagement::InitSession();
		$DB = new Database();
		$_SESSION['User'] =	$DB->GetUserProfile($EmailAddress);
		$_SESSION['loginTime'] = time();	
	}
	
	public static function SetInactiveLogoutTime($mins){
		$inactive = $mins * 60;
		if(isset($_SESSION['loginTime'])){
			if((time() - $_SESSION['loginTime']) > $inactive){
				UserManagement::DestroySession();
			}
		}	
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
	
	// deprecated
	public function GetMostRecentPortfolio($UserId) {
		//print("In Get Most Recent Portfolio");
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
	
	// deprecated
	public function GetMostRecentPortfolio_v2($UserId){
		$this->Connect();
		$Output = array();
		if($Statement = $this->MySQLi->prepare("SELECT stock_id, symbol_name, symbol_description, ownership_value, ownership_type, portfolio_type FROM user_stock_symbol_correlation LEFT JOIN stock_symbols ON stock_symbols.symbol_id = user_stock_symbol_correlation.stock_id LEFT JOIN user_portfolios ON user_portfolios.portfolio_id = user_stock_symbol_correlation.portfolio_id WHERE user_portfolios.portfolio_id = (SELECT MAX(portfolio_id) FROM user_portfolios WHERE user_id = ?)")) 
		{
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			
			$Statement->bind_result($symbol_id, $symbol_name, $symbol_description, $ownership_value, $ownership_type, $portfolio_type);
			//echo ($symbol_name . " " . $portfolio_type);
			/* fetch values */
			while ($Statement->fetch()) {
				array_push($Output, (object) array("symbol_description"=>$symbol_description,"symbol_name"=>$symbol_name,"symbol_id"=>$symbol_id,"ownership_value"=>$ownership_value,"ownership_type"=>$ownership_type,"portfolio_type"=>$portfolio_type));
			}
			$Statement->close();
		}
		return $Output;
	}
	
	public function trackTotal_getAllIndividualData($UserId){
		
		$this->Connect();
		$this->MySQLi->options(MYSQLI_OPT_CONNECT_TIMEOUT, 500);
		$Output = array();
		$search = "SELECT `portfolio_id`, `symbol_name`, `ownership_value`, `portfolio_type`, `log_last_change`, `log_sofyr_return`, `log_first_return`, `last_update` FROM `user_stock_symbol_correlation` LEFT JOIN `stock_symbols` ON `stock_symbols`.`symbol_id` = `user_stock_symbol_correlation`.`stock_id` WHERE `user_id`=? ORDER BY `portfolio_id` ASC, `ownership_value` DESC";
		if($Statement = $this->MySQLi->prepare($search)){
			
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			
			$Statement->store_result();
			$Statement->bind_result($portfolio_id, $symbol_name, $ownership_value, $portfolio_type, $log_last_change, $log_sofyr_return, $log_first_return, $last_update);
			$count = 0;
			
			while($Statement->fetch() /*&& $count++ < 25*/){
				$date = date_parse($last_update);
				$year = $date['year'];
				$month = $date['month'];
				$day = $date['day'];
				
				if($log_last_change != NULL){
					$last_change = exp($log_last_change)-1.0;
				} else {
					$last_change = 0.0;
				}
				
				if($log_sofyr_return != NULL){
					$sofyr_return = exp($log_sofyr_return)-1.0;
				} else {
					$sofyr_return = 0.0;
				}
				
				if($log_first_return != NULL){
					$first_return = exp($log_first_return)-1.0;
				} else {
					$first_return = 0.0;
				}

				array_push($Output, (object) array(	"month"			=>	$month,
													"day"			=>	$day,
													"year"			=>	$year,
													"portfolio_id" 	=> 	$portfolio_id,
													"symbol_name"	=>	$symbol_name,
													"amount"		=>	$ownership_value,
													"portfolio_type"=>	$portfolio_type,
													"last_change"	=>	$last_change,
													"sofyr_return"	=>	$sofyr_return,
													"first_return"	=>	$first_return
													));
				
			}
			$Statement->close();
		}
		$this->Disconnect();
		return $Output;
	}
	
	public function trackPortfolio_getDataNumRows($UserId, $PortfolioId){
		//ChromePhp::log("in trackPortfolio_getDataNumRows -- UserId: " . $UserId . " PortfolioId: " . $PortfolioId);
		$this->Connect();
		$Output = 0;
		$string = "SELECT * FROM `graph_source_2` WHERE user_id=? AND portfolio_type=?";
		if(($Statement = $this->MySQLi->prepare($string))){
			//ChromePhp::log($string);
			$Statement->bind_param("ii", $UserId, $PortfolioId);
			$Statement->execute();
			$Output = $Statement->num_rows();
			//ChromePhp::log($Statement->num_rows());
		}
		
		$this->Disconnect();
		//ChromePhp::log($Output);
		return $Output;	
	}
	
	public function trackIndividual_getDataNumRows($UserId, $StockId){
		$this->Connect();
		$Output = 0;
		
		if(($Statement = $this->MySQLi->prepare("SELECT ownership_value, portfolio_type, last_update, log_last_change, log_sofyr_return, log_first_return FROM user_stock_symbol_correlation WHERE user_id=? AND stock_id=? ORDER BY correlation_id DESC LIMIT 250"))){
			$Statement->bind_param("ii", $UserId, $StockId);
			$Statement->execute();
			$Statement->store_result();
			$Output = $Statement->num_rows();
		}
		
		$this->Disconnect();
		return $Output;	
	}

	public function trackPortfolio_getData($UserId, $PortfolioId){
		$this->Connect();
		$Output = array();
		if(($Statement = $this->MySQLi->prepare("SELECT DISTINCT DATE(last_update), ownership_value, last_change, sofyr_return, first_return FROM graph_source_2 WHERE user_id=? AND portfolio_type=? ORDER BY id DESC LIMIT 250"))){
			$Statement->bind_param("ii", $UserId, $PortfolioId);
			$Statement->execute();
			$Statement->store_result();
			$Statement->bind_result($last_update, $amount, $last_change, $sofyr_return, $first_return);
			$count = 0;
			while($Statement->fetch()){
				$date = date_parse($last_update);
				$year = $date['year']; $month = $date['month']; $day = $date['day'];
				array_unshift($Output, (object) array("year"=>$year, "month"=>$month, "day"=>$day, "amount"=>$amount, "last_change"=>$last_change, "sofyr_return"=>$sofyr_return, "first_return"=>$first_return));
			} 
		}
		
		$this->Disconnect();
		return $Output;	
	}

	
	public function trackIndividual_getData($UserId, $StockId){
		$this->Connect();
		$Output = array();
		
		if(($Statement = $this->MySQLi->prepare("SELECT DISTINCT DATE(last_update), ownership_value, portfolio_type,log_last_change, log_sofyr_return, log_first_return FROM user_stock_symbol_correlation WHERE user_id=? AND stock_id=? ORDER BY correlation_id DESC LIMIT 250"))){
			$Statement->bind_param("ii", $UserId, $StockId);
			$Statement->execute();
			$Statement->store_result();
			$Statement->bind_result($last_update, $amount, $portfolio_type, $log_last_change, $log_sofyr_return, $log_first_return);
			$count = 0;
			while($Statement->fetch()/* && $count++ <= 10*/){
				////ChromePhp::log("$amount $portfolio_type $last_update $log_last_change $log_sofyr_return $log_first_return");
				$date = date_parse($last_update);
				$year = $date['year']; $month = $date['month']; $day = $date['day'];
				$last_change = ($log_last_change)?round((exp($log_last_change)-1.0)*100.0,2):0.0;
				$sofyr_return = ($log_sofyr_return)?round((exp($log_sofyr_return)-1.0)*100.0,2):0.0;
				$first_return = ($log_first_return)?round((exp($log_first_return)-1.0)*100.0,2):0.0;
				array_unshift($Output, (object) array("year"=>$year, "month"=>$month, "day"=>$day, "amount"=>$amount, "portfolio_type"=>$portfolio_type, "last_change"=>$last_change, "sofyr_return"=>$sofyr_return, "first_return"=>$first_return));
			}
		}
		
		$this->Disconnect();
		return $Output;	
	}
	
	public function trackTotal_getData($UserId){
		////ChromePhp::log("in trackTotal_getData");
		$this->Connect();
		$Output = array();
		if($Statement = $this->MySQLi->prepare("SELECT DISTINCT DATE(timestamp), `portfolio_id`, `amount`, `last_change`, `sofyr_return`, `first_return` FROM graph_source WHERE user_id=? ORDER BY `index` DESC LIMIT 200")){
			$Statement->bind_param("i", $UserId);
			$Statement->execute();	
			$Statement->bind_result($timestamp, $portfolio_id, $amount, $last_change, $sofyr_return, $first_return);
			while($Statement->fetch()){
				$date = date_parse($timestamp);
				$year = $date['year'];
				$month = $date['month'];
				$day = $date['day'];

				array_unshift($Output, (object) array(	"month"			=>	$month,
													"day"			=>	$day,
													"year"			=>	$year,
													"amount"		=>	$amount, 
													"last_change"	=>	$last_change,
													"sofyr_return" => $sofyr_return,
													"first_return" => $first_return
													/*,"stock_row"		=> 	$stock_row*/
													));	
			}
			$Statement->close();
		}
		$this->Disconnect();
		return $Output;
	}
	
	public function GetUserSettings($UserId){
		$this->Connect();
		$Output = array();
		
		if($Statement = $this->MySQLi->prepare("SELECT paycheck_amount, paycheck_frequency, symbol_name, symbol_percent, portfolio_type FROM user_settings WHERE user_id=? AND group_id=(SELECT MAX(group_id) FROM user_settings WHERE user_id=?)")){
			$Statement->bind_param("ii", $UserId, $UserId);
			$Statement->execute();
			$Statement->store_result();
			$Statement->bind_result($paycheck_amount, $paycheck_frequency, $symbol_name, $symbol_percent, $portfolio_type);
			while($Statement->fetch()){
				array_push($Output, (object) array("paycheck_amount"=>$paycheck_amount, "paycheck_frequency"=>$paycheck_frequency, "symbol_name"=>$symbol_name, "symbol_percent"=>$symbol_percent, "portfolio_type"=>$portfolio_type));
			}
		}
		
		$this->Disconnect();
		return $Output;	
	}
	
	
	public function GetMostRecentPortfolio_v3($UserId){
		////ChromePhp::log("logging is working?");
		$this->Connect();
		$Output = array();
		
		$Statement = $this->MySQLi->prepare("SELECT stock_id, symbol_name, symbol_description, ownership_value, ownership_type, portfolio_type, log_last_change,log_sofyr_return,log_first_return FROM user_stock_symbol_correlation LEFT JOIN stock_symbols ON stock_symbols.symbol_id = user_stock_symbol_correlation.stock_id LEFT JOIN user_portfolios ON user_portfolios.portfolio_id = user_stock_symbol_correlation.portfolio_id WHERE user_portfolios.portfolio_id = (SELECT MAX(portfolio_id) FROM user_portfolios WHERE user_id = ?) ORDER BY portfolio_type");
		if($Statement){
			//print("<p>Valid Statement</p>");	
		} else {
			//print("<p>Invalid Statement</p>");
		}
		$Statement->bind_param("i",$UserId);
		$Statement->execute();
			
		$Statement->bind_result($symbol_id, $symbol_name, $symbol_description, $ownership_value, $ownership_type, $portfolio_type, $log_last_change, $log_sofyr_return, $log_first_return);
		/* fetch values */
		//$num_rows = 0;
		while ($Statement->fetch()) {
			//$num_rows++;
			array_push($Output, (object) array(	"symbol_description"=>$symbol_description,
												"symbol_name"=>$symbol_name,
												"symbol_id"=>$symbol_id,
												"ownership_value"=>$ownership_value,
												"ownership_type"=>"$",
												"portfolio_type"=>$portfolio_type,
												"log_last_change"=>$log_last_change,
												"log_sofyr_return"=>$log_sofyr_return,
												"log_first_return"=>$log_first_return));
		}
		$Statement->close();

		return $Output;
	}
	
	// deprecated
	private function _insertPortfolioRow($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType) {
		$SymbolId = -1;
		if($Statement = $this->MySQLi->prepare("INSERT INTO user_stock_symbol_correlation (stock_id, user_id, last_update, ownership_value, portfolio_id, ownership_type) VALUES (?,?,NOW(),?,?,?)")) {
			$Statement->bind_param("iidis", $SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
	}
	
	private function _insertPortfolioRow_v2($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type) {
		if($Statement = $this->MySQLi->prepare("INSERT INTO user_stock_symbol_correlation (stock_id, user_id, last_update, ownership_value, portfolio_id, ownership_type, portfolio_type) VALUES (?,?,NOW(),?,?,?,?)")) {
			$Statement->bind_param("iidiss", $SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type);
			$Statement->execute();
			$Rows = $this->MySQLi->affected_rows;
			$Statement->close();
			return ($Rows==1 ? true : false);
		}
	}
	
	private function _insertPortfolioRow_v3($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type){
		if($Statement = $this->MySQLi->prepare("SELECT log_last_change, log_sofyr_return, log_first_return FROM user_stock_symbol_correlation WHERE portfolio_id<>? AND stock_id=? ORDER BY portfolio_id DESC LIMIT 1")){
			//////ChromePhp::log("First search is a valid statement");
			$Statement->bind_param("ii", $PortfolioId, $SymbolId);
			$Statement->execute();
			$Statement->bind_result($log_last_change, $log_sofyr_return, $log_first_return);
			if($Statement->fetch()){
				////ChromePhp::log("$SymbolId -- $PortfolioId: $log_last_change, $log_sofyr_return, $log_first_return");
				$Statement->close();
				if($Statement = $this->MySQLi->prepare("INSERT INTO user_stock_symbol_correlation (stock_id, user_id, last_update, ownership_value, portfolio_id, ownership_type, portfolio_type, log_last_change, log_sofyr_return, log_first_return) VALUES (?,?,NOW(),?,?,?,?,?,?,?)")){
					$Statement->bind_param("iidissddd", $SymbolId, $UserId, $Value, $PortfolioId, $OwnershipType, $portfolio_type, $log_last_change, $log_sofyr_return, $log_first_return);
					$Statement->execute();
					$Rows = $this->MySQLi->affected_rows;
					$Statement->close();
					if($Rows == 1){
						////ChromePhp::log("Success");	
					} else {
						////ChromePhp::log("Failed");	
					}
					return ($Rows==1 ? true : false);
				} else {
					////ChromePhp::log("Invalid insert statement");	
				}
			} else {
				////ChromePhp::log("No results");	
			}
		} else {
			////ChromePhp::log("invalid statement: SELECT portfolio_id, log_last_change, log_sofyr_return, log_first_return FROM user_stock_symbol_correlation WHERE portfolio_id<>? AND stock_id=? ORDER BY portfolio_id DESC LIMIT 1");
		}
		return false;
	}
	
	private function _insertPortfolioRow_v4($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type){
		////ChromePhp::log("Hello from _insertPortfolioRow_v3($SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type)");
		if($Statement = $this->MySQLi->prepare("SELECT portfolio_id FROM user_portfolios WHERE user_id=? ORDER BY portfolio_id DESC LIMIT 2")){
			$Statement->bind_param("i", $UserId);
			$Statement->execute();
			$Statement->bind_result($old_id);

			$num_rows = 0;
			while($Statement->fetch())
			{
				$num_rows++;
				////ChromePhp::log("Row Number: $num_rows -- Old id: $old_id");
				if($num_rows == 2){
					////ChromePhp::log("Second row");
					if($Statement = $this->MySQLi->prepare("SELECT log_last_change, log_sofyr_return, log_first_return FROM user_stock_symbol_correlation WHERE portfolio_id=? AND stock_id=?"))
					{
						////ChromePhp::log("Final Old id is $old_id");
						if($Statement->bind_param("ii", $old_id, $SymbolId)){
							////ChromePhp::log("Bind param worked");	
						}
						
						$Statement->execute();
						
						if($Statement->bind_result($log_last_change, $log_sofyr_return, $log_first_return)){
							////ChromePhp::log("Bind result worked");	
						}
					
						$num_rows = 0;
					
						while($Statement->fetch())
						{
							////ChromePhp::log("Results found: ($SymbolId) $log_last_change  $log_sofyr_return  $log_first_return");
							$num_rows++;
							if($Statement = $this->MySQLi->prepare("INSERT INTO user_stock_symbol_correlation (stock_id, user_id, last_update, ownership_value, portfolio_id, ownership_type, portfolio_type, log_last_change, log_sofyr_return, log_first_return) VALUES (?,?,NOW(),?,?,?,?,?,?,?)")) 
							{
								$Statement->bind_param("iidissddd", $SymbolId, $UserId, $Value, $PortfolioId,$OwnershipType,$portfolio_type,$log_last_change,$log_sofyr_return,$log_first_return);
								$Statement->execute();
								$Rows = $this->MySQLi->affected_rows;
								$Statement->close();
								return ($Rows==1 ? true : false);
							} else {
								////ChromePhp::log("Invalid insert statement");
							}
							break;
						}
					} else {
						////ChromePhp::log("Invalid percent search statement");	
					}
				} else {
					////ChromePhp::log("This should only print once");	
				}
			}
			if($num_rows <= 1){
				////ChromePhp::log("Only found one portfolio for this user");
				return false;	
			}
		} else {
			////ChromePhp::log("Invalid first search statement");
			return false;	
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
		if($Statement = $this->MySQLi->prepare("INSERT INTO user_portfolios (`generated_by_id`, `user_id`, `email_frequency`,last_update) VALUES (?, ?, ?, FROM_UNIXTIME(?));")) {
			$now = time();
			$Statement->bind_param("iiss", $UserId, $UserId, $Data->emailFrequency, $now);
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
	
	// deprecated
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
	
	public function InsertPortfolioRows_v2($Data, $UserId) {
		////ChromePhp::log("Hello from InsertPortfolioRows_v2");
		$OldPortfolio = $this->GetMostRecentPortfolio_v2($UserId);
		foreach($OldPortfolio as $PortfolioItem) {
			$this->_decrementStockInUse($PortfolioItem->symbol_id);	
		}
		$PortfolioId = $this->_createNewPortfolio($UserId,$Data);
		foreach($Data->symbols as $RowItem) {
			//$this->_insertPortfolioRow_v2((int) $RowItem->symbol_id, (int) $UserId, (double) $RowItem->ownership_value, (int) $PortfolioId, $RowItem->ownership_type, (string) $RowItem->portfolio_type);
			if( ($this->_insertPortfolioRow_v3((int) $RowItem->symbol_id, (int) $UserId, (double) $RowItem->ownership_value, (int) $PortfolioId, $RowItem->ownership_type, (string) $RowItem->portfolio_type)) == false){
				////ChromePhp::log("v3 failed -- have to resort to v2");
				$this->_insertPortfolioRow_v2((int) $RowItem->symbol_id, (int) $UserId, (double) $RowItem->ownership_value, (int) $PortfolioId, $RowItem->ownership_type, (string) $RowItem->portfolio_type);
			}
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
		$headers .= "X-Mailer: PHP v".phpversion()."\r\n";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'From: MyAarth <noreply@myaarth.com>' . "\r\n";
		
		$body = "<div style='font-family:Verdana;'><h3><img src='https://myaarth.com/Style/Images/AarthLogoHugeTrackWealth.png' alt='MyAarth, Track Your Wealth' title='MyAarth, Track Your Wealth'></h3><br/><h4>Welcome to MyAarth</h4><br/><p>Before you get started, please <a href='https://myaarth.com/authenticate.php?e=".$EmailAddress."&h=".$WelcomeHash."'>click here</a> to validate your account.</p><p>Thanks, we look forward to working with you soon.</p><p>-MyAarth</p></div>";

		mail($to, $subject, $body,$headers);
	
	}
	
	public function SendRecoveryEmail($EmailAddress, $RecoveryHash) {
		
		$to = $EmailAddress; 
		$subject = "Password Reset - MyAarth";
		
		// To send HTML mail, the Content-type header must be set
		$headers .= "X-Mailer: PHP v".phpversion()."\r\n";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'From: MyAarth <noreply@myaarth.com>' . "\r\n";
		
		$body = "<div style='font-family:Verdana;'><h3><img src='https://myaarth.com/Style/Images/AarthLogoHugeTrackWealth.png' alt='MyAarth, Track Your Wealth' title='MyAarth, Track Your Wealth'></h3><br/><h4>Password Reset</h4><br/><p>You've recently notified us that you'd forgotten your password.</p><p>To reset your password, please <a href='https://myaarth.com/restore.php?e=".$EmailAddress."&h=".$RecoveryHash."'>click here</a> and follow the steps to change your password.</p><p>.</p><p>-MyAarth</p></div>";

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