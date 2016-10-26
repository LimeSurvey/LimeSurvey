jQuery(document).ready(function(){

// jQUnit defines:
// asyncTest,deepEqual,equal,expect,module,notDeepEqual,notEqual,notStrictEqual,ok,QUnit,raises,start,stop,strictEqual,test

/*globals expect,module,ok,QUnit,start,stop,test */

var $ = jQuery,
	treedata = [
		{ key: "1", title: "item 1" },
		{ key: "2", title: "item 2", children: [
			{ key: "2_1", title: "item 2_1" },
			{ key: "2_2", title: "item 2_2", children: [
				{ key: "2_2_1", title: "item 2_2_1" },
				{ key: "2_2_2", title: "item 2_2_2" }
			] }
		]}
	];

/*******************************************************************************
 * QUnit setup
 */

QUnit.log(function(data) {
	if (window.console && window.console.log) {
//		window.console.log(data.result + " :: " + data.message);
	}
});


/*******************************************************************************
 * Tool functions
 */

/** Helper to reset environment for asynchronous Fancytree tests. */
function _setupAsync(){
	QUnit.reset();
	if( $("#tree").is(":ui-fancytree") ){
		$("#tree").fancytree("destroy");
	}
	stop();
}

/** Get FancytreeNode from current tree. */
function _getNode(key){
	return $("#tree").fancytree("getTree").getNodeByKey(key);
}


/*******************************************************************************
 *
 */
module("Table ext");

test("makeVisible not rendered deep node", function () {
	_setupAsync();
	expect(5);

	var node;

	$("#tree").fancytree({
		extensions: [ "table" ],
		source: treedata
	});

	node = _getNode("2_2_2");
	ok(node);
	ok(!node.parent.isExpanded());
	ok(!node.tr); // not rendered yet

	node.makeVisible().done(function () {
		ok(node.parent.isExpanded());
		ok(node.tr, node + " tr is rendered");

		start();
	});
});

test("render deep node", function () {
	// _setupAsync();
	QUnit.reset();
	expect(11);

	$("#tree").fancytree({
		extensions: [ "table" ],
		source: treedata
	});

	var node,
		tree = $("#tree").fancytree("getTree");

	node = _getNode("2_2_2");
	ok(node);
	ok(!node.parent.isExpanded());
	ok(!node.tr, "not rendered yet");

	tree.render();

	ok(!node.parent.isExpanded());
	ok(!node.tr, "not rendered yet");

	tree.render(true, true);

	tree.visit(function (node) {
		// ok(node.parent.isExpanded(), node.parent + " is expanded");
		ok(node.tr, node + " tr is rendered");
	});
});

});
