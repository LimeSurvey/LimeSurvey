/*
 * JavaScript functions for LimeSurvey administrator
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
var LS = LS || {
    onDocumentReady: {}
};

var labelcache=[];

/* Event added on document for all button (new one added in js too)
 * TODO : use a real ajax system : see scripts/question.js validateQuestion function for example
 **/
$(document).on("click","#editsubquestionsform :submit", function() {
    //Validate duplicate before try to submit: surely some other javascript elsewhere
    return code_duplicates_check();
});

$(document).on('ready  pjax:scriptcomplete', function(){

    $('.tab-page:first .answertable tbody').sortable({
        containment:'parent',
        start:startmove,
        stop: endmove,
        update:aftermove,
        distance:3});
        
    $('.btnaddanswer').on("click.subquestions", addinput);
    $('.btndelanswer').on("click.subquestions", deleteinput);
    $('#labelsetbrowserModal').on("shown.bs.modal.", lsbrowser );
    $('#labelsetbrowserModal').on("hidden.bs.modal.", lsbrowser_destruct );

    //$('.btnaddanswer').click(addinput);
    //$('.btndelanswer').click(deleteinput);
    //$('.btnlsbrowser').click(lsbrowser);
    $('#btnlsreplace').on('click', function(e){ e.preventDefault(); transferlabels('replace') });
    $('#btnlsinsert').on('click', function(e){ e.preventDefault(); transferlabels('insert') });



    $('#quickaddModal').on('show.bs.modal', function(e) {
        var scale_id = $(e.relatedTarget).data('scale-id');
        var table_id = $(e.relatedTarget).closest('div.action-buttons').siblings('table.answertable').attr('id');

        $('#btnqainsert').off('click').on('click', function () {
            quickaddlabels(scale_id, 'add', table_id);
        });

        $('#btnqareplace').off('click').on('click', function () {
            quickaddlabels(scale_id, 'replace', table_id);
        });
    });

    $('#labelsets').click(lspreview);
    //$('#languagefilter').click(lsbrowser);
    $('.bthsaveaslabel').click(getlabel);
    $('input[name=savelabeloption]:radio').click(setlabel);
    flag = [false, false];
    $('#btnsave').click(savelabel);
    updaterowproperties();

    bindExpandRelevanceEquation();

});

function rebindClickHandler(){
    $('.btnaddanswer').off("click.subquestions").on("click.subquestions", addinput);
    $('.btndelanswer').off("click.subquestions").on("click.subquestions", deleteinput);
}
/**
 * Bind relevance equation to expand on click (only once)
 *
 * @return void
 */
function bindExpandRelevanceEquation()
{
    $('.relevance').off('click').on('click', function() {
        $('#rel-eq-th').toggleClass('col-md-1 col-md-4', 'fast');
        $('.relevance').data('toggle', '').tooltip('destroy');
        $('.relevance').off('click');
    });
}

function addInputPredefined(i){
    var $elDatas = $('#add-input-javascript-datas');
    var scale_id= $('#current_scale_id').val();
    //We build the datas for the request
    datas = {
        'surveyid':             $elDatas.data('surveyid'),
        'gid':                  $elDatas.data('gid'),
        'codes':                JSON.stringify({'lbl_1':'eins'}),
        'scale_id':             scale_id,
        'position':             i,
        'type':                 'subquestion',
        'languages' :           JSON.stringify($elDatas.data('languages').join(';')),
    };
      // We get the HTML of the new row to insert
     return $.ajax({
        type: "GET",
        contentType: 'json',
        url: $elDatas.data('url'),
        data: datas,
    });
}
/**
 * @return {boolean} true if relevance equation field is expanded
 */
function relevanceIsExpanded()
{
    return $('#rel-eq-th').hasClass('col-md-4');
}

/**
 * Bind click to expand relevance equation
 * if not already expanded.
 *
 * @return {void}
 */
function bindClickIfNotExpanded()
{
    if (!relevanceIsExpanded())
    {
        bindExpandRelevanceEquation();
        // Activate tooltip
        window.LS.doToolTip();
    }

}

