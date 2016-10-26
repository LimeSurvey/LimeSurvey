/*!
 * jquery.fancytree.filter.js
 *
 * Remove or highlight tree nodes, based on a filter.
 * (Extension module for jquery.fancytree.js: https://github.com/mar10/fancytree/)
 *
 * Copyright (c) 2008-2016, Martin Wendt (http://wwWendt.de)
 *
 * Released under the MIT license
 * https://github.com/mar10/fancytree/wiki/LicenseInfo
 *
 * @version 2.19.0
 * @date 2016-08-11T15:51
 */

;(function($, window, document, undefined) {

"use strict";


/*******************************************************************************
 * Private functions and variables
 */

var KeyNoData = "__not_found__",
	escapeHtml = $.ui.fancytree.escapeHtml;

function _escapeRegex(str){
	/*jshint regexdash:true */
	return (str + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1");
}

function extractHtmlText(s){
	if( s.indexOf(">") >= 0 ) {
		return $("<div/>").html(s).text();
	}
	return s;
}

$.ui.fancytree._FancytreeClass.prototype._applyFilterImpl = function(filter, branchMode, opts){
	var leavesOnly, match, statusNode, re, re2,
		count = 0,
		treeOpts = this.options,
		escapeTitles = treeOpts.escapeTitles,
		filterOpts = treeOpts.filter,
		hideMode = filterOpts.mode === "hide";

	opts = opts || {};
	leavesOnly = !!opts.leavesOnly && !branchMode;

	// Default to 'match title substring (not case sensitive)'
	if(typeof filter === "string"){
		// console.log("rex", filter.split('').join('\\w*').replace(/\W/, ""))
		if( filterOpts.fuzzy ) {
			// See https://codereview.stackexchange.com/questions/23899/faster-javascript-fuzzy-string-matching-function/23905#23905
			// and http://www.quora.com/How-is-the-fuzzy-search-algorithm-in-Sublime-Text-designed
			// and http://www.dustindiaz.com/autocomplete-fuzzy-matching
			match = filter.split("").reduce(function(a, b) {
				return a + "[^" + b + "]*" + b;
			});
		} else {
			match = _escapeRegex(filter); // make sure a '.' is treated literally
		}
		re = new RegExp(".*" + match + ".*", "i");
		re2 = new RegExp(filter, "gi");
		filter = function(node){
			var display,
				text = escapeTitles ? node.title : extractHtmlText(node.title),
				res = !!re.test(text);

			if( res && filterOpts.highlight ) {
				display = escapeTitles ? escapeHtml(node.title) : text;
				node.titleWithHighlight = display.replace(re2, function(s){
					return "<mark>" + s + "</mark>";
				});
				// node.debug("filter", escapeTitles, text, node.titleWithHighlight);
			}
			return res;
		};
	}

	this.enableFilter = true;
	this.lastFilterArgs = arguments;

	this.$div.addClass("fancytree-ext-filter");
	if( hideMode ){
		this.$div.addClass("fancytree-ext-filter-hide");
	} else {
		this.$div.addClass("fancytree-ext-filter-dimm");
	}
	// Reset current filter
	this.visit(function(node){
		delete node.match;
		delete node.titleWithHighlight;
		node.subMatchCount = 0;
	});
	statusNode = this.getRootNode()._findDirectChild(KeyNoData);
	if( statusNode ) {
		statusNode.remove();
	}

	// Adjust node.hide, .match, and .subMatchCount properties
	this.visit(function(node){
		if ( leavesOnly && node.children != null ) {
			return;
		}
		var res = filter(node);
		if( res === "skip" ) {
			node.visit(function(c){
				c.match = false;
			}, true);
			return "skip";
		} else if( res ) {
			count++;
			node.match = true;
			node.visitParents(function(p){
				p.subMatchCount += 1;
				if( opts.autoExpand && !p.expanded ) {
					p.setExpanded(true, {noAnimation: true, noEvents: true, scrollIntoView: false});
					p._filterAutoExpanded = true;
				}
			});
			if( branchMode || res === "branch" ) {
				node.visit(function(c){
					c.match = true;
				});
				if( opts.autoExpand && !node.expanded ) {
					node.setExpanded(true, {noAnimation: true, noEvents: true, scrollIntoView: false});
					node._filterAutoExpanded = true;
				}
				return "skip";
			}
		}
	});
	if( count === 0 && filterOpts.nodata ) {
		statusNode = filterOpts.nodata;
		if( $.isFunction(statusNode) ) {
			statusNode = statusNode();
		}
		if( statusNode === true ) {
			statusNode = {};
		} else if( typeof statusNode === "string" ) {
			statusNode = { title: statusNode };
		}
		statusNode = $.extend({
			statusNodeType: "nodata",
			key: KeyNoData,
			title: this.options.strings.noData
		}, statusNode);

		this.getRootNode().addNode(statusNode).match = true;
	}
	// Redraw whole tree
	this.render();
	return count;
};

/**
 * [ext-filter] Dimm or hide nodes.
 *
 * @param {function | string} filter
 * @param {boolean} [opts={autoExpand: false, leavesOnly: false}]
 * @returns {integer} count
 * @alias Fancytree#filterNodes
 * @requires jquery.fancytree.filter.js
 */
$.ui.fancytree._FancytreeClass.prototype.filterNodes = function(filter, opts) {
	if( typeof opts === "boolean" ) {
		opts = { leavesOnly: opts };
		this.warn("Fancytree.filterNodes() leavesOnly option is deprecated since 2.9.0 / 2015-04-19.");
	}
	return this._applyFilterImpl(filter, false, opts);
};

/**
 * @deprecated
 */
$.ui.fancytree._FancytreeClass.prototype.applyFilter = function(filter){
	this.warn("Fancytree.applyFilter() is deprecated since 2.1.0 / 2014-05-29. Use .filterNodes() instead.");
	return this.filterNodes.apply(this, arguments);
};

/**
 * [ext-filter] Dimm or hide whole branches.
 *
 * @param {function | string} filter
 * @param {boolean} [opts={autoExpand: false}]
 * @returns {integer} count
 * @alias Fancytree#filterBranches
 * @requires jquery.fancytree.filter.js
 */
$.ui.fancytree._FancytreeClass.prototype.filterBranches = function(filter, opts){
	return this._applyFilterImpl(filter, true, opts);
};


/**
 * [ext-filter] Reset the filter.
 *
 * @alias Fancytree#clearFilter
 * @requires jquery.fancytree.filter.js
 */
$.ui.fancytree._FancytreeClass.prototype.clearFilter = function(){
	var $title,
		statusNode = this.getRootNode()._findDirectChild(KeyNoData),
		escapeTitles = this.options.escapeTitles,
		enhanceTitle = this.options.enhanceTitle;

	if( statusNode ) {
		statusNode.remove();
	}
	this.visit(function(node){
		if( node.match ) {  // #491
			$title = $(">span.fancytree-title", node.span);
			if( escapeTitles ) {
				$title.text(node.title);
			} else {
				$title.html(node.title);
			}
			if( enhanceTitle ) {
				enhanceTitle({type: "enhanceTitle"}, {node: node, $title: $title});
			}
		}
		delete node.match;
		delete node.subMatchCount;
		delete node.titleWithHighlight;
		if ( node.$subMatchBadge ) {
			node.$subMatchBadge.remove();
			delete node.$subMatchBadge;
		}
		if( node._filterAutoExpanded && node.expanded ) {
			node.setExpanded(false, {noAnimation: true, noEvents: true, scrollIntoView: false});
		}
		delete node._filterAutoExpanded;
	});
	this.enableFilter = false;
	this.lastFilterArgs = null;
	this.$div.removeClass("fancytree-ext-filter fancytree-ext-filter-dimm fancytree-ext-filter-hide");
	this.render();
};


/**
 * [ext-filter] Return true if a filter is currently applied.
 *
 * @returns {Boolean}
 * @alias Fancytree#isFilterActive
 * @requires jquery.fancytree.filter.js
 * @since 2.13
 */
$.ui.fancytree._FancytreeClass.prototype.isFilterActive = function(){
	return !!this.enableFilter;
};


/**
 * [ext-filter] Return true if this node is matched by current filter (or no filter is active).
 *
 * @returns {Boolean}
 * @alias FancytreeNode#isMatched
 * @requires jquery.fancytree.filter.js
 * @since 2.13
 */
$.ui.fancytree._FancytreeNodeClass.prototype.isMatched = function(){
	return !(this.tree.enableFilter && !this.match);
};


/*******************************************************************************
 * Extension code
 */
$.ui.fancytree.registerExtension({
	name: "filter",
	version: "2.19.0",
	// Default options for this extension.
	options: {
		autoApply: true,  // Re-apply last filter if lazy data is loaded
		counter: true,  // Show a badge with number of matching child nodes near parent icons
		fuzzy: false,  // Match single characters in order, e.g. 'fb' will match 'FooBar'
		hideExpandedCounter: true,  // Hide counter badge, when parent is expanded
		highlight: true,  // Highlight matches by wrapping inside <mark> tags
		nodata: true,  // Display a 'no data' status node if result is empty
		mode: "dimm",  // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
		// events
		enhanceTitle: null
	},
	nodeLoadChildren: function(ctx, source) {
		return this._superApply(arguments).done(function() {
			if( ctx.tree.enableFilter && ctx.tree.lastFilterArgs && ctx.options.filter.autoApply ) {
				ctx.tree._applyFilterImpl.apply(ctx.tree, ctx.tree.lastFilterArgs);
			}
		});
	},
	nodeSetExpanded: function(ctx, flag, callOpts) {
		delete ctx.node._filterAutoExpanded;
		// Make sure counter badge is displayed again, when node is beeing collapsed
		if( !flag && ctx.options.filter.hideExpandedCounter && ctx.node.$subMatchBadge ) {
			ctx.node.$subMatchBadge.show();
		}
		return this._superApply(arguments);
	},
	nodeRenderStatus: function(ctx) {
		// Set classes for current status
		var res,
			node = ctx.node,
			tree = ctx.tree,
			opts = ctx.options.filter,
			$title = $("span.fancytree-title", node.span),
			$span = $(node[tree.statusClassPropName]),
			enhanceTitle = ctx.options.enhanceTitle,
			escapeTitles = ctx.options.escapeTitles;

		res = this._superApply(arguments);
		// nothing to do, if node was not yet rendered
		if( !$span.length || !tree.enableFilter ) {
			return res;
		}
		$span
			.toggleClass("fancytree-match", !!node.match)
			.toggleClass("fancytree-submatch", !!node.subMatchCount)
			.toggleClass("fancytree-hide", !(node.match || node.subMatchCount));
		// Add/update counter badge
		if( opts.counter && node.subMatchCount && (!node.isExpanded() || !opts.hideExpandedCounter) ) {
			if( !node.$subMatchBadge ) {
				node.$subMatchBadge = $("<span class='fancytree-childcounter'/>");
				$("span.fancytree-icon, span.fancytree-custom-icon", node.span).append(node.$subMatchBadge);
			}
			node.$subMatchBadge.show().text(node.subMatchCount);
		} else if ( node.$subMatchBadge ) {
			node.$subMatchBadge.hide();
		}
		// node.debug("nodeRenderStatus", node.titleWithHighlight, node.title)
		if( node.titleWithHighlight ) {
			$title.html(node.titleWithHighlight);
		} else if ( escapeTitles ) {
			$title.text(node.title);
		} else {
			$title.html(node.title);
		}
		if( enhanceTitle ) {
			enhanceTitle({type: "enhanceTitle"}, {node: node, $title: $title});
		}
		return res;
	}
});
}(jQuery, window, document));
