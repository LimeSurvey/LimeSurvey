$(document).on('click', '#selector__showChangePassword', function(e){
    e.preventDefault();
    $('#newpasswordshown').val('1');
    showHiddenRow('.selector__oldpassword-row');
    showHiddenRow('.selector__password-row');
    $(this).closest('div').remove();
});

$(document).on('click', '#selector__showChangeEmail', function(e){
    e.preventDefault();
    $('#newemailshown').val('1');
    showHiddenRow('.selector__oldpassword-row');
    showHiddenRow('.selector__email-row');
    $(this).closest('div').remove();
});

function showHiddenRow(selector) {
    $(selector).removeClass('d-none').find('input').each(
        function(i,item){
            $(item).prop('disabled', false);
       }
    );
}
