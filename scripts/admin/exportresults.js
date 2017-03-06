$(document).ready(function(){
    updateColumnCountDisplay();
    $('#colselect').change(function(){
        updateColumnCountDisplay();
    });
});

function updateColumnCountDisplay()
{
    selectedCount = $("#colselect :selected").length;
    totalCount = $("#colselect option").length;
    $('#columncount').html(sprintf(sMsgColumnCount,selectedCount,totalCount));
}
