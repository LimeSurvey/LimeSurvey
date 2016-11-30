/**!
 * jquery.fancytree.hotkeys.js
 * 3rd party jQuery Hotkeys extension for jQuery Fancytree
 *
 * Copyright 2010, John Resig
 * Web: https://github.com/jeresig/jquery.hotkeys/
 *
 * Copyright (c) 2012, Martin Wendt (http://wwWendt.de)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://code.google.com/p/fancytree/wiki/LicenseInfe
 */
(function($, document) {
  "use strict";

	var initHotkeys = function(tree, data) {
		$.each(data, function(event, keys) {
			$.each(keys, function(key, handler) {
				$(tree.$container).on(event, null, key, function() {
					var node = tree.getActiveNode();
					handler(node);
				});
			});
		});
	};

	$.ui.fancytree.registerExtension({
		name: "hotkeys",
		version: "1.0",
                hotkeys: { },
		treeInit: function(ctx) {
			this._superApply(arguments);
                        initHotkeys(this, ctx.options.hotkeys);
		}
	});
}(jQuery, document));
