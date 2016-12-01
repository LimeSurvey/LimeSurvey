function getDropSourceList(sourceNode, mode){
	var res = [];
	if ( !sourceNode ) {
		return res;
	}
	if( sourceNode.isSelected() ){
		res = sourceNode.tree.getSelectedNodes();
		if( mode === "after" ){
//            res.reverse();
		}
	} else {
		res = [ sourceNode ];
	}
	return res;
}


$(document).ready(function(){

	// fancytree on left
	$("#fancytree").fancytree({
		extensions: ["dnd"],
		source: [
				 {title: "Folder1", folder: true },
				 {title: "Folder2", folder: true },
				 {title: "Folder3", folder: true },
				 {title: "Lazy1", folder: true, lazy: true }
				 ],
//		checkbox: true,
//		icon: false,
//        activeVisible: true,
		dnd: {
			preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
			preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
			autoExpandMS: 400,
			dragStart: function(node, data) {
				// Only allow dragging items, but not folders
//                return !node.folder;
				return true;
			},
			dragEnter: function(node, data) {
				// Dropping over a folder will always create children,
				// but we never create children of items
//                return node.folder ? ["over"] : ["before", "after"];

				// This version will only accept the same tree or another draggable,
				// but not a foreign tree node
				if(data.otherNode){
					// Extern node must be dropped *inside* a folder
					return node.tree === data.otherNode.tree ? true : ["over"];
				}else{
					// Standard draggable must be dropped *inside* a folder
					return ["over"];
				}
			},
			dragDrop: function(node, data) {

				var srcNodes = getDropSourceList(data.otherNode, data.hitMode);
				// This version will only allow inter-tree d'n'd:
				if ( data.otherNode ) {
					if ( node.tree === data.otherNode.tree ) {
						// reorder folders
						data.otherNode.moveTo(node, data.hitMode);
					} else {
						alert("TODO: update tree node counters by " + srcNodes.length  + " (if this is NOT the currently active folder)");
					}
				}else{
					srcNodes = $("#sortablelist2 li.selected").hide("slow");

					alert("TODO: update tree node counters by " + srcNodes.length + "");
				}

				// This version will accept multiple node drops:
/*
				var i, srcNodes,
					newNode = null;

				if(data.otherNode){
					srcNodes = getDropSourceList(data.otherNode, data.hitMode);
					if( node.tree === data.otherNode.tree ) {
						// moving inside this tree -> sorting
//								data.otherNode.moveTo(node, data.hitMode);
						for(i = 0; i < srcNodes.length; i++){
							srcNodes[i].moveTo(node, data.hitMode);
						}
					}else{
						// inter-tree drag&drop
						for(i = 0; i < srcNodes.length; i++){
							newNode = srcNodes[i].copyTo(node, data.hitMode, function(n){
//                                      n.title = "Copy of " + n.title;
								n.selected = false;
								n.icon = false;
							});
						}
					}
				}else{
					// dropped a standard draggable (not a tree node)
					var title = ui.helper.html();
					newNode = node.addNode({title: title}, data.hitMode);
				}
				// If we dropped s.th. on a folder, expand it
				if( newNode && data.hitMode === "over" ) {
//							node.setExpanded();
					newNode.setActive();
				}
*/
			}
		},
		activate: function(event, data) {
//				alert("activate " + data.node);
		},
		lazyLoad: function(event, data) {
			data.result = {url: "test-dnd-sortabble-subfolders.json", debugDelay: 1000};
		}
	});

	/* Fancytree oin the right, simulating a list */

	$("#fancytree2").fancytree({
		extensions: ["dnd"],
		selectMode: 2, // multi-select
		icon: false,
//                source: "#sourceItems",
		dnd: {
			preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
			dragStart: function(node, data) {
				return true;
			},
			dragEnter: function(node, data) {
				return ["before", "after"];
//				if ( data.otherNode /*&& data.otherNode.tree === node.tree*/ ) {
//					return ["before", "after"];
//				} else{
//					return false;
//				}
			},
			dragDrop: function(node, data) {
				var i, srcNodes;
				if(data.otherNode){
					srcNodes = getDropSourceList(data.otherNode, data.hitMode);
					for(i = srcNodes.length - 1; i >= 0; i--){
						srcNodes[i].moveTo(node, data.hitMode);
					}
//							data.otherNode.moveTo(node, data.hitMode);
				}else{
					// dropped a standard draggable (not a tree node)
					var title = ui.helper.html();
					newNode = node.addNode({title: title}, data.hitMode);
				}
			}
		},
		click: function(event, data) {
			// Tree is in multiselect mode, so we have to handle
			// deselecting ourselves
			var anchor, idx, inc,
				tree = data.tree,
				node = data.node;

			if ( event.shiftKey ) {
				// Select contigous region (only inside a common parent)
				tree.visit(function(n){
					n.setSelected(false);
				});
				node.setSelected();

				anchor = tree.getActiveNode();
				if( anchor && anchor.parent === node.parent ) {
					// select range up to active node (only if within common parent)
					idx = anchor.getIndex();
					inc = ( idx <= node.getIndex() ) ? +1 : -1;
					do{
						anchor.setSelected();
						idx += inc;
						anchor = node.parent.children[idx];
					} while ( anchor && anchor !== node );
				}
			} else if ( event.ctrlKey || event.altKey || event.metaKey ) {
				node.toggleSelected();
			} else {
				data.tree.visit(function(n){
					n.setSelected(false);
				});
				data.node.setSelected();
			}
		},
		activate: function(event, data) {
			//              alert("activate " + data.node);
		}
	});


	$("#sortablelist2 li").draggable({
		revert: "invalid",
		connectToFancytree: true,
		cursorAt: { top: -5, left: -5 },
		helper: "clone"
	});

/*
			$("#sortablelist2").sortable({
//				revert: true
//				cursorAt: { top: -5, left: -5 }
//				helper: "clone"
			});
*/
	// click event for the highlighting of line items, filtered by modifier key
	$("#sortablelist2 li").click(function(event){
		var ctrl = event.ctrlKey || event.altKey || event.metaKey;
		if (ctrl) {
			//$("#sortablelist").sortable("option", "helper", 'clone');
			$(this).toggleClass('selected');
		} else {
			$("#sortablelist2 li").removeClass('selected');
			$(this).addClass('selected');
		}
	});

});
