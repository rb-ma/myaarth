$(document).ready(function () {
    Autocomplete.init($("#searchTextBox"));
    $("body").on("click", ".removeStock", function () {
        var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
        TickerTracker.removeSymbol(symbol_index);
    });

    $("body").on("keyup blur change", ".stockValue", function () {
        var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
        var value = $(this).val();
        TickerTracker.updateSymbolOwnership(symbol_index, value);
    });

    $("body").on("click", ".toggleVal", function () {
        var current = $(this).html();
        var alt = $(this).attr("data-alt-value");
        var index = $(this).closest("tr").attr("data-tracker-index");
        $(".toggleVal").html(alt);
        $(".toggleVal").attr("data-alt-value", current);
        TickerTracker.currentPortfolioType = alt;
        TickerTracker.updateAllSymbolOwnershipType(alt);
        if (alt == "%") {
            $("#totalAmountInput").removeClass("disabled").removeAttr("disabled").val("");
            TickerTracker.toggleAmountsToPercentages();
            TickerTracker.showTotalEmphasis();
        } else {
            if (TickerTracker.togglePercentagesToAmounts()) {
                $("#totalAmountInput").addClass("disabled").attr("disabled", "disabled");
                TickerTracker.showTotalEmphasis();
            }
        }
    });


    $("#finishButton").on("click", function () {
        var total_val = parseInt(TickerTracker.totalValue, 10);
        if (TickerTracker.currentPortfolioType == "%") {
            if (isNaN(TickerTracker.currentPercentageOwnershipValue) || TickerTracker.currentPercentageOwnershipValue <= 0) {
                $("#confirmTotalOwnershipEmpty").modal("show");
            } else {
                $("#confirmModal").modal("show");
            }
        }
        if (TickerTracker.currentPortfolioType == "$") {
            if (isNaN(TickerTracker.totalValue) || TickerTracker.totalValue <= 0) {
                $("#confirmTotalOwnershipEmpty").modal("show");
            } else {
                $("#confirmModal").modal("show");
            }
        }
    });

    $("#searchTextBox").on("blur", function () {
        if ($(this).val() == "") {
            $(this).val($(this).attr("data-default-value"));
            $(this).css({ fontStyle: "italic" });
        }
    });

    $("#searchTextBox").on("focus", function () {
        if ($(this).val() == $(this).attr("data-default-value")) {
            $(this).val("");
            $(this).css({ fontStyle: "normal" });
        }
    });

    $("#manualAddButton").on("click", function () {
        var symbol_name = $("#searchTextBox").val();
        TickerTracker.addManualSymbol(symbol_name);
    });

    $("#closeConfirm").on("click", function () {
        $("#confirmModal").modal("hide");
    });

    $("#confirmEmpty").on("click", function () {
        $("#confirmTotalOwnershipEmpty").modal("hide");
        $("#confirmModal").modal("show");
    });

    $("#savePortfolio").on("click", function () {
        $("#confirmModal").modal("hide");
        Database.insertNewStocks();
    });

    $("#cancelInconsistency").on("click", function () {
        $("#portfolioInconsistent").modal("hide");
    });

    $("#cancelEmpty").on("click", function () {
        $("#confirmTotalOwnershipEmpty").modal("hide");
    });

    $("#proceedWithInconsistency").on("click", function () {
        $("#portfolioInconsistent").modal("hide");
        TickerTracker.allowInconsistency = true;
        Database.insertNewStocks();
    });

    $("#confirmModal, #showSaved, #portfolioInconsistent, #confirmTotalOwnershipEmpty").modal({
        backdrop: true,
        show: false,
        keyboard: true
    });

    $("#closeSaved").on("click", function () {
        $("#showSaved").modal("hide");
    });

    $("#refreshPage").on("click", function () {
        if (confirm("Are you sure that you'd like to reload your latest saved portfolio?")) {
            window.location = "./tracker.php";
        }
    });

    $("#resetPortfolioButton").on("click", function () {
        if (confirm("Are you sure you'd like to reset your portfolio? Doing so will remove all portfolio history.")) {
            Database.resetPortfolio();
        }
    });

    $("body").on("mouseover", ".autocompleteSuggestion", function () {
        $(this).addClass("alert-message warning small").css({ padding: "0" });
    });

    $("body").on("mouseout", ".autocompleteSuggestion", function () {
        $(this).removeClass("alert-message warning small");
    });

    $("body").on("keyup change", "#totalAmountInput", function () {
        TickerTracker.currentPercentageOwnershipValue = parseFloat($(this).val());
    });

});

