jQuery(document).ready(function(){

// jQUnit defines:
// asyncTest,deepEqual,equal,expect,module,notDeepEqual,notEqual,notStrictEqual,ok,QUnit,raises,start,stop,strictEqual,test

/*globals TEST_TOOLS, equal,expect,module,ok,start,test */

var TEST_DATA, TESTDATA_NODES, TESTDATA_TOPNODES, TESTDATA_VISIBLENODES,
	$ = jQuery,
	// Use tools from test-tools.js
	tools = TEST_TOOLS;


function countVisible(root) {
	var count = 0;
	root.visit(function(n){
		if( $(n.span).is(":visible") ) {
			count++;
		}
	});
	return count;
}

function countMatched(root) {
	var count = 0;
	root.visit(function(n){
		if( n.isMatched() ) {
			count++;
		}
	});
	return count;
}

/*******************************************************************************
 * test data
 */
TEST_DATA = [
		{title: "simple node (no explicit id, so a default key is generated)" },
		{key: "2", title: "item1 with key and tooltip", tooltip: "Look, a tool tip!" },
		{key: "3", title: "<span>item2 with <b>html</b> inside a span tag</span>" },
		{key: "4", title: "this nodes uses 'nolink', so no &lt;a> tag is generated", nolink: true},
		{key: "5", title: "using href", href: "http://www.wwWendt.de/" },
		{key: "6", title: "node with some extra classes (will be added to the generated markup)", extraClasses: "my-extra-class" },
		{key: "10", title: "Folder 1", folder: true, children: [
			{key: "10_1", title: "Sub-item 1.1", children: [
				{key: "10_1_1", title: "Sub-item 1.1.1"},
				{key: "10_1_2", title: "Sub-item 1.1.2"}
			]},
			{key: "10_2", title: "Sub-item 1.2", children: [
				{key: "10_2_1", title: "Sub-item 1.2.1"},
				{key: "10_2_2", title: "Sub-item 1.2.2"}
			]}
		]},
		{key: "20", title: "Simple node with active children (expand)", expanded: true, children: [
			{key: "20_1", title: "Sub-item 2.1", children: [
				{key: "20_1_1", title: "Sub-item 2.1.1"},
				{key: "20_1_2", title: "Sub-item 2.1.2"}
			]},
			{key: "20_2", title: "Sub-item 2.2", children: [
				{key: "20_2_1", title: "Sub-item 2.2.1"},
				{key: "20_2_2", title: "Sub-item 2.2.2"}
			]}
		]},
		{key: "30", title: "Lazy folder", folder: true, lazy: true }
	];
TESTDATA_NODES = 21,
TESTDATA_TOPNODES = 9,
TESTDATA_VISIBLENODES = 11;


/*******************************************************************************
 * Initialize QUnit
 */

tools.initQUnit();

// Create an Info section (will be expanded when tests are completed)
tools.createInfoSection();

// Silence, please
$.ui.fancytree.debugLevel = 1;


/*******************************************************************************
 * Module Init
 */
module("Filter");

test("Default options (mode='dimm')", function() {
	expect(24);

	tools.setupAsync();

	$("#tree").fancytree({
		extensions: ["filter"],
		filter: {
		},
		source: TEST_DATA,
		generateIds: true, // for testing
		init: function(event, data){
			var tree = data.tree;

			// Class methods:
			ok($.isFunction(tree.isFilterActive), "Has tree.isFilterActive()");
			ok($.isFunction(tree.clearFilter), "Has tree.clearFilter()");
			ok($.isFunction(tree.filterNodes), "Has tree.filterNodes()");
			ok($.isFunction(tree.filterBranches), "Has tree.filterBranches()");
			ok($.isFunction(tree.rootNode.isMatched), "Has node.isMatched()");
			// Default options:
			equal(data.options.filter.autoApply, true, "opts.autoApply ===  true");
			equal(data.options.filter.counter, true, "opts.counter ===  true");
			equal(data.options.filter.fuzzy, false, "opts.fuzzy ===  false");
			equal(data.options.filter.hideExpandedCounter, true, "opts.hideExpandedCounter ===  true");
			equal(data.options.filter.highlight, true, "opts.highlight ===  true");
			equal(data.options.filter.mode, "dimm", "opts.mode ===  'dimm'");
			//
			equal(tree.count(), TESTDATA_NODES, "All visible by default");
			equal(countVisible(tree), TESTDATA_VISIBLENODES, "Has some visible nodes");
			equal(countMatched(tree), TESTDATA_NODES, "All matched by default");
			equal(tree.isFilterActive(), false, "No filter active");
			//
			equal(tree.filterNodes("Sub-item 1.1"), 3, "'Sub-item 1.1' matched 3 nodes");
			equal(tree.isFilterActive(), true, "filter is active");
			equal(countMatched(tree), 3, "3 nodes matched");
			equal(countVisible(tree), TESTDATA_VISIBLENODES, "All nodes still visible");
			//
			equal(tree.filterNodes("Sub-item 1.1.1"), 1, "'Sub-item 1.1.1' matched 1 nodes");
			equal(tree.isFilterActive(), true, "filter is active");
			//
			tree.clearFilter();
			equal(tree.isFilterActive(), false, "No filter is active");
			equal(countMatched(tree), TESTDATA_NODES, "all nodes matched after clearFilter");
			equal(countVisible(tree), TESTDATA_VISIBLENODES, "All nodes visible");
			//
			start();
		}
	});
});

test("Default options, mode='hide'", function() {
	expect(8);

	tools.setupAsync();

	$("#tree").fancytree({
		extensions: ["filter"],
		filter: {
			mode: "hide"
		},
		source: TEST_DATA,
		generateIds: true, // for testing
		init: function(event, data){
			var tree = data.tree;

			//
			equal(tree.filterNodes("Sub-item 1.1"), 3, "'Sub-item 1.1' matched 3 nodes");
			equal(tree.isFilterActive(), true, "filter is active");
			equal(countMatched(tree), 3, "3 nodes matched");
			equal(countVisible(tree), 1, "1 (parent) node visible");
			//
			tree.clearFilter();
			equal(tree.isFilterActive(), false, "No filter is active");
			equal(countMatched(tree), TESTDATA_NODES, "all nodes matched after clearFilter");
			equal(countVisible(tree), TESTDATA_VISIBLENODES, "All nodes visible");
			//
			equal(tree.filterNodes("Sub-item 1.1", {leavesOnly: true}),
				  2, "'Sub-item 1.1' / leavesOnly matched 2 nodes");

			start();
		}
	});
});

});
