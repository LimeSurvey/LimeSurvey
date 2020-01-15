(function () {
'use strict';

/* globals jQuery */

var popoverTemplate = function popoverTemplate(replacements) {

    var defaultReplacements = {
        more: 'More on this:',
        icon: 'fa-question'
    };

    var oReplacements = jQuery.extend({}, defaultReplacements, replacements);

    var basicTemplate = function basicTemplate() {
        return '\n<div class="popover" role="tooltip">\n    <div class="arrow"></div>\n    <h3 class="popover-title"><i class="fa ' + oReplacements.icon + '"></i> </h3>\n    <div class="popover-content">\n    </div>\n</div>\n';
    };

    var moreTemplate = function moreTemplate(link) {
        return '\n<div class="lshelp-popover-footer">\n    ' + oReplacements.more + ' \n    <a href="' + link.href + '" title="' + link.title + '" target="_blank">' + link.text + '</a>\n</div>\n';
    };
    return {
        basicTemplate: basicTemplate,
        moreTemplate: moreTemplate
    };
};

/* globals jQuery,ConsoleShim */
var LimeHelper = function LimeHelper(action, options) {

    var settings = {},
        getTemplates = {};

    var logger = new ConsoleShim('LSHELP', !window.debugState.backend);

    //get basic this as selector
    var elements = $(this);

    //List of possible options
    var defaults = {
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
    var _parseOptions = function _parseOptions() {
        return $.extend({}, defaults, options);
    },
        _addIcon = function _addIcon(element) {
        $(element).append('&nbsp;').append($(settings.helpIcon));
    },
        _createPopover = function _createPopover(element) {

        var content = $(element).data(settings.helpTextData);

        if (content == '') {
            content = $(element).attr('title');
        }

        if ($(element).data(settings.helpLinkData)) {
            content += getTemplates.moreTemplate($(element).data(settings.helpLinkData));
        }

        var popoverObject = {
            content: content,
            html: true,
            template: getTemplates.basicTemplate(),
            trigger: (settings.onHover ? 'hover ' : '') + (settings.onClick ? 'click ' : '') + 'manual',
            title: $(element).data(settings.helpTextTitle) || $(element).attr('title') || ''
        };

        logger.log("Popover object", popoverObject);
        logger.log("This element", $(element).data());
        $(element).find('.selector__lshelp').popover(popoverObject);
    },
        _bindActions = function _bindActions(element) {
        if (settings.onHover) {
            $(element).find('.selector__lshelp').on('mouseenter', _onHoverInItem);
            $(element).find('.selector__lshelp').on('mouseleave', _onHoverOutItem);
        }
        if (settings.onClick) {
            $(element).find('.selector__lshelp').on('mouseleave', _onClickItem);
        }
    },
        _onClickItem = function _onClickItem(event) {
        logger.log('Item clicked', event.target);
    },
        _onHoverInItem = function _onHoverInItem(event) {
        logger.log('Item hovered', event.target);
    },
        _onHoverOutItem = function _onHoverOutItem(event) {
        logger.log('Item unhovered', event.target);
    };

    //define public methods
    var init = function init() {
        settings = _parseOptions(options);
        getTemplates = popoverTemplate({ more: '' });
        elements.each(function (i, element) {
            _addIcon(element);
            _createPopover(element);
            _bindActions(element);
        });
    };

    switch (action) {
        case 'destroy':
            break;
        case 'show':
            break;
        case 'hide':
            break;
        case 'init': //fallthrough
        default:
            init();break;
    }
};

/* globals jQuery */
$.fn.limeHelper = LimeHelper;

}());
