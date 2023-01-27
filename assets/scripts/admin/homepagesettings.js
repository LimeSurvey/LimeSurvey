/**
 * JavaScript functions for HomePage Settings
 */

// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:scriptcomplete', function(){

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
     * Toggle show survey list value
     */
    $('#show_survey_list').on('switchChange.bootstrapSwitch', function(event, state) {
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
    $('#show_survey_list_search').on('switchChange.bootstrapSwitch', function(event, state) {
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
    $('#boxes_in_container').on('switchChange.bootstrapSwitch', function(event, state) {
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
            type : 'GET',
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

    /**
     * Confirmation modal
     */
    $('a[data-confirm]').click(function(ev) {
        var href = $(this).attr('href');
        if (!$('#dataConfirmModal').length) {
            $('body').append('<div  id="dataConfirmModal" class="modal  fade" role="dialog" aria-labelledby="dataConfirmLabel">  <div class="modal-dialog">    <div class="modal-content">      <div class="modal-header">   <h4 class="modal-title">'+strConfirm+'</h4>     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>      </div>      <div class="modal-body">      </div>      <div class="modal-footer"><a class="btn btn-primary" id="dataConfirmOK">'+strOK+'</a><button  type="button" class="btn btn-cancel" data-bs-dismiss="modal" >'+strCancel+'</button>      </div>    </div><!-- /.modal-content -->  </div><!-- /.modal-dialog --></div><!-- /.modal -->');
        }
        $('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
        $('#dataConfirmOK').attr('href', href);
        $('#dataConfirmModal').modal('show');
        return false;
    });


    // Create Update : icons
    if($('.option-icon').length>1){
        $('.option-icon').on('click', function (ev, that) {
            ev.preventDefault()
            var icon = $(ev.currentTarget).attr('data-icon');
            var iconId = $(ev.currentTarget).attr('data-iconId');

            // Set icon preview and hidden input
            $('input[name="Box[ico]"]').val(iconId);
            $('#chosen-icon').attr('class', icon + ' text-success');
        });
    }
});
