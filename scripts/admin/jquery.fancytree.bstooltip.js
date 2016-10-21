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
                options['title']     = node.data.title;
                options['trigger']   = node.data.trigger;
                options['viewport']  = node.data.viewport;

                $("span.fancytree-title", node.span).tooltip(options);
            }

            if (node.data.buttonlinks)
            {
                //console.log(node.data.buttons);
                $.each( node.data.buttonlinks, function( key, button ){
                    //console.log(button);

                    var jQbutton = $('<a role="button"></a>)');
                    jQbutton.attr("href",button.url);

                    if (button.cssclasses)
                    {
                        jQbutton.addClass(button.cssclasses);
                    }
                    else
                    {
                        jQbutton.addClass("btn btn-xs btn-default");
                    }

                    if (button.toggle)
                    {
                        jQbutton.data("toggle", button.toggle);
                    }
                    if (button.placement)
                    {
                        jQbutton.data("placement", button.placement);
                    }

                    if (button.title)
                    {
                        jQbutton.attr("title", button.title);
                    }


                    if (button.icon)
                    {
                        jQbutton.append('<i class="'+button.icon+'"></i>');
                    }

                    if (button.buttontext)
                    {
                        jQbutton.append(button.buttontext);
                    }

                    $("span.fancytree-title", node.span).append(' ').append( jQbutton );

                    if (button.toggle=='tooltip')
                    {
                        jQbutton.tooltip();
                    }

                    if (button.toggle=='popover')
                    {
                        jQbutton.popover();
                    }

                } );
            }

         }
     });
 }(jQuery));
