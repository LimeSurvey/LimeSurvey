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
    var sRadix=LSvar.sLEMradix;
    var sTableID=$(this).closest('table').attr('id');
    var sTable=$(this).closest('table');
    var iGrandTotal=new Decimal(0);

    // Sum all rows
    sTable.find('tr').each(function () {
        //the value of sum needs to be reset for each row, so it has to be set inside the row loop
        var sum = new Decimal(0);
        //find the elements in the current row and sum it
        $(this).find('input:enabled:visible').each(function () {
            //sum the values
            var value = normalizeValue($(this).val());
            sum = sum.plus(value);
        });
        //set the value of currents rows sum to the total-combat element in the current row
        $(this).find('input:disabled').val(formatValue(sum));
        iGrandTotal = iGrandTotal.plus(sum);
    });
    // Sum all columns
    // First get number of columns (only visible and enabled inputs)
    var iColumnNum=$('#'+sTableID+' tbody tr:first-child input:enabled:visible').length;
    //Get An array of jQuery Objects
    var $iRow = sTable.find('tr'); 
    //Iterate through the columns
    for (var i = 1; i <= iColumnNum; i++) 
    {
        var sum = new Decimal(0);
        $iRow.each(function(){
            var item = $($(this).find('td').get((i-1))).find('input:enabled:visible'),
            	val = normalizeValue($(item).val());
            //sum the values
           sum = sum.plus(val);
        });
        $($iRow.last().find('td').get((i-1))).find('input:disabled').val(formatValue(sum));
    }

    //$('#'+sTableID+' tr:last-child td.total:nth-of-type('+iColumns+') input:disabled').val(formatValue(iGrandTotal));
    $iRow.last().find('td.grand.total').find('input:disabled').val(formatValue(iGrandTotal));
    // Grand total
}
function formatValue(sValue)
{
    
    sValue=Number(sValue).toString();
    var sRadix=LSvar.sLEMradix;
    sValue=sValue.replace('.',sRadix);
    return sValue;
}

function normalizeValue(aValue)
{
    aValue = aValue || 0;
    var number = new Decimal(aValue);
    if(number.isNaN())
    {
        var numReplaced = aValue.replace(/,/g, ".");
        var number = new Decimal(numReplaced);
        return number;
    } else {
        return number;
    }
}


