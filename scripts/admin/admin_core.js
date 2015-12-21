/*
 * JavaScript functions for LimeSurvey administrator
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later

/* Set a variable to test if browser have HTML5 form ability
 * Need to be replaced by some polyfills see #8009
 */
hasFormValidation= typeof document.createElement( 'input' ).checkValidity == 'function';
linksInDialog();
$(document).ready(function(){
    initializeAjaxProgress();
    tableCellAdapters();
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
            }, $.datepicker.regional[LS.data.language]);
        });
        $(".popupdatetime").datepicker({ dateFormat: userdateformat+' 00:00',
            showOn: 'button',
            changeYear: true,
            changeMonth: true,
            duration: 'fast'
        }, $.datepicker.regional[LS.data.language]);
    }
    $(".sf-menu").superfish({speed: 'fast'});
    doToolTip();
    $('button,input[type=submit],input[type=button],input[type=reset],.button').button();
    $('button,input[type=submit],input[type=button],input[type=reset],.button').addClass("limebutton");

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


    if ($('#showadvancedattributes').length>0) updatequestionattributes();

    $('#showadvancedattributes').click(function(){
        $('#showadvancedattributes').hide();
        $('#hideadvancedattributes').show();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    });
    $('#hideadvancedattributes').click(function(){
        $('#showadvancedattributes').show();
        $('#hideadvancedattributes').hide();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    });
    $('#question_type').change(updatequestionattributes);

    $('#MinimizeGroupWindow').click(function(){
        $('#groupdetails').hide();
    });
    $('#MaximizeGroupWindow').click(function(){
        $('#groupdetails').show();
    });
    $('#tabs').tabs({
        activate: function(event, ui) {
            if(history.pushState) {
                history.pushState(null, null, '#'+ui.newPanel.attr('id'));
            }
            else {
                location.hash = ui.newPanel.attr('id');
            }
        }
    });
    $('.tab-nav').tabs();
    $(".flashmessage").each(function() {
        $(this).notify().notify('create','themeroller',{},{custom:true,
        speed: 500,
        expires: 5000
        });
    });
    if ($("#question_type").not('.none').length > 0 && $("#question_type").attr('type')!='hidden'){

        /*
        $("#question_type").msDropDown({
            'on' : {
                'create' :qTypeDropdownInit
            }
        });
        */
       qTypeDropdownInit();
        $("#question_type").change(function(event){
            OtherSelection(this.value);
        });
        $("#question_type").change();
    }
    else
    {
        $("#question_type.none").change(function(event){
            OtherSelection(this.value);
        });
        $("#question_type.none").change();
    }
});

