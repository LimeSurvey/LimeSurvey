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
    $('#exportlabelset').on('submit', function(){
        $('#ls-loading').show();
        const token = $(this).find('input[name="export_token"]').val();
        const pollUrl = $(this).find('input[name="url"]').val() + encodeURIComponent(token);

        const interval = setInterval(() => {
            $.get(pollUrl, function (data) {
                if (data.done) {
                    clearInterval(interval);
                    $('#ls-loading').hide();
                }
            }).fail(() => {
                console.warn('Failed to poll export status');
            });
        }, 1000);
    });


    const answersTable = $(".answertable tbody");
    if (answersTable.length && answersTable.children().length == 0) {
        add_label(undefined);
    }

    $(document).on('click', '.btnaddanswer', add_label);
    $(document).on('click', '.btndelanswer', del_label);
    $(document).on('keyup change', '.codeval', sync_label);
    $(document).on('keyup change', '.assessmentval', sync_label);

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

    // parsedRows is an array of objects with the parsed row data (ie: [{code:"code", titles:{"en":"Title"}}])
    let parsedRows = [];
    $(lsrows).each(function(index, row) {
        var code = undefined;

        var params = row.split(separatorchar);
        var k = 0;
        if (params.length > $(".lslanguage").length){
            code = params[0].replace(/[^a-zA-Z 0-9]+/g,'').substr(0,5);
            k++;
        }

        let parsedRow = {
            code: code,
            titles: {},
        };

        $(".lslanguage").each(function(i) {
            parsedRow.titles[$(this).val()] = params[k];
            k++;
            if (k >= params.length) {
                return false; // Break out of the loop if there are no more params
            }
        });

        parsedRows.push(parsedRow);
    });

    const inputData = $('#add-label-input-javascript-datas').data();
    const datas = {
        lid: inputData.lid,
        newId: '{{newid_placeholder}}',
        code: '{{code_placeholder}}',
        title: '{{title_placeholder}}',
    };
    const mainTable = $(".first tbody");
    retrieveRowHtml(datas)
        .then((result) => {
            $.each(parsedRows, (index, parsedRow) => {
                // We insert each row for each language
                $.each(result.arrayofhtml, (lang, htmlRow) => {
                    const randomid = 'new' + Math.floor(Math.random()*1111111);
                    const title = parsedRow.titles[lang] || '';
                    let finalRowHtml = htmlRow;
                    finalRowHtml = finalRowHtml.replaceAll("{{newid_placeholder}}", randomid);
                    finalRowHtml = finalRowHtml.replaceAll("{{code_placeholder}}", parsedRow.code);
                    finalRowHtml = finalRowHtml.replaceAll("{{title_placeholder}}", title);
                    const tableToUpdate = mainTable.closest('.tab-content').find(`.lang-${lang} > table > tbody`); // The table for the current language
                    tableToUpdate.append(finalRowHtml);
                });
            });
        })
        .then(() => {
            $("#quickaddarea").val('');
            $('#quickadd').modal('hide');
        });
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

/*:: declare function retrieveRowHtml({}): {} */
/**
 * Retrieves the HTML for new label rows. Returns a promise.
 *
 * @param {object} datas
 * @return {Promise}
 */
function retrieveRowHtml(datas) {
    const inputData = $('#add-label-input-javascript-datas').data();
    const url = inputData.url;
    const errormessage = inputData.errormessage;
    const $defer = $.Deferred();
    $.ajax({
        type: 'GET',
        url: url,
        data: datas,
        success(arrayofhtml) {
            // arrayofhtml is a json object containing the different HTML row by language
            // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}
            $defer.resolve({ arrayofhtml: arrayofhtml });
        },
        error(html, status) {
            alert('Internal error retrieving row html: ' + errormessage);
            $defer.reject([html, status, errormessage]);
        },
    });
    return $defer.promise();
}

/**
 * @param {object} event
 * @param {object} datas
 * @return {string} Random id.
 */
function add_label(event, datas) {
    const inputData = $('#add-label-input-javascript-datas').data();

    var next_code = get_next_code(event);

    let mainTable;
    let rowIndex;
    if (typeof(event) == "undefined") {
        mainTable = $(".first tbody");
        rowIndex = mainTable.children().index(mainTable.find("tr:last-child"));
    } else {
        mainTable = $(event.target).closest('tbody');
        rowIndex = mainTable.children().index($(event.target).closest('tr'));
    }

    var randomid = 'new' + Math.floor(Math.random()*1111111);

    datas = datas || {};
    datas.lid = datas.lid || inputData.lid;
    datas.newId = datas.newId || randomid;
    datas.code = datas.code || next_code;

    retrieveRowHtml(datas).then((result) => {
        // We insert each row for each language
        $.each(result.arrayofhtml, (lang, htmlRow) => {
            const tableToUpdate = mainTable.closest('.tab-content').find(`.lang-${lang} > table > tbody`); // The table for the current language
            const rowToUpdate = tableToUpdate.children()[rowIndex];
            if (rowToUpdate) {
                $(rowToUpdate).after(htmlRow); // We insert the HTML of the new row after this one
            } else {
                tableToUpdate.append(htmlRow);
            }
        });
    });

    fix_highlighting();

    return randomid;
}

/**
 * @param {object} event
 */
function del_label(event) {
    event.preventDefault();
    var sRowID = $(event.target).closest('tr').attr('id');
    // tooltip remains after delete label after bootstrap 5 upgrade, so remove tooltip manually here until find better solution.
    $('.tooltip').tooltip('dispose');
    var aRowInfo = sRowID.split('_');// first is row, second langage and last the row number
    $(".tab-pane").each(function(divindex,divelement){
        var div_language = $(".lslanguage",divelement).val();

        if (typeof(div_language)!="undefined") {
            $("#row_"+div_language+"_"+aRowInfo[2]).remove();
        }
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
