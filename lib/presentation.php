<?php


class Presentation  {

	public static function outputHeader() {
		print ('<div class="aarth-logo" style="position:relative;top:30px"><img src="Style/Images/AarthLogoHugeTrackWealth.png"/></div>');
	}
	
	public static function outputFooter() {
		require_once("lib.php");
		UserManagement::InitSession();
		if(isset($_SESSION['User'])) {
			print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a> | <a href='menu.php'>Return to Menu</a> | <a href='/User/logout.php'>Logout</a></center></footer>");
		}else{
			print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a> | <a href='menu.php'>Return to Menu</a></center></footer>");	
		}
	}
	
	public static function outputFooterNoMenu() {
		require_once("lib.php");
		UserManagement::InitSession();
		if(isset($_SESSION['User'])) {
			print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a> | <a href='/User/logout.php'>Logout</a></center></footer>");
		}else{
			print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a></center></footer>");	
		}
	}
	
	public static function outputFooterConstruction(){
		print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a></center></footer>");	
	}
			
	public static function outputFooterMenu() {		require_once("lib.php");		UserManagement::InitSession();		if(isset($_SESSION['User'])) {			print("<footer><center>&copy; myAarth LLC 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a> | <a href='/User/logout.php'>Logout</a></center></footer>");		}else{			print("<footer><center>&copy; myAarth 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a></center></footer>");			}	}
	
	public static function outputPageTitle() {
		print "MyAarth.com";
	}


}

?>