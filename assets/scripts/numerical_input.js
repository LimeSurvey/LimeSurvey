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

    var selector = '.thousandsseparator input.numeric, input.integeronly, .numberonly input[type=text]';
    $(selector).unbind('keydown');
    $('.thousandsseparator input.numeric').priceFormat({
        'centsSeparator' : centsSep,
        'thousandsSeparator' : thousandsSep,
        'centsLimit' : 2,
        'prefix' : '',
        'allowNegative' : true
    });
    $('.thousandsseparator input.integeronly').priceFormat({
        'centsSeparator' : centsSep,
        'thousandsSeparator' : thousandsSep,
        'centsLimit' : 0,
        'prefix' : '',
        'allowNegative' : true
    });

    $(selector).bind('keyup', custom_checkconditions);
    // Initialize LEM tabs first.
    LEMsetTabIndexes();

    $(selector).removeAttr('onkeyup');
    $('form#limesurvey').bind('submit', {'selector': selector}, ls_represent_all    );

    window.orgLEMval = window.LEMval;
    window.LEMval = function (alias) {

        var varName = LEMalias2varName[alias.split(".", 1)];
        var attr = LEMvarNameAttr[varName];
        if (attr && attr.onlynum == 1)
        {
            return ls_represent($('#' + attr.jsName_on).val());
        }
        return orgLEMval(alias);
    };
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


/*
 This function is called after priceformat has applied its layouting.
*/
function custom_checkconditions(evt_type)
{
    evt_type = typeof evt_type !== 'undefined' ? evt_type : 'onchange';

    // We get the value.

//    var val = $(this).attr('value');
//    var pos = $(this).caret();
//    $(this).attr('value', ls_represent(val));
    ExprMgr_process_relevance_and_tailoring(evt_type, $(this).attr('name'), $(this).attr('type'));
//    $(this).attr('value', val);
//    $(this).caret(pos);

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
  Takes a value from a box and returns the representation limesurvey uses to save it.
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

function ls_represent_all(e)
{
    $(e.data.selector).each(function () {
        $(this).attr('value', ls_represent($(this).attr('value')));

    });
}