/**
 * Get toolrip data for relevance equation.
 * If expanded, returns empty string
 *
 * @return {string}
 */
function getRelevanceToolTip()
{
    var relevanceTooltip = !relevanceIsExpanded() ?
        'data-toggle="tooltip" data-title="' + clickToExpand + '"' :
        '';

    return relevanceTooltip;
}

function deleteinput(e)
{
    e.preventDefault();
    // 1.) Check if there is at least one answe
    var position,
        countanswers=$(this).closest("tbody").children("tr").length; //Maybe use class is better
    if (countanswers>1)
    {
        // 2.) Remove the table row
        var classes=$(this).closest('tr').attr('class').split(' ');
        LS.ld.forEach(classes, function(curClass, x) {
            if (curClass.substr(0,3)=='row'){
                position=curClass.substr(4);
            }
        });

        var info = $(this).closest('table').attr('id').split("_"),
            language = info[1],
            scale_id = info[2],
            languages = langs.split(';');


        LS.ld.forEach(languages, function (curLanguage, x) {
            var tablerow = $('#tabpage_' + languages[x]).find('#answers_' + languages[x] + '_' + scale_id + ' .row_' + position);
            if (x == 0)
            {
                tablerow.fadeTo(400, 0, function () {
                    $(this).remove();
                    updaterowproperties();
                });
            } else
            {
                tablerow.remove();
            }
            deleteSubquestionrow($(tablerow));
        });


    } else
    {
        $.blockUI({message:"<p><br/>"+strCantDeleteLastAnswer+"</p>"});
        setTimeout(jQuery.unblockUI,1000);
    }
    updaterowproperties();
}

function deleteSubquestionrow(jQueryItem){
    if ($(jQueryItem).is('[id]'))
    {
        rowinfo=$(jQueryItem).attr('id').split('_');
        $('#deletedqids').val($('#deletedqids').val()+' '+rowinfo[2]);
    }
}

/**
 * add addinputQuickEdit : for usage with the quickAdd Button
 */
function addinputQuickEdit($currentTable, language, first, scale_id, codes)
{
    codes = codes || [];
    var $elDatas               = $('#add-input-javascript-datas'),  // This hidden element  on the page contains various datas for this function
        $url                   = $elDatas.data('quickurl'),         // Url for the request
        $errormessage          = $elDatas.data('errormessage'),     // the error message if the AJAX request failed
        qid = "{{quid_placeholder}}",
        $defer                 = $.Deferred(),
        $codes, datas;

    // We get all the subquestion codes currently displayed
        // We get all the subquestion codes currently displayed
    if($currentTable.find('.code').length>0){
        $currentTable.find('.code').each(function(){
            codes.push($(this).val());
        });
    } else {
        $currentTable.find('.code-title').each(function(){
            codes.push($(this).text().trim());
        });
    }

    // We convert them to json for the request
    $codes = JSON.stringify(codes);
    //We build the datas for the request
    datas = {
        'codes' : $codes,
        'scale_id' : scale_id, // In $elDatas.data('scale-id') ?
        'type' : 'subquestion',
        'position' : null,
        'first' : first,
        'language' : language,
    }

    // We get the HTML of the new row to insert
     $.ajax({
        type: "POST", // We don't update DB, no need post
        url: $url,
        data: datas,
        success: function(htmlrow) {
            var $lang_table = $('#answers_'+language+'_'+scale_id);
            $defer.resolve({lng: language, langtable: $lang_table, html: htmlrow});
        },
        error :  function(html, statut){
            alert($errormessage);
            $defer.reject([html, statut, $errormessage]);
        }
    });
    return $defer.promise();
}


/**
 * add input : the ajax way
 */

