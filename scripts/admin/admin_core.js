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

// Namespace
var LS = LS || {};

/* Set a variable to test if browser have HTML5 form ability
 * Need to be replaced by some polyfills see #8009
 */
hasFormValidation= typeof document.createElement( 'input' ).checkValidity == 'function';

$(document).ready(function(){

    initializeAjaxProgress();
    tableCellAdapters();
    linksInDialog();
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



    if($('#survey-grid').length>0)
    {
        $(document).on('click', '.has-link', function () {
            $linkUrl = $(this).find('a').attr('href');
            window.location.href=$linkUrl;
            console.log($linkUrl);
        });
    }

    /* Switch format group */
    if ($('#switchchangeformat').length>0){
        $('#switchchangeformat button').on('click', function(event, state) {
            $('#switchchangeformat button.active').removeClass('active');
            $(this).addClass('active');
            $value = $(this).data('value');
            $url = $('#switch-url').attr('data-url')+'/format/'+$value;

            console.log('required format: '+$value);
            console.log('format url: '+$url);

            $.ajax({
                url : $url,
                type : 'GET',
                dataType : 'html',

                // html contains the buttons
                success : function(html, statut){
                },
                error :  function(html, statut){
                    alert('error');
                }
            });

        });
    };


    $('#survey-action-chevron').click(function(){
        $url = $(this).data('url');
        $.ajax({
            url : $url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
                $('#survey-action-container').animate({
            "height": "toggle", "opacity": "toggle"
        });
                $('#survey-action-chevron').toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');
            },
            error :  function(html, statut){
                alert('error');
            }
        });
    });


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

    $('#question_type_button  li a').click(function(){
        $(".btn:first-child .buttontext").text($(this).text());
        $('#question_type').val($(this).data('value'));

        updatequestionattributes();
       });

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
    if ($("#question_type_button").not('.none').length > 0 && $("#question_type_button").attr('type')!='hidden'){

       qTypeDropdownInit();
        $("#question_type_button").change(function(event){
            OtherSelection(this.value);
        });
        $("#question_type_button").change();
    }
    else
    {
        $("#question_type.none").change(function(event){
            OtherSelection(this.value);
        });
        $("#question_type.none").change();
    }

    /**
     * Confirmation modal
     *
     * Either provide a data-href to redirect after OK button is clicked,
     * or data-onclick to be run when OK is clicked.
     */
    $('#confirmation-modal').on('show.bs.modal', function(e) {

        var onclick = null;
        var href = null;

        if ($(this).data('href')) {
            href = $(this).data('href');    // When calling modal from javascript
        }
        else {
            href = $(e.relatedTarget).data('href');
        }

        if ($(this).data('onclick')) {
            onclick = $(this).data('onclick');
        }
        else {
            onclick = $(e.relatedTarget).data('onclick');
        }

        // Get message
        var message = $(this).data('message');
        if (message) {
            $(this).find('.modal-body-text').html(message);
        }

        $keepopen = $(this).data('keepopen');
        if (href != '' && href !== undefined) {
            $(this).find('.btn-ok').attr('href', href);
        }
        else if (onclick != '' && onclick !== undefined) {

            var onclick_fn = eval(onclick);

            if (typeof onclick_fn == 'function') {
                $(this).find('.btn-ok').off('click');
                $(this).find('.btn-ok').on('click', function(ev) {
                    if(! $keepopen )
                    {
                        $('#confirmation-modal').modal('hide');
                    }
                    onclick_fn();
                });
            }
            else {
                throw "Confirmation modal: onclick is not a function. Wrap data-onclick content in (function() { ... }).";
            }

        }
        else if ($(e.relatedTarget).data('ajax-url')) {
            var postDatas   = $(e.relatedTarget).data('post');
            var gridid      = $(e.relatedTarget).data('gridid');

            $(this).find('.btn-ok').on('click', function(ev)
            {
                $.ajax({
                    type: "POST",
                    url: $(e.relatedTarget).data('ajax-url'),
                    data: postDatas,

                    success : function(html, statut)
                    {
                        $.fn.yiiGridView.update(gridid);                   // Update the surveys list
                        $('#confirmation-modal').modal('hide');
                    },
                    error :  function(html, statut){
                        $('#confirmation-modal .modal-body-text').append(html.responseText);
                    }

                });
            });
        }
        else {
            throw "Confirmation modal: Found neither data-href or data-onclick.";
        }

        $(this).find('.modal-body-text').html($(e.relatedTarget).data('message'));
    });

    // Error modal
    $('#error-modal').on('show.bs.modal', function(e) {
        $(this).find('.modal-body-text').html($(e.relatedTarget).data('message'));
    });

});

