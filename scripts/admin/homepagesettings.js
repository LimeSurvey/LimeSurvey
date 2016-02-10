/**
 * JavaScript functions for HomePage Settings
 */
$(document).ready(function(){

    /**
     * Toggle show logo value
     */
    $('#show_logo').on('switchChange.bootstrapSwitch', function(event, state) {
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
    $('#show_last_survey_and_question').on('switchChange.bootstrapSwitch', function(event, state) {
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
     * Save box settings
     */
    $('#save_boxes_setting').on('click', function(){
        $url = $(this).attr('data-url');
        $iBoxesByRow = $('#iBoxesByRow').val();
        $iBoxesOffset = $('#iBoxesOffset').val();
        $successMessage = $('#boxesupdatemessage').data('ajaxsuccessmessage');
        console.log($successMessage);
        $.ajax({
            url : $url+'/boxesbyrow/'+$iBoxesByRow+'/boxesoffset/'+$iBoxesOffset,
            type : 'GET',
            dataType : 'html',
            // html contains the buttons
            success : function(html, statut){
                $('#notif-container').append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>×</span></button>'+$successMessage+'</div>');
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });
});