SymbolCache =  {};
SymbolCache.addSymbolToCache = function (symbol_id, symbol_name, symbol_description) {
    SymbolCache.Cache.push({symbol_id:symbol_id,symbol_name:symbol_name,symbol_description:symbol_description});
};
SymbolCache.Cache = [];

TickerTracker = {};

TickerTracker.userTrackingSymbols = [];
TickerTracker.totalValue = 0;
TickerTracker.currentPercentageOwnershipValue = 0;

TickerTracker.lastPortfolioType = null;
TickerTracker.currentPortfolioType = null;
TickerTracker.allowInconsistency = false
TickerTracker.noTotalValue = false;

TickerTracker.addSymbol = function (symbol_id) {
    TickerTracker.appendSymbol(symbol_id);
    $("#searchTextBox").val("").trigger("blur");
    TickerTracker.drawTable();
}

TickerTracker.toggleAmountsToPercentages = function () {
    var percentageMultiplier = TickerTracker.totalValue / 100;
    TickerTracker.currentPercentageOwnershipValue = TickerTracker.totalValue;
    TickerTracker.currentPortfolioType = "%";
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
        o.ownership_value = o.ownership_value / percentageMultiplier;
        o.ownership_value = o.ownership_value.toFixed(2);
        o.ownership_type = "%";
    });
    TickerTracker.drawTable();
}

TickerTracker.togglePercentagesToAmounts = function () {
    if (isNaN(TickerTracker.currentPercentageOwnershipValue)) {
        alert("Please enter a total ownership value before switching to amounts.");
        return false;
    }
    var percentageMultiplier = TickerTracker.currentPercentageOwnershipValue / 100;
    TickerTracker.totalValue = TickerTracker.currentPercentageOwnershipValue;
    TickerTracker.currentPortfolioType = "$";
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
        o.ownership_value = o.ownership_value * percentageMultiplier;
        o.ownership_value = o.ownership_value.toFixed(2);
        o.ownership_type = "$";
    });
    TickerTracker.drawTable();
    return true;
}

TickerTracker.removeSymbol = function (symbol_id) {
    TickerTracker.userTrackingSymbols.remove(symbol_id, symbol_id);
    TickerTracker.drawTable();
    if(TickerTracker.currentPortfolioType != "%") {
        TickerTracker.updateTotalValue();
    }
}

TickerTracker.addManualSymbol = function (symbol_name) {
    $.each(SymbolCache.Cache, function (i, o) {
        if (o.symbol_name.toLowerCase().trim() == symbol_name.toLowerCase().trim()) {
            TickerTracker.addSymbol(o.symbol_id);
            Autocomplete.hideSuggestions();
            return false;
        }
    });
}

TickerTracker.updateTotalValue = function () {
    TickerTracker.totalValue = 0;
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
        TickerTracker.totalValue += o.ownership_value-0;
    });
    if(!isNaN(TickerTracker.totalValue)) {
        $("#totalAmountInput").val(TickerTracker.totalValue.toFixed(2));
    }else{
        $("#totalAmountInput").val("-");
    }
}

TickerTracker.updateSymbolOwnership = function (symbol_id, value) {
    if(!isNaN(value)) {
        var userSymbol = jQuery.extend({}, TickerTracker.userTrackingSymbols[symbol_id]);
        userSymbol.ownership_value = value;
        TickerTracker.userTrackingSymbols[symbol_id] = userSymbol;
        if(TickerTracker.currentPortfolioType == "$") {
            TickerTracker.updateTotalValue();
        }
    }
}

TickerTracker.updateSymbolOwnershipType = function (symbol_id, type) {
    var userSymbol = TickerTracker.userTrackingSymbols[symbol_id];
    userSymbol.ownership_type = type;
    TickerTracker.userTrackingSymbols[symbol_id] = userSymbol;
};

TickerTracker.updateAllSymbolOwnershipType = function (type) {
    $.each(TickerTracker.userTrackingSymbols, function(i,o) {
        o.ownership_type = type;
    });
}

TickerTracker._symbol = function () {
    this.symbol_id = null;
    this.symbol_name = null;
    this.symbol_description = null;
    this.ownership_value = null;
    this.ownership_type = '$';
}