function addinput(e)
{
    e.preventDefault();
       var $that              = $(this),                               // The "add" button
        $currentRow            = $that.closest('.row-container'),   // The row containing the "add" button
        $currentTable          = $that.closest('.answertable'),
        $commonId              = $currentRow.data('common-id'),     // The common id of this row in the other languages
        $elDatas               = $('#add-input-javascript-datas'),  // This hidden element  on the page contains various datas for this function
        $url                   = $elDatas.data('url'),              // Url for the request
        $errormessage          = $elDatas.data('errormessage'),     // the error message if the AJAX request failed
        $languages             = JSON.stringify(langs),             // The languages
        $codes, datas;

    // We get all the subquestion codes currently displayed
    var codes = [];
    $currentTable.find('.code').each(function(){
        codes.push($(this).val());
    });

    // We convert them to json for the request
    $codes = JSON.stringify(codes);

    //We build the datas for the request
    datas                  = 'surveyid='+$elDatas.data('surveyid'),
    datas                 += '&gid='+$elDatas.data('gid'),
    datas                 += '&qid='+$elDatas.data('qid'),
    datas                 += '&codes='+$codes,
    datas                 += '&scale_id='+$(this).find('i').data('scale-id'),
    datas                 += '&type=subquestion',
    datas                 += '&position=',
    datas                 += '&languages='+$languages;

    // We get the HTML of the different rows to insert  (one by language)
    $.ajax({
        type: "GET",
        url: $url,
        data: datas,
        success: function(arrayofhtml) {

            // arrayofhtml is a json object containing the different HTML row by language
            // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}

            // We insert each row for each language
            $.each(arrayofhtml, function(lang, htmlRow){
                $elRowToUpdate = $('#row_'+lang+'_'+$commonId);                 // The row for the current language
                $elRowToUpdate.after(htmlRow);                                  // We insert the HTML of the new row after this one
            });
            rebindClickHandler();
        },
        error :  function(html, statut){
            alert($errormessage);
        }
    });
}

function startmove(event,ui)
{
    var $that = ui.item,
    	oldindex = Number($that.parent().children().index($that)+1);
    $that.data('oldindex', oldindex);
}

function endmove(event,ui){
    updaterowproperties();
}

function aftermove(event,ui)
{
    // But first we have change the sortorder in translations, too
    var $that = ui.item,
    	newindex = Number($that.parent().children().index($that)+1),
    	oldindex = $that.data('oldindex'),
        info=$that.closest('table').attr('id').split("_"),
        languages=langs.split(';');

    LS.ld.forEach(languages, function(curLanguage, x) {
        if (x>0)
        {
            var tablerow=$('#tabpage_'+languages[x]+' tbody tr:nth-child('+newindex+')'),
                tablebody=$('#tabpage_'+languages[x]).find('tbody');
            if (newindex<oldindex)
            {
                $('#tabpage_'+languages[x]+' tbody tr:nth-child('+newindex+')').before($('#tabpage_'+languages[x]+' tbody tr:nth-child('+oldindex+')'));
            }
            else
            {
                $('#tabpage_'+languages[x]+' tbody tr:nth-child('+newindex+')').after($('#tabpage_'+languages[x]+' tbody tr:nth-child('+oldindex+')'));
                //tablebody.find('.row_'+newindex).after(tablebody.find('.row_'+oldindex));
            }
        }
    });
}

// This function adjusts the alternating table rows
// if the list changed

function updateIfEmpty($elm, $attr, $attr_value)
{
    if($elm.attr($attr)=='')
    {
        $elm.attr($attr,$attr_value);
    }
}

