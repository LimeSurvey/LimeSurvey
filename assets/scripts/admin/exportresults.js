
// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:scriptcomplete',  function(){
    updateColumnCountDisplay();
    $('#colselect').change(updateColumnCountDisplay);
});

function updateColumnCountDisplay()
{
    selectedCount = $("#colselect :selected").length;
    totalCount = $("#colselect option").length;
    $('#columncount').html(sprintf(sMsgColumnCount,selectedCount,totalCount));
}
