
// Namespace
var LS = LS || {  onDocumentReady: {} };


function jquery_goodchars(e, goods)
{
   var key, keychar;
   key = e.which;
   if (key == null) return true;

   // get character
   keychar = String.fromCharCode(key);
   keychar = keychar.toLowerCase();
   goods = goods.toLowerCase();

   // check goodkeys
   if (goods.indexOf(keychar) != -1)
        return true;

   // control keys
   if ( key==null || key==0 || key==8 || key==9  || key==27 )
      return true;

   // else return false
   return false;
}

/* todo: is this used somewhere??
var assessmentTable = '#selector__assessment-table',
    $assessmentTable = $(assessmentTable);

 */

var bindAction = function(){

    $('.action_assessments_deleteModal').on('click.assessments', function(){
        $('#assessmentsdeleteform').find('input[name=id]').val($(this).data('assessment-id'));
        $('#assesements-delete').modal('show');
    });

    $('.action_assessments_editModal').on('click.assessments', function(){
        $('input[name=action]').val('assessmentupdate');
        var linkLoadEditUrl = document.getElementById('loadEditUrl_forModalView');
        var loadEditUrl = linkLoadEditUrl.dataset.editurl;
        $.ajax({
            url: loadEditUrl,
            data: {id: $(this).data('assessment-id')},// crsf is already in ajaxsetup
            method: 'GET',
            success: function(responseData){
                $("#in_survey_common").css({cursor: ""});
                $.each(responseData.editData, function(key, value){
                    var itemToChange = $('#assessmentsform').find('[name='+key+']');
                    if(!itemToChange.is('input[type=checkbox]') && !itemToChange.is('input[type=radio]')) {
                        const oCKeditor_itemToChange = CKEDITOR.instances[key];
                        if (oCKeditor_itemToChange) {
                            oCKeditor_itemToChange.setData(value);
                        } else {
                            itemToChange.val(value).trigger('change');
                        }
                    } else {
                        $('#assessmentsform').find('[name='+key+'][value='+value+']').prop('checked',true).trigger('change');
                    }
                });
                $('#assesements-edit-add').modal('show');
            },
            error: function(err){
                console.ls.error(err);
            }
        });
    });

    $('#selector__assessment-add-new').on('click.assessments', function(){
        var editAddForm = $('#assesements-edit-add');

        $('input[name=action]').val('assessmentadd');

        editAddForm.modal('show');
        editAddForm.on('shown.bs.modal',  function removeValues(){
            // We clear only visible input to keep the CSRF token
            $('#assessmentsform input:visible:not([type=radio]):not([type=checkbox])').val('');
            $('#assessmentsform textarea:visible').val('');
            $(this).off('shown.bs.modal', removeValues);
        });
        // TODO: Clear <select> and radio buttons?

    });

    $('#assessmentsdeleteform').on('submit', function(e){
        e.preventDefault();
        var params = $('#assessmentsdeleteform').serializeArray();
        var url = $('#assessmentsdeleteform').attr('action');
        $.ajax({
            url : url,
            method: 'post',
            data: params,
            success : function(result) {
                if (result.success) {
                    window.LS.ajaxAlerts(result.success, 'success');
                } else {
                    var errorMsg = result.error.message ? result.error.message : result.error;
                    if (!errorMsg) errorMsg = "Unexpected error";
                    window.LS.ajaxAlerts(errorMsg, 'danger');
                }
                $('#assessmentsdeleteform').find('input[name=id]').val(' ');
                $('#assesements-delete').modal('hide');
                $.fn.yiiGridView.update('assessments-grid');
            },
            error: function(err){
                console.ls.error(err);
            }
        });
    });

    $('#selector__assessements-delete-modal').on('click.assessments', function(){
        $(this).closest('form').trigger('submit');
    });

    $('#selector__assessments-save-modal').on('click.assessments', function(){
        $(this).closest('form').trigger('submit');
    });
};

$(document).on('ready  pjax:scriptcomplete', function(){
    bindAction();
    //$('#languagetabs').tabs();
    if ($(".assessmentlist tbody tr").size()>0)
    {
        $(".assessmentlist").tablesorter({sortList: [[0,0]] });
    }
    $('#radiototal,#radiogroup').change(
        function()
        {
              if ($('#radiototal').attr('checked')==true)
              {
                $('#gid').attr('disabled','disabled');
              }
              else
              {
                if ($('#gid>option').length==0){
                  $('#radiototal').attr('checked',true);
                  alert (strnogroup);
                }
                else
                {
                    $('#gid').attr('disabled',false);
                }
              }
        }
    )
    $('#radiototal,#radiogroup').change();
    $('.numbersonly').keypress(
        function(e){
            return jquery_goodchars(e,'1234567890-');
        }
    );
});
