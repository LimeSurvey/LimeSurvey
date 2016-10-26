/*
 * TODO: implement a simple nose-based server that
 *
 *     - serves the demo folder, so we don't need to use restricted local file
 *       file://... access
 *     - act as an ajax server that send lazy subnodes
 *     - See https://gist.github.com/701407
 */

/*jshint node:true */
// TODO enable strict mode again
//"use strict";

var assert = require("assert"),
	http = require("http"),
	url = require("url");

/*
 * Helpers
 */
var NODE_ATTRS = ["title", "key"];

function copyNode(node, deep){
	var i, l, name,
		node2 = {};
	for(i=0, l=NODE_ATTRS.length; i<l; i++){
		name = NODE_ATTRS[i];
		node2[name] = node[name];
	}
	return node2;
}
/*
 * Class TreeModel
 * Simple tree data structure
 */
var TreeModel = function(){
	this.children = [];
	this.keyMap = {};
};
TreeModel.prototype.find = function(key){
	return this.keyMap[key];
};
TreeModel.prototype.append = function(node, obj){
	// Calling append(obj) -> append(root, obj)
	if(obj === undefined){
		obj = node;
		node = this;
	}
	assert.ok(obj.key && this.keyMap[obj.key] === undefined);
	if(node.children){
		node.children.push(obj);
	}else{
		node.children = [ obj ];
	}
	this.keyMap[obj.key] = obj;
	obj.parent = node;
	return obj;
};
TreeModel.prototype.remove = function(key){
	var node = this.keyMap[key],
		parent = node.parent,
		idx = parent.children.indexOf(node);
	parent.children.splice(idx, 1);
	delete this.keyMap[key];
};


/*
 * Init a new tree with some sample data
 */
var _tree = new TreeModel();
var n = _tree.append({title: "node 1", key: "1", folder: true});
_tree.append(n, {title: "node 1.1", key: "1.1"});
_tree.append(n, {title: "node 1.2", key: "1.2"});
n = _tree.append({title: "node 2", key: "2"});
_tree.append(n, {title: "node 2.1", key: "2.1"});


/**
 * Ajax server
 */
http.createServer(function (request, response) {
	var i,
		args = url.parse(request.url, true),
		query = args.query,
		parts = args.pathname.substring(1).split("/"),
		cmd = parts[0],
		node = _tree.find(query.key),
		res = {error: "invalid command"};
	console.log("args", args, parts);
	switch(cmd){
	case "get":
		res = copyNode(node);
		break;
	case "children":
		res = [];
		if(node.children){
			for(i=0; i<node.children.length; i++){
				res.push(copyNode(node.children[i]));
			}
		}
		break;
	}
	console.log("children", node.children);
	console.log("json", copyNode(node));

	response.writeHead(200, {"Content-Type": "application/json"});
	response.end(JSON.stringify(res));
}).listen(8124);


console.log("Server running at http://127.0.0.1:8124/");
