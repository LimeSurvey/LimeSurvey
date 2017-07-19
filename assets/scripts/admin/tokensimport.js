
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:complete', function() {

    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").prop('checked')) {
            $("#lifilterduplicatefields").slideDown();
        } else {
            $("#lifilterduplicatefields").slideUp();
        }
    });
});
