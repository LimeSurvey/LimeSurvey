var labelcache=[];
$(document).ready(function(){

    $('.tab-page:first .answertable tbody').sortable({   containment:'parent',
        update:aftermove,
        distance:3});


    $(document).on('click','.btnquickadd', function(){
        scale_id = $(this).data('scale-id');
    });

    $('#quickaddModal').on('show.bs.modal', function(e) {
        var scale_id = $(e.relatedTarget).data('scale-id');
        var table_id = $(e.relatedTarget).closest('div.action-buttons').siblings('table.answertable').attr('id');

        $('#btnqainsert').unbind('click').on('click', function () {
            quickaddlabels(scale_id, 'add', table_id);
        });

        $('#btnqareplace').unbind('click').on('click', function () {
            quickaddlabels(scale_id, 'replace', table_id);
        });
    });

    $('#editanswersform').submit(checkForDuplicateCodes);
    $('#btnlsreplace').click(transferlabels);
    $('#btnlsinsert').click(transferlabels);
    $('#labelsets').click(lspreview);
    $('#languagefilter').click(lsbrowser);
    $('.bthsaveaslabel').click(getlabel);
    $('input[name=savelabeloption]:radio').click(setlabel);
    flag = [false, false];
    $('#btnsave').click(savelabel);
    updaterowproperties();


    $(document).on("click", '.btnaddanswer', addinput);
    $(document).on("click", '.btndelanswer', deleteinput);
    $(document).on("click", '.btnlsbrowser', lsbrowser );
});


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
    }
    else
        {
        $.blockUI({message:"<p><br/>"+strCantDeleteLastAnswer+"</p>"});
        setTimeout(jQuery.unblockUI,1000);
    }
    updaterowproperties();
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
    datas                  = 'surveyid='+$elDatas.data('surveyid'),
    datas                 += '&gid='+$elDatas.data('gid'),
    datas                 += '&qid='+$elDatas.data('qid');
    datas                 += '&codes='+$codes,
    datas                 += '&scale_id='+scale_id,
    datas                 += '&type=answer',
    datas                 += '&position=',
    datas                 += '&first='+first,
    datas                 += '&language='+language+'';

    // We get the HTML of the new row to insert
     $.ajax({
        type: "GET",
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
function addinput()
{
      var $that              = $(this),                               // The "add" button
        $currentRow            = $that.parents('.row-container'),   // The row containing the "add" button
        $currentTable          = $that.parents('.answertable'),
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
    datas                  = 'surveyid='+$elDatas.data('surveyid');
    datas                 += '&gid='+$elDatas.data('gid');
    datas                 += '&qid='+$elDatas.data('qid');
    datas                 += '&codes='+$codes;
    datas                 += '&scale_id='+$(this).data('scale-id');
    datas                 += '&type=answer';
    datas                 += '&position='+$(this).data('position');
    datas                 += '&assessmentvisible'+$(this).data('assessmentvisible');
    datas                 += '&languages='+$languages;

    $scaleId  = $(this).data('scale-id')
    $position = $(this).data('position')

    // We get the HTML of the different rows to insert  (one by language)
    $.ajax({
        type: "GET",
        url: url,
        data: datas,
        success: function(arrayofhtml) {

            // arrayofhtml is a json string containing the different HTML row by language
            // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}

            $arrayOfHtml = JSON.parse(arrayofhtml);                             // Convert the JSON to a javascript object

            // We insert each row for each language
            $.each($arrayOfHtml, function(lang, htmlRow){
                $elRowToUpdate = $('#row_'+lang+'_'+$commonId);                 // The row for the current language
                $elRowToUpdate.after(htmlRow);                                  // We insert the HTML of the new row after this one
                updaterowproperties();
            });

            $('#answercount_'+$scaleId).val($position+2);

        },
        error :  function(html, statut){
            console.log(statut);
            console.log(html);
        }
    });
}


function aftermove(event,ui)
{
    // But first we have change the sortorder in translations, too
    var x;
    classes=ui.item.attr('class').split(' ');
    for (x in classes)
        {
        if (classes[x].substr(0,3)=='row'){
            oldindex=classes[x].substr(4);
        }
    }

    var newindex = Number($(ui.item[0]).parent().children().index(ui.item[0]))+1;

    info=$(ui.item[0]).closest('table').attr('id').split("_");
    language=info[1];
    scale_id=info[2];

    languages=langs.split(';');
    var x;
    for (x in languages)
        {
        if (x>0) {
            tablebody=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' tbody');
            if (newindex<oldindex)
                {
                tablebody.find('.row_'+newindex).before(tablebody.find('.row_'+oldindex));
            }
            else
                {
                tablebody.find('.row_'+newindex).after(tablebody.find('.row_'+oldindex));
            }
        }
    }
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
    })
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
        }
        else
            {
            $("#labelsetpreview").html("<p class='ui-state-highlight ui-corner-all ui-notify-message'>"+strNoLabelSet+"</p>");
            $('#btnlsreplace').addClass('ui-state-disabled');
            $('#btnlsinsert').addClass('ui-state-disabled');
            $('#btnlsreplace').attr('disabled','disabled');
            $('#btnlsinsert').attr('disabled','disabled');
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

                        //tabindex=tabindex+'<li><a href="#language_'+y+'">'+language[y][1]+'</a></li>';
                        //tabbody=tabbody+"<div id='language_"+y+"'><table class='limetable'>";

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
                            if (lsrows[z].title==null) {
                                lsrows[z].title='';
                            }
                            tabbody=tabbody+'><td>'+lsrows[z].code+'</td><td';
                            if (!assessmentvisible)
                            {
                                tabbody=tabbody+' style="display:none;"';
                            }
                            tabbody=tabbody+'>'+lsrows[z].assessment_value+'</td>';
                            tabbody=tabbody+'<td>'+htmlspecialchars(lsrows[z].title)+'</td></tr><tbody>';
                        }
                        tabbody=tabbody+'<thead><tr><th>'+strcode+'</th><th';
                        if (!assessmentvisible)
                        {
                            tabbody=tabbody+' style="display:none;"';
                        }
                        tabbody=tabbody+'>'+sAssessmentValue+'</th>';
                        tabbody=tabbody+'<th>'+strlabel+'</th></tr></thead></table></div>';
                    }
                }
                tabindex=tabindex+'</ul>';
                tabbody=tabbody+'</div>';
                $("#labelsetpreview").append(tabindex+tabbody);
                labelcache[lsid]=tabindex+tabbody;
            }
        });
    }
    else
    {
        $("#labelsetpreview").empty();
        $("#labelsetpreview").append(labelcache[lsid]);
    }
}


