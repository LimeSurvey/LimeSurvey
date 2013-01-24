//$Id: admin_core.js 10154 2011-05-31 11:45:24Z c_schmitz $

$(document).ready(function(){
    if(typeof(userdateformat) !== 'undefined')
        {
        $(".popupdate").each(function(i,e) {
            format=$('#dateformat'+e.name).val();
            if(!format) format = userdateformat;
            $(e).datepicker({ dateFormat: format,
                showOn: 'button',
                changeYear: true,
                changeMonth: true,
                duration: 'fast'
            }, $.datepicker.regional[userlanguage]);
        });
        $(".popupdatetime").datepicker({ dateFormat: userdateformat+' 00:00',
            showOn: 'button',
            changeYear: true,
            changeMonth: true,
            duration: 'fast'
        }, $.datepicker.regional[userlanguage]);
    }

    $('button,input[type=submit],input[type=button],input[type=reset]').addClass("limebutton ui-state-default ui-corner-all");
    $('button,input[type=submit],input[type=button],input[type=reset]').hover(
    function(){
        $(this).addClass("ui-state-hover");
    },
    function(){
        $(this).removeClass("ui-state-hover");
    }
    )


    // Loads the tooltips for the toolbars  except the surveybar
    $('img[alt],input[src]').not('.surveybar img').each(function() {
        if($(this).attr('alt') != '')
            {
            $(this).qtip({
                style: {name: 'light',
                    tip:true,
                    border: {
                        width: 1,
                        radius: 5
                    }
                },
                position: {adjust: {
                        screen: true, scroll:true},
                    corner: {
                        target: 'bottomRight'}
                },
                show: {effect: {length:50}},
                hide: {when: 'mouseout'}

            });
        }
    });

    $(".progressbar").each(function(){
        var pValue = parseInt($(this).attr('name'));

        $(this).progressbar({
            value: pValue
        });

        if (pValue > 85){
            $("div",$(this)).css({ 'background': 'Red' });
        }

        $("div",this).html(pValue + "%");
    });



    $('label[title]').each(function() {
        if($(this).attr('title') != '')
            {
            $(this).qtip({
                style: {name: 'cream',
                    tip:true,
                    color:'#1D2D45',
                    border: {
                        width: 1,
                        radius: 5,
                        color: '#EADF95'}
                },
                position: {adjust: {
                        screen: true, scroll:true},
                    corner: {
                        target: 'bottomRight'}
                },
                show: {effect: {length:50}}
            });
        }
    });

    $('.dosurvey').qtip({
        content:{
            text:$('#dosurveylangpopup')
        },
        style: {name: 'cream',
            tip:true,
            color:'#1D2D45',
            border: {
                width: 1,
                radius: 5,
                color: '#EADF95'}
        },
        position: {adjust: {
                screen: true, scroll:true},
            corner: {
                target: 'bottomMiddle',
                tooltip: 'topMiddle'}
        },
        show: {effect: {length:50},
            when: {
                event:'click'
        }},
        hide: {fixed:true,
            when: {
                event:'unfocus'
        }}
    });

    $('#previewquestion').qtip({
        content:{
            text:$('#previewquestionpopup')
        },
        style: {name: 'cream',
            tip:true,
            color:'#111111',
            border: {
                width: 1,
                radius: 5,
                color: '#EADF95'}
        },
        position: {adjust: {
                screen: true, scroll:true},
            corner: {
                target: 'bottomMiddle',
                tooltip: 'topMiddle'}
        },
        show: {effect: {length:50},
            when: {
                event:'click'
        }},
        hide: {fixed:true,
            when: {
                event:'unfocus'
        }}
    });

    $('.tipme').each(function() {
        if($(this).attr('alt') != '')
            {
            $(this).qtip({
                style: {name: 'cream',
                    tip:true,
                    color:'#111111',
                    border: {
                        width: 1,
                        radius: 5,
                        color: '#EADF95'}
                },
                position: {adjust: {
                        screen: true, scroll:true},
                    corner: {
                        target: 'topRight',
                        tooltip: 'bottomLeft'
                    }
                },
                show: {effect: {length:100}}

            });
        }
    });


    if ($('#showadvancedattributes').length>0) updatequestionattributes();

    $('#showadvancedattributes').click(function(){
        $('#showadvancedattributes').hide();
        $('#hideadvancedattributes').show();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    })
    $('#hideadvancedattributes').click(function(){
        $('#showadvancedattributes').show();
        $('#hideadvancedattributes').hide();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    })
    $('#question_type').change(updatequestionattributes);

    $('#MinimizeGroupWindow').click(function(){
        $('#groupdetails').hide();
    });
    $('#MaximizeGroupWindow').click(function(){
        $('#groupdetails').show();
    });
    $('#tabs').tabs();
    $("#flashmessage").notify().notify('create','themeroller',{},{custom:true,
        speed: 500,
        expires: 5000
    });

    if ($("#question_type").not('.none').length > 0 && $("#question_type").attr('type')!='hidden'){
        $("#question_type").msDropDown({onInit:qTypeDropdownInit});

        $("#question_type").change(function(event){
            var selected_value = qDescToCode[''+$("#question_type_child .selected").text()];
            OtherSelection(selected_value);
        });
    }
    $("#question_type.none").change(function(event){
        var selected_value = $("#question_type").val();
        OtherSelection(selected_value);
    });



});

