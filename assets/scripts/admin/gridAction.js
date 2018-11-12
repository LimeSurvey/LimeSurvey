/* Todo : move this function to admin base grig.js */
var noGridAction = function() {
    return false;
}
function confirmGridAction() {
    var actionUrl = $(this).attr('href');
    var text = $(this).data('confirm-text') || $(this).attr('title') || $(this).data('original-title');
    var utf8 = $(this).data('confirm-utf8') || LS.lang.confirm;
    $.bsconfirm(text,utf8,function onClickOK() {
        $('#responses-grid').yiiGridView('update', {
            type : 'POST',
            url : actionUrl, // No need to add csrfToken, already in ajaxSetup
            success: function(data) {
                jQuery('#responses-grid').yiiGridView('update');
                $('#identity__bsconfirmModal').modal('hide');
                // todo : show an success alert box
            },
            error: function (request, status, error) {
                $('#identity__bsconfirmModal').modal('hide');
                alert(request.responseText);// Use a better alert box (see todo success)
            }
        });
    });
    return false;
}
