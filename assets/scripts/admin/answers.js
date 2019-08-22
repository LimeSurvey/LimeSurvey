
// Namespace
var LS = LS || {  onDocumentReady: {} };

var labelcache=[];
$(document).on('ready  pjax:scriptcomplete', function(){

    $('.tab-page:first .answertable tbody').sortable({   containment:'parent',
        update:aftermove,
        distance:3});


    $(document).on('click','.btnquickadd', function(){
        scale_id = $(this).data('scale-id');
    });

    $('#quickaddModal').on('show.bs.modal', function (e) {
        var scale_id = $(e.relatedTarget).data('scale-id');
        var table_id = $(e.relatedTarget).closest('div.action-buttons').siblings('table.answertable').attr('id');

        $('#btnqainsert').off('click').on('click', function () {
            quickaddlabels(scale_id, 'add', table_id);
        });

        $('#btnqareplace').off('click').on('click', function () {
            quickaddlabels(scale_id, 'replace', table_id);
        });
    });

    $('#editanswersform').submit(checkForDuplicateCodes);
    $('#btnlsreplace').on('click', function(e){e.preventDefault(), transferlabels('replace')});
    $('#btnlsinsert').on('click', function(e){e.preventDefault(), transferlabels('insert')});
    $('.bthsaveaslabel').click(getlabel);
    $('input[name=savelabeloption]:radio').click(setlabel);
    flag = [false, false];
    $('#btnsave').click(savelabel);
    updaterowproperties();


    $('.btnaddanswer').on("click.answeroptions", debouncedAddInput);
    $('.btndelanswer').on("click.answeroptions", deleteinput);
    $('#labelsetbrowserModal').on("shown.bs.modal.", lsbrowser );
    $('#labelsetbrowserModal').on("hidden.bs.modal.", lsbrowser_destruct );
});

function debouncedAddInput(e){
    e.preventDefault();
    var btnAddAnswer = $('.btnaddanswer');
    btnAddAnswer.off("click.answeroptions");
    btnAddAnswer.find('i').attr('class', "fa fa-cog fa-spin");
    addinput.apply(this, arguments).then(function () {
        $('.btnaddanswer').find('i').attr('class', "icon-add text-success");
        $('.btnaddanswer').on("click.answeroptions", debouncedAddInput);
    });
}

function rebindClickHandler(){
    $('.btndelanswer').off("click").on("click", deleteinput);
}

