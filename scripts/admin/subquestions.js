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

var labelcache=[];

/* Event added on document for all button (new one added in js too)*/
$(document).on("click","#editsubquestionsform :submit", function() {
    //Validate duplicate before try to submit: surely some other javascript elsewhere
    return code_duplicates_check();
});

$(document).ready(function(){

    $('.tab-page:first .answertable tbody').sortable({
        containment:'parent',
        start:startmove,
        update:aftermove,
        distance:3});
    $('.btnaddanswer').click(addinput);
    $('.btndelanswer').click(deleteinput);
    $('.btnlsbrowser').click(lsbrowser);
    $('#btnlsreplace').click(transferlabels);
    $('#btnlsinsert').click(transferlabels);


    $('#quickaddModal').on('show.bs.modal', function(e) {
        var scale_id = $(e.relatedTarget).data('scale-id');

        $('#btnqainsert').unbind('click').on('click', function () {
            quickaddlabels(scale_id, 'add');
        });

        $('#btnqareplace').unbind('click').on('click', function () {
            quickaddlabels(scale_id, 'replace');
        });
    });

    $('#labelsets').click(lspreview);
    $('#languagefilter').click(lsbrowser);
    $('.bthsaveaslabel').click(getlabel);
    $('input[name=savelabeloption]:radio').click(setlabel);
    flag = [false, false];
    $('#btnsave').click(savelabel);

    updaterowproperties();

    bindExpandRelevanceEquation();

});

/**
 * Bind relevance equation to expand on click (only once)
 *
 * @return void
 */