function updaterowproperties()
{
    var sID=$('input[name=sid]').val(),
    	gID=$('input[name=gid]').val(),
    	qID=$('input[name=qid]').val();

    $('.answertable tbody').each(function(){
        var info=$(this).closest('table').attr('id').split("_");
        var language=info[1];
        var scale_id=info[2];
        var rownumber = 1;

        $(this).children('tr').each(function(){

            var uniqueRowId=$(this).data('common-id').split('_').shift();

            if(!$(this).hasClass('row_'+uniqueRowId))
            {
                $(this).addClass('row_'+uniqueRowId);
            }

            updateIfEmpty($(this).find('.oldcode'), 'name', 'oldcode_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.code'), 'id', 'code_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.code'), 'name', 'code_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.answer'), 'id', 'answer_'+language+'_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.answer'), 'name', 'answer_'+language+'_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.assessment'), 'id', 'assessment_'+uniqueRowId+'_'+scale_id);
            updateIfEmpty($(this).find('.assessment'), 'name', 'assessment_'+uniqueRowId+'_'+scale_id);
            // Newly inserted row editor button
            $(this).find('.editorLink').attr('href','javascript:start_popup_editor(\'answer_'+language+'_'+uniqueRowId+'_'+scale_id+'\',\'[Answer:]('+language+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')');
            $(this).find('.editorLink').attr('id','answer_'+language+'_'+uniqueRowId+'_'+scale_id+'_ctrl');
            $(this).find('.btneditanswerena').attr('id','answer_'+language+'_'+uniqueRowId+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerena').attr('name','answer_'+language+'_'+uniqueRowId+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerdis').attr('id','answer_'+language+'_'+uniqueRowId+'_'+scale_id+'_popupctrldis');
            $(this).find('.btneditanswerdis').attr('name','answer_'+language+'_'+uniqueRowId+'_'+scale_id+'_popupctrldis');
            rownumber++;
        });
    });
}

function updatecodes()
{

}

function getNextCode(sSourceCode)
{
    sourcecode = sSourceCode;
    i=1;
    found=true;
    foundnumber=-1;
    sclength = sourcecode.length;
    while (i<=sclength && found == true)
    {
        found=is_numeric(sourcecode.substr(sclength-i,i));
        if (found)
        {
            foundnumber=sourcecode.substr(sclength-i,i);
            i++;
        }
    }
    if (foundnumber==-1)
    {
        return(sourcecode);
    }
    else
    {
        foundnumber++;
        foundnumber=foundnumber+'';
        result=sourcecode.substr(0,sclength-foundnumber.length)+foundnumber;
        return(result);
    }
}

/**
* Check if all existing codes are unique
* If sNewValue is not empty then only sNewValue is checked for uniqueness against the existing codes
*
* @param sNewValue
*
* @returns {Boolean} False if codes are not unique
*/
function areCodesUnique(sNewValue)
{
    languages=langs.split(';');
    var dupefound=false;
    $('#tabpage_'+languages[0]+' .answertable tbody').each(function(){
        var codearray=[];
        $(this).find('tr .code').each(function(){
            codearray.push($(this).val());
        })
        if (sNewValue!='')
        {
            codearray=window.LS.getUnique(codearray);
            codearray.push(sNewValue);
        }
        if (window.LS.arrHasDupes(codearray))
            {
            dupefound=true;
            return;
        }
    })
    if (dupefound)
        {
        return false;
    }
}

function is_numeric (mixed_var) {
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}

function popupeditor()
{
    input_id=$(this).parent().find('.answer').attr('id');
    start_popup_editor(input_id);
}

function code_duplicates_check()
{
    //$('.code[data-toggle="tooltip"]').data('toggle', '').tooltip('destroy');

    languages=langs.split(';');
    var cansubmit=true;
    $('#tabpage_'+languages[0]+' .answertable tbody').each(function()
    {
        var codearray=[];
        $(this).find('tr .code').each(function(){
            codearray.push($(this).val().toLowerCase());
        });
        var theDuplicate = window.LS.arrHasDupesWhich(codearray);
        if (theDuplicate !== false)
        {

            $('#error-modal .modal-body-text').html(duplicatesubquestioncode);
            $('#error-modal').modal();
            cansubmit= false;
        }
    });
    console.ls.log('cansubmit: '+cansubmit);
    return cansubmit;
}

function lsbrowser_destruct(e){
    $('#labelsets').select2('destroy');
    $("#labelsetpreview").empty();
}