function qTypeDropdownInit()
{
    $(document).ready(function () {
        $("#question_type_button .questionType").each(function(index,element){
            $(element).qtip({
                style: {
                    classes: 'qtip-bootstrap'
                },
                content: getToolTip($(element).text()),
                position: {
                    my : 'center right',
                    at: 'center left',
                    target: $('label[for=question_type]'),
                    viewport: $(window),
                    adjust: {
                        x: 0
                    }

                }
            });

        });
    });
    $(document).ready(function() {

        $('.questionType').on('mouseenter', function(e){
            //alert($(this).attr('class'));
            $('.questionType').qtip('hide');
            $(this).qtip('option', 'position.target', $(this).qtip('show'));
        });

        $('.questionType').on('mouseleave', function(e){
            $(this).qtip('hide');
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
    var type = $('#question_type').val();
    OtherSelection(type);

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
        $('#pleaseselectfile-popup').modal();
        form.the_file.focus();
        return false ;
    }
    return true ;
}

function doToolTip()
{
    $('.btntooltip').tooltip();

    // Since you can only have one option per data-toggle,
    // we need this to enable both modal and toggle on one
    // button. E.g., <button data-toggle='modal' data-tooltip='true' title="foo">...</button>
    $('[data-tooltip="true"]').tooltip();

    $('[data-toggle="tooltip"]').tooltip()

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

/**
 * Like arrHasDupes, but returns the duplicated item
 *
 * @param {array} A
 * @return {mixed|boolean} Array item] or false if no duplicate is found
 */
function arrHasDupesWhich(A) {
    var i, j, n;
    n=A.length;
    // to ensure the fewest possible comparisons
    for (i=0; i<n; i++) {                        // outer loop uses each item i at 0 through n
        for (j=i+1; j<n; j++) {              // inner loop only compares items j at i+1 to n
            if (A[i]==A[j]) return A[i];
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
            dialog.dialog("option", "title", title);
            dialog.dialog("open");
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

/**
 * A method to use the implemented notifier, via ajax or javascript
 * 
 * @param text string  | The text to be displayed
 * @param classes string | The classes that will be put onto the inner container
 * @param styles object | An object of css-attributes that will be put onto the inner container
 * @param customOptions | possible options are: 
 *                         useHtml (boolean) -> use the @text as html
 *                         timeout (int) -> the timeout in milliseconds until the notifier will fade/slide out 
 *                         inAnimation (string) -> The jQuery animation to call for the notifier [fadeIn||slideDown]
 *                         outAnimation (string) -> The jQuery animation to remove the notifier [fadeOut||slideUp]
 *                         animationTime (int) -> The time in milliseconds the animation will last             
 */
function NotifyFader(){
    var count = 0;
    
    var increment = function(){count = count+1;},
        decrement = function(){count = count-1;},
        getCount = function(){return count;};

    var create = function(text, classes, styles, customOptions){
        increment();
        customOptions = customOptions || {};
        styles = styles || {};
        classes = classes || "well well-lg";

        var options = {
            useHtml : customOptions.useHtml || true,
            timeout : customOptions.timeout || 3500,
            inAnimation : customOptions.inAnimation || "slideDown", 
            outAnimation : customOptions.outAnimation || "slideUp", 
            animationTime : customOptions.animationTime || 450
        };
        var container = $("<div> </div>");
        container.addClass(classes);
        container.css(styles);
        if(options.useHtml){
            container.html(text);
        } else {
            container.text(text);
        }
        var newID = "notif-container_"+getCount();
        $('#notif-container').clone()
            .attr('id', newID)
            .css({
                display: 'none',
                top : (8*((getCount())))+"%",
                position: 'fixed',
                left : "15%",
                width : "70%",
                'z-index':3500 
            })
            .appendTo($('#notif-container').parent())
            .html(container);

        $('#'+newID)[options.inAnimation](options.animationTime, function(){
            var remove = function(){
                $('#'+newID)[options.outAnimation](options.animationTime, function(){
                    $('#'+newID).remove();
                    decrement();
                });
            }
            $(this).on('click', remove);
            setTimeout(remove, options.timeout);
        });
    };

    return {
        create : create,
        increment: function(){count = count+1;},
        decrement: function(){count = count-1;},
        getCount: function(){return count;}
        };
};
var LsGlobalNotifier = new NotifyFader();

function notifyFader(text, classes, styles, customOptions) {

    // Hide all modals
    // TODO: Where is this needed?
    // TODO: Commented, because doesn't work quick condition quick-add where modal should stay open
    //$('.modal').modal('hide');

    LsGlobalNotifier.create(text, classes, styles, customOptions);
}

/**
 * Part of ajax helper
 * @param {object} JSON object from server
 * @return {boolean} true if the original success method should be run after this (always, except on failed login)
 * @todo Localization
 * @todo Branch on message type?
 */
LS.ajaxHelperOnSuccess = function(response) {
    // Check type of response and take action accordingly
    if (response == '') {
        alert('No response from server');
    }
    else if (!response.loggedIn) {

        // Hide any modals that might be open
        $('.modal').modal('hide');

        $('#ajax-helper-modal .modal-content').html(response.html);
        $('#ajax-helper-modal').modal('show');
        return false;
    }
    // No permission
    else if (!response.hasPermission) {
        notifyFader(response.noPermissionText, 'well-lg bg-danger text-center');
    }
    // Error popup
    else if (response.error) {
        notifyFader(response.error.message, 'well-lg bg-danger text-center');
    }
    // Success popup
    else if (response.success) {
        notifyFader(response.success, 'well-lg bg-primary text-center');
    }
    // Modal popup
    else if (response.html) {
        $('#ajax-helper-modal .modal-content').html(response.html);
        $('#ajax-helper-modal').modal('show');
    }

    return true;
}

/**
 * Like $.ajax, but with checks for errors,
 * permission etc. Should be used together
 * with the PHP AjaxHelper.
 * @todo Handle error from server (500)?
 * @param {object} options - Exactly the same as $.ajax options
 * @return {object} ajax promise
 */
LS.ajax = function(options) {

    var oldSuccess = options.success;
    var oldError = options.error;
    options.success = function(response) {

        $('#ls-loading').hide();

        // User-supplied success is always run EXCEPT when login fails
        var runOldSuccess = LS.ajaxHelperOnSuccess(response);

        if (oldSuccess && runOldSuccess) {
            oldSuccess(response);
        }
    }

    options.error = function(response) {
        $('#ls-loading').hide();
        if (oldError) {
            oldError();
        }
    }

    $('#ls-loading').show();

    return $.ajax(options);
}
