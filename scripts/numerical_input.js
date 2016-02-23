
/**
 * replace the LEMval function, do it at ready to be sure em_javascript is loaded
 * better to move this to em_javascript directly, no ?
 */
window.orgLEMval = window.LEMval;
window.LEMval = function (alias) {
    var varName = LEMalias2varName[alias.split(".", 1)];
    var attr = LEMvarNameAttr[varName];
    if (attr.onlynum == 1 && $('#' + attr.jsName_on).length && $('#' + attr.jsName_on).data("thousandsseparator")) // Do it only if value is in page
    {
        return ls_represent($('#' + attr.jsName_on).val(),$('#' + attr.jsName_on).data("thousandsseparator"));
    }
    return orgLEMval(alias);
};

/**
 * Set a question to thousand separator by qid
 * @param integer qid : question id
 */
function setThousand(qid,options)
{
    if (typeof LEMradix === 'undefined') { return; }
    var defaults =
    {
        prefix: '',
        suffix: '',
        centsSeparator : LEMradix,
        thousandsSeparator : getThousandSeparator(),
        limit: false,
        clearPrefix: false,
        clearSufix: false,
        centsLimit : 2,
        allowNegative: true,
        insertPlusSign: false,
        clearOnEmpty:true,// This allow empty string but disallow 0 ...., else disallow empty, allow 0
    };
    var options = $.extend(defaults, options);
    console.log(options);
    $(function(){ // Only when document is ready
        $("#question"+qid+" input.numeric").each(function(){
            if($(this).val()!="")
            {
                var newVal=$(this).val();
                if(LEMradix == ',')
                    newVal=newVal.split(',').join('.');
                newVal=newVal*1;
                newVal=Math.round(newVal * 100); // What for 0 .....
                $(this).val(newVal);
            }
            $(this).unbind('keydown')
                .removeAttr('onkeyup')
                .priceFormat(options)
                .data("thousandsseparator",options.thousandsSeparator)// data thousandsseparator for this function
                //.data("number",true)// data number for em action : need fixing
                .trigger("keyup");
        });
        LEMsetTabIndexes(); // I don't know the role of this function
    });
}
/**
 * fix value on submit : no need to control when submitted in PHP
 */
$(document).on('submit','#limesurvey', function(){
    $('[data="thousandsseparator"]').each(function(){
            var re = new RegExp(escapeRegExp(thousandsSep()), 'g');
           $(this).val($(this).val().replace(re, ''));
    });
});
/**
 * Send the condition when keyup
 */
$(document).on('keyup','[data="thousandsseparator"]', function(){
    fixnum_checkconditions(ls_represent($('#' + attr.jsName_on).val(),$('#' + attr.jsName_on).data("thousandsseparator")), $(this).attr('name'), 'text', 'keyup', $(this).data("integer"))
});
/**
 * Set the thousand separator for this page
 */
function getThousandSeparator()
{
    if (typeof LEMradix === 'string')
    {
        if (LEMradix == ',')
        {
            return ".";
        }
        else
        {
            return ",";
        }
    }
    return "";
}
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

/**
 *  Takes a value and returns the representation for EM according to thousandsSep
 */
function ls_represent(value,thousandsSep)
{
    if (typeof value == 'string')
    {
        var re = new RegExp(escapeRegExp(thousandsSep), 'g');
        var re2 = new RegExp(escapeRegExp(LEMradix), 'g');
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

function escapeRegExp(str)
{
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

