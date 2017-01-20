/*
 * JavaScript functions in survey taking
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Some function can be launch before document ready (and seems intersting)
limesurveySubmitHandler();
needConfirmHandler();
tableCellAdapters();
activateLanguageChanger();
$(document).ready(function()
{
    navbuttonsJqueryUi();
    showStartPopups();
    addClassEmpty();
    noScrollOnSelect();
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
 */
function setJsVar(){
    bFixNumAuto=LSvar.bFixNumAuto;
    bNumRealValue=LSvar.bNumRealValue;
    LEMradix=LSvar.sLEMradix;
    numRegex = new RegExp('[^-\.,0-9]','g');
    intRegex = new RegExp('[^-0-9]','g');
}
// Deactivate all other button on submit
function limesurveySubmitHandler(){
    // Return false disallow all other system
    $(document).on("click",".disabled",function(){return false});
    $(document).on("click",'.active',function(){return false;});// "[active]" don't seem to work with jquery-1.10.2

    $(document).on('click',"#limesurvey .button", function(event){
        $(this).prop('active',true).addClass('active');
        $("#limesurvey .button.ui-button" ).not($(this)).button( "option", "disabled", true );
        $("#limesurvey .button").not($(this)).prop('disabled',true).addClass('disabled');
    });
    if (document.all && !document.querySelector) { // IE7 or lower
        $(function() {
            $("#defaultbtn").css('display','inline').css('width','0').css('height','0').css('padding','0').css('margin','0').css('overflow','hidden');
            $("#limesurvey [type='submit']").not("#defaultbtn").first().before($("#defaultbtn"));
        });
    }
}


// Ask confirmation on click on .needconfirm
function needConfirmHandler(){
    $(document).on('click',"[data-confirmedby]", function(event){
        text=$("label[for='"+$(this).data('confirmedby')+"']").text();
        if (confirm(text)) {
            $("#"+$(this).data('confirmedby')).prop('checked',true);
            return true;
        }
        $(".button.ui-button" ).button( "option", "disabled", false );
        $(".button").prop('disabled',false).removeClass('disabled');
        $(this).prop('active',false).removeClass('active');
        return false;
    });
}
/**
 * checkconditions : javascript function attach to some element
 * Launch ExprMgr_process_relevance_and_tailoring with good value
 */
function checkconditions(value, name, type, evt_type)
{
    if (typeof evt_type === 'undefined')
    {
        evt_type = 'onchange';
    }
    if (type == 'radio' || type == 'select-one')
    {
        $('#java'+name).val(value);
    }
    else if (type == 'checkbox')
    {
        if ($('#answer'+name).is(':checked'))
        {
            $('#java'+name).val('Y');
        } else
        {
            $('#java'+name).val('');
        }
    }
    else if (type == 'text' && name.match(/other$/))
    {
        $('#java'+name).val(value);
    }

    aQuestionsWithDependencies = $('#aQuestionsWithDependencies').data('qids');

    var result;
    if(typeof name !== 'undefined')
    {
        result = name.split('X');
        result = result[2]
    }

    // $isRelevant = $.inArray(result, aQuestionsWithDependencies); NEED TO ADD THE QUESTIONS WITH CONDITIONS BEFORE WE CAN USE IT !!!!
    $isRelevant = 1;
    if($.isFunction(window.ExprMgr_process_relevance_and_tailoring ) && $isRelevant!=-1)
        ExprMgr_process_relevance_and_tailoring(evt_type,name,type);
}

/**
 * fixnum_checkconditions : javascript function attach to some element
 * Update the answer of the user to be numeric and launch checkconditions
 * 
 * Also checks if any of the arrow keys is pressed to avoid unecessary hassle.
 */