TickerTracker.appendSymbol = function (symbol_id) {
    $.each(SymbolCache.Cache, function (x, y) {
        if (symbol_id === y.symbol_id) {
            var temp_symbol = new TickerTracker._symbol();
            temp_symbol.symbol_id = y.symbol_id;
            temp_symbol.symbol_name = y.symbol_name;
            temp_symbol.symbol_description = y.symbol_description;
            temp_symbol.ownership_type = TickerTracker.currentPortfolioType;
            TickerTracker.userTrackingSymbols.push(temp_symbol);
            return false;
        }
    });

}

TickerTracker.drawTable = function () {
    var output = ["<table><thead><tr><th>Ticker</th><th>Company / Mutual Fund Name</th><th>Amount (as of date)</th><th></th></tr></thead><tbody>"];
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
        output.push("<tr data-tracker-index='" + i + "'><td>" + o.symbol_name + "</td><td>" + o.symbol_description + "</td><td><div class='input-prepend'><span class='add-on btn primary toggleVal unselectable' data-alt-value='" + (o.ownership_type == '$' ? "%" : "$") + "' style='z-index:0;'>");
        output.push((o.ownership_type == '$' ? "$" : "%"));
        output.push("</span><input type='text' class='span2 stockValue' value='" + (o.ownership_value || "") + "'></div></td><td><button class='btn error removeStock'>X</button></td></tr>");
    });
    output.push("<tr><td colspan='2' style='text-align:right;'>Total Value </td><td  colspan='2'><div class='input-prepend'>");
    if (TickerTracker.currentPortfolioType == null || TickerTracker.currentPortfolioType == "$") {
        if (TickerTracker.currentPortfolioType == "%") {
            if (isNaN(TickerTracker.currentPercentageOwnershipValue)) {
                outVal = "0.00";
            } else {
                outVal = TickerTracker.currentPercentageOwnershipValue.toFixed(2);
            }
        } else {
            if (isNaN(TickerTracker.totalValue)) {
                outVal = "0.00";
            } else {
                outVal = TickerTracker.totalValue.toFixed(2);
            }
        }
        output.push("<span style='z-index:0;' data-alt-value='$' class='add-on btn primary unselectable disabled'>$</span><input id='totalAmountInput' type='text' value='0.00' class='span2' disabled='disabled'></div></td></tr>");
    } else {
        var outVal = 0;
        if (TickerTracker.currentPortfolioType == "%") {
            if (isNaN(TickerTracker.currentPercentageOwnershipValue)) {
                outVal = "0.00";
            } else {
                outVal = TickerTracker.currentPercentageOwnershipValue.toFixed(2);
            }
        } else {
            if (isNaN(TickerTracker.totalValue)) {
                outVal = "0.00";
            } else {
                outVal = TickerTracker.totalValue.toFixed(2);
            }
        }
        output.push("<span style='z-index:0;' data-alt-value='%' class='add-on btn primary unselectable'>$</span><input id='totalAmountInput' type='text' value='" + outVal + "' class='span2'></div></td></tr>");
    }
    output.push("</tbody></table>");
    $("#tickerTrackerTableDiv").html(output.join(""));
    if (TickerTracker.currentPortfolioType == "$") {
       TickerTracker.updateTotalValue();
    }
}

TickerTracker.computeFinalPercentageValues = function () {
    var output = [];
    var tempval = jQuery.extend(true, [], TickerTracker.userTrackingSymbols);
    var percentageMultiplier = 0;
    TickerTracker.noTotalValue = false;
    if (TickerTracker.currentPercentageOwnershipValue == 0 || isNaN(TickerTracker.totalValue)) {
        percentageMultiplier = 1;
        TickerTracker.noTotalValue = true;
    } else {
        if (isNaN(TickerTracker.currentPercentageOwnershipValue)) {
            percentageMultiplier = 1;
        } else {
            percentageMultiplier = TickerTracker.currentPercentageOwnershipValue / 100;
            //percentageMultiplier = 1;
        }
    }
    $.each(tempval, function (i, o) {
        if (TickerTracker.noTotalValue) {
            tempval[i]['ownership_value'] = o.ownership_value;
        } else {
            tempval[i]['ownership_value'] = o.ownership_value * percentageMultiplier;
        }
    });
    return tempval;
};

