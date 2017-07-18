
var LS = LS || {
    onDocumentReady: {}
};

$(document).ready(LS.onDocumentReady.Tokenimport);
$(document).on('pjax:end',LS.onDocumentReady.Tokenimport);

LS.onDocumentReady.Tokenimport = function() {

    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").prop('checked')) {
            $("#lifilterduplicatefields").slideDown();
        } else {
            $("#lifilterduplicatefields").slideUp();
        }
    });
};
