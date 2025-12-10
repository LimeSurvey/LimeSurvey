/*
 * JavaScript functions in survey taking
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Some function can be launch before document ready (and seems intersting)
// But put it in ready : allowing update by template.js (before moving at end of HTML : best place */
$(document).on('ready pjax:scriptcomplete',function()
{
    tableCellAdapters();
    doToolTipTable();

    if (typeof LEMsetTabIndexes === 'function') { LEMsetTabIndexes(); }
    if (typeof checkconditions!='undefined') checkconditions();
    if (typeof template_onload!='undefined') template_onload();
    if (typeof(focus_element) != 'undefined')
    {
        $(focus_element).focus();
    }

    // Keypad functions
    var kp = $("input.num-keypad");
    if(kp.length)
    {
        kp.keypad({
            showAnim: 'fadeIn', keypadOnly: false,
            onKeypress: function(key, value, inst) {
                $(this).trigger('keyup');
            }
        });
    }
    kp = $(".text-keypad");
    if(kp.length)
    {
        var spacer = $.keypad.HALF_SPACE;
        for(var i = 0; i != 8; ++i) spacer += $.keypad.SPACE;
        kp.keypad({
            showAnim: 'fadeIn',
            keypadOnly: false,
            layout: [
                spacer + $.keypad.CLEAR + $.keypad.CLOSE, $.keypad.SPACE,
                '!@#$%^&*()_=' + $.keypad.HALF_SPACE + $.keypad.BACK,
                $.keypad.HALF_SPACE + '`~[]{}<>\\|/' + $.keypad.SPACE + $.keypad.SPACE + '789',
                'qwertyuiop\'"' + $.keypad.HALF_SPACE + $.keypad.SPACE + '456',
                $.keypad.HALF_SPACE + 'asdfghjkl;:' + $.keypad.SPACE + $.keypad.SPACE + '123',
                $.keypad.SPACE + 'zxcvbnm,.?' + $.keypad.SPACE + $.keypad.SPACE + $.keypad.HALF_SPACE + '-0+',
                $.keypad.SHIFT + $.keypad.SPACE_BAR + $.keypad.ENTER],
                onKeypress: function(key, value, inst) {
                    $(this).trigger('keyup');
                }
            });
    }

    // Maxlength for textareas TODO limit to not CSS3 compatible browser
    maxlengthtextarea();

});

/**
 * setJsVar : Get all global used var
 * @deprecated in 3.0.0 not lauched under certain condition … …
 */
function setJsVar(){
    bFixNumAuto=LSvar.bFixNumAuto;
    bNumRealValue=LSvar.bNumRealValue;
    LEMradix=LSvar.sLEMradix;
    numRegex = new RegExp('[^-\.,0-9]','g');
    intRegex = new RegExp('[^-0-9]','g');
}

/**
 * Adapt cell to have a click on cell do a click on input:radio or input:checkbox (if unique)
 * Using delegate the can be outside document.ready (using .on is possible but on $(document) then : less readbale
 * @author Denis Chenu / Shnoulle
 */
function tableCellAdapters()
{
    $(".ls-answers tbody").on('click',' td',function(event) {// 'table.question tbody td' or 'td.radio-item,td.checkbox-item': maybe less js here
        var eventTarget=$(event.target).prop("tagName");// Alternative us data
        var eventActivate=$(this).find("input:radio,input:checkbox");
        if(eventActivate.length==1 && (eventTarget!='INPUT' && eventTarget!='LABEL' ) )
        {
            $(eventActivate).click();
            $(eventActivate).triggerHandler("click");
            // Why not use trigger('click'); only ?
        }
    });
}

Array.prototype.push = function()
{
    var n = this.length >>> 0;
    for (var i = 0; i < arguments.length; i++)
    {
        this[n] = arguments[i];
        n = n + 1 >>> 0;
    }
    this.length = n;
    return n;
};

Array.prototype.pop = function() {
    var n = this.length >>> 0, value;
    if (n) {
        value = this[--n];
        delete this[n];
    }
    this.length = n;
    return value;
};


//defined in group.php & question.php & survey.php, but a static function
function inArray(needle, haystack)
{
    for (h in haystack)
    {
        if (haystack[h] == needle)
        {
            return true;
        }
    }
    return false;
}

