$(document).on('submit','#limesurvey', function(){
    $('.thousandsseparator input.numeric').each(function(){
            var re = new RegExp(escapeRegExp(thousandsSep()), 'g');
           $(this).val($(this).val().replace(re, ''));
    });
});
$(document).on('keyup','.thousandsseparator input.numeric', function(){
    ExprMgr_process_relevance_and_tailoring('onchange', $(this).attr('name'), $(this).attr('type'));
});

$(document).ready(function () {
    if (typeof LEMradix === 'undefined') { return; }
    if (LEMradix == ',')
    {
        var centsSep = ',';
        var thousandsSep = '.';
    }
    else
    {
        var centsSep = '.';
        var thousandsSep = ',';
    }
    // Replace the LEMval
    window.orgLEMval = window.LEMval;
    window.LEMval = function (alias) {
        var varName = LEMalias2varName[alias.split(".", 1)];
        var attr = LEMvarNameAttr[varName];
        if (attr.onlynum == 1 && $('#' + attr.jsName_on).closest(".numeric-item").length && $('#' + attr.jsName_on).closest(".numeric-item").hasClass("thousandsseparator")) // Do it only if value is in page
        {
            return ls_represent($('#' + attr.jsName_on).val());
        }
        return orgLEMval(alias);
    };
    $('.thousandsseparator  input.numeric').not('.integeronly').each(function(){
        if($(this).val()!="")
        {

            var newVal=$(this).val();
            if(centsSep == ',')
                newVal=newVal.split(',').join('.');
            newVal=newVal*1;
            newVal=Math.round(newVal * 100); // What for 0 .....
            $(this).val(newVal);
        }
        $(this).unbind('keydown').removeAttr('onkeyup').priceFormat({
            'centsSeparator' : centsSep,
            'thousandsSeparator' : thousandsSep,
            'centsLimit' : 2,
            'prefix' : '',
            'allowNegative' : true
        }).trigger("keyup");
    });
    $('.thousandsseparator input.integeronly').unbind('keydown').removeAttr('onkeyup').priceFormat({
        'centsSeparator' : centsSep,
        'thousandsSeparator' : thousandsSep,
        'centsLimit' : 0,
        'prefix' : '',
        'allowNegative' : true
    }).trigger("keyup");
    LEMsetTabIndexes();

});


/*
 This function is called on key down and checks the value when tab has been pressed.
 (Replaces LEMsetTabIndexes and the function bindings it contains).
*/
function custom_tab(e)
{
    if (e.keyCode == 9)
    {
        custom_checkconditions.call(this, 'TAB');
    }
}

function centsSep()
{
    return LEMradix;
}

function thousandsSep()
{
    if (LEMradix == ',')
    {
        return '.';
    }
    else
    {
        return ',';
    }
}

/*
  Takes a value from a box and returns the representation for EM.
*/
function ls_represent(value)
{
    if (typeof value == 'string')
    {
        var re = new RegExp(escapeRegExp(thousandsSep()), 'g');
        var re2 = new RegExp(escapeRegExp(centsSep()), 'g');
        value = value.replace(re, '').replace(re2, '.');
        if (value == parseFloat(value)) {
            return +value;
        } else {
            return value;
        }
    }
    else
    {
        return value;
    }
}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

