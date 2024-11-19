// Namespace
var LS = LS || {  onDocumentReady: {} };

/**
 * Needed to calculate correct pager position at RTL language
 * @var {number}
 */
var initialScrollValue = 0;

/**
 * True if admin uses an RTL language
 * @var {boolean}
 */
var useRtl = false;

// Return public functions for this module
LS.resp = {
    /**
     * Scroll the pager and the footer when scrolling horizontally
     * @return
     */
    setListPagerPosition : function (pager) {
        var $elListPager = $('#listPager');

        if (useRtl) {
            var scrollAmount = Math.abs($(pager).scrollLeft() - initialScrollValue);
            $elListPager.css({
                'position': 'relative',
                'right': scrollAmount
            });
        } else {
            $elListPager.css({
                'position': 'relative',
                'left': $(pager).scrollLeft()
            });
        }
    },
    /**
     * Bind fixing pager position on scroll event
     * @return
     */
    bindScrollWrapper: function () {
        LS.resp.setListPagerPosition();
        $('#bottom-scroller').scroll(function () {
            LS.resp.setListPagerPosition(this);
            $("#top-scroller").scrollLeft($("#bottom-scroller").scrollLeft());
        });
        $('#top-scroller').scroll(function () {
            LS.resp.setListPagerPosition(this);
            $("#bottom-scroller").scrollLeft($("#top-scroller").scrollLeft());
        });

        $(document).trigger('bindscroll');
    },

    /**
     * Set value of module private variable initialScrollValue
     * @param {number} val
     */
    setInitialScrollValue: function (val) {
        initialScrollValue = val;
    },

    /**
     * @param {boolean} val
     */
    setUseRtl: function (val) {
        useRtl = val;
    }
};

$(window).bind("load", function () {
    onDocumentReadyGridview();
});

$(document).off('pjax:scriptcomplete.gridscroll').on('pjax:scriptcomplete.gridscroll', onDocumentReadyGridview);

function onDocumentReadyGridview() {
    if ($('#bottom-scroller').length > 0)
        $('#fake-content').width($('#bottom-scroller')[0].scrollWidth);

    $('#top-scroller').height('18px');

    LS.resp.setInitialScrollValue($('.scrolling-wrapper').scrollLeft());
    LS.resp.setUseRtl($('input[name="rtl"]').val() === '1');
    LS.resp.bindScrollWrapper();
}
