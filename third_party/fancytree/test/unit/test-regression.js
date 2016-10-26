/*******************************************************************************
 * Reproduced issues
 ******************************************************************************/

jQuery(document).ready(function(){

// jQUnit defines:
// asyncTest,deepEqual,equal,expect,module,notDeepEqual,notEqual,notStrictEqual,ok,QUnit,raises,start,stop,strictEqual,test

/*globals TEST_TOOLS,expect,module,ok,start,test */

var $ = jQuery,
	// Use tools from test-tools.js
	tools = TEST_TOOLS;

/*******************************************************************************
 * Initialize QUnit
 */

tools.initQUnit();

// Create an Info section (will be expanded when tests are completed)
tools.createInfoSection();

// Silence, please
$.ui.fancytree.debugLevel = 1;


/*******************************************************************************
 * Module
 */
module("Issues");

// TODO: this fixture doesn't really make sense, it is rather meynt as an example...
test("issue310: Loading animation never ends using lazy read on empty folder", function() {
	tools.setupAsync();
	expect(1);

	$("#tree").fancytree({
		source: [{title: "lazy folder", key: "1", folder: true, lazy: true}],
		lazyLoad: function(event, data){
			ok(true, "got `lazyLoad` event");
			data.result = [];
			start();
		}
	});
	var node = $("#tree").fancytree("getTree").getNodeByKey("1");
	 $("span.fancytree-expander", node.span).click();
});


// ---
});
