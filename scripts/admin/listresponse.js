/*
* JavaScript functions for LimeSurvey response browse
*
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later


/**
 * Scroll the pager and the footer when scrolling horizontally
 */
$(document).ready(function(){
    $('#ListPager').css({
        position: 'relative'
    });

    $('.scrolling-wrapper').scroll(function(){
        $('#ListPager').css({
            'left': $(this).scrollLeft() ,
        });
    });

});

$(document).on("click","[data-delete]",function(event){
    event.preventDefault();
    var responseid=$(this).data("delete");
    var url=$(this).attr("href"); // Or replace responseid  by post if needed
    var buttons = {};
    buttons[sDelCaption] = function(){
        $.ajax({
            url : url,
            type : "POST"
        })
        .done(function() {
            jQuery("#displayresponses").delRowData(responseid);
        });
        $( this ).dialog( "close" );
    };
    buttons[sCancel] = function(){ $( this ).dialog( "close" ); };
    var dialog=$("<p>"+strdeleteconfirm+"</p>").dialog({
        modal:true,
        buttons: buttons
    });
});
