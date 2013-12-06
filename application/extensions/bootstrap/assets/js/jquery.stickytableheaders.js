/*! Copyright (c) 2011 by Jonas Mosbech - https://github.com/jmosbech/StickyTableHeaders
 MIT license info: https://github.com/jmosbech/StickyTableHeaders/blob/master/license.txt */

;(function ($, window, undefined) {
    'use strict';

    var pluginName = 'stickyTableHeaders';
    var defaults = {
        fixedOffset: 0
    };

    function Plugin (el, options) {
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        // Cache DOM refs for performance reasons
        base.$window = $(window);
        base.$clonedHeader = null;
        base.$originalHeader = null;

        // Keep track of state
        base.isCloneVisible = false;
        base.leftOffset = null;
        base.topOffset = null;

        base.init = function () {
            base.options = $.extend({}, defaults, options);

            base.$el.each(function () {
                var $this = $(this);

                // remove padding on <table> to fix issue #7
                $this.css('padding', 0);

                base.$originalHeader = $('thead:first', this);
                base.$clonedHeader = base.$originalHeader.clone();

                // check whether the cloned header has filters
                if($('tr.filters', base.$clonedHeader).length)
                {
                    // remove them, they will have to be added dynamically
                    $('tr.filters', base.$clonedHeader).remove();
                }
                base.$clonedHeader.addClass('tableFloatingHeader');
                base.$clonedHeader.css({
                    'position': 'fixed',
                    'top': 0,
                    'z-index': 1, // #18: opacity bug
                    'display': 'none',
                    'background-color':'#fff'
                });

                base.$originalHeader.addClass('tableFloatingHeaderOriginal');

                base.$originalHeader.after(base.$clonedHeader);

                // enabling support for jquery.tablesorter plugin
                // forward clicks on clone to original
                $('th', base.$clonedHeader).click(function (e) {
                    var index = $('th', base.$clonedHeader).index(this);
                    $('th', base.$originalHeader).eq(index).click();
                });
                $this.bind('sortEnd', base.updateWidth);
            });

            base.updateWidth();
            base.toggleHeaders();

            base.$window.scroll(base.toggleHeaders);
            base.$window.resize(base.toggleHeaders);
            base.$window.resize(base.updateWidth);
        };

        base.toggleHeaders = function () {
            if(!base.$originalHeader.parent('table').is(':visible'))
                return;
            base.$el.each(function () {
                var $this = $(this);

                var newTopOffset = isNaN(base.options.fixedOffset) ?
                    base.options.fixedOffset.height() : base.options.fixedOffset;

                var offset = $this.offset();
                var scrollTop = base.$window.scrollTop() + newTopOffset;
                var scrollLeft = base.$window.scrollLeft();
                var filters = null;
                if ((scrollTop > offset.top) && (scrollTop < offset.top + $this.height())) {
                    var newLeft = offset.left - scrollLeft;
                    if (base.isCloneVisible && (newLeft === base.leftOffset) && (newTopOffset === base.topOffset)) {
                        return;
                    }
                    filters = $('tr.filters', base.$originalHeader);
                    if(filters.length)
                    {
                        filters.insertAfter(base.$clonedHeader.children().eq(0));
                    }
                    base.$clonedHeader.css({
                        'top': newTopOffset,
                        'margin-top': 0,
                        'left': newLeft,
                        'display': 'block'
                    });
                    base.$originalHeader.css('visibility', 'hidden');
                    base.isCloneVisible = true;
                    base.leftOffset = newLeft;
                    base.topOffset = newTopOffset;
                }
                else if (base.isCloneVisible) {
                    filters = $('tr.filters', base.$clonedHeader);
                    base.$clonedHeader.css('display', 'none');
                    if(filters.length)
                    {
                        filters.insertAfter(base.$originalHeader.children().eq(0));
                    }
                    base.$originalHeader.css('visibility', 'visible');
                    base.isCloneVisible = false;
                }
            });
        };

        base.updateWidth = function () {
            // Copy cell widths and classes from original header
            $('th', base.$clonedHeader).each(function (index) {
                var $this = $(this);
                var $origCell = $('th', base.$originalHeader).eq(index);
                this.className = $origCell.attr('class') || '';
                $this.css('width', $origCell.width());
            });

            // Copy row width from whole table
            base.$clonedHeader.css('width', base.$originalHeader.width());
        };

        // Run initializer
        base.init();
    }

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
            }
        });
    };

})(jQuery, window);