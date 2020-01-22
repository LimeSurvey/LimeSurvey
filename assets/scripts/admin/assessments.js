
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

var assessmentTable = '#selector__assessment-table',
    $assessmentTable = $(assessmentTable);
var bindAction = function(){

    $('.action_assessments_deleteModal').on('click.assessments', function(){
        $('#assessmentsdeleteform').find('input[name=id]').val($(this).closest('tr').data('assessment-id'));
        $('#assesements-delete').modal('show');
    });

    $('.action_assessments_editModal').on('click.assessments', function(){
        $('input[name=action]').val('assessmentupdate');
        $.ajax({
            url: loadEditUrl,
            data: {id: $(this).closest('tr').data('assessment-id')},// crsf is already in ajaxsetup
            method: 'GET',
            success: function(responseData){
                $("#in_survey_common").css({cursor: ""});
                $.each(responseData.editData, function(key, value){
                    var itemToChange = $('#assessmentsform').find('[name='+key+']');
                    if(!itemToChange.is('input[type=checkbox]') && !itemToChange.is('input[type=radio]')) {
                        if (CKEDITOR.instances[key]) {
                            CKEDITOR.instances[key].setData(value);
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

        // Clear all fields.
        $.each(CKEDITOR.instances, function(name, instance) {
            instance.setData('');
        });

        // We clear only visible input to keep the CSRF token
        $('#assessmentsform input:visible').val('');
        // TODO: Clear <select> and radio buttons?

        $('#assesements-edit-add').modal('show');
    });

    $('#assessmentsdeleteform').on('submit', function(e){
        e.preventDefault();
        var params = $('#assessmentsdeleteform').serializeArray();
        var url = $('#assessmentsdeleteform').attr('action');
        $.ajax({
            url : url,
            method: 'post',
            data: params,
            success : function(){
                $('#assessmentsdeleteform').find('input[name=id]').val(' ');
                $('#assesements-delete').modal('hide');
                $.fn.yiiGridView.update('assessments-grid');
            },
            error: function(err){
                console.ls.error(err);
            }
        })
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
    )
});
