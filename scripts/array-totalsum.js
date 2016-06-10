/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

$( document ).ready(function() {
    $('div.array-multi-flexi-text table.show-totals input:enabled').keyup(updatetotals);
    $('div.array-multi-flexi-text table.show-totals input:enabled').each(updatetotals);
});

function updatetotals()
{
    sRadix=LSvar.sLEMradix;
    sTableID=$(this).closest('table').attr('id')
    iGrandTotal=0;

    // Sum all rows
    $('#'+sTableID+' tr').each(function () {
        //the value of sum needs to be reset for each row, so it has to be set inside the row loop
        var sum = 0
        //find the elements in the current row and sum it
        $(this).find('input:enabled:visible').each(function () {
            //sum the values
            sum +=normalizeValue($(this).val());
        });
        //set the value of currents rows sum to the total-combat element in the current row
        $(this).find('input:disabled').val(formatValue(sum));
        iGrandTotal +=sum;
    });
    // Sum all columns
    // First get number of columns (only visible and enabled inputs)
    iColumns=$('#'+sTableID+' tbody tr:first-child input:enabled:visible').length;
    for (i = 1; i <= iColumns; i++) {
        var sum = 0;
        $('#'+sTableID+' tbody tr td.text-item:nth-of-type('+i+') input:enabled:visible').each(function () {
            //sum the values
            sum +=normalizeValue($(this).val());
        });
        $('#'+sTableID+' tr td.total:nth-of-type('+i+') input:disabled').val(formatValue(sum));
    }
    iColumns++;
    $('#'+sTableID+' tr:last-child td.total:nth-of-type('+iColumns+') input:disabled').val(formatValue(iGrandTotal));
    // Grand total
}
function formatValue(sValue)
{
    sValue=''+sValue;
    sRadix=LSvar.sLEMradix;
    sValue=sValue.replace('.',sRadix)
    return sValue;
}

function normalizeValue(aValue)
{
    var numRegex;
    var sRadix;

    sRadix=LSvar.sLEMradix;
    if (sRadix=='.')
    {
        numRegex = /^[+-]?((\d+(\.\d*)?)|(\.\d+))$/;
    }
    else
    {
        numRegex = /^-?\d{1,3}(?:\.\d{3})*(?:,\d+)?$/;
    }
    if (aValue.slice(-1)==sRadix)
    {
        aValue=aValue.slice(0, -1);
    }
    if (numRegex.test(aValue))
    {
        if (sRadix==',')
        {
            aValue=aValue.replace(/ \./g,'')
            aValue=aValue.replace(/,/g,'.')
        }
        else{
            aValue=aValue.replace(/ ,/g,'')
        }
    }
    else
    {
      aValue=0;
    }
    return +aValue || 0;
}


