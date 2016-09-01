function bindButtons(){
    // Code for AJAX download
    jQuery.download = function(url, data, method){
        //url and data options required
        if( url && data ){
            //data can be string of parameters or array/object
            data = typeof data == 'string' ? data : jQuery.param(data);
            //split params into form inputs
            var inputs = '<input type="hidden" name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'">';
            jQuery.each(data.split('&'), function(){
                var pair = this.split('=');
                inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'">';
            });
            //send request
            jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
            .appendTo('body').submit().remove();
        };
    };

    $('.action_participant_editModal').on('click', function(e){
        e.preventDefault();
        console.log("I sure hope it's working");
        $('#participant_edit_modal').modal('show');
        $('#participant_edit_modal').on('hidden.bs.modal', function(){
             $.fn.yiiGridView.update('list_central_participants',{});
        })
       jQuery.ajax({
           url: openEditParticipant, 
           data: {'participant_id' : $(this).closest('tr').data('participant_id')},
           method: 'POST',
           success: function(page){
            $('#participant_edit_modal').find('.modal-content').html(page);
            $('#participant_edit_modal').find('.action_save_modal_editParticipant').on('click', function(e){
                e.preventDefault();

                var action = $('#participant_edit_modal').find('#editPartcipantActiveForm').attr('action');
                var data = $('#participant_edit_modal').find('#editPartcipantActiveForm').serializeArray();
                //$.blockUI({ message: null }); 
                $.ajax({
                    url: action,
                    data: data,
                    method: 'POST',
                    dataType: "json",
                    success: function(result){
                        //$.unblockUI;
                        $('#participant_edit_modal').find('.modal-body ').html(result.successMessage);
                        $('#participant_edit_modal').find('.action_save_modal_editParticipant').css('display','none');
                        setTimeout(function(){$('#participant_edit_modal').modal('hide');},3500);
                    },
                    error : function(err){
                        //$.unblockUI;
                        console.log(arguments);
                        alert(err);
                    }
                })
                console.log( $('#participant_edit_modal').find('#editPartcipantActiveForm').serializeArray());
            })
            }
       });
    })
    $('#action_toggleAllParticipant').on('click', function(){
        $('.selector_participantCheckbox').prop('checked',$('#action_toggleAllParticipant').prop('checked'));
    });

    $('.action_changeBlacklistStatus').bootstrapSwitch();

    $('.action_changeBlacklistStatus').on('switchChange.bootstrapSwitch', function(event,state){
        var self = this;
        $.ajax({
            url: changeBlacklistStatus, 
            method: "POST",
            data: {'participant_id': $(self).closest('tr').data('participant_id'), 'blacklist': state},
            dataType: 'json', 
            success: function(resolve){
                console.log(resolve);
                $(self).prop("checked", (resolve.newValue == "Y"));
            }
        })
    });

    $('#pageSizeParticipantView').on("change", function(){
        $.fn.yiiGridView.update('list_central_participants',{ data:{ pageSizeParticipantView: $(this).val() }});
    });

            
    $('#export').click(function(){
        var dialog_buttons={};
        dialog_buttons[exportBtn]=function(){
            $.download(exportToCSVURL,{ attributes: $('#attributes').val().join(' ') },"POST");
            $(this).dialog("close");
        };            
        dialog_buttons[cancelBtn]=function(){
            $(this).dialog("close");};

        $.post(exporttocsvcountall,
            function(data) {
                count = data;
                if(count == 0)
                {
                    $('#exportcsvallnorow').dialog({
                        modal: true,
                        title: error,
                        buttons: dialog_buttons,
                        width : 300,
                        height : 160
                    });
                }
                else
                {
                    $('#exportcsv').dialog({
                        modal: true,
                        title: count,
                        buttons: dialog_buttons,
                        width : 600,
                        height : 300,
                        open: function(event, ui) {
                            $('#attributes').multiselect({ includeSelectAllOption:true, 
                                                           selectAllValue: '0',
                                                           selectAllText: sSelectAllText,
                                                           nonSelectedText: sNonSelectedText,
                                                           nSelectedText: sNSelectedText,
                                                           maxHeight: 140 });
                        }
                    });

                    /* $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));*/
                }
        });
    });
};
$(document).ready(bindButtons);

