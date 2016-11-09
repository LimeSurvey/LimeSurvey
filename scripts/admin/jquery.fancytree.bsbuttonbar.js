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
         name: "bsbuttonbar",
         version: "0.0.1",
         options: {},
         /* Init */
         nodeRenderTitle: function(ctx, title) {
             var node = ctx.node;
             var _customwrapper = function(span){
                //Create the necessary wrap elements
                var wrapperContainerElement = $("<div class='fancytree-innerhtml-container container-fluid'></div>"),
                    wrapperElement = $("<div class='fancytree-innerhtml-row row '></div>"),
                    //now export the created spans from the base-element
                    expandElement = span.find('.fancytree-expander'),
                    iconElement = span.find('.fancytree-icon'),
                    titleElement = span.find('.fancytree-title');
                //Add the bootstrap classes
                expandElement.addClass('col-xs-2');
                iconElement.addClass('col-xs-2');
                titleElement.addClass('col-xs-10');
                //combine in row-wrapper
                if(expandElement.hasClass('fa')){
                    wrapperElement.append(expandElement);
                    titleElement.removeClass('col-xs-10');
                    titleElement.addClass('col-xs-8');
                }
                wrapperElement
                    .append(iconElement)
                    .append(titleElement);
                //combine in outer wrapper
                wrapperContainerElement.append( wrapperElement );
                //return
                return wrapperContainerElement;
             },
             renderButtons = function(node){
                var baseButton = $('<a role="button"></a>)'),
                    buttonContainer = $('<div class="btn-group pull-right fancytree-innerhtml-buttonbar" role="group"></div>'),
                    container = $('<div class="row text-right" style="margin:0;padding:0;height:15px;"></div>');

                //console.log(node.data.buttons);
                $.each( node.data.buttonlinks, function( key, button ){
                    var jQbutton = baseButton.clone(), //Take a button as foundation
                        baseOptions = { //define some base options
                        toggle          : {method : 'combined', value : (button.toggle || "") },
                        placement       : {method : 'data', value : (button.placement || "") },
                        title           : {method : 'attr', value : (button.title || "") },
                        target          : {method : 'combined', value : (button.target || "") }
                    },
                    //Collect and filter extended options, that have to be appended/added
                        extendedOptions = {
                        cssclasses : button.cssclasses || "btn btn-xs btn-default",
                        iconHtml : button.icon ? '<i class="'+button.icon+'">&nbsp;</i>' : "&nbsp;",
                        content : button.buttontext || ""
                    }
                    //check if it calls a modal, then append the url as a data-href
                    if (button.toggle=='modal')
                    {
                        baseOptions['href'] = {method: 'combined', value: button.url};
                        jQbutton.attr("href",'#'); //Has to be there for compatibility and html-validators
                    } else {
                        baseOptions['href'] = {method: 'attr', value: button.url};
                    }
                    //Iterate through the baseOptions and set them accorting to their predefined methods
                    //@TODO: Create a method to define that in the generation, through source
                    for(var key in baseOptions){
                        var value = baseOptions[key].value, method = baseOptions[key].method;
                        switch(method){
                            case 'attr' : jQbutton.attr(key,value); break;
                            case 'data' : jQbutton.data(key,value); break;
                            case 'both' : jQbutton.attr(key,value); jQbutton.data(key,value); break;
                            case 'combined' : //fall through
                            default     : jQbutton.attr('data-'+key,value); jQbutton.data(key,value); break;
                        }
                    }
                    //Append/add the extended Options
                    jQbutton.addClass(extendedOptions.cssclasses);
                    jQbutton.append(extendedOptions.iconHtml);
                    jQbutton.append(extendedOptions.content);

                    //combine Element
                    buttonContainer.append(jQbutton);
                    jQbutton = null;

                    if (button.toggle)
                    {
                        try{jQbutton.call(button.toggle);}catch(e){}
                    }

                } );
                container.append(buttonContainer);
                $(node.span).find('.fancytree-innerhtml-container').prepend(container);
                
             };
             this._superApply(arguments);
             var newHtml = _customwrapper($(node.span));
             $(node.span).html(newHtml);
             renderButtons(node);
         }
     });
 }(jQuery));