function lsbrowser(e)
{
    var scale_id= $(e.relatedTarget).data('scale-id');
    $('body').append('<input type="hidde" id="current_scale_id" value="'+scale_id+'" name="current_scale_id" />');
    
    $('#labelsets').select2();
    $("#labelsetpreview").html('');
    var surveyid=$('input[name=sid]').val();
    $.ajax({
        url: lspickurl,
        data: {sid:surveyid, match:1},
        success: function(jsonString){
            console.ls.log("combined String", jsonString);
            if (jsonString.success !== true) {
                $("#labelsetpreview").html("<p class='alert'>"+strNoLabelSet+"</p>");
                $('#btnlsreplace').addClass('disabled');
                $('#btnlsinsert').addClass('disabled');
                $('#btnlsreplace').attr('disabled','disabled');
                $('#btnlsinsert').attr('disabled','disabled');
            } else {
                $('#labelsets').find('option').each(function(i,option){if($(option).attr('value')){ $(option).remove(); }});
                console.ls.group('SelectParsing');
                console.ls.log('allResults', jsonString.labelsets);
                $.each(jsonString.labelsets, function(i,item){
                    console.log('SelectItem', item);
                    var newOption = $('<option value="'+item.lid+'">'+item.label_name+'</option>');
                    console.ls.log('newOption', newOption);
                    $('#labelsets').append(newOption).trigger('change');
                });
                console.ls.groupEnd('SelectParsing');
            }
        }
    });

    $('#labelsets').on('change', function(){
        var value = $(this).val();
        if(parseFloat(value) == value)
            lspreview(value);
    });

}

// previews the labels in a label set after selecting it in the select box
// previews the labels in a label set after selecting it in the select box
function lspreview(lid)
{    
    var surveyid=$('input[name=sid]').val();
    return $.ajax({
        url: lsdetailurl,
        data: {sid:surveyid, lid:lid},
        cache: true,
        success: function(json){
            console.ls.log('lspreview', json);
            if(json.languages == []){
                console.ls.console.warn('NOTHING TO RENDER!', json);
                return;
            }

            var $liTemplate = $('<li role="presentation"></li>'),
                $aTemplate = $('<a data-toggle="tab"></a>'),
                $tabTodyTemplate = $('<div></div>'),
                $listTemplate = $('<div class="list-group selector_label-list"></div>');
                $listItemTemplate = $('<div class="list-group-item row selector_label-list-row"></div>');
                $tabindex=$('<ul class="nav nav-tabs" role="tablist"></ul>'),
                $tabbody=$('<div class="tab-content" style="max-height: 50vh; overflow:auto;"></div>'),
                count=0;


            console.ls.group('LanguageParsing');
            var i=0;
            $.each(json.languages, function(language, languageName){
                console.ls.log('Language', language, languageName);
                var $linkItem = $aTemplate.clone();
                var $bodyItem = $tabTodyTemplate.clone();
                var $itemList = $listTemplate.clone();

                var classLink = i===0 ? 'active' : '';
                var classBody = i===0 ? 'tab-pane tab-pane fade in active' : 'tab-page tab-pane fade';

                $linkItem.addClass(classLink).attr('href', "#language_"+language ).text(languageName);
                $liTemplate.clone().append($linkItem).appendTo($tabindex);
                

                $bodyItem.addClass(classBody).attr('id', 'language_'+language );
                $tabbody.append($bodyItem);

                console.ls.group('ParseLabelSet');                    
                
                var labelSet = json.results[language]
                console.ls.log('LabelSet', labelSet);

                var $itemList = $listTemplate.clone();

                console.ls.group('ParseLabels');                    
                $.each(labelSet.labels, function(i,label){
                    console.ls.log('Label', i, label);                
                    var $listItem = $listItemTemplate.clone();
                    $listItem.append('<div class="col-md-3 text-right" style="border-right: 4px solid #cdcdcd">'+label.code+'</div>');
                    $listItem.append('<div class="col-md-8">'+(label.title || '')+'</div>');
                    $listItem.append('<div class="col-md-1"></div>');
                    $listItem.attr('data-label', JSON.stringify(label));
                    $itemList.append($listItem);

                });
                
                console.ls.groupEnd('ParseLabels');
                $bodyItem.append('<h4>'+labelSet.label_name+'</h4>');
                $itemList.appendTo($bodyItem);
                
                console.ls.groupEnd('ParseLabelSet');
            });
            console.ls.groupEnd('LanguageParsing');
            $("#labelsetpreview").empty();
            $('<div></div>').append($tabindex).append($tabbody).appendTo($("#labelsetpreview"));
            $tabindex.find('li').first().find('a').trigger('click');
        }
    });
}


