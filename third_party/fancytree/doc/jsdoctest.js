/*!
 * jquery.fancytree.js
 * Tree view control with support for lazy loading and much more.
 * https://github.com/mar10/fancytree/
 *
 * Copyright (c) 2008-2016, Martin Wendt (http://wwWendt.de)
 * Released under the MIT license
 * https://github.com/mar10/fancytree/wiki/LicenseInfo
 *
 * @version @VERSION
 * @date @DATE
 */

/** Core TestJSDocFancytree module.
 * @file This file contains sample classes and namespaces for JSDoc testing.
 * @author Martin Wendt
 */

/*jshint unused:false */


/**
 * Construct a new foobar object.
 *
 * @class
 * @classdesc This class represents a foobar.
 */
function TestGlobalClass(widget) {
	/** @type {Object} */
	this.member = null;
}

TestGlobalClass.prototype = {
	/** Drive the car. 
	 * @param {number} speed Driving speed in km/h
	 * @param {boolean} [leftSide=false] true if english
	 * @returns {object} The new baz.
	 */
	drive: function(speed, leftSide){
		return null;
	},
	/** Honk the horn. 
	 * @param {number} duration duration in seconds
	 */
	honk: function(duration){
	}
};


;( /** @lends <global> */ function($, window, document, undefined)  {
"use strict";

/* -----------------------------------------------------------------------------
 * Private functions and variables
 */

function _localFunction(msg){
	msg = msg || "";
	$.error("Not implemented: " + msg);
}

var localVar1,
	localVar2 = null;
/**
 * Construct a new foobar object.
 *
 * @class 
 * @classdesc This class represents a foobar.
 */
function TestClosureClass(widget) {
	/** This is a member.
	 * @type {object} 
	 * @default null
	 */
	this.member = null;
	/** This is another member.
	 * @type {string} 
	 * @default "untitled"
	 */
	this.member2 = "untitled";
}

TestClosureClass.prototype = /** @lends TestClosureClass# */ {
	/** Drive the car. 
	 * @param {number} speed Driving speed in km/h
	 * @param {boolean} [leftSide=false] true if english
	 * @returns {object} The new baz.
	 */
	drive: function(speed, leftSide){
		return null;
	},
	/** Honk the horn. 
	 * @param {number} duration duration in seconds
	 */
	honk: function(duration){
	}
};


/*------------------------------------------------------------------------------
 * jQuery UI widget boilerplate
 */

/**
 * This contructor is not called directly. Use `$(selector).fancytre({})` instead.
 * @example
 * var tree = $(selector).fancytree();
 * @class moogle.myWidget 
 * @classdesc The plugin (derrived from [jQuery.Widget](http://api.jqueryui.com/jQuery.widget/)).<br>
 * <pre class="sunlight-highlight-javascript">// Access instance methods and members:
 * var tree = $(selector).fancytree("getTree");
 * // Access static members:
 * alert($.moogle.myWidget.version);
 * </pre>
 */
$.widget("moogle.myWidget",
	/** @lends moogle.myWidget# */
	{
	/**These options will be used as defaults
	 * @type {FancytreeOptions} */
	options:
	{
		activeVisible: true,
		ajax: {
			type: "GET",
			cache: false, // false: Append random '_' argument to the request url to prevent caching.
//          timeout: 0, // >0: Make sure we get an ajax error if server is unreachable
			dataType: "json" // Expect json format and pass json object to callbacks.
		},  //
		aria: false, // TODO: default to true
		postProcess: null
	},
	/* Set up the widget, Called on first $().fancytree() */
	_create: function() {
		this.tree._callHook("treeCreate", this.tree);
	},
	/** @returns {TestJSDocFancytreeNode} the invisible system root node */
	getRootNode: function() {
		return this.tree.rootNode;
	},
	/** @returns {TestJSDocFancytree} the current tree instance */
	getTree: function() {
		return this.tree;
	}
});

$.extend($.moogle.myWidget,
	/** @lends moogle.myWidget */
	{
	/** @type {string} */
	version: "@VERSION",
	/** Add TestJSDocFancytree extension definition to the list of globally available extensions.
	 * @param {object} definition
	 */
	registerExtension: function(definition){
		$.ui.fancytree._extensions[definition.name] = definition;
	},
	/** Print a warning message to the console.
	 *
	 * @param {string} msg
	 */
	warn: function(msg){
	}
});

}(jQuery, window, document));
