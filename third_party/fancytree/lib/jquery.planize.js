/**
 * The planize jQuery plugin adds some features for dealing with hierarchical headings in a given DOM element.
 *
 *  - adds enumerations and anchors in all headings,
 *  - can generates an HTML table of content and append it to an existing DOM element,
 *  - in an unobstrusive way.
 *
 * Example of use:
 *
 *    $('html *').planize();
 *
 * Configuration object parameters documentation:
 *  - add_anchors      : generates anchors for each header (automatically set to true if `generate_toc` is set to true)
 *  - callback         : a function called when processing is finished
 *  - debug            : prints pretty debug messages into the firebug or opera console, if available
 *  - generate_toc     : generates an html unordered list containing the table of content of the document
 *  - min_level        : min heading level needed to be included in toc and be renumbered (0 = all headings)
 *  - max_level        : max heading level needed to be included in toc and be renumbered (0 = all headings)
 *  - number_suffix    : heading identifier suffix, eg. ')' in "1.2.3)"
 *  - number_separator : separator for numbers, eg. '.' in "1.2.3)"
 *  - toc_elem         : the dom element where the toc will be append
 *  - toc_none         : the message to display if no headings have been found in the current document
 *  - toc_title        : the title of the table of content
 *
 * @requires  jQuery v1.2 or higher
 * @author    Nicolas Perriault <nperriault _at_ gmail _dot_ com>
 * @license   MIT (http://www.opensource.org/licenses/mit-license.php)
 * @param     Object  config  Plugin configuration
 * @return    jQuery(this)
 *
 */
(function(jQuery){

  jQuery.fn.planize = function(config) {

	var self          = jQuery(this);
	var processed     = false;
	var toc           = '';
	var defaultConfig = {
	  add_anchors      : false,
	  callback         : null,
	  debug            : false,
	  generate_toc     : false,
	  min_level        : 1,
	  max_level        : 6,
	  number_suffix    : '',
	  number_separator : '.',
	  toc_elem         : null,
	  toc_none         : 'No heading found for this document',
	  toc_title        : 'Table of contents'
	};
	config = jQuery.extend(defaultConfig, config);

	/**
	 * Prepends all headers text with the current tree number reference

	 * @return void
	 */
	var process = function() {
	  var level       = 0;
	  var levels      = [0,0,0,0,0,0,0];
	  var hLevelText  = '';
	  var prependText = '';
	  var prevLevel   = 0;
	  var n           = 0;
	  self.children('*:header:visible').each(function(index, heading) {
		log('Processing heading %o', heading);
		level = parseInt(heading.tagName.substring(1));
		if (config.min_level <= level && level <= config.max_level) {
		  n++;
		  levels[level]++;
		  for (var l = 1; l <= level; l++) {
			hLevelText += levels[l] > 0 ? levels[l] + config.number_separator : '';
		  }
		  levels[level + 1] = 0;
		  hLevelText = hLevelText.substring(0, hLevelText.length - 1);
		  prependText = hLevelText;
		  if (config.generate_toc || config.add_anchors) {
			if (config.generate_toc) {
			  var link = '<a href="#h' + hLevelText + '">' +jQuery('<span/>').text(jQuery(this).text()).html() + '</a>';
			  var elem = "\n"+'<li>' + hLevelText + (config.number_suffix ? config.number_suffix : '') + ' ' + link;
			  if (level < prevLevel) {
				log(hLevelText + ', unnesting because:' + level + '<' + prevLevel);
				var unnest = '';
				while (level < prevLevel) {
				  unnest += '</ul>';
				  prevLevel--;
				}
				toc += unnest + elem + '</li>';
			  } else if (level > prevLevel) {
				log(hLevelText + ', nesting because:' + level + '>' + prevLevel);
				toc += '<ul>' + elem;
			  } else {
				log(hLevelText + ', same level (' + level + ')');
				toc += elem;
			  }
			}
			prependText = '<span id="h' + hLevelText + '"></span>' + hLevelText;
		  }
		  if (config.number_suffix) {
			prependText += config.number_suffix;
		  }
		  jQuery(this).prepend(prependText + ' ');
		  prependText = hLevelText = '';
		  prevLevel = level;
		}
	  });

	  if (config.generate_toc) {
		if (config.toc_title) {
		  toc = '<h4>' + config.toc_title + '</h4>' + toc;
		}
		if (n == 0) {
		  toc += config.toc_none ? '<p>' + config.toc_none + '</p>' : '';
		}
		jQuery(config.toc_elem ? config.toc_elem : 'body').append(toc);
	  }

	  processed = true;
	};

	/**
	 * Logs a message into the firebug or opera console if available
	 *
	 */
	var log = function() {
	  if (!config.debug) {
		return;
	  }
	  try {
		console.log.apply(console, arguments);
	  } catch(e) {
		try {
		  opera.postError.apply(opera, arguments);
		} catch(e){}
	  }
	}

	process();

	if (config.callback) {
	  config.callback(config.toc_elem);
	}

	return jQuery(this);
  };

})(jQuery);
