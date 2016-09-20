/*
* JavaScript functions for LimeSurvey response browse
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later


/**
 * Scroll the pager and the footer when scrolling horizontally
 */
function setListPagerPosition(){
    var $elListPager = $(document).find('#ListPager');
    $elListPager.css({
        position: 'relative',
        'left': $(document).find('.scrolling-wrapper').scrollLeft() ,
    });

}

function bindScrollWrapper(){
    setListPagerPosition();
    $(document).find('.scrolling-wrapper').scroll(function(){
        setListPagerPosition();
    });

    reinstallResponsesFilterDatePicker();
}

$(document).ready(function(){

    bindScrollWrapper();

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