TickerTracker.showTotalEmphasis = function () {
    setTimeout(function () {
        $("#totalAmountInput, .toggleVal").addClass("showEmphasis");
    },1);
    setTimeout(function () {
        $("#totalAmountInput, .toggleVal").removeClass("showEmphasis");
    },400);
}

Autocomplete = {};
Autocomplete.SearchSource = "methods.php?a=complexstocksearch";
Autocomplete.Parameters = {
    x:0,
    y:0,
    width:0,
    height:0,
    targetTextBox:null
};
Autocomplete.init = function (targetTextBox) {
    Autocomplete.Parameters.targetTextBox = targetTextBox;
    Autocomplete.generateSuggestionBox();
    Autocomplete.positionSuggestionBox();
    Autocomplete.monitorChanges();

};
Autocomplete.generateSuggestionBox = function () {
    $("body").append("<div id='autocomplete' style='position:absolute;display:none; overflow:none;' class='alert-message info'></div>");
};
Autocomplete.positionSuggestionBox = function () {
    var offset = $(Autocomplete.Parameters.targetTextBox).offset();
    var positionLeft = offset.left;
    var positionTop = offset.top;
    var elementWidth = $(Autocomplete.Parameters.targetTextBox).width();
    var elementHeight = $(Autocomplete.Parameters.targetTextBox).height();
    $("#autocomplete").css({
        left: positionLeft,
        top: (positionTop+elementHeight+10),
        width: 500
    });
}

Autocomplete.monitorChanges = function () {
    $(Autocomplete.Parameters.targetTextBox).on("keyup change", function (e) {
        if (e.keyCode == 13) {
            var symbol_name = $("#searchTextBox").val();
            TickerTracker.addManualSymbol(symbol_name);
            $("#searchTextBox").val("");
            return false;
        }
        var currentValue = $(this).val();
        if (currentValue.length > 2) {
            Autocomplete.search(currentValue);
            Autocomplete.showSuggestions();
        } else {
            Autocomplete.hideSuggestions();
        }
    });
    $(Autocomplete.Parameters.targetTextBox).on("blur", function () {
        Autocomplete.hideSuggestions();
    });
    $(Autocomplete.Parameters.targetTextBox).on("focus", function () {
        var current_value = $(this).val();
        if(current_value != "Enter Symbol" && current_value.length>2) {
            Autocomplete.showSuggestions();
        }
    });
    $(window).on("resize", function () {
        Autocomplete.positionSuggestionBox();
    });
    $("#autocomplete").on("click", "div", function () {
        var parent_element = $(this).closest("div");
        var symbolId = ($(parent_element).attr("data-symbol-id")) - 0;
        TickerTracker.addSymbol(symbolId);
        setTimeout(function () {
            Autocomplete.hideSuggestions();
        },1);
    });

}

Autocomplete.search = function (searchString, callback) {
    $.ajax({
        type: "GET",
        dataType: "JSON",
        url: Autocomplete.SearchSource,
        data: { s: searchString },
        success: function (data) {
            Autocomplete.generateSuggestionBoxContents(data.response.data);
        }
    });
}

Autocomplete.generateSuggestionBoxContents = function (data) {
    if(data.length==0) {
        //$("#autocomplete").html("<div style='font-style:italic;text-align:center;vertical-align:middle;'>No results</div>");
        Autocomplete.hideSuggestions();
        return false;
    }
    var output = ["<div style='margin-bottom:0;'>"];
    $.each(data, function (i, o) {
        SymbolCache.addSymbolToCache(o.symbol_id,o.symbol_name,o.symbol_description);
        output.push("<div class='autocompleteSuggestion' data-symbol-id='"+o.symbol_id+"'><span style='padding-left:10px;width:50px;display:block;float:left;clear:none;'>"+o.symbol_name+"</span><span style='width:370px;display:block;clear:none;float:left;'>" + o.symbol_description + "</span><span>"+o.exchange_name+"</span></div>");
    });
    output.push("</div>");
    $("#autocomplete").html(output.join(""));
};

Autocomplete.hideSuggestions = function () {
    $("#autocomplete").fadeOut(100, function () {
    });
}

Autocomplete.showSuggestions = function () {
    $("#autocomplete").fadeIn(100);
}

Autocomplete.clickSelection = function () {

};

Database = {};

Database.insertNewStocks = function () {

    if (Database.cleanData() == false) {
        return false;
    }

    TickerTracker.drawTable();
    Database.commitAjaxInsertNewStocks();
};

