
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:complete', LS.onDocumentReady.Tokenimport);
$(document).on(' pjax:complete',LS.onDocumentReady.Tokenimport);

LS.onDocumentReady.Tokenimport = function() {

    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").prop('checked')) {
            $("#lifilterduplicatefields").slideDown();
        } else {
            $("#lifilterduplicatefields").slideUp();
        }
    });
};
