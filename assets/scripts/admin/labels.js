/*
* LimeSurvey
* Copyright (C) 2007-2012 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Namespace
var LS = LS || {  onDocumentReady: {} };

/**
 * delete button
 */
$(document).on('click','[data-action="deletelabelset"]',function(event){
    event.preventDefault();
    if(confirm($(this).data('confirm'))){
        sendPost($(this).data('url'),'',['action','lid'],[$(this).data('action'),$('[name="lid"]').val()]);
    }
});

$(document).on('ready  pjax:scriptcomplete', function(){
    $('#btnDumpLabelSets').click(function(){
        if ($('#labelsets > option:selected').size()==0) {
            alert(strSelectLabelset);
            return false;
        } else {
            return true;
        }
    });

    if ($(".answertable tbody").children().length == 0) {
        add_label(undefined);
    }

    $('.btnaddanswer').on('click', add_label);
    $('.btndelanswer').on('click', del_label);
    $('.codeval').on('keyup change', sync_label);
    $('.assessmentval').on('keyup change', sync_label);

    $('#neweditlblset0 .answertable tbody').sortable({
        update:sort_complete,
        distance:2
    });

    $('#btnqareplace').click(quickaddfunction);
    $('#btnqainsert').click(quickaddfunction);

    $("#mainform").submit(function(event,ui) {

        if (code_duplicates_check()==false) return false;
        var dataToSend = {};
        dataToSend['langs'] = [];
        dataToSend['codelist'] = [];

        $(".tab-pane").each(function(divindex,divelement){
            var div_language = $(".lslanguage",divelement).val();

            if (typeof(div_language)!="undefined")
                dataToSend['langs'].push(div_language);
        });

        $(".tab-content div:first .labelDatas").each(function(trindex,trelement){

            var tr_code = $(trelement).attr('id');
            tr_code=tr_code.split('_');// first is row, second langage and last the row number
            tr_code=tr_code[2];
            dataToSend['codelist'].push(tr_code);
            dataToSend[tr_code] = {
                code: $("#code_"+tr_code).val(),
                assessmentvalue: $("#assessmentvalue_"+tr_code).val()
            };

            $(dataToSend['langs']).each(function(i,e){
                dataToSend[tr_code]['text_'+e] = $("[name=title_"+e+"_"+tr_code+"]").val();
            });
        });
        $("input[name=dataToSend]").remove();

        $("#mainform").append("<input type='hidden' id='dataToSend' name='dataToSend' value='' />");
        $('#dataToSend').val($.toJSON(dataToSend));
    });

    fix_highlighting();
});

/**
 * @todo
 */
function quickaddfunction() {
    var lsreplace = false;
    if ($(this).attr('id') == 'btnqareplace') {
        lsreplace = true;
    }

    if (lsreplace) {
        $(".answertable tbody>tr").remove();
    }

    // NB: lsrows is all the lines from the quick add modal.
    var lsrows = $('#quickaddarea').val().split("\n");

    var separatorchar = "\t";
    if (lsrows[0].indexOf("\t") == -1) {
        separatorchar = ';';
    }

    console.log('lsrows', lsrows);
    $(lsrows).each(function(index, row) {
        var code = undefined;

        var params = row.split(separatorchar);
        var k = 0;
        if (params.length > $(".lslanguage").length){
            code = params[0].replace(/[^a-zA-Z 0-9]+/g,'').substr(0,5);
            k++;
        }

        var event = {};
        event.target = $(".btnaddanswer:last");
        var retcode = add_label(event);

        if (lsreplace) {
            if (index!=0|| (!lsreplace && $("div[id^='newedit']:not(:last) tbody>tr").length > 0)) {
                event = {};
                event.target = $(".btnaddanswer:last");
            } else {
                retcode = add_label();
            }
        }

        // seems always undefined
        if (typeof(code) != "undefined") {
            $("#code_"+retcode).val(code);
        }

        // TODO: What does this do?
        $(".lslanguage").each(function(i) {
            //console.log("input[name=title_"+$(this).val()+"_"+retcode+"]");
            //console.log('i', i);
            $("input[name=title_" + $(this).val() + "_" + retcode + "]").val(params[k]);
            if (typeof(code) != "undefined" && i > 0) {
                $("#row_" + $(this).val() + "_" + retcode + " td:first").text(code);
            }
            k++;
        });
    });
    $("#quickaddarea").val('');
    $('#quickadd').modal('hide');
}

