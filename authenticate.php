<?php
date_default_timezone_set('America/New_York');

require_once("lib/presentation.php");
require_once("lib/lib.php");
UserManagement::InitSession();
if(isset($_GET['h']) && isset($_GET['e'])) {
	$DB = new Database();
	$Matches = $DB->ConfirmEmailHashMatch($_GET['e'],$_GET['h']);
	if(!$Matches) {
		header("Location: /");
		die();	
	}else{
		$Profile = $DB->GetUserProfile($_GET['e']);
		$DB->UpdateValidatedFlag($Profile->user_id,$Profile->user_email,1);	
	}
}else{
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
	<script type="text/javascript" src="Script/Restore.js"></script>
	<script type="text/javascript">
		var emailAddress = "<?php print($_GET['e']); ?>";
		var hash = "<?php print($_GET['h']); ?>";
		$(document).ready(function () {
			$("#homePage").on("click", function () {
				window.location = "/";
			});
		});
	</script>
  </head>

  <body>
    <div class="container">

      <div class="content">
        <div class="page-header" style="height:100px;">
		<?PHP
		Presentation::outputHeader();
		?>
		<div id="moneyTreeImage" style="position:relative;left:400px;top:-70px;width:200px;">
		</div>
        </div>
        <div class="row">
          <div class="span11">
			<h3>Account Confirmed</h3><br/>
			<p>Your account has been confirmed!</p>
			<p>Click the button below to go to the home page, and log in.</p>
			<button class="btn primary" id="homePage">Go to home page</button>
          </div>
        </div>
      </div>

	<?php
	Presentation::outputFooter();
	?>

    </div> <!-- /container -->

  </body>
</html>