function bindExpandRelevanceEquation()
{
    $('.relevance').unbind('click').on('click', function() {
        $('#rel-eq-th').toggleClass('col-md-1 col-md-4', 'fast');
        $('.relevance').data('toggle', '').tooltip('destroy');
        $('.relevance').unbind('click');
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
        $('[data-toggle="tooltip"]').tooltip()
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

function deleteinput()
{
    // 1.) Check if there is at least one answe
    countanswers=$(this).closest("tbody").children("tr").length;//Maybe use class is better
    if (countanswers>1)
    {
        // 2.) Remove the table row
        var x;
        classes=$(this).closest('tr').attr('class').split(' ');
        for (x in classes)
            {
            if (classes[x].substr(0,3)=='row'){
                position=classes[x].substr(4);
            }
        }
        info=$(this).closest('table').attr('id').split("_");
        language=info[1];
        scale_id=info[2];
        languages=langs.split(';');

        var x;
        for (x in languages)
            {
            tablerow=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' .row_'+position);
            if (x==0) {
                tablerow.fadeTo(400, 0, function(){
                    $(this).remove();
                    updaterowproperties();
                });
            }
            else {
                tablerow.remove();
            }
        }
        if ($(tablerow).is('[id]'))
        {
            rowinfo=$(tablerow).attr('id').split('_');
            $('#deletedqids').val($('#deletedqids').val()+' '+rowinfo[2]);
        }
    }
    else
        {
        $.blockUI({message:"<p><br/>"+strCantDeleteLastAnswer+"</p>"});
        setTimeout(jQuery.unblockUI,1000);
    }
    updaterowproperties();
}


function addinput()
{
    var sID=$('input[name=sid]').val();
    var gID=$('input[name=gid]').val();
    var qID=$('input[name=qid]').val();
    var x;

    scale_id=removechars($(this).closest('table').attr('id'));

    languages=langs.split(';');
    sNextCode=getNextCode($(this).data('code'));

    sNextCode=getNextCode($(this).parent().parent().find('.code').val());

    classes=$(this).parent().parent().attr('class').split(' ');
    for (x in classes)
    {
        if (classes[x].substr(0,3)=='row')
        {
            position=classes[x].substr(4);
        }
    }
    //newposition = Number($(this).closest('tr').parent().children().index($(this).closest('tr')))+1;
    newposition = Number($('#rowcontainer').find('tr').index($(this).closest('tr')))+1;
    info=$(this).closest('table').attr('id').split("_");
    language=info[1];
    scale_id=info[2];


    languages=langs.split(';');

    sNextCode=getNextCode($(this).parent().parent().find('.code').val());

    for (x in languages)
    {
        var randomid='new'+Math.floor(Math.random()*111111);
        relbutton='';

        tablerow=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' .row_'+position);

        if (x==0)
        {
            /*
            if (scale_id==0)
            {
                relbutton  = '<td>';
                relbutton += '  <span class="icon-conditions text-success btntogglerelevance"></span>';
                relbutton += '  <input style="display: none" type="text" size="20" id="relevance_'+randomid+'_'+scale_id+'" name="relevance_'+randomid+'_'+scale_id+'" class="relevance"  value="1"></input>';
                relbutton += '</td>';
            }
            */

            // Line insertion
            var idAndScale = '' + randomid + '_' + scale_id;

            inserthtml= '<tr id="row_'+newposition+'" class="row_'+newposition+'" style="">';
            inserthtml+='   <td style="vertical-align: middle;">';
            inserthtml+='       <span class="glyphicon glyphicon-move"></span>';
            inserthtml+='   </td>';
            inserthtml+='   <td style="vertical-align: middle;">';
            inserthtml+='       <input class="code form-control input-lg" id="code_'+randomid+'_'+scale_id+'" name="code_'+randomid+'_'+scale_id+'" required="required" pattern="^[a-zA-Z0-9]*$" class="code" type="text" maxlength="20" size="20" value="'+htmlspecialchars(sNextCode)+'" />';
            inserthtml+='   </td>';

            inserthtml+='   <td style="vertical-align: middle;">';
            inserthtml+='       <div class="">';
            inserthtml+='           <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg"  placeholder="'+htmlspecialchars(newansweroption_text)+'" value=""></input>';
            inserthtml+='       </div>';
            inserthtml+='  </td>';

            inserthtml+='  <td>';
            inserthtml+='      <input id="relevance_' + idAndScale + '" name="relevance_' + idAndScale + '" class="relevance form-control input-lg" type="text" value="1" ' + getRelevanceToolTip() + '/>';
            inserthtml+='  </td>';

            inserthtml+='  <td style="vertical-align: middle;">';
            inserthtml+='           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">';
            inserthtml+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="glyphicon glyphicon-pencil btneditanswerena"></span>';
            inserthtml+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="glyphicon glyphicon-pencil btneditanswerdis" alt="Give focus to the HTML editor popup window" style="display: none;"></span>';
            inserthtml+='           </a>';
            inserthtml+='       <span class="icon-add text-success btnaddanswer" data-code="'+htmlspecialchars(sNextCode)+'"></span>';
            inserthtml+='       <span class="glyphicon glyphicon-trash text-danger btndelanswer"  ></span>';
            inserthtml+='  </td>' + relbutton + '</tr>';
        }
        else
        {
            if (scale_id==0)
            {
                relbutton ='<td>'
                relbutton+='           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">';
                relbutton+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="btneditanswerena glyphicon glyphicon-pencil text-success"></span>';
                relbutton+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>';
                relbutton+='           </a>';
                //relbutton+='    <span class="icon-conditions text-success btntogglerelevance"></span>';
                relbutton+='    <span style="display: none" class="relevance">1</span>';
                relbutton+='</td>';
            }
            else
            {
                relbutton ='<td>'
                relbutton+='           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">';
                relbutton+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="btneditanswerena glyphicon glyphicon-pencil text-success"></span>';
                relbutton+='               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>';
                relbutton+='           </a>';
                relbutton+='</td>';
            }
            inserthtml ='<tr id="row_'+newposition+'" class="row_'+newposition+'" style="display:none;">';
            inserthtml+='   <td>&nbsp;</td>';
            inserthtml+='   <td>'+htmlspecialchars(sNextCode)+'</td>';

            inserthtml+='   <td style="vertical-align: middle;">';
            inserthtml+='       <div class="">';
            inserthtml+='           <input type="text" size="0" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" placeholder="'+htmlspecialchars(newansweroption_text)+'" value="" />';
            inserthtml+='       </div>';
            inserthtml+='   </td>' + relbutton + '</tr>';
        }
        tablerow.after(inserthtml);

        tablerow.next().find('.btnaddanswer').click(addinput);

        tablerow.next().find('.btndelanswer').click(deleteinput);
        tablerow.next().find('.answer').focus(function()
        {
            if ($(this).val()==newansweroption_text)
            {
                $(this).val('');
            }
        });
        tablerow.next().find('.code').blur(updatecodes);
    }
    $('.row_'+newposition).fadeIn('slow');
    $('.row_'+newposition).show(); //Workaround : IE does not show with fadeIn only

    $('.tab-page:first .answertable tbody').sortable('refresh');

    bindClickIfNotExpanded();

}

function startmove(event,ui)
{
    oldindex = Number($(ui.item[0]).parent().children().index(ui.item[0]))+1;
}


function aftermove(event,ui)
{
    // But first we have change the sortorder in translations, too
    var newindex = Number($(ui.item[0]).parent().children().index(ui.item[0]))+1;

    info=$(ui.item[0]).closest('table').attr('id').split("_");
    languages=langs.split(';');
    var x;
    for (x in languages)
        {
        if (x>0) {
            tablerow=$('#tabpage_'+languages[x]+' tbody tr:nth-child('+newindex+')');
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
    }
    updaterowproperties();
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
    var sID=$('input[name=sid]').val();
    var gID=$('input[name=gid]').val();
    var qID=$('input[name=qid]').val();

    $('.answertable tbody').each(function(){
        info=$(this).closest('table').attr('id').split("_");
        language=info[1];
        scale_id=info[2];

        var $firstrow = $(this).children('tr').first();
        var datas=$firstrow.attr('id').split('_');
        var rownumber=datas[2];


        $(this).children('tr').each(function()
        {
            $(this).addClass('row_'+rownumber);

            updateIfEmpty($(this).find('.oldcode'), 'name', 'oldcode_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.code'), 'id', 'code_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.code'), 'name', 'code_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.answer'), 'id', 'answer_'+language+'_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.answer'), 'name', 'answer_'+language+'_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.assessment'), 'id', 'assessment_'+rownumber+'_'+scale_id);
            updateIfEmpty($(this).find('.assessment'), 'name', 'assessment_'+rownumber+'_'+scale_id);

            // Newly inserted row editor button
            $(this).find('.editorLink').attr('href','javascript:start_popup_editor(\'answer_'+language+'_'+rownumber+'_'+scale_id+'\',\'[Answer:]('+language+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')');
            $(this).find('.editorLink').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_ctrl');
            $(this).find('.btneditanswerena').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerena').attr('name','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerdis').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrldis');
            $(this).find('.btneditanswerdis').attr('name','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrldis');
            rownumber++;
        }
        );
    });
}

