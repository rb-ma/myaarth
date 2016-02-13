$(document).ready(function () {
    $("#registerPasswordInput").on("keyup change", function () {
        var value = $(this).val();
        var strength = calculatePasswordStrength(value);
        updatePasswordStrengthMeter(strength);
    });

    $("body").on("keyup change", "input[type=password]", function () {
        checkPasswordMatch();
    });
	
	$("body").on("keydown change", function(e){
		if(modal_showing){
			if(e.keyCode == 9 || e.keyCode == 32){
				return false;	
			}
		}
	});
	
	$("body").on("keyup change", "input[type=user_email]", function () {
        checkEmailMatch();
    });

    $("#termsOfServiceLink").on("click", function () {
		////console.log("Showing terms of service");
		if(!modal_showing){
			modal_showing = true;
			$("#termsOfServiceModal").modal('show');
		}
	});

    $("#closeTOS").on("click", function () {
        $("#termsOfServiceModal").modal('hide');
		modal_showing = false;
    });

    $("#closeConfirm").on("click", function () {
        $("#confirmAccount").modal("hide");	
		modal_showing = false;	
    });

    $("#closeRegistrationFailed").on("click", function () {
        $("#registrationFailed").modal("hide");
		modal_showing = false;
    });

    $("#closeLoginFailed").on("click", function () {
        $("#loginFailed").modal("hide");
		document.getElementById("passwordInput").value = "";
		modal_showing = false;
    });

    $("#closeConfirm2").on("click", function () {
        $("#noConfirmedEmail").modal("hide");
		modal_showing = false
    });

    $("#forgotPasswordLink").on("click", function () {
		if(!modal_showing){
			modal_showing = true;
			$("#lostPassword").modal("show");
			var tempEmail = $("#emailAddressInput").val();
			$("#lostPasswordEmail").val(tempEmail);
		}
    });

    $("#passwordInput").on("keydown change", function (e) {
        if (e.keyCode == 13) {
            $("#loginButtonRemember").trigger("click");
        }
    });

    $("#termsOfServiceModal, #loginFailed, #registrationFailed, #confirmAccount, #noConfirmedEmail, #lostPassword").modal({
        backdrop: true,
        show: false,
        keyboard: false
    });

    $("#loginButtonRemember").on("click", function () {
		var email = $("#emailAddressInput").val();
    	var password = $("#passwordInput").val();
		var remember = $("#rememberEmail").val();
		attemptUserLoginNew(email, password, remember);
    });

	$("#loginButton").on("click", function () {
        var email = $("#emailAddressInput").val();
        var password = $("#passwordInput").val();
        attemptUserLogin(email, password);
    });

    $("#registerButton").on("click", function () {
        precommitRegisterUser();
    });

    $("#lostCancel").on("click", function () {
        $("#lostPassword").modal("hide");
		modal_showing = false;
    });

    $("#lostSendEmail").on("click", function () {
        var email = $("#lostPasswordEmail").val();
        checkEmailIsInDatabase(email);
    });

    $("#acceptTOS").on("click", function () {
        var val = $(this).is(":checked");
        AppNamespace.RegistrationValidation.TOSAccepted = val;
    });
});

var AppNamespace = AppNamespace || {};
modal_showing = false;
AppNamespace.AwaitingLoginResult = false;
AppNamespace.successfulLogin = false;
AppNamespace.RegistrationValidation = {
    EmailFormat : false,
    EmailMatch: false,
    PasswordLength: false,
    PasswordMatch : false,
    EmailInUse: false,
    TOSAccepted: false,
	recaptchaMatch: false
}

function attemptUserLogin(email, password) {
    AppNamespace.AwaitingLoginResult = true;
    var dataObject = { a: 'validateusercredentials', e: email, p: password, s: true };
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            AppNamespace.AwaitingLoginResult = false;
            if (data.response.result == "Success") {
                window.location = "tracker.php";
            } else {
                if (data.response.data == "Email not confirmed") {
                    indicateNoConfirm();
                } else {
                    indicateLoginFailure();
                }
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function attemptUserLoginNew(email, password, rememberEmail) {
	AppNamespace.AwaitingLoginResult = true;
	var success = false;
    var dataObject = { a: 'validateusercredentials', e: email, p: password, s: true };
	$.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            AppNamespace.AwaitingLoginResult = false;
            if (data.response.result == "Success") {
                window.location = "tracker.php";
            } else {
                if (data.response.data == "Email not confirmed") {
                    indicateNoConfirm();
                } else {
                    indicateLoginFailure();
                }
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });

	if((document.getElementById("rememberEmail").checked == true)){
		eraseCookie("Email");
		createCookie("Email",email,365);
	} else {
		eraseCookie("Email");
	}
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function getCookie() {
	var nameEQ = "Email=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0){
			document.getElementById("emailAddressInput").value=c.substring(nameEQ.length,c.length);
			document.getElementById("rememberEmail").checked = true;
			return true;
		}
	}
	document.getElementById("emailAddressInput").value="";
	document.getElementById("rememberEmail").checked =false;
	return false;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function sendPasswordReset(email) {
    var dataObject = { e: email, a:"requestpasswordreset"};
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            AppNamespace.AwaitingLoginResult = false;
            if (data.response.result == "Success") {
                alert("Password reset sent.");
            } 
        }
    });
}

