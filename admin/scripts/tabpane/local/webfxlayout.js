/* this is a dummy webfxlayout file to be used in download zip files */


/* Do includes */

if (window.pathToRoot == null)
	pathToRoot = "./";

document.write('<link type="text/css" rel="stylesheet" href="local/webfxlayout.css">');
webfxMenuDefaultImagePath = pathToRoot + "images/";

/* end includes */

/* set up browser checks and add a simple emulation for IE4 */

// check browsers
var op = /opera 5|opera\/5/i.test(navigator.userAgent);
var ie = !op && /msie/i.test(navigator.userAgent);	// preventing opera to be identified as ie
var mz = !op && /mozilla\/5/i.test(navigator.userAgent);	// preventing opera to be identified as mz

if (ie && document.getElementById == null) {	// ie4
	document.getElementById = function(sId) {
		return document.all[sId];
	};
}

/* end browser checks */

webfxLayout = {
	writeTitle		:	function (s, s2) {
		document.write("<div id='webfx-title-background'></div>");
		if (op) {
			document.write("<h1 id='webfx-title' style='top:9px;'>" + s + "</h1>");
		}
		else {
			document.write("<h1 id='webfx-title'>" + s + "</h1>");
		}

		if (s2 == null)
			s2 = "WebFX - What you never thought possible!";
		
		if (op) {
			document.write("<span id='webfx-sub-title' style='top:46px;'>" + s2 + "</span>");
		}
		else {
			document.write("<span id='webfx-sub-title'>" + s2 + "</span>");
		}
	},
	writeMainTitle	:	function () {
		this.writeTitle("WebFX", "What you never thought possible!");	
	},
	writeTopMenuBar		:	function () {
		document.write("<div id='webfx-menu-bar-1'></div>");
		if (op) {
			document.write("<style>.webfx-menu-bar a {padding-top:3px;}</style>");
			document.write("<div id='webfx-menu-bar-2' style='height:2px;'></div>");
		}
		else
			document.write("<div id='webfx-menu-bar-2'></div>");
		document.write("<div id='webfx-menu-bar'>");// div is closed in writeBottomMenuBar
	},
	writeBottomMenuBar	:	function () {
		document.write("</div>");
		if (op)
			document.write("<div id='webfx-menu-bar-3' style='height:0px;'></div>");
		else
			document.write("<div id='webfx-menu-bar-3'></div>");
		document.write("<div id='webfx-menu-bar-4'></div>");
		document.write("<div id='webfx-menu-bar-5'></div>");
	},
	writeMenu			:	function () {
		this.writeTopMenuBar();
		//document.write(webfxMenuBar);
		document.write("<div class='webfx-menu-bar'><a href='http://webfx.eae.net'>WebFX Home</a></div>");
		this.writeBottomMenuBar();
	},
	writeDesignedByEdger	:	function () {
		if (ie && document.body.currentStyle.writingMode != null)
			document.write("<div id='webfx-about'>Page designed and maintained by " +
					"<a href='mailto:erik@eae.net'>Erik Arvidsson</a> &amp; " +
					"<a href='mailto:eae@eae.net'>Emil A Eklund</a>.</div>");
	}
};

if (ie && window.attachEvent) {
	window.attachEvent("onload", function () {
		var scrollBorderColor	=	"rgb(120,172,255)";
		var scrollFaceColor		=	"rgb(234,242,255)";
		with (document.body.style) {
			scrollbarDarkShadowColor	=	scrollBorderColor;
			scrollbar3dLightColor		=	scrollBorderColor;
			scrollbarArrowColor			=	"black";
			scrollbarBaseColor			=	scrollFaceColor;
			scrollbarFaceColor			=	scrollFaceColor;
			scrollbarHighlightColor		=	scrollFaceColor;
			scrollbarShadowColor		=	scrollFaceColor;
			scrollbarTrackColor			=	"white";
		}
	});
}

/* we also need some dummy constructors */
webfxMenuBar = {
	add : function () {}
};
function WebFXMenu() {
	this.add = function () {};
}
function WebFXMenuItem() {}
function WebFXMenuSeparator() {}
function WebFXMenuButton() {}