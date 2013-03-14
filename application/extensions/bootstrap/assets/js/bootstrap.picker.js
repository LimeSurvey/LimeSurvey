!function ($) {

    "use strict"; // jshint ;_;


    /* Picker PUBLIC CLASS DEFINITION
     * =============================== */

    var Picker = function (element, options) {
        this.init('picker', element, options)
    }


    /* NOTE: PICKER EXTENDS BOOTSTRAP-TOOLTIP.js
     ========================================== */

    Picker.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, {

        constructor:Picker, setContent:function () {
            $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
                // make sure
                $('a.pickeron').removeClass('pickeron').picker('toggle');
            });
            var $tip = this.tip()
                , title = this.getTitle()
                , content = this.getContent()

            $tip.find('.picker-title')[this.options.html ? 'html' : 'text'](title)
            $tip.find('.picker-content > *')[this.options.html ? 'html' : 'text'](content)

            $tip.removeClass('fade top bottom left right in')
        }, hasContent:function () {
            return this.getTitle() || this.getContent()
        }, getContent:function () {
            var content
                , $e = this.$element
                , o = this.options

            content = $e.attr('data-content')
                || (typeof o.content == 'function' ? o.content.call($e[0]) : o.content)

            return content
        }, tip:function () {
            if (!this.$tip) {
                this.$tip = $(this.options.template);
                if(this.options.width)
                {
                    this.$tip.css('width', this.options.width);
                }
            }
            return this.$tip
        }, destroy:function () {
            this.hide().$element.off('.' + this.type).removeData(this.type)
        }
    })


    /* PICKER PLUGIN DEFINITION
     * ======================= */

    $.fn.picker = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('picker')
                , options = typeof option == 'object' && option
            if (!data) $this.data('picker', (data = new Picker(this, options)))
            if (typeof option == 'string') data[option]()
        })
    }

    $.fn.picker.Constructor = Picker

    $.fn.picker.defaults = $.extend({}, $.fn.tooltip.defaults, {
        placement:'bottom', trigger:'manual', content:'', template:'<div class="picker dropdown-menu"><div class="picker-title"></div><div class="picker-content"><p></p></div></div>'
    });

    $(document).on('click', function(){
        $('a.pickeron').removeClass('pickeron').picker('toggle');
    });
}(window.jQuery);