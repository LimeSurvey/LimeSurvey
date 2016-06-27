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

                    var buttonHtml = '<a href="'+button.url+'" role="button"';

                    if (button.cssclasses)
                    {
                        buttonHtml += ' class="'+button.cssclasses+'"';
                    }
                    else
                    {
                        buttonHtml += ' class="btn btn-xs btn-default" ';
                    }

                    if (button.toggle)
                    {
                        buttonHtml += ' data-toggle="'+button.toggle+'"';
                    }

                    if (button.placement)
                    {
                        buttonHtml += ' data-placement="'+button.placement+'"';
                    }

                    if (button.title)
                    {
                        buttonHtml += ' title="'+button.title+'"';
                    }

                    buttonHtml += '>';

                    if (button.icon)
                    {
                        buttonHtml += '<span class="'+button.icon+'"></span>';
                    }

                    if (button.buttontext)
                    {
                        buttonHtml += button.buttontext;
                    }

                    buttonHtml += '</a>';

                    var $elButton = $(buttonHtml)

                    $("span.fancytree-title", node.span).append(' ').append( $elButton );

                    if (button.toggle=='tooltip')
                    {
                        $elButton.tooltip();
                    }

                    if (button.toggle=='popover')
                    {
                        $elButton.popover();
                    }

                } );
            }

         }
     });
 }(jQuery));