function updatecodes()
{

}

function getNextCode(sSourceCode)
{

        i=1;
        found=true;
        mNumberFound=-1;
        while (i<=sSourceCode.length && found)
        {
            found=is_numeric(sSourceCode.substr(-i));
            if (found)
                {
                mNumberFound=sSourceCode.substr(-i);
                i++;
            }
        }
        if (mNumberFound==-1)
        {
            sBaseCode=sSourceCode;
            mNumberFound=0
        }
        else
        {
            sBaseCode=sSourceCode.substr(0,sSourceCode.length-mNumberFound.length);
        }
        var iNumberFound=+mNumberFound;
        do
        {
            iNumberFound=iNumberFound+1;
            sNewNumber=iNumberFound+'';
            sResult=sBaseCode+sNewNumber;
            if (sResult.length>5)
            {
              sResult=sResult.substr(sResult.length - 5);
            }
        }
        while (areCodesUnique(sResult)==false);
        return(sResult);
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
            codearray=codearray.filter( onlyUnique );
            codearray.push(sNewValue);
        }
        if (arrHasDupes(codearray))
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
    $('#tabpage_'+languages[0]+' .answertable tbody').each(function(){
        var codearray=[];
        $(this).find('tr .code').each(function(){
            codearray.push($(this).val().toLowerCase());
        });
        var theDuplicate = arrHasDupesWhich(codearray);
        if (theDuplicate !== false)
        {

            $('.code').each( function() {
                if ($(this).val() == theDuplicate) {
                    var $theDuplicateElement = $(this);

                    $('#error-modal .modal-body-text').html(duplicatesubquestioncode);
                    $('#error-modal').modal();

                    // Tooltip doesn't scroll
                    /*
                    $theDuplicateElement.data('toggle', 'tooltip');
                    $theDuplicateElement.data('title', duplicatesubquestioncode);
                    $theDuplicateElement.tooltip({
                        placement: 'left'
                    })
                    $theDuplicateElement.tooltip('show');
                    */

                    // Flash the elements that are duplicates
                    //$theDuplicateElement.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            
                }
            });
            cansubmit= false;
        }
    });
    return cansubmit;
}

