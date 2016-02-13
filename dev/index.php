<?php

require_once("lib/lib.php");
require_once("lib/presentation.php");
UserManagement::InitSession();
if(isset($_SESSION['User'])) {
	header("Location: tracker.php");
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

	<link rel="stylesheet" href="Style/bootstrap.min.css"/>
	<link rel="stylesheet" href="Style/Main.css"/>
	<script type="text/javascript" src="Script/jquery.min.js"></script>
	<script type="text/javascript" src="Script/bootstrap-modal.js"></script>
	<script type="text/javascript" src="Script/bootstrap-twipsy.js"></script>
	<script type="text/javascript" src="Script/bootstrap-popover.js"></script>
	<script type="text/javascript" src="Script/Main.js"></script>


  </head>

  <body>

    <div id="termsOfServiceModal" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Terms of Service</h3>
    </div>
    <div class="modal-body">
		<p>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer et ligula neque, at lacinia magna. Donec varius lectus augue. Fusce dictum felis sit amet nulla vulputate egestas. Nullam et risus sit amet nisi placerat laoreet vel sit amet lectus. Fusce ac pretium ipsum. Vivamus sollicitudin, eros vel aliquam rutrum, quam massa molestie elit, a pretium turpis nibh sit amet purus. Fusce sit amet elit sit amet tellus lobortis cursus vel sed nisi. Integer fermentum, nibh ut congue porttitor, odio ligula lacinia quam, a sollicitudin odio est ac est.
		</p>
		<p>
			Donec euismod euismod nisl vel tincidunt. Vestibulum metus felis, pellentesque et aliquet sed, pharetra non ipsum. Aenean vitae nibh ante. Etiam malesuada, neque id aliquet viverra, felis ligula gravida lacus, sed euismod sem nisl in tellus. Donec vulputate est vel ante vehicula at tincidunt ante adipiscing. Pellentesque nec massa purus, a facilisis neque. Donec sodales elit sed massa mattis pretium. Cras volutpat rhoncus libero, quis auctor mauris pharetra ut. Sed eleifend accumsan porta. 
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" id="closeTOS">Close</a>
    </div>
    </div>
	
	<div id="confirmAccount" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Confirm Your Account</h3>
    </div>
    <div class="modal-body">
		<p>
			Your account has been created, but you need to confirm your email address before we can get started. 
		</p>
		<p>
			We just sent you an email which you should be receiving shortly, just follow the instructions within and then you'll be set.
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" id="closeConfirm">Close</a>
    </div>
    </div>
	
	<div id="noConfirmedEmail" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Confirm Your Account</h3>
    </div>
    <div class="modal-body">
		<p>
			Your account has been created, but hasn't been confirmed through the email we've sent.
		</p>
		<p>
			Please follow the instructions in the registration email to get started.
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" id="closeConfirm2">Close</a>
    </div>
    </div>
	
	<div id="lostPassword" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Forget your password?</h3>
    </div>
    <div class="modal-body">
		<p>
			If you've forgot your password, enter your email address below to receive an email that will let you reset your password.
		</p>
		<p>
			Email: <input id="lostPasswordEmail" type="text">
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn primary" id="lostSendEmail">Send Email</a>
		<a href="#" class="btn" id="lostCancel">Cancel</a>
    </div>
    </div>
	
	<div id="loginFailed" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Login Failed</h3>
    </div>
    <div class="modal-body">
		<p>
		<div><small>Your login has failed, please see below for some possible reasons:</small></div>
		<br/>
		<ul>
			<li>Invalid email address</li>
			<li>Incorrect password</li>
			<li>Account doesn't exist</li>
		</ul>
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" id="closeLoginFailed">Try Again</a>
    </div>
    </div>
	
	<div id="registrationFailed" class="modal hide">
    <div class="modal-header">
        <a href="#" class="close">&times;</a>
        <h3>Before You Register...</h3>
    </div>
    <div class="modal-body">
		<p>
		<div><small>It looks like there are some issues with your registration. Clear them up, then you'll be all set.</small></div>
		<br/>
		<ul id="registrationIssues">
			<li>Invalid email address</li>
			<li>Incorrect password</li>
			<li>Account doesn't exist</li>
		</ul>
		</p>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" id="closeRegistrationFailed">Try Again</a>
    </div>
    </div>


<div class="container">
	<div class="leftFade">
	</div>
      <div class="content" style="padding: 20px 20px 5px 20px">
        <div class="aarth-logo" style="position:relative;top:30px">
			<img src="Style/Images/AarthLogoHugeTrackWealth.png"/>
        </div>
        <div class="row">
          <div class="span9" style="margin-left:0;overflow:none; height:100px;">
			<img src="Style/Images/AarthMainSimple.gif">
          </div>
          <div class="span4">
            <h3>Log In</h3>
			<div>
				<label for="emailAddressInput">Email:</label>
				<input id="emailAddressInput">
				<label for="passwordInput">Password:</label>
				<input type="password" id="passwordInput">
				<br/>
				<a href="#" id="forgotPasswordLink">Forgot your password?</a><br/><br/>
				<button class="btn primary span4" id="loginButton">Login</button>
			</div>
			<hr/>
            <h3>Register</h3>
			<div class="control-group">
					<label for="emailAddressInput">Email:</label>
					<input id="registerEmailAddressInput">
					<label for="emailAddressConfirmInput">Confirm Email:</label>
					<input id="emailAddressConfirmInput">
					<label for="registerPasswordInput">Password:</label>
					<input type="password" id="registerPasswordInput" class="passwordCheck">
					<label for="registerPasswordConfirmInput">Confirm Password:</label>
					<input type="password" id="registerPasswordConfirmInput" class="passwordCheck">
					<div style="margin-top:20px;">
						<div style="float:left;clear:none;margin-right:10px;"><small ><strong>Password Strength: </strong></small></div>
						<span class="strengthSpan empty" style="float:left;clear:right"></span>
					</div>
					<div style="margin-top:10px;">
					
					</div>
					<br/><br/>
					I accept Aarth's <a href="javascript:void(0)" id="termsOfServiceLink">Terms of Service</a>. <input type="checkbox" id="acceptTOS"/><br/><br/>
					<button class="btn success span4" id="registerButton">Register</button>
			</div>
          </div>
        </div>
		
<footer>
        <p><center><?php Presentation::outputFooter(); ?></center></p>
      </footer>
      </div>




	<div class="rightFade"></div>
	    </div> <!-- /container -->
		      
  </body>
</html>
