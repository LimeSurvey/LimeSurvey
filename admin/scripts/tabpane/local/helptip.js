/*
 * This script was created by Erik Arvidsson (erik(at)eae.net)
 * for WebFX (http://webfx.eae.net)
 * Copyright 2001
 * 
 * For usage see license at http://webfx.eae.net/license.html	
 *
 * Version: 1.0
 * Created: 2001-09-27 * Updated: 2001-11-25	Added a resize to the tooltip if the document width is too small
 *
 * Dependencies: helptip.css (To set up the CSS of the help-tooltip class)
 *
 *	Usage:
 *
 *    <script type="text/javascript" src="helptip.js"></script>
 *    <link type="text/css" rel="StyleSheet" href="helptip.css" />
 *
 *    <a class="helpLink" href="?" onclick="showHelp(event, 'String to show'); return false">Help</a>
 *
 */

function showHelpTip(e, s) {
	// find anchor element
	var el = e.target ? e.target : e.srcElement;
	while (el.tagName != "A")
		el = el.parentNode;
	
	// is there already a tooltip? If so, remove it
	if (el._helpTip) {
		document.body.removeChild(el._helpTip);
		el._helpTip = null;
		el.onblur = null;
		return;
	}

	// create element and insert last into the body
	var d = document.createElement("DIV");
	d.className = "help-tooltip";
	document.body.appendChild(d);
	d.innerHTML = s;
	
	// Allow clicks on A elements inside tooltip
	d.onmousedown = function (e) {
		if (!e) e = event;
		var t = e.target ? e.target : e.srcElement;
		while (t.tagName != "A" && t != d)
			t = t.parentNode;
		if (t == d) return;
		
		el._onblur = el.onblur;
		el.onblur = null;
	};
	d.onmouseup = function () {
		el.onblur = el._onblur;
		el.focus();
	};
	
	// position tooltip
	var dw = document.width ? document.width : document.documentElement.offsetWidth - 25;	
	if (d.offsetWidth >= dw)
		d.style.width = dw - 10 + "px";	else
		d.style.width = "";	
	var scroll = getScroll();
	if (e.clientX > dw - d.offsetWidth)
		d.style.left = dw - d.offsetWidth + scroll.x + "px";
	else
		d.style.left = e.clientX - 2 + scroll.x + "px";
	d.style.top = e.clientY + 18 + scroll.y + "px";

	// add a listener to the blur event. When blurred remove tooltip and restore anchor
	el.onblur = function () {
		document.body.removeChild(d);
		el.onblur = null;
		el._helpTip = null;
	};
	
	// store a reference to the tooltip div
	el._helpTip = d;
}

// returns the scroll left and top for the browser viewport.
function getScroll() {
	if (document.all && document.body.scrollTop != undefined) {	// IE model
		var ieBox = document.compatMode != "CSS1Compat";
		var cont = ieBox ? document.body : document.documentElement;
		return {x : cont.scrollLeft, y : cont.scrollTop};
	}
	else {
		return {x : window.pageXOffset, y : window.pageYOffset};
	}
}