function transferlabels(type)
{
    var surveyid = $('input[name=sid]').val();
    var languages = langs.split(';');
    var labels = [];
    var scale_id= $('#current_scale_id').val();

    addInputPredefined(1).then(function(result){
        console.ls.log(result);
        $.each(result, function(lng, row){
            var $table = $('#answers_'+lng+'_'+scale_id);
            
            if(type == 'replace'){
                $table.find('tbody').find('tr').each(function(i,tableRow){
                    deleteSubquestionrow($(tableRow));
                    $(tableRow).remove();
                });
            }
            
            $("#labelsetpreview").find('#language_'+lng).find('.selector_label-list').find('.selector_label-list-row').each(function(i,item){
                try{
                    var label = $(item).data('label');
                    var $row = $(row)
                    var $tr = $row.eq(4);
                    var randId = 'new'+Math.floor(Math.random()*10000);

                    $tr.attr('data-common-id', $tr.attr('data-common-id').replace(/new[0-9]{3,6}/,randId));
                    $tr.attr('id', $tr.attr('id').replace(/new[0-9]{3-5}/,randId));

                    $row.find('input').each(function(j,inputField){
                        $(inputField).attr('name', $(inputField).attr('name').replace(/new[0-9]{3,6}/,randId));
                        $(inputField).attr('id', $(inputField).attr('id').replace(/new[0-9]{3,6}/,randId));
                    });
                    
                    $row.find('td.code-title').find('input[type=text]').length >0 ? $row.find('td.code-title').find('input[type=text]').val(label.code) : $row.find('td.code-title').text(label.code);
                    $row.find('td.relevance-equation').find('input[type=text]').length >0 ? $row.find('td.relevance-equation').find('input[type=text]').val(1) : '';
                    
                    $row.find('td.code-title').find('input[type=text]').val(label.code);
                    $row.find('td.subquestion-text').find('input[type=text]').val(label.title);
                    $table.find('tbody').append($row);
                    
                } catch(e) {console.ls.error(e);}

            });

            $('.tab-page:first .answertable tbody').sortable('refresh');
            updaterowproperties();
            $('#labelsetbrowserModal').modal('hide');
            $('#current_scale_id').remove();
        });
        var $lang_table = $('#answers_'+language+'_'+scale_id);
    });
}

/**
 * Quick-add subquestions/answers
 *
 * @global langs
 *
 * @param {int} scale_id
 * @param {string} addOrReplace - Either 'add' or 'replace'
 * @return {void}
 */
