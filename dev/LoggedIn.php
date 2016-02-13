<?php

require_once("lib/lib.php");
UserManagement::InitSession();
if(!isset($_SESSION['EmailAddress'])) {
	header("Location: /");
	die();	
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Aarth, Helping You Build Wealth</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

	<link rel="stylesheet" href="/Style/bootstrap.min.css"/>
	<link rel="stylesheet" href="/Style/Main.css"/>
	<script type="text/javascript" src="/Script/jquery.min.js"></script>
	<script type="text/javascript" src="/Script/bootstrap-modal.js"></script>
	<script type="text/javascript" src="/Script/bootstrap-twipsy.js"></script>
	<script type="text/javascript" src="/Script/bootstrap-popover.js"></script>
	<script type="text/javascript" src="/Script/Main.js"></script>
	
	<script type="text/javascript">
	$(document).ready(function () {

		$("#logOutButton").on("click", function () {
			 var dataObject = { a: 'destroysession' };
			 $.ajax({
				url: "methods.php",
				data: dataObject,
				type: "get",
				dataType: "json",
				success: function (data) {
					if (data.response.result == "Success") {
						window.location = "/";
					}
				}
			});
		});
	});
	</script>
  </head>

  <body>

    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1>Website Name <small>Your slogan here.</small></h1>
        </div>
        <div class="row">
          <div class="span10">
			<h1>You have logged in successfully!</h1>
			<h3>Your email address is : <?php print($_SESSION['EmailAddress']); ?></h3>
			<p>
				Because you are logged in now, you can't see the homepage anymore. 
			</p>
			<p>
				Try it here: <a href="/">Home Page</a>
			</p>
			<button class="btn error" id="logOutButton">Log Out</button>
          </div>
        </div>
      </div>

      <footer>
        <p>&copy; Aarth 2011</p>
      </footer>

    </div> <!-- /container -->

  </body>
</html>