//defined in group.php & survey.php, but a static function
function match_regex(testedstring,str_regexp)
{
    // Regular expression test
    if (str_regexp == '' || testedstring == '') return false;
    pattern = new RegExp(str_regexp);
    return pattern.test(testedstring)
}

function addHiddenField(theform,thename,thevalue)
{
    var myel = document.createElement('input');
    myel.type = 'hidden';
    myel.name = thename;
    theform.appendChild(myel);
    myel.value = thevalue;
}

function cancelBubbleThis(eventObject)
{
    if (!eventObject) var eventObject = window.event;
    eventObject.cancelBubble = true;
    if (eventObject && eventObject.stopPropagation) {
        eventObject.stopPropagation();
    }
}

function cancelEvent(e)
{
  e = e ? e : window.event;
  if(e.stopPropagation)
    e.stopPropagation();
  if(e.preventDefault)
    e.preventDefault();
  e.cancelBubble = true;
  e.cancel = true;
  e.returnValue = false;
  return false;
}

function hookEvent(element, eventName, callback)
{
  if(typeof(element) == "string")
    element = document.getElementById(element);
  if(element == null)
    return;
  if(element.addEventListener)
  {
    if(eventName == 'mousewheel')
      element.addEventListener('DOMMouseScroll', callback, false);
    element.addEventListener(eventName, callback, false);
  }
  else if(element.attachEvent)
    element.attachEvent("on" + eventName, callback);
}

function noScroll(e)
{
  e = e ? e : window.event;
  cancelEvent(e);
}


function getkey(e)
{
   if (window.event) return window.event.keyCode;
    else if (e) return e.which; else return null;
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

function show_hide_group(group_id)
{
    var questionCount;

    // First let's show the group description, otherwise, all its childs would have the hidden status
    $("#group-" + group_id).show();
    // If all questions in this group are conditionnal
    // Count visible questions in this group
        questionCount=$("div#group-" + group_id).find("div[id^='question']:visible").size();

        if( questionCount == 0 )
        {
            $("#group-" + group_id).hide();
        }
}

// round function from phpjs.org
function round (value, precision, mode) {
    // http://kevin.vanzonneveld.net
    var m, f, isHalf, sgn; // helper variables
    precision |= 0; // making sure precision is integer
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0); // sign of the number
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
        case 'PHP_ROUND_HALF_DOWN':
            value = f + (sgn < 0); // rounds .5 toward zero
            break;
        case 'PHP_ROUND_HALF_EVEN':
            value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
            break;
        case 'PHP_ROUND_HALF_ODD':
            value = f + !(f % 2); // rounds .5 towards the next odd integer
            break;
        default:
            value = f + (sgn > 0); // rounds .5 away from zero
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
}

/* Maxlengt on textarea */
function maxlengthtextarea(){
    // Calling this function at document.ready : use maxlength attribute on textarea
    // Can be replaced by inline javascript
    $("textarea[maxlength]").change(function(){ // global solution
        var maxlen=$(this).attr("maxlength");
        if ($(this).val().length > maxlen) {
            $(this).val($(this).val().substring(0, maxlen));
        }
    });
    $("textarea[maxlength]").keyup(function(){ // For copy/paste (not for all browser)
        var maxlen=$(this).attr("maxlength");
        if ($(this).val().length > maxlen) {
            $(this).val($(this).val().substring(0, maxlen));
        }
    });
    $("textarea[maxlength]").keydown(function(event){ // No new key after maxlength
        var maxlen=$(this).attr("maxlength");
        var k =event.keyCode;
        if (($(this).val().length >= maxlen) &&
         !(k == null ||k==0||k==8||k==9||k==13||k==27||k==37||k==38||k==39||k==40||k==46)) {
            // Don't accept new key except NULL,Backspace,Tab,Enter,Esc,arrows,Delete
            return false;
        }
    });
}
/**
 * Add a title on cell with answer
 * Title must be updated because label can be updated by expression : mouseover do it only if needed. Accessibility must use aria-labelledby
 **/
function doToolTipTable()
{
    $(document).on("mouseover"," td.answer-item",function() {
        var text = $(this).find('label').text().trim();
        if(text!==""){
            $(this).attr('title', text);
        }else{
            $(this).removeAttr('title');
        }
    });
}
