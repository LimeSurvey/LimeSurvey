$(document).ready(function(){
    updateColumnCountDisplay();
    $('#colselect').change(function(){
        if ($(this).val().length > 255 && $('#xls').prop('checked')) {
            alert(sMsgMaximumExcelColumns);
            $(this).val(last_valid_selection);
        } else {
            last_valid_selection = $(this).val();
        }
        updateColumnCountDisplay(); 
    });
    $('#xls').change(function(){
        aOptions=$($("#colselect :selected").get().reverse());
        selectedCount = $("#colselect :selected").length;
        bLimited=false;
        aOptions.each(function(){
            if (selectedCount>255)
            {
                this.selected=false;
                selectedCount--;
                bLimited=true;
            }
        });
        if (bLimited)
        {
            alert(sMsgMaximumExcelColumns+' '+"\n"+sMsgExcelColumnsReduced);
        }   
        updateColumnCountDisplay(); 
    });


});

function updateColumnCountDisplay()
{
    selectedCount = $("#colselect :selected").length;
    totalCount = $("#colselect option").length;
    $('#columncount').html(sprintf(sMsgColumnCount,selectedCount,totalCount));
}