function fixnum_checkconditions(value, name, type, evt_type, intonly)
{
    if(window.event){
    var keyPressed =  window.event.keyCode || 0;
    if(
            keyPressed == 37 //left arrow
        ||  keyPressed == 39 //right arrow
    ){return false; }
    }

    var decimalValue;
    var newval = new String(value);
    var checkNumericRegex = new RegExp(/^(-)?[0-9]*(,|\.)[0-9]*$/);
    var cleansedValue = newval.replace(numRegex,'');
    /**
     * If have to use parsed value.
     */
    if(!bNumRealValue)
    {                
        if(checkNumericRegex.test(value)) {
            try{
                decimalValue = new Decimal(cleansedValue);
            } catch(e){
                decimalValue = new Decimal(cleansedValue.replace(',','.'));
            }
            
            if (typeof intonly !=='undefined' && intonly==1) {
                newval = decimalValue.trunc();
            }
        } else {
            newval = cleansedValue;
        }

    }

    /**
     * If have to fix numbers automatically.
     */
    if(bFixNumAuto && (newval != ""))
    {
        var addition = "";
        if(cleansedValue.split("").pop().match(/(,)|(\.)/)){
            addition = cleansedValue.split("").pop();
        }
        var matchFollowingZeroes =  cleansedValue.match(/^-?([0-9])*(,|\.)(0+)$/);
        if(matchFollowingZeroes){
            addition = LEMradix+matchFollowingZeroes[3];
        }
        if(decimalValue == undefined){
            try{
                decimalValue = new Decimal(cleansedValue);
            } catch(e){
                decimalValue = new Decimal(cleansedValue.replace(',','.'));
            }
        }

        /**
         * Work on length of the number
         * Avoid numbers longer than 20 characters before the decimal separator and 10 after the decimal separator.
         */
        // Treat decimal part, if there is one.
        // Trim after 10th decimal if larger than 10 decimals.
        if(decimalValue.dp()>10){
            decimalValue.toDecimalPlaces(10);
        }

        /**
         * Set display value
         */
        displayVal = decimalValue.toString();

        if(LEMradix==",")
            displayVal = displayVal.replace(/\./,',');

        newval = displayVal+addition

        if (name.match(/other$/)) {
            if($('#answer'+name+'text').val() != newval){
                $('#answer'+name+'text').val(newval);
            }
        }

        if($('#answer'+name).val() != newval){
            $('#answer'+name).val(newval);
        }
    }

    /**
     * Check conditions
     */
    if (typeof evt_type === 'undefined')
    {
        evt_type = 'onchange';
    }
    checkconditions(newval, name, type, evt_type);
}

// Set jquery-ui to LS Button
function navbuttonsJqueryUi(){
    $('[dir!="rtl"] #moveprevbtn').button({
    icons: {
        primary: 'ui-icon-triangle-1-w'
    }
    });
    $('[dir="rtl"] #moveprevbtn').button({
    icons: {
        secondary: 'ui-icon-triangle-1-e'
    }
    });
    $('[dir!="rtl"] #movenextbtn').button({
    icons: {
        secondary: 'ui-icon-triangle-1-e'
    }
    });
    $('[dir="rtl"] #movenextbtn').button({
    icons: {
        primary: 'ui-icon-triangle-1-w'
    }
    });
    $(".button").button();
    // TODO trigger handler activate/deactivate to update ui-button class
}
/**
 * showStartPopups : Take all message in startPopups json array and launch an alert with text
 */
function showStartPopups(){
    if(typeof showpopup=="undefined"){showpopup=1;}
    if(typeof startPopups=="undefined"){startPopups=[];}
    if(showpopup){
        $.each(startPopups,function(key, text){
            alert($("<div/>").html(text).text());// Parse HTML because of &#039;
        });
    }
}
/**
 * Update survey just when select a new language
 */
