var IE6;

/* 
 * BGIFrame adaption (http://plugins.jquery.com/project/bgiframe)
 * Special thanks to Brandon Aaron
 */
function Ie6(api)
{
	var self = this,
		elems = api.elements,
		options = api.options,
		tooltip = elems.tooltip,
		namespace = '.ie6-' + api.id,
		bgiframe = $('select, object').length < 1,
		isDrawing = 0,
		modalProcessed = FALSE,
		redrawContainer;

	api.checks.ie6 = {
		'^content|style$': function(obj, o, v){ redraw(); }
	};

	$.extend(self, {
		init: function()
		{
			var win = $(window), scroll;

			// Create the BGIFrame element if needed
			if(bgiframe) {
				elems.bgiframe = $('<iframe class="qtip-bgiframe" frameborder="0" tabindex="-1" src="javascript:\'\';" ' +
					' style="display:block; position:absolute; z-index:-1; filter:alpha(opacity=0); ' +
						'-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";"></iframe>');

				// Append the new element to the tooltip
				elems.bgiframe.appendTo(tooltip);

				// Update BGIFrame on tooltip move
				tooltip.bind('tooltipmove'+namespace, self.adjustBGIFrame);	
			}

			// redraw() container for width/height calculations
			redrawContainer = $('<div/>', { id: 'qtip-rcontainer' })
				.appendTo(document.body);

			// Set dimensions
			self.redraw();

			// Fixup modal plugin if present too
			if(elems.overlay && !modalProcessed) {
				scroll = function() {
					elems.overlay[0].style.top = win.scrollTop() + 'px';
				};
				win.bind('scroll.qtip-ie6, resize.qtip-ie6', scroll);
				scroll(); // Fire it initially too

				elems.overlay.addClass('qtipmodal-ie6fix'); // Add fix class

				modalProcessed = TRUE; // Set flag
			}
		},

		adjustBGIFrame: function()
		{
			var dimensions = api.get('dimensions'), // Determine current tooltip dimensions
				plugin = api.plugins.tip,
				tip = elems.tip,
				tipAdjust, offset;

			// Adjust border offset
			offset = parseInt(tooltip.css('border-left-width'), 10) || 0;
			offset = { left: -offset, top: -offset };

			// Adjust for tips plugin
			if(plugin && tip) {
				tipAdjust = (plugin.corner.precedance === 'x') ? ['width', 'left'] : ['height', 'top'];
				offset[ tipAdjust[1] ] -= tip[ tipAdjust[0] ]();
			}

			// Update bgiframe
			elems.bgiframe.css(offset).css(dimensions);
		},

		// Max/min width simulator function
		redraw: function()
		{
			if(api.rendered < 1 || isDrawing) { return self; }

			var style = options.style,
				container = options.position.container,
				perc, width, max, min;

			// Set drawing flag
			isDrawing = 1;

			// If tooltip has a set height/width, just set it... like a boss!
			if(style.height) { tooltip.css(HEIGHT, style.height); }
			if(style.width) { tooltip.css(WIDTH, style.width); }

			// Simulate max/min width if not set width present...
			else {
				// Reset width and add fluid class
				tooltip.css(WIDTH, '').appendTo(redrawContainer);

				// Grab our tooltip width (add 1 if odd so we don't get wrapping problems.. huzzah!)
				width = tooltip.width();
				if(width % 2 < 1) { width += 1; }

				// Grab our max/min properties
				max = tooltip.css('max-width') || '';
				min = tooltip.css('min-width') || '';

				// Parse into proper pixel values
				perc = (max + min).indexOf('%') > -1 ? container.width() / 100 : 0;
				max = ((max.indexOf('%') > -1 ? perc : 1) * parseInt(max, 10)) || width;
				min = ((min.indexOf('%') > -1 ? perc : 1) * parseInt(min, 10)) || 0;

				// Determine new dimension size based on max/min/current values
				width = max + min ? Math.min(Math.max(width, min), max) : width;

				// Set the newly calculated width and remvoe fluid class
				tooltip.css(WIDTH, Math.round(width)).appendTo(container);
			}

			// Set drawing flag
			isDrawing = 0;

			return self;
		},

		destroy: function()
		{
			// Remove iframe
			if(bgiframe) { elems.bgiframe.remove(); }

			// Remove bound events
			tooltip.unbind(namespace);
		}
	});

	self.init();
}

IE6 = PLUGINS.ie6 = function(api)
{
	var self = api.plugins.ie6;
	
	// Proceed only if the browser is IE6
	if(PLUGINS.ie !== 6) { return FALSE; }

	return 'object' === typeof self ? self : (api.plugins.ie6 = new Ie6(api));
};

IE6.initialize = 'render';