function commitNewUser(email, password) {
    AppNamespace.AwaitingLoginResult = true;
    var dataObject = { a: 'commitnewuser', e: email, p: password, s: true };
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            AppNamespace.AwaitingLoginResult = false;
            if (data.response.result == "Success") {
				if(!modal_showing){
					modal_showing = true;
		       	 	$("#confirmAccount").modal("show");
				}
	        } else {
                indicateLoginFailure();
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function commitNewUser_recaptcha(email, password, challenge, response) {
	//console.log("In commitNewUser_recaptcha");
	AppNamespace.AwaitingLoginResult = true;
    var dataObject = { a: 'commitnewuser_recaptcha', e: email, p: password, s: true, c: challenge, r: response };
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            AppNamespace.AwaitingLoginResult = false;
            if (data.response.result == "Success") {
				//console.log("recaptcha check passed");
				AppNamespace.RegistrationValidation.recaptchaMatch = true;
				if(!modal_showing){
					modal_showing = true;
		       	 	$("#confirmAccount").modal("show");
				}
	        } else {
 				//console.log("recaptcha check failed 1");
                indicateRegistrationFailure();
            }
        },
        error: function () {
			//console.log("recaptcha check failed 2");
			indicateRegistrationFailure();
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function indicateLoginFailure() {
	if(!modal_showing){
		modal_showing = true;
		$("#loginFailed").modal("show");
	}
}

function checkEmailIsInDatabase(email) {
    var dataObject = { a: 'emailinuse', e: email };
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            if (data.response.data == true) {
                sendPasswordReset(email);
            } else {
                alert("There isn't a MyAarth account for the email address you specified. \r\n Please verify that the address you entered is valid.");
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function checkEmailInUsePreCommit(email, password) {
    var dataObject = { a: 'emailinuse', e: email };
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            if (data.response.data == true) {
                AppNamespace.RegistrationValidation.EmailInUse = true;
                indicateRegistrationFailure();
            } else {
                commitNewUser(email, password);
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function checkEmailInUsePreCommit_recaptcha(email, password, challenge, response) {
    //console.log("in checkEmailInUsePreCommit -- really");
	//console.log(email);
	//console.log(password);
	//console.log(challenge);
	//console.log(response);
	var dataObject = { a: 'emailinuse', e: email };
	//console.log(dataObject);
    $.ajax({
        url: "methods.php",
        data: dataObject,
        type: "get",
        dataType: "json",
        success: function (data) {
            if (data.response.data == true) {
                AppNamespace.RegistrationValidation.EmailInUse = true;
                indicateRegistrationFailure();
            } else {
                commitNewUser_recaptcha(email, password, challenge, response);
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function indicateRegistrationFailure() {
    var outputReasons = [];

    if (!AppNamespace.RegistrationValidation.EmailFormat) {
        outputReasons.push("<li>Incorrect email address format</li>");
    }
    if (!AppNamespace.RegistrationValidation.EmailMatch) {
        outputReasons.push("<li>The email you provided in your confirmation does not match that of your original.</li>");
    }
    if (!AppNamespace.RegistrationValidation.PasswordLength) {
        outputReasons.push("<li>Your password is not long enough.</li>");
    }
    if (!AppNamespace.RegistrationValidation.PasswordMatch) {
        outputReasons.push("<li>Your password does not match the confirmation password you provided.</li>");
    }
    if (AppNamespace.RegistrationValidation.EmailInUse) {
        outputReasons.push("<li>Email address is already in use.</li>");
    }
    if (!AppNamespace.RegistrationValidation.TOSAccepted) {
        outputReasons.push("<li>Terms of service not accepted.</li>");
    }
	if(!AppNamespace.RegistrationValidation.recaptchaMatch){
		outputReasons.push("<li>Robot check -- Your reCAPTCHA input was incorrect</li>");	
	}

    $("#registrationIssues").html(outputReasons.join(""));
	if(!modal_showing){
		modal_showing = true;
	    $("#registrationFailed").modal("show");
	}
}

function indicateNoConfirm() {
	if(!modal_showing){
		modal_showing = true;
	   	$("#noConfirmedEmail").modal("show");
	}
}

function precommitRegisterUser() {
    var email = $("#registerEmailAddressInput").val();
    var password = $("#registerPasswordInput").val();
	var challenge = $("input#recaptcha_challenge_field").val();
	var response = $("input#recaptcha_response_field").val();

    AppNamespace.RegistrationValidation.EmailFormat = (checkRegisterEmailForFormatting(email)) ? true : false;
    AppNamespace.RegistrationValidation.EmailMatch = checkRegisterEmailMatch() ? true : false;
    AppNamespace.RegistrationValidation.PasswordLength = (password.length >= 5) ? true : false;
    AppNamespace.RegistrationValidation.PasswordMatch = checkRegisterPasswordMatch() ? true: false;
	
    if (AppNamespace.RegistrationValidation.TOSAccepted && AppNamespace.RegistrationValidation.EmailFormat && AppNamespace.RegistrationValidation.EmailMatch && AppNamespace.RegistrationValidation.PasswordLength & AppNamespace.RegistrationValidation.PasswordMatch) {
        checkEmailInUsePreCommit_recaptcha(email, password, challenge, response);
    } else {
        document.getElementById("registerEmailAddressInput").value = "";
		document.getElementById("emailAddressConfirmInput").value = "";		
		$(".emailCheck").removeClass("error");
		indicateRegistrationFailure();
    }
	$(".emailCheck").removeClass("error");
	document.getElementById("registerPasswordInput").value = "";
	document.getElementById("registerPasswordConfirmInput").value = "";
	document.getElementById("acceptTOS").checked = false;
	checkPasswordMatch();
	updatePasswordStrengthMeter(0);
}

function checkRegisterEmailForFormatting(email) {
    var match = email.match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/gi);
    return (match != null);
}

function checkRegisterEmailMatch() {
    var emailVal1 = $("#registerEmailAddressInput").val();
    var emailVal2 = $("#emailAddressConfirmInput").val();
    return emailVal1 == emailVal2;
}

function checkRegisterPasswordMatch() {
    var passVal1 = $("#registerPasswordInput").val();
    var passVal2 = $("#registerPasswordConfirmInput").val();
    return passVal1 == passVal2;
}

function updatePasswordStrengthMeter(val) {
    var outputContent = "";
    switch (val) {
        case 1:
            $(".strengthSpan").removeClass().addClass("label empty strengthSpan").html("Weak");
            break;
        case 2:
            $(".strengthSpan").removeClass().addClass("label important strengthSpan").html("Poor");
            break;
        case 3:
            $(".strengthSpan").removeClass().addClass("label warning strengthSpan").html("Fair");
            break;
        case 4:
            $(".strengthSpan").removeClass().addClass("label success strengthSpan").html("Strong");
            break;
		default:
			$(".strengthSpan").removeClass().addClass("label empty strengthSpan").html("");
			break;
    }
}

function checkPasswordMatch() {
    if ($("#registerPasswordInput").val() != $("#registerPasswordConfirmInput").val()) {
        $(".passwordCheck").addClass("error");
        $("#passwordMatch").fadeIn();
    } else {
        $(".passwordCheck").removeClass("error");
        $("#passwordMatch").fadeOut();
    }
}

function checkEmailMatch() {
    if ($("#registerEmailAddressInput").val() != $("#emailAddressConfirmInput").val()) {
        $(".emailCheck").addClass("error");
    } else {
        $(".emailCheck").removeClass("error");
    }
}

function calculatePasswordStrength(input) {
    strength = 0;
    /*length 5 characters or more*/
    if (input.length >= 5) {
        strength++;
    }

    /*contains lowercase characters*/
    if (input.match(/[a-z]+/)) {
        strength++;
    }

    /*contains digits*/
    if (input.match(/[0-9]+/)) {
        strength++;
    }

    /*contains uppercase characters*/
    if (input.match(/[A-Z]+/)) {
        strength++;
    }
    return strength;
}
