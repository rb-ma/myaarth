$(document).ready(function () {

    $("#mismatchModal").modal({
        backdrop: true,
        show: false,
        keyboard: true
    });

    $("#closeMismatch").on("click", function () {
        $("#mismatchModal").modal("hide");
    });

    $("#commitPasswordSave").on("click", function () {
        if (checkRegisterPasswordMatch()) {
            if (calculatePasswordStrength($("#passwordEntry").val()) > 2) {
                savePasswordChange();
            } else {
                alert("Password strength too weak. Please add more complexity to your password.");
            }
        } else {
            $("#mismatchModal").modal("show");
        }
    });
});

function checkRegisterPasswordMatch() {
    var passVal1 = $("#passwordEntry").val();
    var passVal2 = $("#confirmPasswordEntry").val();
    return passVal1 == passVal2;
}

function savePasswordChange() {
    $.ajax({
        type: "post",
        dataType: "json",
        data: { e: emailAddress, h: hash, p: $("#passwordEntry").val() },
        url: "/methods.php?a=updatepassword",
        success: function (data) {
            alert("Your password has been updated successfully. You will be transferred to the home page now.");
            window.location = "/";
        }
    });
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