function quickaddlabels(scale_id, addOrReplace, table_id)
{
    console.ls.log('quickaddlabels');
    var sID=$('input[name=sid]').val(),
        gID=$('input[name=gid]').val(),
        qID=$('input[name=qid]').val(),
        codes = [],
        closestTable = $('#'+table_id);
        lsreplace = (addOrReplace === 'replace');

    if (lsreplace)
    {
        $('.answertable:eq('+scale_id+') tbody tr').each(function(){
            var aRowInfo=this.id.split('_');
            $('#deletedqids').val($('#deletedqids').val()+' '+aRowInfo[2]);
        });
    }

    if(closestTable.find('.code').length<0){
        closestTable.find('.code-title').each(function(){
            codes.push($(this).text());
        });
    } else {
        closestTable.find('.code').each(function(){
            codes.push($(this).val());
        });
    }

    languages=langs.split(';');
    var promises = [];
    var answers = [];
    var separatorchar;
    var lsrows=$('#quickaddarea').val().split("\n");
    var allrows = $('.answertable:eq('+scale_id+') tbody tr').length;

    if (lsrows[0].indexOf("\t")==-1)
    {
        separatorchar=';';
    }
    else
    {
        separatorchar="\t";
    }

    var numericSuffix = '',
        n = 1,
        numeric = true,
        currentCharacter,
        codeSigil = (codes[0] !== undefined ? codes[0].split("") : ("001").split(""));
    while(numeric == true && n <= codeSigil.length){
        currentCharacter = codeSigil.pop()                          // get the current character
        if ( !isNaN(Number(currentCharacter)) )                         // check if it's numerical
        {
            numericSuffix    = currentCharacter+""+numericSuffix;       // store it in a string
            n++;
        }
        else
        {
            $numeric = false;                                           // At first non numeric character found, the loop is stoped
        }
    }
    //Sometimes "0" is interpreted as NaN so test if it's just a missing Zero
    if(isNaN(Number(currentCharacter))){
        codeSigil.push(currentCharacter);
    }

    var tablerows = "";
    LS.ld.forEach(lsrows, function(value, k) {
        var thisrow=value.splitCSV(separatorchar);


        if (thisrow.length<=languages.length)
        {
            var qCode = (parseInt(k)+1);
            if (lsreplace===false){
                qCode+=(parseInt(allrows));}
            while(qCode.toString().length < numericSuffix.length){
                qCode = "0"+qCode;
            }
            thisrow.unshift( codeSigil.join('')+qCode);
        }
        else
        {
            thisrow[0]=thisrow[0].replace(/[^A-Za-z0-9]/g, "").substr(0,20);
        }
        var quid = "new"+(Math.floor(Math.random()*10000));

        LS.ld.forEach(languages, function(curLanguage, x) {
            
            if (typeof thisrow[parseInt(x)+1]=='undefined')
            {
                thisrow[parseInt(x)+1]=thisrow[1];
            }

            if(!answers[curLanguage]){
                answers[curLanguage] = [];
            }
            if (lsreplace)
            {
                $('#answers_'+curLanguage+'_'+scale_id+' tbody').empty();
            }
            answers[curLanguage].push(
                {text: thisrow[(parseInt(x)+1)], code: thisrow[0], quid: quid}
            );
        });

        //$('#answers_'+languages[x]+'_'+scale_id+' tbody').append(tablerows);

    });
    
    LS.ld.forEach(languages, function(curLanguage, x) {
        // Unbind any previous events
        $('#answers_'+curLanguage+'_'+scale_id+' .btnaddanswer').off('click.subquestions');
        $('#answers_'+curLanguage+'_'+scale_id+' .btndelanswer').off('click.subquestions');
        $('#answers_'+curLanguage+'_'+scale_id+' .answer').off('focus');
        $('#answers_'+curLanguage+'_'+scale_id+' .btnaddanswer').on('click.subquestions', addinput);
        $('#answers_'+curLanguage+'_'+scale_id+' .btndelanswer').on('click.subquestions', deleteinput);


        promises.push(
            addinputQuickEdit(closestTable, curLanguage, (x==0), scale_id, codes)
        );
    });

    $.when.apply($,promises).done(
            function(){
                /*$('#quickadd').dialog('close');*/
                $.each(arguments, function(i,item){
                    $.each(answers[item.lng], function(j,row){
                        var html = item.html;
                        var html_quid = html.replace(/({{quid_placeholder}})/g,row.quid);
                        var htmlRowObject = $(html_quid);
                        htmlRowObject.find('input.answer').val(row.text);
                        if(htmlRowObject.find('input.code').length > 0)
                        {
                            htmlRowObject.find('input.code').val(row.code);
                        }
                        else
                        {
                            htmlRowObject.find('td.code-title').text(row.code);
                        }
                        htmlRowObject.find('td.relevance-equation').find('input').val(1);

                        item.langtable.find('tbody').append(htmlRowObject);
                    })
                });
                $('#quickaddarea').val('');
                $('.tab-page:first .answertable tbody').sortable('refresh');
                updaterowproperties();
                $('#quickaddModal').modal('hide')
                bindClickIfNotExpanded();
            },
            function(){
                 console.ls.log(arguments);
                /*$('#quickadd').dialog('close');*/
                $('#quickaddarea').val('');
                $('.tab-page:first .answertable tbody').sortable('refresh');
                updaterowproperties();
                $('#quickaddModal').modal('hide')
                bindClickIfNotExpanded();
            }
        )

}

function getlabel()
{
    var answer_table = $(this).parent().children().eq(0);
    scale_id=window.LS.removechars($(this).attr('id'));

    //$('#saveaslabel').dialog('open');
    updaterowproperties();
}

