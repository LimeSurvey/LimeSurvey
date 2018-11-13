/* Todo : move this function to admin base grig.js */
function noGridAction (event) {
    event.preventDefault();
}
function confirmGridAction(event) {
    event.preventDefault();
    var actionUrl = $(this).attr('href');
    var text = $(this).data('confirm-text') || $(this).attr('title') || $(this).data('original-title');
    var utf8 = $(this).data('confirm-utf8') || LS.lang.confirm;
    var gridid = $(this).data('gridid') || $(this).closest(".grid-view").attr("id");
    $.bsconfirm(text,utf8,function onClickOK() {
        $('#'+gridid).yiiGridView('update', {
            type : 'POST',
            url : actionUrl, // No need to add csrfToken, already in ajaxSetup
            success: function(data) {
                jQuery('#'+gridid).yiiGridView('update');
                $('#identity__bsconfirmModal').modal('hide');
                // todo : show an success alert box
            },
            error: function (request, status, error) {
                $('#identity__bsconfirmModal').modal('hide');
                alert(request.responseText);// Use a better alert box (see todo success)
            }
        });
    });
}
function postGridAction(event) {
    event.preventDefault();
    var parts = $(this).attr('href').split("#");
    var actionUrl = parts[0];
    var postAction = '';
    if(parts.length > 1) {
        postAction= parts[1];
    }
    window.LS.sendPost(actionUrl,postAction);
}
