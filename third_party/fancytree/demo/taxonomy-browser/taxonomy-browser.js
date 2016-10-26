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

var taxonTree, searchResultTree, tmplDetails, tmplInfoPane, tmplMedia,
	timerMap = {},
	USER_AGENT = "Fancytree Taxonomy Browser/1.0",
	GBIF_URL = "http://api.gbif.org/v1/",
	TAXONOMY_KEY = "d7dddbf4-2cf0-4f39-9b2a-bb099caae36c",  // GBIF backbone taxonomy
	SEARCH_PAGE_SIZE = 5,
	CHILD_NODE_PAGE_SIZE = 200,
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
			expanderClosed: "glyphicon glyphicon-menu-right",
			expanderLazy: "glyphicon glyphicon-menu-right",  // glyphicon-plus-sign
			expanderOpen: "glyphicon glyphicon-menu-down",  // glyphicon-collapse-down
			folder: "glyphicon glyphicon-folder-close",
			folderOpen: "glyphicon glyphicon-folder-open",
			loading: "glyphicon glyphicon-refresh glyphicon-spin"
		}
	};

// Load and compile handlebar templates

$.get( "details.tmpl.html", function( data ) {
	tmplDetails = Handlebars.compile(data);
	Handlebars.registerPartial("tmplDetails", tmplDetails);
});
$.get( "media.tmpl.html", function( data ) {
	tmplMedia = Handlebars.compile(data);
	Handlebars.registerPartial("tmplMedia", tmplMedia);
});
$.get( "info-pane.tmpl.html", function( data ) {
	tmplInfoPane = Handlebars.compile(data);
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
function _callWebservice(cmd, data) {
	return $.ajax({
		url: GBIF_URL + cmd,
		data: $.extend({
		}, data),
		cache: true,
		headers: { "Api-User-Agent": USER_AGENT },
		dataType: "jsonp"
	});
}

/**
 */
function updateItemDetails(key) {
	$("#tmplDetails").addClass("busy");
	$.bbq.pushState({key: key});

	$.when(
		_callWebservice("species/" + key),
		_callWebservice("species/" + key + "/speciesProfiles"),
		_callWebservice("species/" + key + "/synonyms"),
		_callWebservice("species/" + key + "/descriptions"),
		_callWebservice("species/" + key + "/media")

	).done(function(species, profiles, synonyms, descriptions, media){
		// Requests are resolved as: [ data, statusText, jqXHR ]
		species = species[0];
		profiles = profiles[0];
		synonyms = synonyms[0];
		descriptions = descriptions[0];
		media = media[0];

		var info = $.extend(species, {
			profileList: profiles.results, // marine, extinct
			profile: profiles.results.length === 1 ? profiles.results[0] : null, // marine, extinct
			synonyms: synonyms.results,
			descriptions: descriptions.results,
			descriptionsByLang: {},
			media: media.results,
			now: new Date().toString()
			});

		$.each(info.descriptions, function(i, o){
			if( !info.descriptionsByLang[o.language] ) {
				info.descriptionsByLang[o.language] = [];
			}
			info.descriptionsByLang[o.language].push(o);
		});

		console.log("updateItemDetails", info);
		$("#tmplDetails")
			// .html(tmplDetails(info))
			.removeClass("busy");
		$("#tmplMedia")
			// .html(tmplMedia(info))
			.removeClass("busy");
		$("#tmplInfoPane")
			.html(tmplInfoPane(info))
			.removeClass("busy");

		$("[data-toggle='popover']").popover();
		$(".carousel").carousel();
		$("#mediaCounter").text("" + (media.results.length || ""));
		// $("[data-toggle='collapse']").collapse();
		updateControls();
	});
}

/**
 */
function updateBreadcrumb(key, loadTreeNodes) {

	var $ol = $("ol.breadcrumb").addClass("busy"),
		activeNode = taxonTree.getActiveNode();

	if( activeNode && activeNode.key !== key ) {
		activeNode.setActive(false); // deactivate, in case the new key is not found
	}
	$.when(
		_callWebservice("species/" + key + "/parents"),
		_callWebservice("species/" + key)
	).done(function(parents, node){
		// Both requests resolved (result format: [ data, statusText, jqXHR ])
		var nodeList = parents[0],
			keyList = [];

		nodeList.push(node[0]);

		// Display as <OL> list (for Bootstrap breadcrumbs)
		$ol.empty().removeClass("busy");
		$.each(nodeList, function(i, o){
			var name = o.vernacularName || o.canonicalName;
			keyList.push(o.key);
			if( "" + o.key === "" + key ) {
				$ol.append(
					$("<li class='active'>").append(
						$("<span>", {
							text: name,
							title: o.rank
						})));
			} else {
				$ol.append(
					$("<li>").append(
						$("<a>", {
							href: "#key=" + o.key,
							text: name,
							title: o.rank
						})));
			}
		});
		if( loadTreeNodes ) {
			// console.log("updateBreadcrumb - loadKeyPath", keyList);
			taxonTree.loadKeyPath("/" + keyList.join("/"), function(node, status){
				// console.log("... updateBreadcrumb - loadKeyPath " + node.title + ": " + status);
				switch( status ) {
				case "loaded":
					node.makeVisible();
					break;
				case "ok":
					node.setActive();
					// node.makeVisible();
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
	// Store the source options for optional paging
	searchResultTree.lastSourceOpts = {
		// url: GBIF_URL + "species/match",  // Fuzzy matches scientific names against the GBIF Backbone Taxonomy
		url: GBIF_URL + "species/search",  // Full text search of name usages covering the scientific and vernacular name, the species description, distribution and the entire classification across all name usages of all or some checklists
		data: {
			q: query,
			datasetKey: TAXONOMY_KEY,
			// name: query,
			// strict: "true",
			// hl: true,
			limit: SEARCH_PAGE_SIZE,
			offset: 0
		},
		cache: true
		// headers: { "Api-User-Agent": USER_AGENT }
		// dataType: "jsonp"
	};
	$("#searchResultTree").addClass("busy");
	searchResultTree.reload(searchResultTree.lastSourceOpts).done(function(result){
		// console.log("search returned", result);
		if( result.length < 1) {
			searchResultTree.getRootNode().setStatus("nodata");
		}
		$("#searchResultTree").removeClass("busy");

		// https://github.com/tbasse/jquery-truncate
		// SLOW!
		// $("div.truncate").truncate({
		// 	multiline: true
		// });

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
	autoCollapse: true,
	activeVisible: true,
	autoScroll: true,
	source: {
		url: GBIF_URL + "species/root/" + TAXONOMY_KEY,
		data: {},
		cache: true
		// dataType: "jsonp"
	},
	init: function(event, data) {
		updateControls();
		$(window).trigger("hashchange"); // trigger on initial page load
	},
	lazyLoad: function(event, data) {
		data.result = {
			url: GBIF_URL + "species/" + data.node.key + "/children",
			data: {
				limit: CHILD_NODE_PAGE_SIZE
			},
			cache: true
			// dataType: "jsonp"
		};
		// store this request options for later paging
		data.node.lastSourceOpts = data.result;
	},
	postProcess: function(event, data) {
		var response = data.response;

		data.node.info("taxonTree postProcess", response);
		data.result = $.map(response.results, function(o){
			return o && {title: o.vernacularName || o.canonicalName, key: o.key, nubKey: o.nubKey, folder: true, lazy: true};
		});
		if( response.endOfRecords === false ) {
			// Allow paging
			data.result.push({
				title: "(more)",
				statusNodeType: "paging"
				});
		} else {
			// No need to store the extra data
			delete data.node.lastSourceOpts;
		}
	},
	activate: function(event, data) {
		$("#tmplDetails").addClass("busy");
		$("ol.breadcrumb").addClass("busy");
		updateControls();
		_delay("showDetails", 500, function(){
			updateItemDetails(data.node.key);
			updateBreadcrumb(data.node.key);
		});
	},
	clickPaging: function(event, data) {
		// Load the next page of results
		var source = $.extend(true, {}, data.node.parent.lastSourceOpts);
		source.data.offset = data.node.parent.countChildren() - 1;
		data.node.replaceWith(source);
	}
});


$("#searchResultTree").fancytree({
	extensions: ["table", "wide"],
	source: [{title: "No Results."}],
	minExpandLevel: 2,
	icon: false,
	table: {
		nodeColumnIdx: 2
	},
	postProcess: function(event, data) {
		var response = data.response;

		data.node.info("search postProcess", response);
		data.result = $.map(response.results, function(o){
			var res = $.extend({
				title: o.scientificName,
				key: o.key
			}, o);
			return res;
		});
		// Append paging link
		if( response.count != null && response.offset + response.limit < response.count ) {
			data.result.push({
				title: "(" + (response.count - response.offset - response.limit) + " more)",
				statusNodeType: "paging"
				});
		}
		data.node.info("search postProcess 2", data.result);
	},
	// loadChildren: function(event, data) {
	// 	$("#searchResultTree td div.cell").truncate({
	// 		multiline: true
	// 	});
	// },
	renderColumns: function(event, data) {
		var i,
			node = data.node,
			$tdList = $(node.tr).find(">td"),
			cnList = node.data.vernacularNames ? $.map(node.data.vernacularNames, function(o){
					return o.vernacularName;
				}) : [];

		i = 0;
		function _setCell($cell, text){
			$("<div class='truncate'>").attr("title", text).text(text).appendTo($cell);
		}
		$tdList.eq(i++).text(node.key);
		$tdList.eq(i++).text(node.data.rank);
		i++;  // #1: node.title = scientificName
		// $tdList.eq(i++).text(cnList.join(", "));
		_setCell($tdList.eq(i++), cnList.join(", "));
		$tdList.eq(i++).text(node.data.canonicalName);
		// $tdList.eq(i++).text(node.data.accordingTo);
		_setCell($tdList.eq(i++), node.data.accordingTo);
		$tdList.eq(i++).text(node.data.taxonomicStatus);
		$tdList.eq(i++).text(node.data.nameType);
		$tdList.eq(i++).text(node.data.numOccurrences);
		$tdList.eq(i++).text(node.data.numDescendants);
		// $tdList.eq(i++).text(node.data.authorship);
		_setCell($tdList.eq(i++), node.data.authorship);
		// $tdList.eq(i++).text(node.data.publishedIn);
		_setCell($tdList.eq(i++), node.data.publishedIn);
	},
	activate: function(event, data) {
		if( data.node.isStatusNode() ) { return; }
		_delay("activateNode", 500, function(){
			updateItemDetails(data.node.key);
			updateBreadcrumb(data.node.key);
		});
	},
	clickPaging: function(event, data) {
		// Load the next page of results
		var source = $.extend(true, {}, searchResultTree.lastSourceOpts);
		source.data.offset = data.node.parent.countChildren() - 1;
		data.node.replaceWith(source);
	}
});


taxonTree = $("#taxonTree").fancytree("getTree");
searchResultTree = $.ui.fancytree.getTree("#searchResultTree");


// Bind a callback that executes when document.location.hash changes.
// (This code uses bbq: https://github.com/cowboy/jquery-bbq)
$(window).bind( "hashchange", function(e) {
	var key = $.bbq.getState( "key" );
	console.log("bbq key", key);
	if( key ) {
		updateBreadcrumb(key, true);
	}
}); // don't trigger now, since we need the the taxonTree root nodes to be loaded first

$("input[name=query]").keyup(function(e){
	var query = $.trim($(this).val()),
		lastQuery = $(this).data("lastQuery");

	if(e && e.which === $.ui.keyCode.ESCAPE || query === ""){
		$("#btnResetSearch").click();
		return;
	}
	if(e && e.which === $.ui.keyCode.ENTER && query.length >= 2){
		$("#btnSearch").click();
		return;
	}
	if( query === lastQuery || query.length < 2) {
		console.log("Ignored query '" + query + "'");
		return;
	}
	$(this).data("lastQuery", query);
	_delay("search", 1, function(){
		$("#btnSearch").click();
	});
	$("#btnResetSearch").attr("disabled", query.length === 0);
	$("#btnSearch").attr("disabled", query.length < 2);
}).focus();

$("#btnResetSearch").click(function(e){
	$("#searchResultPane").collapse("hide");
	$("input[name=query]").val("");
	searchResultTree.clear();
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
