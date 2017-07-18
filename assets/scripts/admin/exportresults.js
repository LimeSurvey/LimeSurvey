
// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:complete', LS.onDocumentReady.ExportResults);
$(document).on(' pjax:complete',LS.onDocumentReady.ExportResults);

LS.onDocumentReady.ExportResults = function(){
    updateColumnCountDisplay();
    $('#colselect').change(updateColumnCountDisplay);
};

function updateColumnCountDisplay()
{
    selectedCount = $("#colselect :selected").length;
    totalCount = $("#colselect option").length;
    $('#columncount').html(sprintf(sMsgColumnCount,selectedCount,totalCount));
}