/**
 * @todo
 */
function check_onsubmit(element){
    var onsub = $(element).attr('onsubmit');
    var code = onsub.substr(6,onsub.length);

    return eval(code);
}

/**
 * @todo
 */
function sort_complete(event, ui){
    var newposition = ($(ui.item).parent().children().index($(ui.item)));
    var item = ui.item;

    var position = ui.position;
    var originalposition = ui.originalPosition;

    if (originalposition.top > position.top) {
        newposition = newposition - 1;
    }

    $(".not_first [name="+$(item).attr('name')+"]").each(function(index,element){
        var backup = "<tr id='row"+$(item).attr('name')+"'>"+$(element).html()+"</tr>";

        if (newposition >= 0)
            $($(element).parent().children()[newposition]).after(backup);

        else
            $(element).parent().prepend(backup);

        $(element).remove();
    });

    fix_highlighting();
}

/**
 * @todo
 */
function sync_label(event)
{
    event.preventDefault();
    var sRowID = $(event.target).parent().parent().attr('id');
    aRowInfo=sRowID.split('_');// first is row, second langage and last the row number
    $(".ui-tabs-panel").each(function(divindex,divelement){
        var div_language = $(".lslanguage",divelement).val();
        if (typeof(div_language)!="undefined" && div_language!=aRowInfo[1]){
            $("#row_"+div_language+"_"+aRowInfo[2]+" td:first-child").text($("#code_"+aRowInfo[2]).val()); // Sync code
            $("#row_"+div_language+"_"+aRowInfo[2]+" td:nth-child(2)").text($("#assessmentvalue_"+aRowInfo[2]).val()); // Sync assessment value
        }
    });
}

/**
 * @param {object} event
 * @return {string} Next label code.
 */
function get_next_code(event) {
    var next_code;
    if (event) {
        if ($(event.target).closest('tr').find('.codeval').size()>0) {
            next_code = getNextCode($(event.target).closest('tr').find('.codeval').val());
        } else {
            next_code = 'L001';
        }

        while ($('.answertable').find('input[value="'+next_code+'"]').length > 0
                && next_code != $(event.target).closest('tr').find('.codeval').val()) {
            next_code = getNextCode(next_code);
        }
    } else {
        next_code='L001';
    }
    return next_code;
}

/**
 * @param {object} event
 * @return {string} Random id.
 */
function add_label(event) {
    var next_code = get_next_code(event);
    var html = createNewLabelTR(true,true);
    var row_id;

    if (typeof(event) == "undefined") {
        row_id = -1;
    } else {
        row_id = $(event.target)
            .parent()
            .parent()
            .parent()
            .children()
            .index(
                $(event.target)
                    .parent()
                    .parent()
            );
    }

    var randomid = 'new' + Math.floor(Math.random()*1111111);

    html = str_replace("###assessmentval###",'0',html);
    html = str_replace("###codeval###",next_code,html);
    html = str_replace("###next###",randomid,html);
    html = str_replace("###lang###",$("#lslanguagemain").val(),html);

    if (typeof(event) == "undefined") {
        $(".first tbody").append(html);
    } else {
        $(event.target).parent().parent().after(html);
    }
    html = createNewLabelTR(true,false);

    html = str_replace("###assessmentval###",'0',html);
    html = str_replace("###codeval###",next_code,html);
    html = str_replace("###next###",randomid,html);

    // NB: Class "not_first" corresponds to all the language tabs that are not first...
    $(".not_first").each(function(index, element){
        var temp_html = str_replace("###lang###",$(".lslanguage",element).val(),html);
        if (row_id >= 0) {
            $($("tbody",element).children()[row_id]).after(temp_html);
        } else {
            $(".answertable tbody",$(element)).append(temp_html);
        }
    });

    $("tr[id$='_"+randomid+"']").hide().fadeIn(500);

    fix_highlighting();

    return randomid;
}

/**
 * @param {object} event
 */
function del_label(event) {
    event.preventDefault();
    var $sRowID = $(event.target).parent().parent().attr('id');

    $aRowInfo=$sRowID.split('_');// first is row, second langage and last the row number
    $(".tab-pane").each(function(divindex,divelement){
        var div_language = $(".lslanguage",divelement).val();

        if (typeof(div_language)!="undefined")
            $("#row_"+div_language+"_"+$aRowInfo[2]).remove();
    });

    fix_highlighting();

    if ($(".answertable tbody").children().length == 0) {
        add_label(undefined);
    }
}