function activateLanguageChanger(){
    $(document).on('change','select.languagechanger', function() {
        if($(this).hasClass('previewmode'))
        {
            var target=$(this).data('targeturl');
            $('<form>', {
                "html": '<input type="hidden" name="lang" value="' + $(this).find('option:selected').val() + '" />',
                "action": target
            }).appendTo(document.body).submit();
            return false;
        }
        if(!$(this).closest('form').length){// If there are no form : we can't use it, we need to create and submit. This break no-js compatibility in some page (token for example).
            if($('form#limesurvey').length==1){ // The limesurvey form exist in document, move select and button inside and click
                $("form#limesurvey [name='lang']").remove();// Remove existing lang selector
                $("<input type='hidden']>").attr('name','lang').val($(this).find('option:selected').val()).appendTo($('form#limesurvey'));
                $("#changelangbtn").appendTo($('form#limesurvey'));
                $('#changelangbtn').click();
            }else{
                if($(this).data('targeturl')){
                    var target=$(this).data('targeturl');
                }else{
                    var target=document.location.href;
                }
                $('<form>', {
                    "html": '<input type="hidden" name="lang" value="' + $(this).find('option:selected').val() + '" />',
                    "action": target,
                    "method": 'post'
                }).appendTo(document.body).append($("input[name='YII_CSRF_TOKEN']")).submit();
            }
        }else{
            $(this).closest('form').find("[name='lang']").not($(this)).remove();// Remove other lang
            $('#changelangbtn').click();
        }
    });
    $(function(){
        $(".changelang.jshide").hide();
    });
}
/**
 * Manage the index
 */
function manageIndex(){
    $("#index .jshide").hide();
    $("#index").on('click','li,.row',function(e){
        if(!$(e.target).is('button')){
            $(this).children("[name='move']").click();
        }
    });
    $(function() {
        $(".outerframe").addClass("withindex");
        var idx = $("#index");
        var row = $("#index .row.current");
        if(row.length)
            idx.scrollTop(row.position().top - idx.height() / 2 - row.height() / 2);
    });
}
/**
 * Put a empty class on empty answer text item (limit to answers part)
 * @author Denis Chenu / Shnoulle
 */
function addClassEmpty()
{
    $('.answer-item input.text[value=""]').addClass('empty');
    $('.answer-item input[type=text][value=""]').addClass('empty');
    $('.answer-item textarea').each(function(index) {
    if ($(this).val() == ""){
        $(this).addClass('empty');
    }
    });
    $("body").delegate(".answer-item input.text,.text-item input[type=text],.answer-item textarea","blur focusout",function(){
    if ($(this).val() == ""){
        $(this).addClass('empty');
    }else{
        $(this).removeClass('empty');
    }
    });
}

/**
 * Disable scroll on select, put it in function to allow update in template
 *
 */
function noScrollOnSelect()
{
    $(".question").find("select").each(function () {
        hookEvent($(this).attr('id'),'mousewheel',noScroll);
    });
}
/**
 * Adapt cell to have a click on cell do a click on input:radio or input:checkbox (if unique)
 * Using delegate the can be outside document.ready (using .on is possible but on $(document) then : less readbale
 * @author Denis Chenu / Shnoulle
 */
function tableCellAdapters()
{
//	$('table.question').delegate('tbody td input:checkbox,tbody td input:radio,tbody td label',"click", function(e) {
//		e.stopPropagation();
//	});
    $(document).on('click','table.question tbody td',function(event) {// 'table.question tbody td' or 'td.radio-item,td.checkbox-item': maybe less js here
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
//Hide the Answer and the helper field in an
$(document).ready(
    function(){
        $('.question-container').each(function(){
            if($(this).find('div.answer-container').find('input').length == 1)
            {
                if($(this).find('div.answer-container').find('input[type=hidden]').length >0
                    && $(this).find('div.answer-container').find('select').length < 1)
                {
                    $(this).find('div.answer-container').css({display: 'none'});
                }
                if(trim($(this).find('div.question-help-container').find('div').html()) == "")
                {
                    $(this).find('div.question-help-container').css({display: 'none'});
                }
            }
        });
    }
);
