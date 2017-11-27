
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function() {

    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").prop('checked')) {
            $("#lifilterduplicatefields").slideDown();
        } else {
            $("#lifilterduplicatefields").slideUp();
        }
    });
});
