
// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:complete',  function(){
    updateColumnCountDisplay();
    $('#colselect').change(updateColumnCountDisplay);
});

function updateColumnCountDisplay()
{
    selectedCount = $("#colselect :selected").length;
    totalCount = $("#colselect option").length;
    $('#columncount').html(sprintf(sMsgColumnCount,selectedCount,totalCount));
}
