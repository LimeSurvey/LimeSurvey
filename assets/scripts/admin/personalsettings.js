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

// Show the tab given in the URL, e.g. #api-tokens after creating an API token
$(function () {
    if (!window.location.hash) {
        return;
    }
    var tab = document.querySelector('.nav-tabs a[href="' + window.location.hash.replace(/"/g, '') + '"]');
    if (tab) {
        bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});

$(document).on('click', '#apitoken-copy', function () {
    var $button = $(this);
    var token = $('#apitoken-new').val();
    navigator.clipboard.writeText(token).then(function () {
        $('#apitoken-copy-status').text($button.data('copied-text'));
    }, function () {
        $('#apitoken-new').trigger('focus').trigger('select');
    });
});

$(document).on('click', '.apitoken-revoke', function (e) {
    if (!window.confirm($(this).data('confirm-text'))) {
        e.preventDefault();
    }
});
