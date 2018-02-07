/* globals jQuery,ConsoleShim */
'use strict';
import popoverTemplate from './popoverTemplate.js';

const LimeHelper = function(action, options){
    
    let settings = {}, getTemplates = {};

    const logger = new ConsoleShim('LSHELP', !window.debugState.backend);
    
    //get basic this as selector
    const elements = $(this);

    //List of possible options
    const defaults = {
        helpIcon: '<i class="fa fa-question-circle selector__lshelp lshelp-help-icon"></i>',
        onHover: true,
        onClick: true,
        hideAutomatically: false,
        tooltipOnParent: false,
        helpTextTitle: 'helptitle',
        helpTextData: 'help',
        helpLinkData: 'gethelp'
    };

    //define private methods
    const
        _parseOptions = () => {
            return $.extend({},defaults,options);
        },
        _addIcon = (element)=>{
            $(element).append('&nbsp;').append($(settings.helpIcon));
        },
        _createPopover = (element) => {

            let content =  $(element).data(settings.helpTextData);

            if(content == '') {
                content = $(element).attr('title');
            }

            if($(element).data(settings.helpLinkData)) {
                content += getTemplates.moreTemplate($(element).data(settings.helpLinkData));
            }

            let popoverObject = {
                content: content,
                html: true,
                template : getTemplates.basicTemplate(),
                trigger : (settings.onHover ? 'hover ' : '') + (settings.onClick ? 'click ': '') + 'manual',
                title: $(element).data(settings.helpTextTitle) || $(element).attr('title') || ''
            };

            logger.log("Popover object", popoverObject);
            logger.log("This element", $(element).data());
            $(element).find('.selector__lshelp').popover(popoverObject);
        },
        _bindActions = (element)=>{
            if(settings.onHover) {
                $(element).find('.selector__lshelp').on('mouseenter', _onHoverInItem);
                $(element).find('.selector__lshelp').on('mouseleave', _onHoverOutItem);
            }
            if(settings.onClick) {
                $(element).find('.selector__lshelp').on('mouseleave', _onClickItem);
            }
        
        },
        _onClickItem = function(event) {
            logger.log('Item clicked', event.target);

        },
        _onHoverInItem = function(event) {
            logger.log('Item hovered', event.target);
        },
        _onHoverOutItem = function(event) {
            logger.log('Item unhovered', event.target);

        };
    
    //define public methods
    const 
        init = () => {
            settings = _parseOptions(options);
            getTemplates = popoverTemplate({more: ''});
            elements.each((i, element) => {
                _addIcon(element);
                _createPopover(element);
                _bindActions(element);
            });
        },
        destroy = () => {},
        show = () => {},
        hide = () => {};
    
    switch(action) {
    case 'destroy' : destroy(); break;
    case 'show'    : show(); break;
    case 'hide'    : hide(); break;
    case 'init'    : //fallthrough
    default   : init(); break;
    }
};

export default LimeHelper;
