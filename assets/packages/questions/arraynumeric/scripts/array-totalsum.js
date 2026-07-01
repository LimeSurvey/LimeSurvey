/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

$(document).on('ready pjax:scriptcomplete',function () {
    $('div.array-multi-flexi-text table.show-totals input:enabled').keyup(updatetotals);
    $('div.array-multi-flexi-text table.show-totals input:enabled').each(updatetotals);
    $('div.array-multi-flexi-text table.show-totals tr.subquestion-list').on('relevance:on relevance:off', function(){
        sumTable(this);
    });
    $('div.array-multi-flexi-text table.show-totals').closest('div.array-multi-flexi-text').on('relevance:on', function(){
        var firstRow = $(this).find('table.show-totals input:enabled:visible').first();
        if (firstRow.length) {
            sumTable(firstRow[0]);
        }
    });
});

function updatetotals(e) {
    console.ls.log(e);
    var inputValue = $(this).val();

    if (!normalizeValue(inputValue)) {
        $(this).val(inputValue.substring(0, (inputValue.length - 1)));
        return;
    }

    sumTable(this);
}

function sumTable(element) {
    var table = $(element).closest('table');
    var iGrandTotal = new Decimal(0);

    // Sum all rows
    table.find('tr').each(function () {
        //the value of sum needs to be reset for each row, so it has to be set inside the row loop
        var sum = new Decimal(0);
        //find the elements in the current row and sum it
        $(this).find('input:enabled:visible').each(function () {
            //sum the values
            var value = normalizeValue($(this).val());
            sum = sum.plus(value);
        });
        //set the value of currents rows sum to the total-combat element in the current row
        $(this).find('td.total input:disabled').val(formatValue(sum)).trigger('change').trigger('keyup').trigger('keydown');
        iGrandTotal = iGrandTotal.plus(sum);
    });
    
    // Sum all columns
    //Get An array of jQuery Objects
    var $iRow = table.find('tr');
    // If Totals are enabled for columns, there is a row with class "total"
    var lastRow = $iRow.last();
    if (lastRow.hasClass('total')) {
        // First get number of columns (only visible and enabled inputs)
        var visibleRows = table.find('tbody tr:visible');
        var iColumnNum = visibleRows.first().find('input:enabled:visible').length;
        //Iterate through the columns
        for (var i = 1; i <= iColumnNum; i++) {
            var sum = new Decimal(0);
            $iRow.each(function () {
                var item = $($(this).find('td').get((i - 1))).find('input:enabled:visible'),
                    val = normalizeValue($(item).val());
                //sum the values
                sum = sum.plus(val);
            });
            $(lastRow.find('td.total').get((i - 1))).find('input:disabled').val(formatValue(sum)).trigger('change').trigger('keyup').trigger('keydown');
        }
    }

    // Grand total
    $iRow.last().find('td.grand.total').find('input:disabled').val(formatValue(iGrandTotal)).trigger('change').trigger('keyup').trigger('keydown');
}

function formatValue(sValue) {

    sValue = Number(sValue).toString();
    var sRadix = LSvar.sLEMradix;
    sValue = sValue.replace('.', sRadix);
    return sValue;
}

function normalizeValue(aValue) {
    var regexCheck = new RegExp(/^-?([0-9]*)((,|\.){1}([0-9]*)){0,1}$/);
    if (!regexCheck.test(aValue) && bFixNumAuto) {
        return 0;
    }
    aValue = aValue || 0;
    var outNumber = false;
    try {
        outNumber = new Decimal(aValue);
    } catch (e) {}

    if (outNumber == false) {
        var numReplaced = aValue.toString().replace(/,/g, ".");
        try {
            outNumber = new Decimal(numReplaced);
        } catch(e){}
        return outNumber;
    } else {
        return outNumber;
    }
}