function lsbrowser()
{
    scale_id=removechars($(this).attr('id'));
    surveyid=$('input[name=sid]').val();
    $.getJSON(lspickurl,{sid:surveyid, match:1},function(json){
        var x=0;
        $("#labelsets").removeOption(/.*/);
        for (x in json)
        {
            $('#labelsets').addOption(json[x][0],json[x][1]);
            if (x==0){
                remind=json[x][0];
            }
        }
        if ($('#labelsets > option').size()>0)
        {
            $('#labelsets').selectOptions(remind);
            lspreview();
            $('#btnlsreplace').removeClass('ui-state-disabled');
            $('#btnlsinsert').removeClass('ui-state-disabled');
            $('#btnlsreplace').removeProp('disabled');
            $('#btnlsinsert').removeProp('disabled');
        }
        else
        {
            $("#labelsetpreview").html("<p class='alert alert-warning'>"+strNoLabelSet+"</p>");
            $('#btnlsreplace').addClass('disabled');
            $('#btnlsinsert').addClass('disabled');
            $('#btnlsreplace').prop( "disabled", true );
            $('#btnlsinsert').prop( "disabled", true );
        }
    });

}

// previews the labels in a label set after selecting it in the select box
function lspreview()
{
    if ($('#labelsets > option').size()==0)
    {
        return;
    }

    var lsid=$('#labelsets').val();
    surveyid=$('input[name=sid]').val();
    // check if this label set is already cached
    if (!isset(labelcache[lsid]))
    {
        $.ajax({
            url: lsdetailurl,
            dataType: 'json',
            data: {lid:lsid, sid:surveyid},
            cache: true,
            success: function(json){
                //$("#labelsetpreview").tabs('destroy');
                $("#labelsetpreview").empty();
                var tabindex='<ul class="nav nav-tabs">';
                var tabbody='<div class="tab-content">';
                var count=0;
                for ( x in json)
                {
                    language=json[x];
                    for (y in language)
                    {
                        if(count==0)
                        {
                            active="active";
                            bodyactive="in active";
                            count++;
                        }
                        else
                        {
                            active = bodyactive = "";
                        }

                        tabindex=tabindex+
                            '<li role="presentation" class="'+active+'">'+
                            '   <a data-toggle="tab" href="#language_'+y+'">'+
                                    language[y][1]+
                            '   </a>'+
                            '</li>';

                        tabbody=tabbody+
                                '<div id="language_'+y+'" class="tab-page tab-pane fade '+bodyactive+'">'+
                                '   <table class="limetable">';
                        lsrows=language[y][0];
                        tablerows='';
                        var highlight=true;
                        for (z in lsrows)
                        {
                            highlight=!highlight;
                            tabbody=tabbody+'<tbody><tr';
                            if (highlight==true) {
                                tabbody=tabbody+" class='highlight' ";
                            }
                            if (lsrows[z].title==null)
                            {
                                lsrows[z].title='';
                            }
                            tabbody=tabbody+'><td>'+lsrows[z].code+'</td><td>'+htmlspecialchars(lsrows[z].title)+'</td></tr><tbody>';
                        }
                        tabbody=tabbody+'<thead><tr><th>'+strcode+'</th><th>'+strlabel+'</th></tr></thead></table></div>';
                    }
                }
                tabindex=tabindex+'</ul>';
                tabbody=tabbody+'</div>';
                $("#labelsetpreview").append(tabindex+tabbody);
                labelcache[lsid]=tabindex+tabbody;
        }}
        );
    }
    else
    {
        $("#labelsetpreview").empty();
        $("#labelsetpreview").append(labelcache[lsid]);
    }


}

