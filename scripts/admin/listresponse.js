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
}

$(document).ready(function(){

/*

*/

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
