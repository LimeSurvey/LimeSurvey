/*
* JavaScript functions for LimeSurvey response browse
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later

// Module
LS.resp = (function() {

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

    /**
     * Scroll the pager and the footer when scrolling horizontally
     * @return
     */
    function setListPagerPosition(pager) {
        var $elListPager = $('#ListPager');

        if (useRtl) {
            var scrollAmount = Math.abs($(pager).scrollLeft() - initialScrollValue);
            $elListPager.css({
                'position': 'relative',
                'right': scrollAmount
            });
        }
        else {
            $elListPager.css({
                'position': 'relative',
                'left': $(pager).scrollLeft()
            });
        }
    }

    // Return public functions for this module
    return {

        /**
         * Bind fixing pager position on scroll event
         * @return
         */
        bindScrollWrapper: function () {
            setListPagerPosition();
            $('#bottom-scroller').scroll(function() {
                setListPagerPosition(this);
                $("#top-scroller").scrollLeft($("#bottom-scroller").scrollLeft());
            });
            $('#top-scroller').scroll(function() {
                setListPagerPosition(this);
                $("#bottom-scroller").scrollLeft($("#top-scroller").scrollLeft());
            });

            reinstallResponsesFilterDatePicker();
        },

        /**
         * Set value of module private variable initialScrollValue
         * @param {number} val
         */
        setInitialScrollValue: function(val) {
            initialScrollValue = val;
        },

        /**
         * @param {boolean} val
         */
        setUseRtl: function(val) {
            useRtl = val;
        }
    };
})();

$(document).ready(function(){

    $('#fake-content').width($('#bottom-scroller')[0].scrollWidth);
    $('#top-scroller').height('18px');
    
    LS.resp.setInitialScrollValue($('.scrolling-wrapper').scrollLeft());
    LS.resp.setUseRtl($('input[name="rtl"]').val() === '1');

    LS.resp.bindScrollWrapper();

    $('#display-mode').click(function(event){
        event.preventDefault();

        var $that        = $(this);
        var $actionUrl   = $(this).data('url');
        var $display     = $that.find('input:not(:checked)').val();
        var $postDatas   = {state:$display};

        $.ajax({
            url  : encodeURI($actionUrl),
            type : 'POST',
            data :  $postDatas,

            // html contains the buttons
            success : function(html, statut){
                location.reload();
            },
            error :  function(html, statut){
                console.log(html);
            }
        });

    });

});

/**
 * When date-picker is used in responses gridview
 * @return
 */
function reinstallResponsesFilterDatePicker() {

    // Since grid view is updated with Ajax, we need to fetch date format each update
    var dateFormatDetails = JSON.parse($('input[name="dateFormatDetails"]').val());

    $('#SurveyDynamic_startdate').datetimepicker({
        format: dateFormatDetails.jsdate
    });
    $('#SurveyDynamic_datestamp').datetimepicker({
        format: dateFormatDetails.jsdate
    });

    $('#SurveyDynamic_startdate').on('focusout', function() {
        var data = $('#responses-grid .filters input, #responses-grid .filters select').serialize();
        $.fn.yiiGridView.update('responses-grid', {data: data});
    });

    $('#SurveyDynamic_datestamp').on('focusout', function() {
        var data = $('#responses-grid .filters input, #responses-grid .filters select').serialize();
        $.fn.yiiGridView.update('responses-grid', {data: data});
    });

}
