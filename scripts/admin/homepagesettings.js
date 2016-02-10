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

    });
});
