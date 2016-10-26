jQuery(document).ready(function(){

// jQUnit defines:
// asyncTest,deepEqual,equal,expect,module,notDeepEqual,notEqual,notStrictEqual,ok,QUnit,raises,start,stop,strictEqual,test

/*jshint unused:false */
/*globals TEST_TOOLS,deepEqual,equal,expect,module,ok,start,test */

var TEST_DATA, TESTDATA_NODES, TESTDATA_TOPNODES, TESTDATA_VISIBLENODES,
	$ = jQuery,
	// Use tools from test-tools.js
	tools = TEST_TOOLS;


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
			{key: "10_2", title: "Sub-item 2.2", children: [
				{key: "10_2_1", title: "Sub-item 1.2.1"},
				{key: "10_2_2", title: "Sub-item 1.2.2"}
			]}
		]},
		{key: "20", title: "Simple node with active children (expand)", expanded: true, children: [
			{key: "20_1", title: "Sub-item 2.1", children: [
				{key: "20_1_1", title: "Sub-item 2.1.1", refKey: "rk_1"},
				{key: "20_1_2", title: "Sub-item 2.1.2"},
				{title: "Sub-item 2.1.3 (no key)"},
				{title: "Sub-item 2.1.4 (no key, but refKey)", refKey: "rk_2"}
			]},
			{key: "20_2", title: "Sub-item 1.2", children: [
				{key: "20_2_1", title: "Sub-item 2.2.1", refKey: "rk_1"},
				{key: "20_2_2", title: "Sub-item 2.2.2"}
			]}
		]},
		{key: "30", title: "Lazy folder", folder: true, lazy: true },
		{key: "31", title: "Lazy folder (preload)", folder: true, lazy: true, preload: true },
		{key: "32", title: "Lazy folder (expand on load)", folder: true, lazy: true, expanded: true }
	];

TESTDATA_NODES = 23;
TESTDATA_TOPNODES = 11;
TESTDATA_VISIBLENODES = 13;



/*******************************************************************************
 * Initialize QUnit
 */

tools.initQUnit();

// Create an Info section (will be expanded when tests are completed)
tools.createInfoSection();

// Silence, please
$.ui.fancytree.debugLevel = 1;


/*******************************************************************************
 *
 */
module("clones");

test("sync load", function() {
	tools.setupAsync();
	expect(16);

	$("#tree").fancytree({
		extensions: ["clones"],
		source: TEST_DATA,
		lazyLoad: function(event, data){
			// fake an async, deleayed Ajax request that generates 5 lazy nodes
			data.result = tools.fakeAjaxLoad(data.node, 5, 10);
		}
	});
	var node, nodeList,
		tree = tools.getTree();

	ok($.isPlainObject(tree.keyMap), "has keyMap");
	deepEqual(tree.refMap,  {"rk_1": ["20_1_1", "20_2_1"], "rk_2": ["id_11b7cb44"]}, "has refMap");

	equal(tools.getNode("10_1_1").refKey, undefined, "no default refKey");

	equal(tools.getNode("20_1_1").refKey, "rk_1", "set refKey");
	equal(tools.getNode("20_2_1").refKey, "rk_1", "set refKey 2");

	equal(tools.getNode("10_1_1").isClone(), false, "isClone() non-clone detected");
	equal(tools.getNode("20_1_1").isClone(), true, "isClone() 1 detected");
	equal(tools.getNode("20_2_1").isClone(), true, "isClone() 2 detected");

	nodeList = tree.getNodesByRef("rk_1");
	equal(nodeList.length, 2, "tree.getNodesByRef()");
	deepEqual(tools.getNodeKeyArray(nodeList), ["20_1_1", "20_2_1"], "tree.getNodesByRef() finds all clones");

	nodeList = tree.getNodesByRef("rk_1", tools.getNode("10"));
	equal(nodeList, null, "tree.getNodesByRef() restrict to branch: miss");
	nodeList = tree.getNodesByRef("rk_1", tools.getNode("20"));
	equal(nodeList.length, 2, "tree.getNodesByRef() restrict to branch: hit");

	nodeList = tree.getNodesByRef("rk_2");
	equal(nodeList.length, 1, "also single refKeys are stored in refMap");
	node = tools.getNodeKeyArray(nodeList[0]);
	equal(node.key, "id_11b7cb44", "generate predictable unique default keys");

	nodeList = tools.getNode("20_1_1").getCloneList();
	deepEqual(tools.getNodeKeyArray(nodeList), ["20_2_1"], "node.getCloneList() 1 detected");
	nodeList = tools.getNode("20_1_1").getCloneList(true);
	deepEqual(tools.getNodeKeyArray(nodeList), ["20_1_1", "20_2_1"], "node.getCloneList(true) 2 detected");

	nodeList = tools.getNode("20_1_1").getCloneList();

	start();
});


});
