/*!
 * jquery.fancytree.bstooltip.js
 *
 * Generated bootstrap tooltip
 * (Extension module for jquery.fancytree.js: https://github.com/mar10/fancytree/)
 *
 *
 * @version @VERSION
 * @date @DATE
 */

 ;(function($, undefined) {
     "use strict";
     $.ui.fancytree.registerExtension({
         name: "bstooltip",
         version: "0.0.1",
         options: {},

         /* Init */
         nodeRenderTitle: function(ctx, title) {
             var node = ctx.node;
             this._superApply(arguments);

            if (node.data.toggle=='tooltip')
            {
                var options = [];

                options['animation'] = node.data.animation;
                options['container'] = node.data.container;
                options['delay']     = node.data.delay;
                options['html']      = node.data.html;
                options['placement'] = node.data.placement;
                options['selector']  = node.data.selector;
                options['template']  = node.data.template;
                options['trigger']   = node.data.trigger;
                options['viewport']  = node.data.viewport;
                options['title']     = node.data.title;

                $("span.fancytree-title", node.span).tooltip(options);
            } 
         }
     });
 }(jQuery));
