/*
* JavaScript functions for LimeSurvey response browse
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
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
LS.resp =  {
    /**
     * Scroll the pager and the footer when scrolling horizontally
     * @return
     */
    setListPagerPosition : function (pager) {
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
    },              
    /**
     * Bind fixing pager position on scroll event
     * @return
     */
    bindScrollWrapper: function () {
        LS.resp.setListPagerPosition();
        $('#bottom-scroller').scroll(function() {
            LS.resp.setListPagerPosition(this);
            $("#top-scroller").scrollLeft($("#bottom-scroller").scrollLeft());
        });
        $('#top-scroller').scroll(function() {
            LS.resp.setListPagerPosition(this);
            $("#bottom-scroller").scrollLeft($("#top-scroller").scrollLeft());
        });

        reinstallResponsesFilterDatePicker();
        bindListItemclick();
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

/**
 * When date-picker is used in responses gridview
 * @return
 */
function reinstallResponsesFilterDatePicker() {

    // Since grid view is updated with Ajax, we need to fetch date format each update
    var $input = $('input[name="dateFormatDetails"]');
    if ($input.val()) {
        var dateFormatDetails = JSON.parse($input.val());

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
    } else {
        console.ls.log('Internal error? Run reinstallResponsesFilterDatePicker, but find no input with name dateFormatDetails.');
    }
}

function onDocumentReadyListresponse() {
    if($('#bottom-scroller').length > 0)
        $('#fake-content').width($('#bottom-scroller')[0].scrollWidth);

    $('#top-scroller').height('18px');

    LS.resp.setInitialScrollValue($('.scrolling-wrapper').scrollLeft());
    LS.resp.setUseRtl($('input[name="rtl"]').val() === '1');

    LS.resp.bindScrollWrapper();

    $('#displaymode input').on('change.listresponse', function(event){
        $('#change-display-mode-form').find('input[type=submit]').trigger('click');
    });

}

$(document).on('ready pjax:scriptcomplete',function(){
    onDocumentReadyListresponse();
    reinstallResponsesFilterDatePicker();
});

// hide Modal on submit for pjax preventDefault
$(document).on('click', '#responses-column-filter-modal :submit', function (e) {
    $('#responses-column-filter-modal').modal('hide');
});