function qTypeDropdownInit()
{
    $("#question_type_child a").each(function(index,element){

        $(element).qtip({
            style: {
                margin: 15,
                width: 450,
                height: 'auto',
                border: {
                    width: 4,
                    radius: 2
                }
            },
            content: getToolTip($(element).text()),
            position: {
                corner:{
                    target: 'leftMiddle',
                    tooltip:'rightMiddle'
                },
                adjust:{
                    screen: true
                }
            },
            show: 'mouseover'
            //hide: 'mouseout'
        });

    });
}




var aToolTipData = {

};

var qDescToCode;
var qCodeToInfo;

function getToolTip(type){
    var code = qDescToCode[''+type];
    var multiple = 0;
    if (code=='S') multiple = 2;

    if (code == ":") code = "COLON";
    else if(code == "|") code = "PIPE";
    else if(code == "*") code = "EQUATION";

    if (multiple > 0){
        returnval = '';
        for(i=1;i<=multiple;i++){
            returnval = returnval + "<img src='" + imgurl + "/screenshots/"+code+i+".png' /><br /><br />";
        }
        return returnval;
    }
    return "<img src='" + imgurl + "/screenshots/"+code+".png' />";
}

//We have form validation and other stuff..

function updatequestionattributes()
{
    $('.loader').show();
    $('#advancedquestionsettings').html('');
    var selected_value = qDescToCode[''+$("#question_type_child .selected").text()];
    if (selected_value==undefined) selected_value = $("#question_type").val();
    $('#advancedquestionsettings').load(attr_url,{qid:$('#qid').val(),
        question_type:selected_value,
        sid:$('#sid').val()
    }, function(){
        // Loads the tooltips for the toolbars

        // Loads the tooltips for the toolbars
        $('.loader').hide();
        $('label[title]').qtip({
            style: {name: 'cream',
                tip: true,
                color:'#111111',
                border: {
                    width: 1,
                    radius: 5,
                    color: '#EADF95'}
            },
            position: {adjust: {
                    screen: true, scroll:true},
                corner: {
                    target: 'bottomRight'}
            },
            show: {effect: {length:50}}
        });}
    );
}

function validatefilename (form, strmessage )
{
    if (form.the_file.value == "") {
        alert( strmessage );
        form.the_file.focus();
        return false ;
    }
    return true ;
}


// If the length of the element's string is 0 then display helper message
function isEmpty(elem, helperMsg)
{
    if($.trim(elem.value).length == 0){
        alert(helperMsg);
        elem.focus(); // set the focus to this input
        return false;
    }
    return true;
}


function arrHasDupes( A ) {                          // finds any duplicate array elements using the fewest possible comparison
    var i, j, n;
    n=A.length;
    // to ensure the fewest possible comparisons
    for (i=0; i<n; i++) {                        // outer loop uses each item i at 0 through n
        for (j=i+1; j<n; j++) {              // inner loop only compares items j at i+1 to n
            if (A[i]==A[j]) return true;
    }}
    return false;
}


// (c) 2006 Simon Wunderlin, License: GPL, hacks want to be free ;)
// This fix forces Firefox to fire the onchange event if someone changes select box with cursor keys
function ev_gecko_select_keyup_ev(Ev) {
    // prevent tab, alt, ctrl keys from fireing the event
    if (Ev.keyCode && (Ev.keyCode == 1 || Ev.keyCode == 9 ||
    Ev.keyCode == 16 || Ev.altKey || Ev.ctrlKey))
        return true;
    Ev.target.onchange();
    return true;
}

function init_gecko_select_hack() {
    var selects = document.getElementsByTagName("SELECT");
    for(i=0; i<selects.length; i++)
        selects.item(i).addEventListener("keyup", ev_gecko_select_keyup_ev, false);
    return true;
}


function getkey(e)
{
    if (window.event) return window.event.keyCode;
    else
        if (e) return e.which;
    else return null;
}

function goodchars(e, goods)
{
    var key, keychar;
    key = getkey(e);
    if (key == null) return true;

    // get character
    keychar = String.fromCharCode(key);
    keychar = keychar.toLowerCase();
    goods = goods.toLowerCase();

    // check goodkeys
    if (goods.indexOf(keychar) != -1)
        return true;

    // control keys
    if ( key==null || key==0 || key==8 || key==9  || key==27 )
        return true;

    // else return false
    return false;
}


function trim(stringToTrim) {
    return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function DoAdd()
{
    if (document.getElementById("available_languages").selectedIndex>-1)
        {
        var strText = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].text;
        var strId = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].value;
        AddItem(document.getElementById("additional_languages"), strText, strId);
        RemoveItem(document.getElementById("available_languages"), document.getElementById("available_languages").selectedIndex);
        sortSelect(document.getElementById("additional_languages"));
        UpdateLanguageIDs();
    }
}