/**
* This is a debug function
* similar to var_dump in PHP
*/
function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') {
        //Array/Hashes/Objects
        for(var item in arr) {
            var value = arr[item];

            if(typeof(value) == 'object') {
                 //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}

function transferlabels()
{
    var sID=$('input[name=sid]').val();
    var gID=$('input[name=gid]').val();
    var qID=$('input[name=qid]').val();

    surveyid=$('input[name=sid]').val();

    if ($(this).attr('id')=='btnlsreplace')
    {
        var lsreplace=true;

    }
    else
    {
        var lsreplace=false;
    }

    if (lsreplace)
    {
        $('.answertable:eq('+scale_id+') tbody tr').each(function()
        {
            aRowInfo=this.id.split('_');
            $('#deletedqids').val($('#deletedqids').val()+' '+aRowInfo[2]);
        });
    }

    var lsid=$('#labelsets').val();
    $.ajax({
        url: lsdetailurl,
        dataType: 'json',
        data: {lid:lsid, sid:surveyid},
        cache: true,
        success: function(json){
            languages=langs.split(';');
            var x;
            var defaultdata_labels = null;

            for (x in languages)
            {
                lang_x_found_in_label=false;
                var tablerows='';
                var y;
                for (y in json)
                {
                    language=json[y];
                    var lsrows = new Array();
                    //defaultdata=language[languages[0]][0];
                    for (z in language)
                    {
                        if (z==languages[0])
                        {
                            defaultdata_labels=language[languages[0]];
                        }
                        if (z==languages[x])
                        {
                            lang_x_found_in_label = true;
                            lsrows=language[z][0];
                        }

                        var k;
                        for (k in lsrows)
                        {
                            var randomid='new'+Math.floor(Math.random()*111111);
                            if (x==0)
                            {
                                tablerows=tablerows+
                                '<tr id="row_'+k+'_'+scale_id+'" class="row_'+k+'_'+scale_id+'" >'+
                                '   <td>'+
                                '       <span class="glyphicon glyphicon-move text-success"></span>'+
                                '   </td>'+


                                '   <td style="vertical-align: middle;">'+
                                '       <input class="code form-control input-lg" id="code_'+randomid+'_'+scale_id+'" name="code_'+randomid+'_'+scale_id+'" pattern="^[a-zA-Z0-9]*$" required="required" type="text" maxlength="20" size="20" value="'+htmlspecialchars(lsrows[k].code)+'" />'+
                                '   </td>'+

                                '   <td>'+
                                '       <div class="">'+
                                '           <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                                '       </div>'+
                                '   </td>'+

                                '   <td>'+
                                '           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">'+
                                '               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="btneditanswerena glyphicon glyphicon-pencil text-success"></span>'+
                                '               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                                '           </a>'+
                                '       <span class="btnaddanswer icon-add text-success"></span>'+
                                '       <span class="btndelanswer glyphicon glyphicon-trash text-warning"></span>'+
                                '   </td>'+
                                '   <td>'+
                                '       <span class="btntogglerelevance icon-expressionmanagercheck text-success"></span> <input style="display: none" type="text" size="20" id="relevance_'+randomid+'_'+scale_id+'" name="relevance_'+randomid+'_'+scale_id+'" class="relevance"  value="1"></input>'+
                                '   </td>'+
                                '</tr>';
                            }
                            else
                            {
                                tablerows=tablerows+
                                '<tr id="row_'+k+'_'+scale_id+'" class="row_'+k+'_'+scale_id+'">'+
                                '   <td>&nbsp;</td>'+
                                '   <td>'+htmlspecialchars(lsrows[k].code)+'</td>'+

                                '   <td style="vertical-align: middle;">'+
                                '       <div class="">'+
                                '           <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                                '       </div>'+
                                '   </td>'+

                                '   <td>'+
                                '           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">'+
                                '               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="btneditanswerena glyphicon glyphicon-pencil text-success"></span><span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="btneditanswerdis  glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;" ></span>'+
                                '           </a>'+

                                //'       <span class="btntogglerelevance icon-expressionmanagercheck text-success"></span>'+
                                '       <span style="display: none" class="relevance">1</span>'+
                                '   </td>'+
                                '</tr>';
                            }
                        }
                    }
                }
                if (lang_x_found_in_label === false)
                {
                    lsrows=defaultdata_labels[0];
                    k=0;
                    for (k in lsrows)
                    {
                        tablerows=tablerows+
                        '<tr id="row_'+k+'_'+scale_id+'" class="row_'+k+'_'+scale_id+'" >'+
                        '   <td>&nbsp;</td>'+
                        '   <td>'+htmlspecialchars(lsrows[k].code)+'</td>'+

                        '   <td style="vertical-align: middle;">'+
                        '       <div class="">'+
                        '           <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                        '       </div>'+
                        '   </td>'+

                        '   <td>'+
                        '           <a id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_ctrl" href="javascript:start_popup_editor(\'answer_'+languages[x]+'_'+randomid+'_'+scale_id+'\',\'[Subquestion:]('+languages[x]+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')" class="editorLink">'+
                        '               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrlena" class="btneditanswerena  glyphicon glyphicon-pencil text-success" ><span>'+
                        '               <span id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'_popupctrldis" class="btneditanswerdis  glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                        '           </a>'+
                        //'       <span class="btntogglerelevance icon-expressionmanagercheck text-success"></span>'+
                        '       <span style="display: none" class="relevance">1</span>'+
                        '   </td>'+
                        '</tr>';
                    }
                }

                $('#labelsetbrowserModal').modal('hide');
                if (lsreplace)
                {
                    $('#answers_'+languages[x]+'_'+scale_id+' tbody').empty();
                }

                $('#answers_'+languages[x]+'_'+scale_id+' tbody').append(tablerows);
                // Unbind any previous events
                $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').unbind('click');
                $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').unbind('click');
                $('#answers_'+languages[x]+'_'+scale_id+' .answer').unbind('focus');
                $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
                $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);

            }
            /*$('#labelsetbrowser').dialog('close');*/
            $('.tab-page:first .answertable tbody').sortable('refresh');
            updaterowproperties();

    }}
    );


}

/**
 * Quick-add subquestions/answers
 *
 * @param {int} scale_id
 * @param {string} addOrReplace - Either 'add' or 'replace'
 * @return {void}
 */
function quickaddlabels(scale_id, addOrReplace)
{
    var sID=$('input[name=sid]').val();
    var gID=$('input[name=gid]').val();
    var qID=$('input[name=qid]').val();

    var lsreplace = (addOrReplace === 'replace');

    if (lsreplace)
    {
        $('.answertable:eq('+scale_id+') tbody tr').each(function(){
            aRowInfo=this.id.split('_');
            $('#deletedqids').val($('#deletedqids').val()+' '+aRowInfo[2]);
        });
    }

    languages=langs.split(';');

    for (x in languages)
    {
        lsrows=$('#quickaddarea').val().split("\n");

        if (lsrows[0].indexOf("\t")==-1)
        {
            separatorchar=';';
        }
        else
        {
            separatorchar="\t";
        }
        tablerows='';
        for (k in lsrows)
        {
            thisrow=lsrows[k].splitCSV(separatorchar);
            if (thisrow.length<=languages.length)
            {
                thisrow.unshift(parseInt(k)+1);
            }
            else
            {
                thisrow[0]=thisrow[0].replace(/[^A-Za-z0-9]/g, "").substr(0,20);
            }
            var randomid='new'+Math.floor(Math.random()*111111);

            if (typeof thisrow[parseInt(x)+1]=='undefined')
            {
                thisrow[parseInt(x)+1]=thisrow[1];
            }
            if (x==0)
            {
                var idAndScale = '' + randomid + '_' + scale_id;

                tablerows=tablerows+
                '<tr id="row_'+k+'" class="row_'+k+'">'+
                '   <td>'+
                '       <span class="glyphicon glyphicon-move"></span>'+
                '   </td>'+

                '   <td style="vertical-align: middle;">'+
                '       <input'+
                '           class="code third form-control input-lg" required="required" pattern="^[a-zA-Z0-9]*$" '+
                '           type="text" maxlength="20" size="20" '+
                '           pattern="^([a-zA-Z0-9]*|12)$"'+
                '           value="'+thisrow[0]+'" pattern="^[a-zA-Z0-9]*$"  '+
                '           id="code_'+randomid+'_'+scale_id+'" name="code_'+randomid+'_'+scale_id+'" '+
                '           value="'+htmlspecialchars(thisrow[0],'ENT_QUOTES')+'" '+
                '       />'+
                '   </td>'+

                '   <td style="vertical-align: middle;">'+
                '       <div class="">'+
                '           <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" value="'+htmlspecialchars(thisrow[parseInt(x)+1],'ENT_QUOTES')+'"></input>'+
                '       </div>'+
                '   </td>'+

                '   <td>' +
                '       <input id="relevance_' + idAndScale + '" name="relevance_' + idAndScale + '" class="relevance form-control input-lg" type="text" value="1" ' + getRelevanceToolTip() + '/>' +
                '   </td>' +

                '   <td>'+
                '           <a class="editorLink">'+
                '               <span class="btneditanswerena glyphicon glyphicon-pencil text-success"></span>'+
                '               <span class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                '           </a>'+

                '       <span class="btnaddanswer icon-add text-success"></span>'+
                '       <span class="btndelanswer glyphicon glyphicon-trash text-danger"></span>'+
                '   </td>'+
                '</tr>';

            }
            else
                {

                    tablerows=tablerows+
                    '<tr id="row_'+k+'" class="row_'+k+'" >'+
                    '   <td>&nbsp;</td>'+
                    '   <td>&nbsp;</td>'+

                    '   <td style="vertical-align: middle;">'+
                    '       <div class="">'+
                    '          <input type="text" size="20" id="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" name="answer_'+languages[x]+'_'+randomid+'_'+scale_id+'" class="answer form-control input-lg" value="'+htmlspecialchars(thisrow[parseInt(x)+1],'ENT_QUOTES')+'"></input>'+
                    '       </div>'+
                    '   </td>'+

                    '   <td>'+
                    '           <a class="editorLink">'+
                    '               <span class="btneditanswerena glyphicon glyphicon-pencil text-success"></span>'+
                    '               <span class="btneditanswerdis  glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                    '           </a>'+

                    '       <span class="btnaddanswer  icon-add text-success"></span>'+
                    '       <span class="btndelanswer glyphicon glyphicon-trash text-warning"></span>'+
                    '   </td>'+
                    '</tr>';
            }
        }
        if (lsreplace)
        {
            $('#answers_'+languages[x]+'_'+scale_id+' tbody').empty();
        }

        $('#answers_'+languages[x]+'_'+scale_id+' tbody').append(tablerows);
        // Unbind any previous events
        $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').unbind('click');
        $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').unbind('click');
        $('#answers_'+languages[x]+'_'+scale_id+' .answer').unbind('focus');
        $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
        $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);
    }
    /*$('#quickadd').dialog('close');*/
    $('#quickaddarea').val('');
    $('.tab-page:first .answertable tbody').sortable('refresh');
    updaterowproperties();
    $('#quickaddModal').modal('hide')

    bindClickIfNotExpanded();
}

function getlabel()
{
    var answer_table = $(this).parent().children().eq(0);
    scale_id=removechars($(this).attr('id'));

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
        $('.answer').each(function(index) {
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