function transferlabels()
{
    surveyid=$('input[name=sid]').val();
    if ($(this).attr('id')=='btnlsreplace')
        {
        var lsreplace=true;
    }
    else
        {
        var lsreplace=false;
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
                if (assessmentvisible)
                    {
                    assessment_style='';
                    assessment_type='text';
                }
                else
                    {
                    assessment_style='style="display:none;"';
                    assessment_type='hidden';
                }

                var tablerows='';
                var y;
                for (y in json)
                    {

                    language=json[y];
                    var lsrows = new Array();
                    for (z in language)
                        {
                        if (z == languages[0])
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
                            if (x==0) {
                                tablerows=tablerows+
                                '<tr class="row_'+k+'" >'+
                                '   <td>'+
                                '       <span class="glyphicon glyphicon-move"></span>'+
                                '   </td>'+
                                '   <td>'+
                                '       <input class="code second-in-answerjs form-control input-lg" onkeypress="return goodchars(event,\'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_\')" type="text" maxlength="5" size="20" value="'+htmlspecialchars(lsrows[k].code)+'" />'+
                                '   </td>'+
                                '   <td '+assessment_style+'><input class="assessment" type="'+assessment_type+'" maxlength="5" size="5" value="'+htmlspecialchars(lsrows[k].assessment_value)+'"/>'+
                                '   </td>'+

                                '   <td>'+
                                '           <input type="text" size="20" class="answer third-in-answerjs  form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                                '   </td>'+

                                '   <td>'+
                                '           <a class="editorLink">'+
                                '               <span class="glyphicon glyphicon-pencil btneditanswerena" data-toggle="tooltip" data-placement="bottom" title="Start HTML editor in a popup window" ></span>'+
                                '               <span class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                                '           </a>'+

                                '       <span class="btnaddanswer icon-add text-success"></span>'+
                                '       <span class="btndelanswer glyphicon glyphicon-trash text-warning"></span>'+
                                '   </td>'+
                                '</tr>'
                            }
                            else
                                {
                                tablerows=tablerows+
                                '<tr class="row_'+k+'" >'+
                                '   <td>&nbsp;</td>'+
                                '   <td>'+
                                        htmlspecialchars(lsrows[k].code)+
                                '   </td>'+

                                '   <td>'+
                                '           <input type="text" size="20" class="answer fourth-in-answerjs form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                                '   </td>'+

                                '   <td>'+
                                '           <a class="editorLink">'+
                                '               <span class="glyphicon glyphicon-pencil btneditanswerena" data-toggle="tooltip" data-placement="bottom" title="Start HTML editor in a popup window" ></span>'+
                                '               <span class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                                '           </a>'+

                                '       <span class="btnaddanswer icon-add text-success"></span>'+
                                '       <span class="btndelanswer glyphicon glyphicon-trash text-warning"></span>'+
                                '   </td>'+
                                '</tr>'
                            }
                        }
                    }
                }
                if (lang_x_found_in_label === false)
                    {
                    lsrows=defaultdata_labels[0];
                    var k=0;
                    for (k in lsrows)
                        {
                        tablerows=tablerows+
                        '<tr class="row_'+k+'" >'+
                        '   <td>&nbsp;</td>'+
                        '   <td>'+htmlspecialchars(lsrows[k].code)+'</td>'+

                        '   <td>'+
                        '           <input type="text" size="20" class="answer fifth-in-answerjs form-control input-lg" value="'+htmlspecialchars(lsrows[k].title)+'"></input>'+
                        '   </td>'+

                        '   <td>'+
                        '           <a class="editorLink">'+
                        '               <span class="glyphicon glyphicon-pencil btneditanswerena" data-toggle="tooltip" data-placement="bottom" title="Start HTML editor in a popup window" ></span>'+
                        '               <span class="btneditanswerdis glyphicon glyphicon-pencil text-success" title="Give focus to the HTML editor popup window" style="display: none;"></span>'+
                        '           </a>'+

                        '       <span class="btnaddanswer icon-add text-success" />'+
                        '       <span class="btndelanswer  glyphicon glyphicon-trash text-warning" />'+
                        '   </td>'+
                        '</tr>'
                    }
                }
                if (lsreplace) {
                    $('#answers_'+languages[x]+'_'+scale_id+' tbody').empty();
                }
                $('#answers_'+languages[x]+'_'+scale_id+' tbody').append(tablerows);
                // Unbind any previous events
                $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').unbind('click');
                $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').unbind('click');
                $('#answers_'+languages[x]+'_'+scale_id+' .answer').unbind('focus');
                // Bind events again
                $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
                $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);
                $('#answers_'+languages[x]+'_'+scale_id+' .answer').focus(function(){
                    if ($(this).val()==newansweroption_text)
                        {
                        $(this).val('');
                    }
                });
            }
            $('.tab-page:first .answertable tbody').sortable('refresh');
            $('#labelsetbrowserModal').modal('hide');
            updaterowproperties();

    }}
    );


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
    for (var k in lsrows)
    {
        var thisrow=lsrows[k].splitCSV(separatorchar);
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

        for (var x in languages)
        {
            if (typeof thisrow[parseInt(x)+1]=='undefined')
            {
                thisrow[parseInt(x)+1]=thisrow[1];
            }
            var lang_active = languages[x];

            if(!answers[lang_active]){
                answers[lang_active] = [];
            }

            if (lsreplace)
            {
                $('#answers_'+languages[x]+'_'+scale_id+' tbody').empty();
            }
            answers[lang_active].push(
               {text: thisrow[(parseInt(x)+1)], code: thisrow[0], quid: quid}
            );
        }

        $('#answers_'+languages[x]+'_'+scale_id+' tbody').append(tablerows);
        // Unbind any previous events
        $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').unbind('click');
        $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').unbind('click');
        $('#answers_'+languages[x]+'_'+scale_id+' .answer').unbind('focus');
        $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
        $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);
    }

for (var x in languages)
{        
    var lang_active = languages[x];
    promises.push(
        addinputQuickEdit(closestTable, lang_active, (x==0), scale_id, codes)
    )
}

    $.when.apply($,promises).done(
            function(){
                $.each(arguments, function(i,item){
                    var $table = item.langtable;
                    $.each(answers[item.lng], function(j,mapObject){
                        var html = item.html;
                        var htmlRowObject = $(html);
                        if(htmlRowObject.find('input.code').length > 0)
                        {
                            htmlRowObject.find('input.code').val(mapObject.code);
                        } 
                        else 
                        {
                            htmlRowObject.find('td.code-title').text(mapObject.text);
                        }

                        htmlRowObject.find('td.subquestion-text').find('input').val(mapObject.text);

                        $table.find('tbody').append(htmlRowObject);
                    });
                });
                $('#quickaddarea').val('');
                $('.tab-page:first .answertable tbody').sortable('refresh');
                updaterowproperties();
                $('#quickaddModal').modal('hide')
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
    scale_id=removechars($(this).attr('id'));
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