function DoRemove(minItems,strmsg)
{
    var strText = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].text;
    var strId = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].value;
    if (document.getElementById("additional_languages").options.length>minItems)
        {
        AddItem(document.getElementById("available_languages"), strText, strId);
        RemoveItem(document.getElementById("additional_languages"), document.getElementById("additional_languages").selectedIndex);
        sortSelect(document.getElementById("available_languages"));
        UpdateLanguageIDs();
    }
    else
        if (strmsg!=''){alert(strmsg);}
}



function AddItem(objListBox, strText, strId)
{
    var newOpt;
    newOpt = document.createElement("OPTION");
    newOpt = new Option(strText,strId);
    newOpt.id = strId;
    objListBox.options[objListBox.length]=newOpt;
}

function RemoveItem(objListBox, strId)
{
    if (strId > -1)
        objListBox.options[strId]=null;
}

function GetItemIndex(objListBox, strId)
{
    for (var i = 0; i < objListBox.children.length; i++)
        {
        var strCurrentValueId = objListBox.children[i].id;
        if (strId == strCurrentValueId)
            {
            return i;
        }
    }
    return -1;
}


function UpdateLanguageIDs(mylangs,confirmtxt)
{
    document.getElementById("languageids").value = '';

    var lbBox = document.getElementById("additional_languages");
    for (var i = 0; i < lbBox.options.length; i++)
        {
        document.getElementById("languageids").value = document.getElementById("languageids").value + lbBox.options[i].value+ ' ';
    }
    if (mylangs)
        {
        if (checklangs(mylangs))
            {
            return true;
        } else
            {
            return confirm(confirmtxt);
        }
    }
}

function compareText (option1, option2) {
    return option1.text < option2.text ? -1 :
    option1.text > option2.text ? 1 : 0;
}

function compareValue (option1, option2) {
    return option1.value < option2.value ? -1 :
    option1.value > option2.value ? 1 : 0;
}

function compareTextAsFloat (option1, option2) {
    var value1 = parseFloat(option1.text);
    var value2 = parseFloat(option2.text);
    return value1 < value2 ? -1 :
    value1 > value2 ? 1 : 0;
}

function compareValueAsFloat (option1, option2) {
    var value1 = parseFloat(option1.value);
    var value2 = parseFloat(option2.value);
    return value1 < value2 ? -1 :
    value1 > value2 ? 1 : 0;
}

function sortSelect (select, compareFunction) {
    if (!compareFunction)
        compareFunction = compareText;
    var options = new Array (select.options.length);
    for (var i = 0; i < options.length; i++)
        options[i] =
    new Option (
    select.options[i].text,
    select.options[i].value,
    select.options[i].defaultSelected,
    select.options[i].selected
    );
    options.sort(compareFunction);
    select.options.length = 0;
    for (var i = 0; i < options.length; i++)
        select.options[i] = options[i];
}

function checklangs(mylangs)
{
    selObject=document.getElementById("additional_languages");
    var found;

    for (x = 0; x < mylangs.length; x++)
        {
        found = 0;
        for (i=0;i<selObject.options.length;i++)
            {
            if(selObject.options[i].value == mylangs[x])
                {
                found = 1;
                break;
            }
        }
        if (found == 0) {return false;}
    }
    return true;
}

function isset( variable )
{
    return( typeof( variable ) != 'undefined' );
}

String.prototype.splitCSV = function(sep) {
    for (var foo = this.split(sep = sep || ","), x = foo.length - 1, tl; x >= 0; x--) {
        if (foo[x].replace(/"\s+$/, '"').charAt(foo[x].length - 1) == '"') {
            if ((tl = foo[x].replace(/^\s+"/, '"')).length > 1 && tl.charAt(0) == '"') {
                foo[x] = foo[x].replace(/^\s*"|"\s*$/g, '').replace(/""/g, '"');
            } else if (x) {
                foo.splice(x - 1, 2, [foo[x - 1], foo[x]].join(sep));
            } else foo = foo.shift().split(sep).concat(foo);
        } else foo[x].replace(/""/g, '"');
    }return foo;
};

// This is a helper function to extract the question ID from a DOM ID element
function removechars(strtoconvert){
    return strtoconvert.replace(/[-a-zA-Z_]/g,"");
}


function htmlspecialchars (string, quote_style, charset, double_encode) {
    // Convert special characters to HTML entities
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/htmlspecialchars    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'
    var optTemp = 0,
    i = 0,        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }
    return string;
}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
}

// Fix broken substr function with negative start value (in older IE)
if ('ab'.substr(-1) != 'b') {
	String.prototype.substr = function(substr) {
		return function(start, length) {
			if (start < 0) start = this.length + start;
			return substr.call(this, start, length);
		}
	}(String.prototype.substr);
}

/**
* Yii CSRF protection divs breaks this script so this function moves the 
* hidden CSRF field out of the div and remove it if needed
* 
*/
function removeCSRFDivs()
{
    $('input[name=YII_CSRF_TOKEN]').each(function(){
       parent = $(this).parent();
       grandfather = $(parent).parent();
       grandfather.append(this);
       parent.remove();
    });
}