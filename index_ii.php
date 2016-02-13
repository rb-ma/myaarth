<?php
date_default_timezone_set('America/New_York');

require_once("lib/presentation.php");
require_once("lib/lib.php");
UserManagement::InitSession();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php Presentation::outputPageTitle(); ?></title>
</head>

<link rel="stylesheet" href="/Style/bootstrap.min.css"/>
<link rel="stylesheet" href="/Style/Main.css"/>
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

<body>
	<div class="container">
		<div class="content">
			<div class="page-header" style="height:100px;">
				<?PHP
				Presentation::outputHeader();
				?>
				<div id="moneyTreeImage" style="position:relative;padding-left:63%;padding-right:0%;top:-100px;width:200px;z-index:10;">
					<img src="/Style/Images/MoneyTreeSmall.png">
				</div>				
			</div>
			<div style="vertical-align=center;">
				<img height=150 width=150 src="/Style/Images/construction.jpg" alt="Construction" style="padding-top:5%;padding-left:6%;float:left;margin:0 5px 0 0 0;"/>
				<p style="padding-left:19%; padding-right:0%;padding-top:10%; font-family:Calibri; font-size:20px;">Apologies...our site is currently undergoing renovations.</p>
                <p style="padding-left:19%; padding-right:0%;padding-top:3%; font-family:Calibri; font-size:20px;">For institutional investors interested in our library of financial functions (analytics, trading, customizations etc.), please contact us at <a href='mailto:contact@myaarth.com'>contact@myaarth.com</a></p>
			</div>
		</div>
		<?php
		Presentation::outputFooterConstruction();
		?>
    </div> <!-- /container -->
		

</body>
</html>