function setlabel()
{
    switch($(this).attr('id'))
    {
        case 'newlabel':
        if(!flag[0]){
            $('#lasets').parent().remove();
            $(this).parent().after('<p class="label-name-wrapper"><label for="laname">'+sLabelSetName+':</label> ' +
            '<input type="text" name="laname" id="laname"></p>');
            flag[0] = true;
            flag[1] = false;
        }
        break;

        case 'replacelabel':
        if(!flag[1]){
            $('#laname').parent().remove();
            $(this).parent().after('<p class="label-name-wrapper"><select name="laname" id="lasets"><option value=""></option></select></p>');
            jQuery.getJSON(lanameurl, function(data) {
                $.each(data, function(key, val) {
                    $('#lasets').append('<option value="' + key + '">' + val + '</option>');
                });
            });
            $('#lasets option[value=""]').remove();
            flag[1] = true;
            flag[0] = false;
        }
        break;
    }
}

function savelabel()
{
    var lid = $('#lasets').val() ? $('#lasets').val() : 0;
    if(lid == 0)
        {
        var response = ajaxcheckdup();
        response.complete(function() {
            if(check)
                {
                ajaxreqsave();
            }
        });
    }
    else
        {
        aLanguages = langs.split(';');
        $.post(sCheckLabelURL, { languages: aLanguages, lid: lid, bCheckAssessments:1 }, function(data) {
           $('#strReplaceMessage').html(data);
           $('#dialog-confirm-replaceModal').modal();
           $('#btnlconfirmreplace').click(function(){
               ajaxreqsave();
           });
        });
    }
}

function ajaxcheckdup()
{
    check = true; //set check to true everytime on call
    return jQuery.getJSON(lanameurl, function(data) {
        $.each(data, function(key, val) {

            $("#saveaslabelModal").modal('hide');
            $("#dialog-confirm-replaceModal").modal('hide');

            if($('#laname').val() == val)
            {
                if($('#dialog-duplicate').is(":visible"))
                {
                    $('#dialog-duplicate').effect( "pulsate", {times:3}, 3000 );
                }
                else
                {
                    $('#dialog-duplicate').show();
                }
                check = false;
                return false;
            }
        });
    });
}

function ajaxreqsave() {
    var lid = $('#lasets').val() ? $('#lasets').val() : 0;
    // get code for the current scale
    var code = new Array();
    if($('.code').length > 0) { // Deactivated survey
        $('.code').each(function(index) {
            if($(this).attr('id').substr(-1) === scale_id)
                code.push($(this).val());
        });
    }
    else { // Activated survey
        $('.answertable input[name^="code_"]').each(function(index) {
            if($(this).attr('name').substr(-1) === scale_id)
                code.push($(this).attr('value'));
        });
    }

    answers = new Object();
    languages = langs.split(';');

    for(x in languages)
    {
        answers[languages[x]] = new Array();
        $('.answer').each(function(index){
            if($(this).attr('id').substr(-1) === scale_id && $(this).attr('id').indexOf(languages[x]) != -1)
                answers[languages[x]].push($(this).val());
        });
    }


    $.post(lasaveurl, { laname: $('#laname').val(), lid: lid, code: code, answers: answers}, function(data) {
        //$("#saveaslabel").dialog('close');
        $("#saveaslabelModal").modal('hide');
        $("#dialog-confirm-replaceModal").modal('hide');


        if(jQuery.parseJSON(data) == "ok")
            {
                if($('#dialog-result').is(":visible"))
                {
                    $('#dialog-result-content').empty().append(lasuccess);
                    $('#dialog-result').effect( "pulsate", {times:3}, 3000 );
                }
                else
                {
                    $('#dialog-result').removeClass('alert-warning').addClass('alert-success');
                    $('#dialog-result-content').empty().append(lasuccess);
                    $('#dialog-result').show();
                }
        }
        else
        {

            $('#dialog-result').removeClass('alert-success').addClass('alert-warning');
            $('#dialog-result-content').empty().append(lafail);
            $('#dialog-result').show();
        }
    });
}
