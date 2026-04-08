/**
 * JavaScript functions for HomePage Settings
 */

// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:scriptcomplete', function(){

    /**
     * Toggle show logo value
     */
    $('#show_logo input').on('change', function(event, state) {
        $url = $('#show_logo-url').attr('data-url');
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });

    /**
     * Toggle show last_survey_and_question value
     */
    $('#show_last_survey_and_question input').on('change', function(event, state) {
        $url = $('#show_last_survey_and_question-url').attr('data-url');
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });

    /**
     * Toggle show survey list value
     */
    $('#show_survey_list input').on('change', function(event, state) {
        $url = $('#show_survey_list-url').attr('data-url');
        console.ls.log($url);
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });

    /**
     * Toggle show survey list search value
     */
    $('#show_survey_list_search input').on('change', function(event, state) {
        $url = $('#show_survey_list_search-url').attr('data-url');
        console.ls.log($url);
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });

    /**
     * Toggle wrap boxes in container value
     */
    $('#boxes_in_container input').on('change', function(event, state) {
        $url = $('#boxes_in_container-url').attr('data-url');
        console.ls.log($url);
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });

    /**
     * Save box settings
     */
    $('#save_boxes_setting').on('click', function(){
        $url = $(this).attr('data-url');
        $iBoxesByRow = $('#iBoxesByRow').val();
        $iBoxesOffset = $('#iBoxesOffset').val();
        $successMessage = $('#boxesupdatemessage').data('ajaxsuccessmessage');
        $errorMessage = $('#boxeserrormessage').data('ajaxerrormessage');
        $.ajax({
            url : $url+'/boxesbyrow/'+$iBoxesByRow+'/boxesoffset/'+$iBoxesOffset,
            method: "POST",
            data: "",
            dataType : 'html',
            // html contains the buttons
            success : function(html, statut){
                window.LS.ajaxAlerts($successMessage, 'success', {showCloseButton: true});
            },
            error :  function(html, statut){
                window.LS.ajaxAlerts($errorMessage, 'danger', {showCloseButton: true});
            }
        });
    });

    // Create Update : icons
    if($('.option-icon').length>1){
        $('.option-icon').on('click', function (ev, that) {
            ev.preventDefault()
            var icon = $(ev.currentTarget).attr('data-icon');
            var iconId = $(ev.currentTarget).attr('data-iconId');

            // Set icon preview and hidden input
            $('input[name="Box[ico]"]').val(icon);
            $('#chosen-icon').attr('class', icon + ' text-success');
        });
    }
});
