var e = null; $(document).U(function () { Autocomplete.F(); $("body").d("click", ".removeStock", function () { var a = $(this).z("tr").m("data-tracker-index") - 0; TickerTracker.I(a) }); $("body").d("keyup blur change", ".stockValue", function () { var a = $(this).z("tr").m("data-tracker-index") - 0, c = $(this).p(); TickerTracker.K(a, c) }); $("#finishButton").d("click", function () { Database.G() }) }); SymbolCache = {}; SymbolCache.t = function (a, c, b) { SymbolCache.l.push({ g: a, h: c, a: b }) }; SymbolCache.l = []; TickerTracker = { b: [] }; TickerTracker.s = function (a) { TickerTracker.v(a); TickerTracker.j() }; TickerTracker.I = function (a) { TickerTracker.b.remove(a, a); TickerTracker.j() }; TickerTracker.K = function (a, c) { var b = TickerTracker.b[a]; b.f = c; TickerTracker.b[a] = b }; TickerTracker.r = function () { this.f = this.a = this.h = this.g = e }; TickerTracker.v = function (a) { $.k(SymbolCache.l, function (c, b) { if (a === b.g) { var d = new TickerTracker.r; d.g = b.g; d.h = b.h; d.a = b.a; TickerTracker.b.push(d); return !1 } }) }; TickerTracker.j = function () { var a = ["<table><thead><tr><th>Ticker</th><th>Company / Mutual Fund Name</th><th>Amount (as of date)</th><th></th></tr></thead><tbody>"]; $.k(TickerTracker.b, function (c, b) { a.push("<tr data-tracker-index='" + c + "'><td>" + b.h + "</td><td>" + b.a + "</td><td><div class='input-prepend'><span class='add-on' style='z-index:0;'>$</span><input type='text' class='span2 stockValue' value='" + (b.f || "") + "'></div></td><td><button class='btn error removeStock'>X</button></td></tr>") }); a.push("</tbody></table>"); $("#tickerTrackerTableDiv").D(a.join("")) }; Autocomplete = { q: "methods.php?a=stocklookup", c: { x: 0, y: 0, width: 0, height: 0, e: e} }; Autocomplete.F = function () { var a = $("#searchTextBox"); Autocomplete.c.e = a; Autocomplete.B(); Autocomplete.n(); Autocomplete.H() }; Autocomplete.B = function () { $("body").append("<div id='autocomplete' style='position:absolute;display:none; overflow:auto;' class='alert-message info'></div>") }; Autocomplete.n = function () { var a = $(Autocomplete.c.e).T(), c = a.left, a = a.top; $(Autocomplete.c.e).width(); var b = $(Autocomplete.c.e).height(); $("#autocomplete").M({ left: c, top: a + b + 10, width: 500, height: 200 }) }; Autocomplete.H = function () { $(Autocomplete.c.e).d("keyup change", function () { var a = $(this).p(); 2 < a.length ? (Autocomplete.search(a), Autocomplete.o()) : Autocomplete.i() }); $(Autocomplete.c.e).d("blur", function () { Autocomplete.i() }); $(Autocomplete.c.e).d("focus", function () { Autocomplete.o() }); $(window).d("resize", function () { Autocomplete.n() }); $("#autocomplete").d("click", "tr", function () { var a = $(this).m("data-symbol-id") - 0; TickerTracker.s(a); Autocomplete.i() }) }; Autocomplete.search = function (a) { $.u({ type: "GET", dataType: "JSON", url: Autocomplete.q, data: { V: a }, J: function (a) { 0 < a.response.data.length ? Autocomplete.C(a.response.data) : Autocomplete.i() } }) }; Autocomplete.C = function (a) { var c = ["<table>"]; $.k(a, function (a, d) { SymbolCache.t(d.g, d.h, d.a); c.push("<tr class='autocompleteSuggestion' data-symbol-id='" + d.g + "'><td>" + d.h + "</td><td>" + d.a + "</td><td>" + d.Q + "</td></tr>") }); c.push("</table>"); $("#autocomplete").D(c.join("")) }; Autocomplete.i = function () { $("#autocomplete").S(100, function () { }) }; Autocomplete.o = function () { $("#autocomplete").R(100) }; Autocomplete.L = function () { }; Database = {}; Database.G = function () { Database.w() || (TickerTracker.j(), Database.A()) }; Database.A = function () { var a = { W: TickerTracker.b, P: "na", O: $("#user_email").p() }, a = JSON.stringify(a); $.u({ type: "get", dataType: "json", url: "methods.php?a=insertNewStocks", data: { N: a }, J: function (a) { console.log(a) } }) }; Database.w = function () { var a = !1; $.k(TickerTracker.b, function (c, b) { if (b.f == e || 1 > b.f.replace(/[,]/gi, "").match(/[0-9]{1,99}/gi)) return a = !0, alert("Invalid ownership value detected for " + b.a), !1; var d = parseFloat(b.f.replace(/,/gi, "")) + ""; if (1 < (d.match(/[.]/gi) || []).length) return alert("Your ownership value for " + b.a + " has too many periods."), a = !0, !1; d.match(/[.]/gi) == e && (d += ".00"); TickerTracker.b[c].f = d }); return !0 === a ? !0 : !1 }; Array.prototype.remove = function (a, c) { var b = this.slice((c || a) + 1 || this.length); this.length = 0 > a ? this.length + a : a; return this.push.apply(this, b) };