Database.confirmTypeInconsistency = function () {
    $("#portfolioInconsistent").modal("show");
};

Database.commitAjaxInsertNewStocks = function () {
    var upload_symbols = TickerTracker.currentPortfolioType == "$" ? TickerTracker.userTrackingSymbols : TickerTracker.computeFinalPercentageValues();
    var totalValue = TickerTracker.currentPortfolioType == "%" ? TickerTracker.currentPercentageOwnershipValue : TickerTracker.totalValue;
    var upload_object = { symbols: upload_symbols, emailFrequency: $("input[type=radio]:checked").val(), emailAddress: $("#user_email").val(), noTotalVal: TickerTracker.noTotalValue, totalVal: totalValue };
    var upload_string = JSON.stringify(upload_object);
    //debugger;
    //return false;
    $.ajax({
        type: "post",
        dataType: "json",
        url: "methods.php?a=insertNewStocks",
        data: { d: upload_string },
        success: function (data) {
            $("#showSaved").modal("show");
        }
    });
}

Database.resetPortfolio = function () {
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/methods.php?a=resetportfolio",
        success: function (data) {
            window.location = "/";
        }
    });
}

Database.cleanData = function () {
    var abort = false;
    var mixedTypes = false;
    var lastType = null;
    var currentType = null;
    var percentageVal = 0;
    var amountSum = 0;
    var count = 0;
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {

        if(abort) {
            return false;
        }
        
        count++;

        o.ownership_value = o.ownership_value + "";

        if(lastType == null) {
            lastType = o.ownership_type;
        }

        currentType = o.ownership_type;

        if(lastType != currentType) {
            alert("Your portfolio currently utilizes both percentages and absolute values.\r\nPlease update your portfolio to use only one type.");
            abort= true;
            return false;
        }

        if(currentType == "%") {
            percentageVal += parseFloat(o.ownership_value);
        }else{
            amountSum += parseFloat(o.ownership_value);
        }


        if (o.ownership_value == null || o.ownership_value == "" || o.ownership_value.replace(/[,]/gi, "").match(/[0-9]{1,99}/gi).length < 1) {
            abort = true;
            alert("Invalid ownership value detected for " + o.symbol_description);
            return false;
        } else {
            var valZero = parseInt(o.ownership_value,10);
            if(valZero <= 0) {
                alert("Your ownership value for " + o.symbol_description + " is at or below 0. Only positive values are allowed.");
                abort = true;
                return false;
            }
            var working_val = parseFloat(o.ownership_value.replace(/,/gi, "")) + "";
            if ((working_val.match(/[.]/gi) || []).length > 1) {
                alert("Your ownership value for " + o.symbol_description + " has too many periods.");
                abort = true;
                return false;
            }
            if (working_val.match(/[.]/gi) == null) {
                working_val += ".00";
            }
            TickerTracker.userTrackingSymbols[i].ownership_value = working_val;
        }
        

    });

    if(count == 0) {
        alert("Your portfolio contains no stocks, please update your portfolio to contain at least one stock.");
        abort = true;
        return false;
    }
    
    if(lastType == "%" && percentageVal>100.5) {
        if(!abort) {
            alert("The sum of your portfolio percentages is greater than 100.\r\nPlease update your portfolio with the appropriate values to proceed.");
            return false;
        }
        abort = true;
    }

    if(lastType == "%" && percentageVal<99.5) {
        if(!abort) {
            alert("The sum of your portfolio percentages is less than 100.\r\nPlease update your portfolio with the appropriate values to proceed.");
            return false;
        }
        abort = true;
    }

    if(lastType == "$" && amountSum == 0) {
        if(!abort) {
            alert("Your portfolio ownership value must be greater than $0 to save.\r\nPlease update your portfolio with your ownership to continue.");
            return false;
        }
        abort = true;
    }

    if(lastType != TickerTracker.lastPortfolioType && TickerTracker.lastPortfolioType != null && TickerTracker.allowInconsistency != true) {
        Database.confirmTypeInconsistency();
        return false;
    }

    if (abort === true) {
        return false;
    } else {
        return true;
    }

}


Array.prototype.remove = function (from, to) {
    var rest = this.slice((to || from) + 1 || this.length);
    this.length = from < 0 ? this.length + from : from;
    return this.push.apply(this, rest);
};

String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}
