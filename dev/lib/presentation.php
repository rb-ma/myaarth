<?php


class Presentation  {

	public static function outputHeader() {
		print ('<div class="aarth-logo" style="position:relative;top:30px"><a href="https://www.myaarth.com"><img src="Style/Images/AarthLogoHugeTrackWealth.png"/></a></div>');
	}
	
	public static function outputFooter() {
		require_once("lib.php");
		UserManagement::InitSession();
		if(isset($_SESSION['User'])) {
			print("<footer><center>&copy; myAarth 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a> | <a href='/User/logout.php'>log out</a></center></footer>");
		}else{
			print("<footer><center>&copy; myAarth 2012 | <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a></center></footer>");	
		}
	}
	
	public static function outputPageTitle() {
		print "myAarth, Track Your Wealth";
	}


}

?>