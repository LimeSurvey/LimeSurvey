/**!
 * jquery.fancytree.contextmenu.js
 * 3rd party jQuery Context menu extension for jQuery Fancytree
 *
 * Authors: Rodney Rehm, Addy Osmani (patches for FF)
 * Web: http://medialize.github.com/jQuery-contextMenu/
 *
 * Copyright (c) 2012, Martin Wendt (http://wwWendt.de)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://code.google.com/p/fancytree/wiki/LicenseInfe
 */
(function($, document) {
	"use strict";

	var initContextMenu = function(tree, selector, menu, actions) {
		tree.$container.on("mousedown.contextMenu", function(event) {
			var node = $.ui.fancytree.getNode(event);

			if(node) {
        $.contextMenu("destroy", "." + selector);

				node.setFocus(true);
				node.setActive(true);

				$.contextMenu({
					selector: "." + selector,
					build: function($trigger, e) {
						node = $.ui.fancytree.getNode($trigger);

						var menuItems = { };
						if($.isFunction(menu)) {
							menuItems = menu(node);
						} else if($.isPlainObject(menu)) {
							menuItems = menu;
						}

						return {
							callback: function(action, options) {
								if($.isFunction(actions)) {
									actions(node, action, options);
								} else if($.isPlainObject(actions)) {
									if(actions.hasOwnProperty(action) && $.isFunction(actions[action])) {
										actions[action](node, options);
									}
								}
							},
							items: menuItems
						};
					}
				});
			}
		});
	};

	$.ui.fancytree.registerExtension({
		name: "contextMenu",
		version: "1.0",
		contextMenu: {
      selector: "fancytree-title",
			menu: {},
			actions: {}
		},
		treeInit: function(ctx) {
			this._superApply(arguments);
			initContextMenu(ctx.tree,
                      ctx.options.contextMenu.selector || "fancytree-title",
                      ctx.options.contextMenu.menu,
                      ctx.options.contextMenu.actions);
		}
	});
}(jQuery, document));
