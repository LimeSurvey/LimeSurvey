const gridButton = {
    noGridAction : (event,that) => {
        event.preventDefault();
    },
    confirmGridAction : (event,that) => {
        event.preventDefault();
        var actionUrl = $(that).attr('href');
        if(!actionUrl) {
            LOG.error("confirmGridAction without valid element");
            return;
        }
        var text = $(that).data('confirm-text') || $(that).attr('title') || $(that).data('original-title');
        var utf8 = $(that).data('confirm-utf8') || LS.lang.confirm;
        var gridid = $(that).data('gridid') || $(that).closest(".grid-view").attr("id");
        $.fn.bsconfirm(text,utf8,function onClickOK() {
            $('#'+gridid).yiiGridView('update', {
                type : 'POST',
                url : actionUrl,
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
    },
    postGridAction : (event,that) => {
        event.preventDefault();
        var parts = $(that).attr('href').split("#");
        var actionUrl = parts[0];
        if(!actionUrl) {
            LOG.error("postGridAction without valid element");
            return;
        }
        var postAction = '';
        if(parts.length > 1) {
            postAction= parts[1];
        }
        window.LS.sendPost(actionUrl,postAction);
    }
}
export default {gridButton};
