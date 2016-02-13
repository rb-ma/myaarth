$(document).ready(function () {
    Autocomplete.init($("#searchTextBox"));
	var gbtn = false;	
    $("body").on("click", ".removeStock", function () {
        var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
        TickerTracker.removeSymbol(symbol_index);
    });
	
	$("body").on("keydown change", function(e){
		if(modal_showing){
			if(e.keyCode == 9 || e.keyCode == 32){
				return false;	
			}
		}
	});
	
	 $("body").on("click", ".stockInfo", function () {
		if(!modal_showing){
			modal_showing = true;
			$("#stockInfoModal").modal("show");
			var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
			TickerTracker.printStockInfo(symbol_index);
		}
    });
	
	$("#closeStockInfo").on("click", function(){
		$("#stockInfoModal").modal("hide");
		modal_showing = false;
	});

	$("body").on("click", ".portfolioInfo", function () {
		if(!modal_showing){
			modal_showing = true;
			$("#portfolioInfoModal").modal("show");
			var portfolio_index = $(this).closest("tr").attr("portfolio-tracker-index") - 0;
			TickerTracker.printPortfolioInfo(portfolio_index);
		}
    });
	
	$("#closePortfolioInfo").on("click", function(){
		$("#portfolioInfoModal").modal("hide");
		modal_showing = false;
	});

    $("body").on("keyup change", ".stockValue", function () {
        var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
        var value = $(this).val();
        TickerTracker.updateSymbolOwnership(symbol_index, value);
    });
	
	$("body").on("keyup change", ".stockType", function () {
        var symbol_index = $(this).closest("tr").attr("data-tracker-index") - 0;
        var value = $(this).val();
        TickerTracker.updateSymbolPortfolioType(symbol_index, value);
    });

    $("#finishButton").on("click", function () {
		if(!modal_showing){
			modal_showing = true;
			$("#confirmModal").modal("show");
		}
	});
	
	$("#groupButton").on("click", function () {
		//alert(gbtn);
		if(gbtn == false) {
    		TickerTracker.drawTableByPortfolio();
		}
		if(gbtn == true) {
			TickerTracker.drawTable();
		}
		gbtn = !gbtn;
		//alert(gbtn);
	});
	
	$("#closeConfirm").on("click", function () {
        $("#confirmModal").modal("hide");
		modal_showing = false;
    });
	
	$("#savePortfolio").on("click", function () {
        $("#confirmModal").modal("hide");
		modal_showing = false;
        Database.insertNewStocks();
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
	
	$("#trackPerformance").on("click", function () {
       $("#trackModal").modal("show");
    });
	
	$("#exitTrackModal").on("click", function () {
       $("#trackModal").modal("hide");
    });
	
	$("#logout").on("click", function () {
        if(!modal_showing){
			modal_showing = true;
			$("#confirmLogout").modal("show");
		}
	});
	
	$("#confirmLogout").on("click", function () {
        $("#confirmLogout").modal("hide");
    	modal_showing = false;
	});
	
	$("#cancelLogout").on("click", function () {
        $("#confirmLogout").modal("hide");
		modal_showing = false;
    });
	
	$("#closePortolioError").on("click", function (){
		$("#portfolioErrorModal").modal("hide");
		modal_showing = false;
	});

    $("#cancelInconsistency").on("click", function () {
        $("#portfolioInconsistent").modal("hide");
		modal_showing = false;
    });

    $("#cancelEmpty").on("click", function () {
        $("#confirmTotalOwnershipEmpty").modal("hide");
    });

    $("#confirmModal, #showSaved, #portfolioInconsistent, #confirmTotalOwnershipEmpty, #confirmLogout, #portfolioErrorModal, #emptyModal, #refreshModal, #trackModal, #stockInfoModal, #portfolioInfoModal").modal({
        backdrop: true,
        show: false,
        keyboard: false
    });

    $("#closeSaved").on("click", function () {
        $("#showSaved").modal("hide");
		modal_showing = false;
		window.location="tracker.php";
    });

    $("#refreshPage").on("click", function () {
		if(!modal_showing){
	        modal_showing = true;
			$("#refreshModal").modal("show");
		}
	});
	
	$("#confirmRefreshPortfolioButton").on("click", function () {
		$("#refreshModal").modal("hide");
		modal_showing = false;
		window.location="tracker.php";
    });
	
	 $("#cancelRefreshPortfolioButton").on("click", function () {
		$("#refreshModal").modal("hide");
		modal_showing = false;
    });

    $("#resetPortfolioButton").on("click", function () {
		if(!modal_showing){
			modal_showing = true;
			$("#emptyModal").modal("show");
		}
	});
	
	 $("#confirmResetPortfolioButton").on("click", function () {
		$("#emptyModal").modal("hide");
        modal_showing = false;
		Database.resetPortfolio();
    });
	
	 $("#cancelResetPortfolioButton").on("click", function () {
		$("#emptyModal").modal("hide");
		modal_showing = false;
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
SymbolCache.addSymbolToCache = function (symbol_id, symbol_name, symbol_description, portfolio_type) {
    SymbolCache.Cache.push({symbol_id:symbol_id,symbol_name:symbol_name,symbol_description:symbol_description,portfolio_type:portfolio_type});
};
SymbolCache.Cache = [];

TickerTracker = {};
TickerTracker.userTrackingSymbols = [];
var num_tickers = 0;
TickerTracker.totalValue = 0;
TickerTracker.currentPercentageOwnershipValue = 0;

TickerTracker.lastPortfolioType = "$";
TickerTracker.currentPortfolioType = "$";
TickerTracker.allowInconsistency = false
TickerTracker.noTotalValue = false;

//'Unknown', '401(k)', 'Traditional IRA', 'Roth IRA', 'SIMPLE IRA', 'SEP-IRA', 'Solo 401(k)', 'Roth 401(k)', '403(b)', 'Other'
var portfolioType = ["","Unknown","401(k)","Traditional IRA","Roth IRA","SIMPLE IRA","SEP-IRA","Solo 401(k)","Roth 401(k)","403(b)","Other"];

TickerTracker.addSymbol = function (symbol_id) {
    TickerTracker.appendSymbol(symbol_id);
    $("#searchTextBox").val("").trigger("blur");
    TickerTracker.drawTable();
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
	TickerTracker.drawTable();
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

TickerTracker.printStockInfo = function(symbol_id){	
	var this_symbol = TickerTracker.userTrackingSymbols[symbol_id];
	var stock_info = '<r4>' + this_symbol.symbol_name + ':<r4>';
	stock_info += ' <r5>' + this_symbol.symbol_description + '</r5><br/><br/>';
	
	if(this_symbol.ownership_value == null){
		stock_info += '<r6>Current Amount: Not present in last saved portfolio</r6><br/><br/>';
	} else {
		stock_info += '<r6>Current Amount: $' + this_symbol.ownership_value + '</r6><br/><br/>';
	}
	
	if(this_symbol.portfolio_type == null){
		stock_info += '<r6>Portfolio Type: Not present in last saved portfolio</r6><br/><br/>';
	} else if(this_symbol.portfolio_type == ''){
		stock_info += '<r6>Portfolio Type: None selected in last saved portfolio</r6><br/><br/>';
	} else {
		stock_info += '<r6>Portfolio Type: ' + this_symbol.portfolio_type + '</r6><br/><br/>';
	}
	
	
	stock_info += '<r6>Yesterday\'s Return: </r6>';
	
	if(this_symbol.last_change != null){
		if(this_symbol.last_change > 0.0){
			stock_info += '<r6_green><img src="Style/Images/up.png" width=\"9\" height=\"13\"/>+' + this_symbol.last_change + '</r6_green><r6>%</r6><br/>';
		} else {
			stock_info += '<r6_red><img src="Style/Images/down.png" width=\"9\" height=\"13\"/>' + this_symbol.last_change + '</r6_red><r6>%</r6><br/>'
		}
	} else {
		stock_info += '<r6>N/A</r6><br/>';		
	}
	
	stock_info += '<r6>Year-To-Date Return: </r6>';
	if(this_symbol.sofyr_return != null){
		if(this_symbol.sofyr_return > 0.0){
			stock_info += '<r6_green><img src="Style/Images/up.png" width=\"9\" height=\"13\"/>+' + this_symbol.sofyr_return + '</r6_green><r6>%</r6><br/>';
		} else {
			stock_info += '<r6_red><img src="Style/Images/down.png" width=\"9\" height=\"13\"/>' + this_symbol.sofyr_return + '</r6_red><r6>%</r6><br/>'
		}
	} else {
		stock_info += '<r6>N/A</r6><br/>';		
	}
	
	stock_info += '<r6>Return since Inception: </r6>';
	if(this_symbol.first_return != null){
		if(this_symbol.first_return > 0.0){
			stock_info += '<r6_green><img src="Style/Images/up.png" width=\"9\" height=\"13\"/>+' + this_symbol.first_return + '</r6_green><r6>%</r6><br/>';
		} else {
			stock_info += '<r6_red><img src="Style/Images/down.png" width=\"9\" height=\"13\"/>' + this_symbol.first_return + '</r6_red><r6>%</r6><br/>'
		}
	} else {
		stock_info += '<r6>N/A</r6><br/>';	
	}
	
	document.getElementById("stockInfoDiv").innerHTML = stock_info;
}

TickerTracker.updateSymbolPortfolioType = function(symbol_id, value){
	var userPortfolioType = jQuery.extend({}, TickerTracker.userTrackingSymbols[symbol_id]);
	//alert(value);
	userPortfolioType.portfolio_type = value;
	TickerTracker.userTrackingSymbols[symbol_id] = userPortfolioType;
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
	this.portfolio_type = null;
	this.last_change = null;
	this.sofyr_return = null;
	this.first_return = null;
}

TickerTracker.appendSymbol = function (symbol_id) {
    $.each(SymbolCache.Cache, function (x, y) {
        if (symbol_id === y.symbol_id) {
            var temp_symbol = new TickerTracker._symbol();
            temp_symbol.symbol_id = y.symbol_id;
            temp_symbol.symbol_name = y.symbol_name;
            temp_symbol.symbol_description = y.symbol_description;
            temp_symbol.ownership_type = TickerTracker.currentPortfolioType;
			//alert(y.portfolio_type);
			temp_symbol.portfolio_type = y.portfolio_type;
			temp_symbol.last_change = y.last_change;
			temp_symbol.sofyr_return = y.sofyr_return;
			temp_symbol.first_return = y.first_return;
			//alert(temp_symbol.portfolio_type);
			TickerTracker.userTrackingSymbols.push(temp_symbol);
            return false;
        }
    });

}

var userTrackingPortfolios = [];

TickerTracker._portfolio = function() {
	this.name				= "";
	this.total_value 		= 0.0;
	this.percent_of_total 	= 0.0;
	this.last_change	 	= 0.0;
	this.sofyr_return	 	= 0.0;
	this.first_return		= 0.0;
	this.symbols 			= [];
}

TickerTracker.printPortfolioInfo = function(portfolio_id){
	var this_portfolio = userTrackingPortfolios[portfolio_id];
	var output = "<r4>" + this_portfolio.name + "</r4><br/><br/>";
	output += "<r5>Member Stocks/Funds:</r5><br/><r6>";
	
	for(var i = 0; i < this_portfolio.symbols.length; i++){
		output += "(" + this_portfolio.symbols[i].symbol_name + ")" + " " + this_portfolio.symbols[i].symbol_description;
		if(i != (this_portfolio.symbols.length-1)){
			output += ", ";
		}
	}

	output += "</r6><br/><br/><r5>Amount: $" + this_portfolio.total_value + " (" + this_portfolio.percent_of_total + "%)</r5><br/><br/>";
	
	var rtn = this_portfolio.last_change;
	if(rtn > 0.0){
		output += "<r5>Yesterday's Return: <font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/>+" + rtn + "</font>%<br/>";
	} else {
		output += "<r5>Yesterday's Return: <font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/>" + rtn + "</font>%<br/>";
	}
	
	var rtn = this_portfolio.sofyr_return;
	if(rtn > 0.0){
		output += "<r5>Year-To-Date Return: <font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/>+" + rtn + "</font>%<br/>";
	} else {
		output += "<r5>Year-To-Date Return: <font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/>" + rtn + "</font>%<br/>";
	}
	
	var rtn = this_portfolio.first_return;
	if(rtn > 0.0){
		output += "<r5>Return since Inception: <font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/>+" + rtn + "</font>%<br/>";
	} else {
		output += "<r5>Return since Inception: <font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/>" + rtn + "</font>%<br/>";
	}
	
	output += "</r5>";
	document.getElementById("portfolioInfoDiv").innerHTML = output;
}

TickerTracker.drawTableByPortfolio = function() {
	userTrackingPortfolios = [];
	var output = ['<table><thead><tr><th>Portfolio Type</th><th style="text-align:right;">Amount</th><th style="text-align:right;">Return (Yesterday)</th><th style="text-align:right;">Return (YTD)</th><th style="text-align:right;">Return (Inception)</th></tr></thead><tbody>'];
	
	var total_sum = 0.0, total_last_change = 0.0, total_sofyr_return = 0.0, total_first_return = 0.0;
	var num_portfolios = portfolioType.length;
	
	// this will hold the (portfolio_type, total_value, yesterday's return, 
	var portfolios_done = new Array(num_portfolios);
	
	// construct a 2d array
	for(var i = 0; i < num_portfolios; i++){
		portfolios_done[i] = new Array();	
	}
	
	// put all of the appropriate symbols in the appropriate groups
	for(var i = 0; i < num_portfolios; i++){
		var curr_portfolio_type = portfolioType[i].toString();
		var holder = new TickerTracker._portfolio();
		holder.name = portfolioType[i].toString();
		
		for(var j = 0; j < TickerTracker.userTrackingSymbols.length; j++){	
			if(TickerTracker.userTrackingSymbols[j].portfolio_type.toString() == curr_portfolio_type){
				var this_symbol = TickerTracker.userTrackingSymbols[j];	
				var amount = parseFloat(this_symbol.ownership_value);

				holder.symbols.push(TickerTracker.userTrackingSymbols[j]);
				holder.total_value += amount;
				total_sum += amount;
				
				holder.last_change += amount*parseFloat(this_symbol.last_change);
				holder.sofyr_return+= amount*parseFloat(this_symbol.sofyr_return);
				holder.first_return+= amount*parseFloat(this_symbol.first_return);	
				
				total_last_change += amount*parseFloat(this_symbol.last_change);
				total_sofyr_return+= amount*parseFloat(this_symbol.sofyr_return);
				total_first_return+= amount*parseFloat(this_symbol.first_return);	
			}
		}
		if(holder.total_value != 0.0){
			if(i == 0){
				output.push("<tr portfolio-tracker-index='" + i + "'><td><r2>None Selected</r2></td>");	
			} else {
				output.push("<tr portfolio-tracker-index='" + i + "'><td><r2>" + curr_portfolio_type + "</r2></td>");	
			}
			
			holder.total_value = holder.total_value.toFixed(2);
			output.push("<td style='text-align:right'>$" + holder.total_value + "</td>");			
			
			holder.last_change /= holder.total_value;
			holder.last_change = holder.last_change.toFixed(2);
			if(holder.last_change > 0.0){
				output.push("<td style='text-align:right;'><font color=\"green\"><img src='Style/Images/up.png' width=\"9\" height=\"13\"/>+" + holder.last_change + "</font>%</td>");
			} else {
				output.push("<td style='text-align:right;'><font color=\"red\"><img src='Style/Images/down.png' width=\"9\" height=\"13\"/>" + holder.last_change + "</font>%</td>");
			}
			//alert("so much is working");
			
			holder.sofyr_return /= holder.total_value;
			holder.sofyr_return = holder.sofyr_return.toFixed(2);
			if(holder.sofyr_return > 0.0){
				output.push("<td style='text-align:right;'><font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/>+" + holder.sofyr_return + "</font>%</td>");
			} else {
				output.push("<td style='text-align:right;'><font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/>" + holder.sofyr_return + "</font>%</td>");
			}
			
			holder.first_return /= holder.total_value;
			holder.first_return = holder.first_return.toFixed(2);
			if(holder.first_return > 0.0){
				output.push('<td style="text-align:right;"><font color="green"><img src="Style/Images/up.png" width=\"9\" height=\"13\"/>+' + holder.first_return + '</font>%</td><td><button class="btn primary portfolioInfo" style="font-size:12px;height:25px; width:25px; padding:0;">?</button></td></tr>');
			} else {
				output.push('<td style="text-align:right;"><font color="red"><img src="Style/Images/down.png" width=\"9\" height=\"13\"/>' + holder.first_return + '</font>%</td><td><button class="btn primary portfolioInfo" style="font-size:12px;height:25px; width:25px; padding:0;">?</button></td></tr>');
			}
			
			holder.percent_of_total = ((holder.total_value/TickerTracker.totalValue)*100.0).toFixed(2);
		}
		
		userTrackingPortfolios.push(holder);
		//console.log(userTrackingPortfolios);
	}
	
	//total_sum = total_sum.toFixed(2);

	total_last_change /= total_sum;
	total_last_change = total_last_change.toFixed(2);
	total_sofyr_return /= total_sum;
	total_sofyr_return = total_sofyr_return.toFixed(2);
	total_first_return /= total_sum;
	total_first_return = total_first_return.toFixed(2);
	
	output.push("<tr><td style='text-align:right;'><r5>Total</r5></td><td style='text-align:right;'><r5>$" + total_sum.toFixed(2) + "</r5></td>");
	
	if(total_last_change > 0.0){
		output.push("<td style='text-align:right;'><font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/><r5>+" + total_last_change + "%</r5></font></td>");
	} else {
		output.push("<td style='text-align:right;'><font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/><r5>" + total_last_change + "%</r5></font></td>");
	}
	
	if(total_sofyr_return > 0.0){
		output.push("<td style='text-align:right;'><font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/><r5>++" + total_sofyr_return + "%</r5></font></td>");
	} else {
		output.push("<td style='text-align:right;'><font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/><r5>" + total_sofyr_return + "%</r5></font></td>");
	}
	
	if(total_first_return > 0.0){
		output.push("<td style='text-align:right;'><font color='green'><img src='Style/Images/up.png' width=\"9\" height=\"13\"/><r5>+" + total_first_return + "%</r5></font></td><td> </td></tr>");
	} else {
		output.push("<td style='text-align:right;'><font color='red'><img src='Style/Images/down.png' width=\"9\" height=\"13\"/><r5>" + total_first_return + "%</r5></font></td><td> </td></tr>");
	}

	output.push("</tbody></table>");
    $("#tickerTrackerTableDiv").html(output.join(""));
}

TickerTracker.drawTable = function () {
    var output = ["<table><thead><tr><th>Ticker</th><th>Description</th><th>Amount</th><th>Portfolio Type</th></tr></thead><tbody>"];
	var port_type_arr = new Array();
	
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
		var len = o.symbol_description.length;
		//alert(len);
		var font_prepend;
		var font_append;
		if(len > 35){
			font_prepend = "<r>";
			font_append = "</r>";
		} else {
			font_prepend = "<r2>";
			font_append = "</r2>";	
		}
		
		// dynamic ids for each of the selects
		var select_id = "select_num"+i;
				
        output.push("<tr data-tracker-index='" + i + "'><td><r2>" + o.symbol_name + "</r2></td><td>" + font_prepend + o.symbol_description + font_append + "</td><td><div class='input-prepend'><span class='add-on primary toggleVal unselectable' style='z-index:0;'>$</span><input type='text' class='span2 stockValue' style='min-width:100px;max-width:100px;' value='" + (o.ownership_value || "") + "'></div></td><td><select style='min-width:100px; max-width:100px; font-size:13px;' class='stockType'>")
		
		var i = 0;
		var port_type = o.portfolio_type;
		for(i = 0; i < 10; i++){
			if(portfolioType[i] == port_type){
				output.push("<option selected>");	
			} else {
				output.push("<option>");	
			}
			if(i != 0){
				output.push(portfolioType[i]);	
			}
			output.push("</option>");
		}
		
		output.push("</select></td><td><button class='btn primary stockInfo' style='font-size:12px;height:25px; width:25px; padding:0;'>?</button> <button class='btn error removeStock' style='font-size:12px;height:25px; width:25px; padding:0;'>X</button></tr>");
    });
	
	if(TickerTracker.userTrackingSymbols.length != 0){
		output.push("<tr><td colspan='2' style='text-align:right;padding-top:2%;'><r5>Total </r5></td><td><div class='input-prepend'>");
		if (isNaN(TickerTracker.totalValue)) {
			outVal = "0.00";
		} else {
			outVal = TickerTracker.totalValue.toFixed(2);
		}
		
		output.push("<span class='add-on btn primary unselectable disabled' style='z-index:0;'>$</span><input id='totalAmountInput' type='text' disabled='disabled' value='0.00' style='min-width:100px; max-width:100px;'></td><td></td><td></td></div></td></tr>");
		
	}
	
    output.push("</tbody></table>");
    $("#tickerTrackerTableDiv").html(output.join(""));
    TickerTracker.updateTotalValue();
}

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
    var upload_symbols = TickerTracker.userTrackingSymbols;
    var totalValue = TickerTracker.totalValue;
	var upload_object = { symbols: upload_symbols, emailFrequency: $("input[type=radio]:checked").val(), emailAddress: $("#user_email").val(), noTotalVal: TickerTracker.noTotalValue, totalVal: totalValue };
    var upload_string = JSON.stringify(upload_object);
	//return false;
    //debugger;
    //return false;
    $.ajax({
        type: "post",
        dataType: "json",
        url: "methods_v2.php?a=insertNewStocks",
        data: { d: upload_string },
        success: function (data) {
            
        }
    });
	if(!modal_showing){
		modal_showing = true;
		$("#showSaved").modal("show");
	}
}

Database.resetPortfolio = function () {
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/methods.php?a=resetportfolio",
        success: function (data) {
            //window.location="tracker_v2.php";
			window.location="tracker.php";
        }
    });
}

Database.cleanData= function () {
	var count = 0;
	var abort = false;
	
	if(TickerTracker.userTrackingSymbols.length == 0){
		//alert("no symbols");
		if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
		abort = true;
		return false;	
	}
	
    $.each(TickerTracker.userTrackingSymbols, function (i, o) {
		// get the string value
		//alert(o.ownership_value);
		
		var str_value = o.ownership_value + '';
		var num_value = o.ownership_value;
	
		// someone gave no input at all
		if(str_value.length == 0){
			//alert("no value");
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			abort = true;
			return false;
		}
		
		// take out sign character only from beginning
		var neg_value = false;
		if(str_value.charAt(0) == '-'){
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			abort = true;
			return false;
			neg_value = true;
			str_value = str_value.substr(1);
		} else if(str_value.charAt(0) == '+'){
			neg_value = false;
			str_value = str_value.substr(1);	
		} else {
			neg_value = false;
		}
		
		// someone only input a +/- sign
		if(str_value.length == 0){
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			abort = true;
			return false;
		}
		
		// take out leading zeros
		while(str_value.charAt(0) == '0'){
			str_value = str_value.substr(1);
				
		}
		
		// someone gave something like (+/-)00...0
		if(str_value.length == 0){
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			abort = true;
			return false;
		}
		
		// take out all the commas
		str_value = str_value.replace(/\,/g,'');
		if(str_value.length == 0){
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			abort = true;
			return false;
		}
		
		// strip all non_numeric characters and non-decimal point
		var str_value_stripped = str_value.replace(/[^\d\.]/g, '');

		// took out at least one invalid character	
		if(str_value_stripped.length != str_value.length){
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			abort = true;
			return false;
		}
		
		// check for too many decimal points
		var str_value_split = str_value.split('.');
		var num_periods = str_value_split.length - 1;
		if(num_periods > 1){
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			abort = true;
			return false;
		}
	
		if(parseFloat(str_value) == 0.0){
			if(!modal_showing){modal_showing=true;$("#portfolioErrorModal").modal("show");}
			//alert("Your portfolio contains at least one invalid Amount.\r\nPlease ensure that all your amounts are positive, numeric values.");
			abort = true;
			return false;
		}
	
		// at this point, we should have a valid positive or negative number which may or may not be a single/double precision float value
        count++;
		if(num_periods == 0){
			str_value += ".00";	
		}
		TickerTracker.userTrackingSymbols[i].ownership_value = parseFloat(str_value);
		
    });

    if(count == 0) {
        //alert("Your portfolio contains no valid stocks, please update your portfolio to contain at least one stock.");
        abort = true;
		return false;
    }
	
	// gotten here, no issues
	if(abort == true){
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
