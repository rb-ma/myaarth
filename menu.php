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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php Presentation::outputPageTitle(); ?></title>
<script type="text/javascript" src="Script/transitions.js">
	function showImg(id){
		if(document.images){
			switch(id){
				case 1:
					my_link = "construction.php"
					my_img = "Style/Images/Slide2.PNG"
					return true;
				
			}
			document.getElementById("the_pic").src = my_img;
		}
		document.getElementById("the_link").href = my_link;
	}
</script>
</head>

<link rel="stylesheet" href="/Style/bootstrap.min.css"/>
<link rel="stylesheet" href="/Style/Main.css"/>

<body>
	<div class="container">
	  <div class="content">
			<div class="page-header" style="height:100px;">
			<?php
				presentation::outputHeader();
			?>
				<div id="moneyTreeImage" style="position:relative;padding-left:63%;padding-right:0%;top:-100px;width:200px;z-index:10;">
					<img src="/Style/Images/MoneyTreeSmall.png">
				</div>				
			</div>
		<div style="float:right">
			<div style = "padding-top: -5px;">			
					<a href="#" class="button blue" onclick='document.getElementById("the_pic").src="Style/Images/Slide1.PNG";document.getElementById("the_link").href="tracker.php";'/>Portfolio Tracker</a>
			</div>
			<div style="padding-top: 12px;">
					<a href="#" class="button green" onclick='document.getElementById("the_pic").src="Style/Images/Slide2.PNG";document.getElementById("the_link").href="construction.php";'/>Asset Tracker</a>
			</div>
			
            <div style="padding-top: 12px;">
					<a href="#" class="button red"  onclick='document.getElementById("the_pic").src="Style/Images/Slide3.PNG";document.getElementById("the_link").href="construction.php";'/>Service Three</a>
			</div>
            
		</div>
		  <a id="the_link" href="tracker.php"><img id="the_pic" src="Style/Images/Slide1.PNG"  alt="Portfolio Tracker -- Menu Image" height="295" width="640"/></a>
	</div>
		
		<?php
		Presentation::outputFooterMenu();
		?>

    </div> <!-- /container -->
		

</body>
</html>
