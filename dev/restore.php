<?php
date_default_timezone_set('America/New_York');

require_once("lib/presentation.php");
require_once("lib/lib.php");
UserManagement::InitSession();
if(isset($_GET['h']) && isset($_GET['e'])) {
	$DB = new Database();
	$CanRestore = $DB->ValidateEmailRestoreMatch($_GET['e'],$_GET['h']);
	if(!$CanRestore) {
		header("Location: /");
		die();	
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
	</script>
  </head>

  <body>
<div id="mismatchModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Passwords Do Not Match</h3>
    </div>
    <div class="modal-body">
		<p>
			Your passwords do not match.
		</p>
	</div>
    <div class="modal-footer">
		<a href="#" class="btn" id="closeMismatch">OK</a>
    </div>
    </div>
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
			<h3>Password Restore</h3><br/>
			<p>To change your password, please type in the fields below.</p>
			<table>
			<tr>
				<td>Email Address</td>
				<td id='emailAddress'><?php print(isset($_GET['e'])?$_GET['e']:"no email"); ?></td>
			</tr>
			<tr>
				<td>New Password</td>
				<td><input type="password" id="passwordEntry"></td>
			</tr>
			<tr>
				<td>Confirm Password</td>
				<td><input type="password" id="confirmPasswordEntry"></td>
			</tr>
			</table>
			<button class="btn primary" id="commitPasswordSave">Save</button>
          </div>
        </div>
      </div>

	<?php
	Presentation::outputFooter();
	?>

    </div> <!-- /container -->

  </body>
</html>
