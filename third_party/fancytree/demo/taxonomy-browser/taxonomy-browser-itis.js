/*!
 * Fancytree Taxonomy Browser
 *
 * Copyright (c) 2015, Martin Wendt (http://wwWendt.de)
 *
 * Released under the MIT license
 * https://github.com/mar10/fancytree/wiki/LicenseInfo
 *
 * @version @VERSION
 * @date @DATE
 */

;(function($, window, document, undefined) {

/*globals console, Handlebars */

"use strict";

/*******************************************************************************
 * Private functions and variables
 */

var taxonTree, searchResultTree,
	timerMap = {},
	tmplDetails, // =
	USER_AGENT = "Fancytree Taxonomy Browser/1.0",
	ITIS_URL = "http://www.itis.gov/ITISWebService/jsonservice/",
	glyphOpts = {
		map: {
			doc: "glyphicon glyphicon-file",
			docOpen: "glyphicon glyphicon-file",
			checkbox: "glyphicon glyphicon-unchecked",
			checkboxSelected: "glyphicon glyphicon-check",
			checkboxUnknown: "glyphicon glyphicon-share",
			dragHelper: "glyphicon glyphicon-play",
			dropMarker: "glyphicon glyphicon-arrow-right",
			error: "glyphicon glyphicon-warning-sign",
			expanderClosed: "glyphicon glyphicon-plus-sign",
			expanderLazy: "glyphicon glyphicon-plus-sign",  // glyphicon-expand
			expanderOpen: "glyphicon glyphicon-minus-sign",  // glyphicon-collapse-down
			folder: "glyphicon glyphicon-folder-close",
			folderOpen: "glyphicon glyphicon-folder-open",
			loading: "glyphicon glyphicon-refresh glyphicon-spin"
		}
	};

// Load and compile handlebar templates

$.get( "details.tmpl", function( data ) {
	tmplDetails = Handlebars.compile(data);
});

/** Update UI elements according to current status
 */
function updateControls() {
	var query = $.trim($("input[name=query]").val());

	$("#btnPin")
		.attr("disabled", !taxonTree.getActiveNode());
	$("#btnUnpin")
		.attr("disabled", !taxonTree.isFilterActive())
		.toggleClass("btn-success", taxonTree.isFilterActive());
	$("#btnResetSearch")
		.attr("disabled", query.length === 0);
	$("#btnSearch")
		.attr("disabled", query.length < 2);
}

/**
 * Invoke callback after `ms` miliseconds.
 * Any pending action of this type is cancelled before.
 */
function _delay(tag, ms, callback) {
	/*jshint -W040:true */
	var that = this;

	tag = "" + (tag || "default");
	if( timerMap[tag] != null ) {
		clearTimeout(timerMap[tag]);
		delete timerMap[tag];
		// console.log("Cancel timer '" + tag + "'");
	}
	if( ms == null || callback == null ) {
		return;
	}
	// console.log("Start timer '" + tag + "'");
	timerMap[tag] = setTimeout(function(){
		// console.log("Execute timer '" + tag + "'");
		callback.call(that);
	}, +ms);
}

/**
 */
function _callItis(cmd, data) {
	return $.ajax({
		url: ITIS_URL + cmd,
		data: $.extend({
			jsonp: "itis_data"
		}, data),
		cache: true,
		headers: { "Api-User-Agent": USER_AGENT },
		jsonpCallback: "itis_data",
		dataType: "jsonp"
	});
}

/**
 */
// function countMatches(query) {
// 	$("#tsnDetails").text("Loading TSN " + tsn + "...");
// 	_callItis("getAnyMatchCount", {
// 		srchKey: query
// 	}).done(function(result){
// 		console.log("updateTsnDetails", result);
// 		$("#tsnDetails").html(tmplDetails(result));
// 		updateControls();
// 	});
// }

/**
 */
function updateTsnDetails(tsn) {
	$("#tsnDetails").addClass("busy");
	// $("#tsnDetails").text("Loading TSN " + tsn + "...");
	$.bbq.pushState({tsn: tsn});

	_callItis("getFullRecordFromTSN", {
		tsn: tsn
	}).done(function(result){
		console.log("updateTsnDetails", result);
		$("#tsnDetails")
			.html(tmplDetails(result))
			.removeClass("busy");

		updateControls();
	});
}

/**
 */
function updateBreadcrumb(tsn, loadTreeNodes) {
	// var $ol = $("ol.breadcrumb").text("...");
	var $ol = $("ol.breadcrumb").addClass("busy");
	_callItis("getFullHierarchyFromTSN", {
		tsn: tsn
	}).done(function(result){
		console.log("updateBreadcrumb", result);
		// Convert to simpler format
		var list = [];
		// Display as <OL> list (for Bootstrap breadcrumbs)
		$ol.empty().removeClass("busy");
		$.each(result.hierarchyList, function(i, o){
			if( o.parentTsn === tsn ) { return; } // skip direct children
			list.push(o.tsn);
			if( o.tsn === tsn ) {
				$ol.append(
					$("<li class='active'>").append(
						$("<span>", {
							text: o.taxonName,
							title: o.rankName
						})));
			} else {
				$ol.append(
					$("<li>").append(
						$("<a>", {
							href: "#tsn=" + o.tsn,
							text: o.taxonName,
							title: o.rankName
						})));
			}
		});
		if( loadTreeNodes ) {
			console.log("updateBreadcrumb - loadKeyPath", list);
			taxonTree.loadKeyPath("/" + list.join("/"), function(node, status){
				// console.log("... updateBreadcrumb - loadKeyPath", status, node);
				switch( status ) {
				case "loaded":
					node.makeVisible();
					break;
				case "ok":
					node.setActive();
					break;
				}
			});
		}
	});
}

/**
 */
function search(query) {
	query = $.trim(query);
	console.log("searching for '" + query + "'...");
	// NOTE:
	// It seems that ITIS searches don't work with jsonp (always return valid
	// but empty result sets).
	// When debugging, make sure cross domain requests are allowed.
	searchResultTree.reload({
		url: ITIS_URL + "searchForAnyMatchPaged",
		data: {
			// jsonp: "itis_data",
			srchKey: query,
			pageSize: 10,
			pageNum: 1,
			ascend: false
		},
		cache: true
		// jsonpCallback: "itis_data",
		// dataType: "jsonp"
	}).done(function(result){
		// console.log("search returned", result);
		// result.anyMatchList
		updateControls();
	});
}


/*******************************************************************************
 * Pageload Handler
 */

$(function(){

$("#taxonTree").fancytree({
	extensions: ["filter", "glyph", "wide"],
	filter: {
		mode: "hide"
	},
	glyph: glyphOpts,
	activeVisible: true,
	source: {
		// We could use getKingdomNames, but that returns an individual JSON format.
		// getHierarchyDownFromTSN?tsn=0 seems to work as well and allows
		// unified parsing in postProcess.
		// url: ITIS_URL + "getKingdomNames",
		url: ITIS_URL + "getHierarchyDownFromTSN",
		data: {
			jsonp: "itis_data",
			tsn: "0"
		},
		cache: true,
		jsonpCallback: "itis_data",
		dataType: "jsonp"
	},
	init: function(event, data) {
		updateControls();
		$(window).trigger("hashchange"); // trigger on initial page load
	},
	lazyLoad: function(event, data) {
		data.result = {
			url: ITIS_URL + "getHierarchyDownFromTSN",
			data: {
				jsonp: "itis_data",
				tsn: data.node.key
			},
			cache: true,
			jsonpCallback: "itis_data",
			dataType: "jsonp"
		};
	},
	postProcess: function(event, data) {
		var response = data.response;

		data.node.info(response);
		data.result = $.map(response.hierarchyList, function(o){
			return o && {title: o.taxonName, key: o.tsn, folder: true, lazy: true};
		});
	},
	activate: function(event, data) {
		$("#tsnDetails").addClass("busy"); //text("...");
		updateControls();
		_delay("showDetails", 1000, function(){
			updateTsnDetails(data.node.key);
			updateBreadcrumb(data.node.key);
		});
	}
});


$("#searchResultTree").fancytree({
	extensions: ["table", "wide"],
	source: [{title: "No Results."}],
	minExpandLevel: 2,
	icon: false,
	table: {
		nodeColumnIdx: 1
	},
	postProcess: function(event, data) {
		var response = data.response;

		data.node.info("pp", response);
		data.result = $.map(response.anyMatchList, function(o){
			if( !o ) { return; }
			var res = { title: o.sciName, key: o.tsn, author: o.author,
						matchType: o.matchType };
			res.commonNames = $.map(o.commonNameList.commonNames, function(o){
					return o && o.commonName ? {name: o.commonName, language: o.language} : undefined;
				});
			return res;
		});
		// console.log("pp2", data.result)
	},
	renderColumns: function(event, data) {
		var node = data.node,
			$tdList = $(node.tr).find(">td"),
			cnList = node.data.commonNames ? $.map(node.data.commonNames, function(o){
					return o.name;
				}) : [];

		$tdList.eq(0).text(node.key);
		$tdList.eq(2).text(cnList.join(", "));
		$tdList.eq(3).text(node.data.matchType);
		$tdList.eq(4).text(node.data.author);
	},
	activate: function(event, data) {
		_delay("activateNode", 1000, function(){
			updateTsnDetails(data.node.key);
			updateBreadcrumb(data.node.key);
		});
	}
});


taxonTree = $("#taxonTree").fancytree("getTree");
searchResultTree = $.ui.fancytree.getTree("#searchResultTree");


// Bind a callback that executes when document.location.hash changes.
// (This code uses bbq: https://github.com/cowboy/jquery-bbq)
$(window).bind( "hashchange", function(e) {
	var tsn = $.bbq.getState( "tsn" );
	console.log("bbq tsn", tsn);
	if( tsn ) {
		updateBreadcrumb(tsn, true);
	}
}); // don't trigger now, since we need the the taxonTree root nodes to be loaded first


$("input[name=query]").keyup(function(e){
	var query = $.trim($(this).val());

	if(e && e.which === $.ui.keyCode.ESCAPE || query === ""){
		$("#btnResetSearch").click();
		return;
	}
	if(e && e.which === $.ui.keyCode.ENTER && query.length >= 2){
		$("#btnSearch").click();
		return;
	}
	$("#btnResetSearch").attr("disabled", query.length === 0);
	$("#btnSearch").attr("disabled", query.length < 2);
}).focus();

$("#btnResetSearch").click(function(e){
	$("#searchResultPane").collapse("hide");
	$("input[name=query]").val("");
	searchResultTree.clear();
	// $("#btnSearch").attr("disabled", true);
	// $(this).attr("disabled", true);
	updateControls();
});

$("#btnSearch").click(function(event){
	$("#searchResultPane").collapse("show");
	search(	$("input[name=query]").val() );
}).attr("disabled", true);

$("#btnPin").click(function(event){
	taxonTree.filterBranches(function(n){
		return n.isActive();
	});
	updateControls();
});
$("#btnUnpin").click(function(event){
	taxonTree.clearFilter();
	updateControls();
});

// -----------------------------------------------------------------------------
}); // end of pageload handler

}(jQuery, window, document));
