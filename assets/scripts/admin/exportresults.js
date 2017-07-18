
// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).ready(LS.onDocumentReady.ExportResults);
$(document).on('pjax:end',LS.onDocumentReady.ExportResults);

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
