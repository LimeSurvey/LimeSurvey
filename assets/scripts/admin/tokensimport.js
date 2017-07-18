
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready pjax:completed', LS.onDocumentReady.Tokenimport);
$(document).on('pjax:completed',LS.onDocumentReady.Tokenimport);

LS.onDocumentReady.Tokenimport = function() {

    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").prop('checked')) {
            $("#lifilterduplicatefields").slideDown();
        } else {
            $("#lifilterduplicatefields").slideUp();
        }
    });
};