function qTypeDropdownInit()
{
    $(document).ready(function () {
        $("#question_type option").each(function(index,element){
            $(element).qtip({
                style: {
                    classes: 'qtip-questiontype'
                },
                content: getToolTip($(element).text()),
                position: {
                    my : 'top left',
                    at: 'top right',
                    target: $('label[for=question_type]'),
                    viewport: $(window),
                    adjust: {
                        x: 20
                    }

                }
            });

        });
    });
    $(document).ready(function() {
        $('body').on('mouseenter mouseleave', 'li.questionType', function(e) {
            if (e.type == 'mouseenter')
            {
				// Hide all others if we show a new one.
                $('#question_type option').qtip('hide');
                $($(e.currentTarget).data().select2Data.element).qtip('option', 'position.target', $(e.currentTarget)).qtip('show');
            }
            else
            {
                $($(e.currentTarget).data().select2Data.element).qtip('hide');
            }


        });
        $('#question_type').on('close', function(e) {
            $('#question_type option').qtip('hide');
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

function doToolTip()
{
    // ToolTip on menu
    $(".sf-menu li").each(function() {
        tipcontent=$(this).children("a").children("img").attr('alt');
        if(tipcontent && tipcontent!=""){
            $(this).qtip({
                content: {
                    text: tipcontent
                },
                style: {
                    classes: "qtip-light qtip-rounded"
                },
                position: {
                    my: 'bottom left',
                    at: "top right"
                }
            });
            $(this).children("a").children("img").removeAttr('title');
        }
    });
    $(".sf-menu a > img[alt]").data("hasqtip", true ).parent("a").data("hasqtip", true );
    $("a").each(function() {
        if(!$(this).data("hasqtip"))// data-hasqtip not in DOM, then need to be tested directly (:not([data-hasqtip]) don't work)
        {
            tipcontent=$(this).children("img").attr('alt');
            if(!tipcontent){tipcontent=htmlEncode($(this).attr('title'));}
            if(tipcontent && tipcontent!=""){
                $(this).qtip({
                    content: {
                        text: tipcontent
                    },
                    style: {
                        classes: "qtip-light qtip-rounded"
                    },
                    position: {
                        viewport: $(window),
                        at: 'bottom right'
                    }
                });
            }
            $(this).removeAttr('title');
        }
    });
    $("a > img[alt]").data("hasqtip", true ).removeAttr('title');

    // Call the popuptip hover rel attribute
    $('.popuptip').each(function(){
        if($(this).attr('rel')){
            htmlcontent=$(this).html();
            tiptarget=$("#"+$(this).attr('rel'));
            //if($("#"+$(this).attr('rel')).find('img').length==1){ tiptarget=$("#"+$(this).attr('rel')).find('img');}
            tiptarget.qtip({
                content: {
                    text: htmlcontent
                },
                style: {
                    classes: "qtip-light qtip-rounded"
                },
                position: {
                    at: "bottom center",
                    my: "top center"
                },
                hide: {
                    fixed: true,
                    delay: 500,
                    event: "mouseout"
                }
            });
            $("#"+$(this).attr('rel')).find("img").data("hasqtip", true ).removeAttr('title');
        }
    });
    // On label
    $('label[title]').each(function() {
        if($(this).attr('title') != '')
        {
            $(this).qtip({
                style: {
                    classes: "qtip-cream qtip-rounded"
                },
                position: {
                    viewport: $(window),
                    at: "bottom right"
                }
            });
        }
    });
    // Loads the tooltips on image
    $('img[title]').each(function() {
        if($(this).attr('title') != '')
        {
            $(this).qtip({
                style: {
                    classes: "qtip-light qtip-rounded"
                },
                position: {
                    viewport: $(window),
                    at: "bottom right"
                }
            });
        }
    });
    $('img[alt]:not([title]),input[src]').each(function() {
        if($(this).attr('alt') != '' && !$(this).data("hasqtip")){
            $(this).qtip({
                content: {
                    attr: "alt"
                },
                style: {
                    classes: "qtip-light qtip-rounded"
                },
                position: {
                    viewport: $(window),
                    at: "bottom right"
                },
                hide: {
                    event: "mouseout"
                }
            });
        }
    });

    //Still used ?
    $('.tipme').each(function() {
        if($(this).attr('alt') != '')
            {
            $(this).qtip(
            {
                content: {
                    attr: 'alt'
                },
                style: {
                    classes: "qtip-cream qtip-rounded"
                },
                position: {
                        viewport: $(window),
                        at: 'top right',
                        tooltip: 'bottom left'
                    }
            });
        }
    });
}
// A function to encode any HTML for qtip
function htmlEncode(html){
  return $('<div/>').text(html).html();
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
    // Not form phpjs: added because in some condition : subquestion can send null for string
    // subquestion js use inline javascript function
    if (typeof string === 'undefined' || string === null) {
        string="";
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
};

// Fix broken substr function with negative start value (in older IE)
// From https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/substr
if ('ab'.substr(-1) != 'b') {
	String.prototype.substr = function(substr) {
		return function(start, length) {
			if (start < 0) start = this.length + start;
			return substr.call(this, start, length);
		};
	}(String.prototype.substr);
}

function linksInDialog()
{
    $(function () {
        var iframe = $('<iframe id="dialog" allowfullscreen></iframe>');
        var dialog = $("<div></div>").append(iframe).appendTo("body").dialog({
            autoOpen: false,
            modal: false,
            resizable: true,
            width: "60%",
            height: $(window).height()*0.6,
            close: function () {
                iframe.attr("src", "");
            }
        });
        $(document).on('click','a[target=dialog]',function(event){
            event.preventDefault();
            var src = $(this).attr("href");
            var title = $(this).attr("title");
            if(!title && $(this).children("img[alt]"))
                title = $(this).children("img[alt]").attr("alt");
            iframe.attr({
                src: src,
            });
            dialog.dialog("option", "title", title).dialog("open");
        });
    });
}
function initializeAjaxProgress()
{
    $('#ajaxprogress').dialog({
            'modal' : true,
            'closeOnEscape' : false,
            'title' : $('#ajaxprogress').attr('title'),
            'autoOpen' : false,
            'minHeight': 0,
            'resizable': false
        });
    $('#ajaxprogress').bind('ajaxStart', function()
    {
        $(this).dialog('open');
    });
    $('#ajaxprogress').bind('ajaxStop', function()
    {

        $(this).dialog('close');
    });
}

/**
 * Adapt cell to have a click on cell do a click on input:radio or input:checkbox (if unique)
 * Using delegate the can be outside document.ready
 */
function tableCellAdapters()
{
    $('table.activecell').delegate('tbody td input:checkbox,tbody td input:radio,tbody td label,tbody th input:checkbox,tbody th input:radio,tbody th label',"click", function(e) {
        e.stopPropagation();
    });
    $('table.activecell').delegate('tbody td,tbody th',"click", function() {
        if($(this).find("input:radio,input:checkbox").length==1)
        {
          $(this).find("input:radio").click();
          $(this).find("input:radio").triggerHandler("click");
          $(this).find("input:checkbox").click();
          $(this).find("input:checkbox").triggerHandler("click");
        }
    });
}

/**
 * sendPost : create a form, fill with param and submit
 *
 * @param {string} action
 * @param {} checkcode : deprecated
 * @param {array} arrayparam
 * @param {array} arrayval
 *
 */
function sendPost(myaction,checkcode,arrayparam,arrayval)
{
    var $form = $("<form method='POST'>").attr("action", myaction);
    for (var i = 0; i < arrayparam.length; i++)
        $("<input type='hidden'>").attr("name", arrayparam[i]).attr("value", arrayval[i]).appendTo($form);
    $("<input type='hidden'>").attr("name", 'YII_CSRF_TOKEN').attr("value", LS.data.csrfToken).appendTo($form);
    $form.appendTo("body");
    $form.submit();
}
function addHiddenElement(theform,thename,thevalue)
{
    var myel = document.createElement('input');
    myel.type = 'hidden';
    myel.name = thename;
    theform.appendChild(myel);
    myel.value = thevalue;
    return myel;
}
function onlyUnique(value, index, self) {
    return self.indexOf(value) === index;
}

// @license-end