/**
 * @todo
 */
function fix_highlighting(){
    $("tbody tr").removeClass("highlight");
    $("tbody tr:even").addClass("highlight");
}

/**
 * @param {boolean} alternate
 * @param {boolean} first
 * @return {string} HTML
 */
function createNewLabelTR(alternate, first) {
    x = "<tr class='labelDatas ";
    if (alternate) {
        x = x + "highlight";
    }

    x = x + "' style = 'white-space: nowrap;' id='row_###lang###_###next###'>";

    if (!first) {
        x = x + "<td>###codeval###</td><td>###assessmentval###</td>";
    } else {
        x = x + "<td>"
        + "<span class='fa fa-bars bigIcons text-success'></span>"
        + "</td><td>"
        + "<input type='hidden' class='hiddencode' value='###codeval###' />"
        + "<input type='text' class='codeval form-control  ' value='###codeval###' name='code_###next###' id='code_###next###' size='6' maxlength='5' >"
        + "</td><td>"
        + "<input type=\"number\" class='assessmentval form-control  ' value=\"###assessmentval###\" name=\"assessmentvalue_###next###\" id=\"assessmentvalue_###next###\" style=\"text-align: right;\" size=\"6\" maxlength=\"5\" >";
    }

    x = x + "<td><div class='input-group'><input class=' form-control  ' name=\"title_###lang###_###next###\"  type=\"text\" value=\"\" size=\"80\" maxlength=\"3000\" >"+
    "<span class='input-group-addon'><a title=\"\" id=\"title_###lang###_###next###_ctrl\" href=\"javascript:start_popup_editor('title_###lang###_###next###','[Label:](###lang###)','','','','editlabel','labels')\">"+
    "<span class=\"btn btn-default btn-xs fa fa-pencil  text-success\" name=\"title_###lang###_###next###_popupctrlena\" id=\"title_###lang###_###next###_popupctrlena\" alt=\"\"></span>"+
    "<span style=\"display: none;\" class=\"fa fa-pencil  text-success\" name=\"title_###lang###_###next###_popupctrldis\"  id=\"title_###lang###_###next###_popupctrldis\" alt=\"\"></span>"+
    "</a></span></div></td>";

    if (first) {
        x = x + "<td style=\"text-align: center;\">&nbsp;&nbsp;<button class='btn btn-default btn-sm btnaddanswer'><i class=\"icon-add text-success\"></i></button> <button class='btn btn-default btn-sm btndelanswer'><i class=\" fa fa-trash  text-warning\"></i></button></td>";
    }

    x = x + "</tr>";

    return x;
}

/**
 * @param {string} search
 * @param {string} replace
 * @param {string} subject
 * @param {number} count
 * @return ?
 */
function str_replace(search, replace, subject, count) {
    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
    f = [].concat(search),
    r = [].concat(replace),
    s = subject,
    ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;
            }
        }
    }
    return sa ? s : s[0];
}

/**
 * @param {string} sourcecode
 * @return ?
 */
function getNextCode(sourcecode)
{
    i=1;
    found=true;
    foundnumber=-1;
    while (i<=sourcecode.length && found) {
        found=is_numeric(sourcecode.substr(-i));
        if (found) {
            foundnumber=sourcecode.substr(-i);
            i++;
        }
    }

    if (foundnumber==-1) {
        return(sourcecode);
    } else {
        foundnumber++;
        foundnumber = foundnumber+'';
        result = sourcecode.substr(0,sourcecode.length-foundnumber.length)+foundnumber;
        return(result);
    }
}

/**
 * @return {boolean}
 */
function code_duplicates_check() {
    var codearray = [];
    $('.first input.codeval').each(function() {
        sValue=$.trim($(this).val());
        $(this).val(sValue);
        codearray.push(sValue);
    });

    if ($.inArray('other', codearray)!=-1) {
        alert(otherisreserved);
        return false;
    }

    if (window.LS.arrHasDupes(codearray)) {
        alert(duplicatelabelcode);
        return false;
    }
    return true;
}

/**
 * @return {boolean}
 */
function is_numeric (mixed_var) {
    return (typeof(mixed_var) === 'number'
            || typeof(mixed_var) === 'string') 
        && mixed_var !== ''
        && !isNaN(mixed_var);
}
