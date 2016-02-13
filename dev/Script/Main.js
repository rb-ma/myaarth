$(document).ready(function () {
    $("#registerPasswordInput").on("keyup change", function () {
        var value = $(this).val();
        var strength = calculatePasswordStrength(value);
        updatePasswordStrengthMeter(strength);
    });

    $("body").on("keyup change", "input[type=password]", function () {
        checkPasswordMatch();
    });

    $("#termsOfServiceLink").on("click", function () {
        $("#termsOfServiceModal").modal('show');
    });

    $("#closeTOS").on("click", function () {
        $("#termsOfServiceModal").modal('hide');
    });

    $("#closeConfirm").on("click", function () {
        $("#confirmAccount").modal("hide");
    });

    $("#closeRegistrationFailed").on("click", function () {
        $("#registrationFailed").modal("hide");
    });

    $("#closeLoginFailed").on("click", function () {
        $("#loginFailed").modal("hide");
    });

    $("#closeConfirm2").on("click", function () {
        $("#noConfirmedEmail").modal("hide");
    });

    $("#forgotPasswordLink").on("click", function () {
        $("#lostPassword").modal("show");
        var tempEmail = $("#emailAddressInput").val();
        $("#lostPasswordEmail").val(tempEmail);
    });

    $("#passwordInput").on("keyup", function (e) {
        if (e.keyCode == 13) {
            $("#loginButton").trigger("click");
        }
    });

    $("#termsOfServiceModal, #loginFailed, #registrationFailed, #confirmAccount, #noConfirmedEmail, #lostPassword").modal({
        backdrop: true,
        show: false,
        keyboard: true
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

AppNamespace.AwaitingLoginResult = false;
AppNamespace.RegistrationValidation = {
    EmailFormat : false,
    EmailMatch: false,
    PasswordLength: false,
    PasswordMatch : false,
    EmailInUse: false,
    TOSAccepted: false
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
                window.location = "/menu.php";
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
                $("#confirmAccount").modal("show");
            } else {
                indicateLoginFailure();
            }
        },
        error: function () {
            AppNamespace.AwaitingLoginResult = false;
        }
    });
}

function indicateLoginFailure() {
    $("#loginFailed").modal("show");
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

    $("#registrationIssues").html(outputReasons.join(""));
    $("#registrationFailed").modal("show");
}

function indicateNoConfirm() {
    $("#noConfirmedEmail").modal("show");
}

function precommitRegisterUser() {
    var email = $("#registerEmailAddressInput").val();
    var password = $("#registerPasswordInput").val();


    AppNamespace.RegistrationValidation.EmailFormat = (checkRegisterEmailForFormatting(email)) ? true : false;
    AppNamespace.RegistrationValidation.EmailMatch = checkRegisterEmailMatch() ? true : false;
    AppNamespace.RegistrationValidation.PasswordLength = (password.length >= 5) ? true : false;
    AppNamespace.RegistrationValidation.PasswordMatch = checkRegisterPasswordMatch() ? true: false;

    if (AppNamespace.RegistrationValidation.TOSAccepted && AppNamespace.RegistrationValidation.EmailFormat && AppNamespace.RegistrationValidation.EmailMatch && AppNamespace.RegistrationValidation.PasswordLength & AppNamespace.RegistrationValidation.PasswordMatch) {
        checkEmailInUsePreCommit(email, password);
    } else {
        indicateRegistrationFailure();
    }
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