function deleteinput(e)
{
    e.preventDefault();
    // 1.) Check if there is at least one answe

    countanswers=$(this).closest("tbody").children("tr").length;//Maybe use class is better
    if (countanswers>1)
    {
        // 2.) Remove the table row
        var x;
        classes=$(this).closest('tr').attr('class').split(' ');
        LS.ld.forEach(classes, function(curClass, x) {
            if (curClass.substr(0,3)=='row'){
                position=curClass.substr(4);
            }
        });

        info=$(this).closest('table').attr('id').split("_");
        language=info[1];
        scale_id=info[2];
        languages=langs.split(';');

        var x;
        LS.ld.forEach(languages, function(curLng, x) {
            tablerow=$('#tabpage_'+curLng).find('#answers_'+curLng+'_'+scale_id+' .row_'+position);
            if (x==0) {
                tablerow.fadeTo(400, 0, function(){
                    $(this).remove();
                    updaterowproperties();
                });
            }
            else {
                tablerow.remove();
            }
        });
    }
    else
        {
        $.blockUI({message:"<p><br/>"+strCantDeleteLastAnswer+"</p>"});
        setTimeout(jQuery.unblockUI,1000);
    }
    updaterowproperties();
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
        'type':                 'answer',
        'languages' :           JSON.stringify($elDatas.data('languages').join(';')),
        'assessmentvisible':    $elDatas.data('assessmentvisible') == 1,
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
 * add addinputQuickEdit : for usage with the quickAdd Button
 */
function addinputQuickEdit($currentTable, language, first, scale_id, codes)
{
    codes = codes || [];
    var $elDatas               = $('#add-input-javascript-datas'),  // This hidden element  on the page contains various datas for this function
        $url                   = $elDatas.data('quickurl'),         // Url for the request
        $errormessage          = $elDatas.data('errormessage'),     // the error message if the AJAX request failed
        $defer                 = $.Deferred(),
        $codes, datas;


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
    datas  = {
      'codes': $codes,
      'scale_id': scale_id,
      'type' : 'answer',
      'position': '',
      'first': first,
      'language': language+'',
      'assessmentvisible' : ( $elDatas.data('assessmentvisible') == 1 )
    };

    console.ls.log(datas);
    // We get the HTML of the new row to insert
     $.ajax({
        type: "POST",
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
        url                   = $elDatas.data('url'),              // Url for the request
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
    datas = {
        'surveyid':             $elDatas.data('surveyid'),
        'gid':                  $elDatas.data('gid'),
        'qid':                  $elDatas.data('qid'),
        'codes':                $codes,
        'scale_id':             $(this).find('i').data('scale-id'),
        'type':                 'answer',
        'position':             $(this).find('i').data('position'),
        'languages':            $languages,
        'assessmentvisible':    $elDatas.data('assessmentvisible') == 1,
    };

    $scaleId  = $(this).data('scale-id')
    $position = $(this).data('position')

    // We get the HTML of the different rows to insert  (one by language)
    return $.ajax({
        type: "GET",
        url: url,
        data: datas,
        success: function(arrayofhtml) {

            // arrayofhtml is a json object containing the different HTML row by language
            // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}

            // We insert each row for each language
            $.each(arrayofhtml, function(lang, htmlRow){
                $elRowToUpdate = $('#row_'+lang+'_'+$commonId);                 // The row for the current language
                $elRowToUpdate.after(htmlRow);                                  // We insert the HTML of the new row after this one
                updaterowproperties();
            });

            $('#answercount_'+$scaleId).val($position+2);
            rebindClickHandler();
        },
        error :  function(html, statut){
            console.ls.log(statut);
            console.ls.log(html);
        }
    });
}


function aftermove(event,ui)
{
    // But first we have change the sortorder in translations, too
    var x;
    classes=ui.item.attr('class').split(' ');

    LS.ld.forEach(classes, function(curClass,  x) {
        if (curClass.substr(0,3)=='row'){
            oldindex=curClass.substr(4);
        }
    });

    var newindex = Number($(ui.item[0]).parent().children().index(ui.item[0]))+1;

    info=$(ui.item[0]).closest('table').attr('id').split("_");
    language=info[1];
    scale_id=info[2];

    languages=langs.split(';');
    var x;
    LS.ld.forEach(languages, function(curLng, x) {
        if (x>0) {
            tablebody=$('#tabpage_'+curLng).find('#answers_'+curLng+'_'+scale_id+' tbody');
            if (newindex<oldindex)
                {
                tablebody.find('.row_'+newindex).before(tablebody.find('.row_'+oldindex));
            }
            else
                {
                tablebody.find('.row_'+newindex).after(tablebody.find('.row_'+oldindex));
            }
        }
    });
    updaterowproperties();
}


// This function adjust the alternating table rows and renames/renumbers IDs and names
// if the list has really changed
function updaterowproperties()
{
    var sID=$('input[name=sid]').val();
    var gID=$('input[name=gid]').val();
    var qID=$('input[name=qid]').val();

    $('.answertable tbody').each(function(){
        info=$(this).closest('table').attr('id').split("_");
        language=info[1];
        scale_id=info[2];
        var rownumber=1;

        $(this).children('tr').each(function(){

            if(!$(this).hasClass('row_'+rownumber))
            {
                var classes = this.className.split(" ").filter(function (c) {
                    return c.lastIndexOf('row_', 0) !== 0;
                });
                this.className = $.trim(classes.join(" "));
                $(this).addClass('row_'+rownumber);
            }

            $(this).addClass('row-container');
            $(this).find('.oldcode').attr('id','oldcode_'+rownumber+'_'+scale_id);
            $(this).find('.oldcode').attr('name','oldcode_'+rownumber+'_'+scale_id);
            $(this).find('.code').attr('id','code_'+rownumber+'_'+scale_id);
            $(this).find('.code').attr('name','code_'+rownumber+'_'+scale_id);
            $(this).find('.answer').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id);
            $(this).find('.answer').attr('name','answer_'+language+'_'+rownumber+'_'+scale_id);
            $(this).find('.assessment').attr('id','assessment_'+rownumber+'_'+scale_id);
            $(this).find('.assessment').attr('name','assessment_'+rownumber+'_'+scale_id);

            // Newly inserted row editor button
            $(this).find('.editorLink').attr('href','javascript:start_popup_editor(\'answer_'+language+'_'+rownumber+'_'+scale_id+'\',\'[Answer:]('+language+')\',\''+sID+'\',\''+gID+'\',\''+qID+'\',\'editanswer\',\'editanswer\')');
            $(this).find('.editorLink').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_ctrl');
            $(this).find('.btneditanswerena').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerena').attr('name','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrlena');
            $(this).find('.btneditanswerdis').attr('id','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrldis');
            $(this).find('.btneditanswerdis').attr('name','answer_'+language+'_'+rownumber+'_'+scale_id+'_popupctrldis');

            rownumber++;
        });

        $('#answercount_'+scale_id).val(rownumber);
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

function is_numeric (mixed_var) {
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}

function popupeditor()
{
    input_id=$(this).parent().find('.answer').attr('id');
    start_popup_editor(input_id);
}

/**
* Checks for duplicate codes and shows an error message & returns false if there are any duplicates
*
* @returns {Boolean}
*/
function checkForDuplicateCodes()
{
    if  (areCodesUnique('')==false)
    {
        alert(duplicateanswercode);
        return false
    }
    else
    {
        updaterowproperties();
        return true;
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
    //    e.preventDefault();
    var scale_id=window.LS.removechars($(this).attr('id'));
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
                $table.find('tbody').empty();
            }
            
            $("#labelsetpreview").find('#language_'+lng).find('.selector_label-list').find('.selector_label-list-row').each(function(i,item){
                try{
                    var label = $(item).data('label');
                    var $row = $(row);
                    var $row = $(row)
                    var $tr = $row.eq(4);
                    var randId = 'new'+Math.floor(Math.random()*10000);

                    // $tr.attr('data-common-id', $tr.attr('data-common-id').replace(/new[0-9]{3,6}/,randId));
                    // $tr.attr('id', $tr.attr('id').replace(/new[0-9]{3-5}/,randId));

                    $row.find('input').each(function(j,inputField){
                        $(inputField).attr('name', $(inputField).attr('name').replace(/new[0-9]{3,6}/,randId));
                        $(inputField).attr('id', $(inputField).attr('id').replace(/new[0-9]{3,6}/,randId));
                    });
                
                    $row.find('td.code-title').find('input[type=text]').length >0 ? $row.find('td.code-title').find('input[type=text]').val(label.code) : $row.find('td.code-title').text(label.code);
                    $row.find('td.assessment-value').find('input[type=text]').length >0 ? $row.find('td.assessment-value').find('input[type=text]').val(label.assessment_value) : '';
                
                    $row.find('td.code-title').find('input[type=text]').val(label.code);
                    $row.find('td.subquestion-text').find('input[type=text]').val(label.title);
                    $table.find('tbody').append($row);
                    
                } catch(e) {console.ls.error(e);}
            });
        
            $('.tab-page:first .answertable tbody').sortable('refresh');
            updaterowproperties();
            $('#labelsetbrowserModal').modal('hide');
            $('#current_scale_id').remove();
            $('.btnaddanswer').off('click.answeroptions').on("click.answeroptions", debouncedAddInput);
            $('.btndelanswer').off('click.answeroptions').on("click.answeroptions", deleteinput);
        });
        var $lang_table = $('#answers_'+language+'_'+scale_id);
    });
}



function quickaddlabels(scale_id, addOrReplace, table_id)
{
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
        codeAlphaPart = "",
        currentCharacter,
        codeSigil = (codes[0] !== undefined ? codes[0].split("") : ("A01").split(""));
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
    });
    
    LS.ld.forEach(languages, function(curLanguage, x) {
        $('#answers_'+curLanguage+'_'+scale_id+' tbody').append(tablerows);
        // Unbind any previous events
        $('#answers_'+curLanguage+'_'+scale_id+' .btnaddanswer').off('click.answeroptions');
        $('#answers_'+curLanguage+'_'+scale_id+' .btndelanswer').off('click.answeroptions');
        $('#answers_'+curLanguage+'_'+scale_id+' .answer').off('focus');
        $('#answers_'+curLanguage+'_'+scale_id+' .btnaddanswer').on('click.answeroptions',debouncedAddInput);
        $('#answers_'+curLanguage+'_'+scale_id+' .btndelanswer').on('click.answeroptions',deleteinput);
        
        promises.push(
            addinputQuickEdit(closestTable, curLanguage, true, scale_id, codes)
            );
    });

    $.when.apply($, promises).done(
            function(){
                $.each(arguments, function(i,item){
                    var $table = item.langtable;
                    $.each(answers[item.lng], function(j,mapObject){
                        var html = item.html;
                        var html_quid = html.replace(/({{quid_placeholder}})/g,mapObject.quid);
                        var htmlRowObject = $(html_quid);
                        if(htmlRowObject.find('input.code').length > 0)
                        {
                            htmlRowObject.find('input.code').val(mapObject.code);
                        }
                        else
                        {
                            htmlRowObject.find('td.code-title').text(mapObject.text);
                        }

                        htmlRowObject.find('td.subquestion-text').find('input').val(mapObject.text);
                        console.ls.log(htmlRowObject);
                        $table.find('tbody').append(htmlRowObject);
                    });
                });
                $('#quickaddarea').val('');
                $('.tab-page:first .answertable tbody').sortable('refresh');
                updaterowproperties();
                $('#quickaddModal').modal('hide')
                $('.btnaddanswer').off('click.answeroptions').on("click.answeroptions", debouncedAddInput);
                $('.btndelanswer').off('click.answeroptions').on("click.answeroptions", deleteinput);
                //bindClickIfNotExpanded();
            },
            function(){
                /*$('#quickadd').dialog('close');*/
                $('#quickaddarea').val('');
                $('.tab-page:first .answertable tbody').sortable('refresh');
                updaterowproperties();
                $('#quickaddModal').modal('hide')
                //bindClickIfNotExpanded();
            }
        )
}

function getlabel()
{
    var answer_table = $(this).parent().children().eq(0);
    scale_id=window.LS.removechars($(this).attr('id'));
    updaterowproperties();
}

function setlabel()
{
    switch($(this).attr('id'))
    {
        case 'newlabel':
        if(!flag[0]){
            $('#lasets').parent().remove();
            $(this).parent().after(
                '<p class="label-name-wrapper">'+
                '   <label for="laname">'+sLabelSetName+':</label> ' +
                '   <input type="text" name="laname" id="laname" class="form-control">'+
                '</p>');
            flag[0] = true;
            flag[1] = false;
        }
        break;

        case 'replacelabel':
        if(!flag[1]){
            $('#laname').parent().remove();
            $(this).parent().after(
                '<p class="label-name-wrapper">'+
                '   <select name="laname" id="lasets" class="form-control">'+
                '       <option value=""></option>'+
                '   </select>'+
                '</p>');
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
        })
    }
    else
        {
        aLanguages = langs.split(';');
        $.post(sCheckLabelURL, { languages: aLanguages, lid: lid}, function(data) {
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
    $('.code').each(function(index) {
        if($(this).attr('id').substr(-1) === scale_id)
            code.push($(this).val());
    });

    // get assessment values for the current scale
    var assessmentvalues = new Array();
    $('.assessment').each(function(index) {
        if($(this).attr('id').substr(-1) === scale_id)
            assessmentvalues.push($(this).val());
    });

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


    $.post(lasaveurl, { laname: $('#laname').val(), lid: lid, code: code, answers: answers, assessmentvalues:assessmentvalues }, function(data) {
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
