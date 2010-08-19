// $Id$
var labelcache=[];  
$(document).ready(function(){
       $('.tab-page:first .answertable tbody').sortable({   containment:'parent',
                                            update:aftermove,
                                            distance:3});
       $('.btnaddanswer').click(addinput);
       $('.btndelanswer').click(deleteinput); 
       $('#editanswersform').submit(code_duplicates_check)
       $('#labelsetbrowser').dialog({ autoOpen: false,
                                        modal: true,
                                        width:800,
                                        title: lsbrowsertitle});   
       $('#quickadd').dialog({ autoOpen: false,
                                        modal: true,
                                        width:600,
                                        title: quickaddtitle});                                                 
       $('.btnlsbrowser').click(lsbrowser);
       $('#btncancel').click(function(){
           $('#labelsetbrowser').dialog('close');
       });
       $('#btnlsreplace').click(transferlabels);
       $('#btnlsinsert').click(transferlabels);
       $('#labelsets').click(lspreview);
       $('#languagefilter').click(lsbrowser);
       
       $('#btnqacancel').click(function(){
           $('#quickadd').dialog('close');
       });  
       $('#btnqareplace').click(quickaddlabels);
       $('#btnqainsert').click(quickaddlabels);
       $('.btnquickadd').click(quickadddialog);               
       
       updaterowproperties(); 
});


function deleteinput()
{

    // 1.) Check if there is at least one answe
     
    countanswers=$(this).parent().parent().parent().children().length;
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


function addinput()
{
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
    newposition=Number(position)+1;
    languages=langs.split(';');

    for (x in languages)
    {
        tablerow=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' .row_'+position);
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
        if (x==0) {
            inserthtml='<tr class="row_'+newposition+'" style="display:none;"><td><img class="handle" src="../images/handle.png" /></td><td><input class="code" type="text" maxlength="5" size="5" value="'+htmlspecialchars(getNextCode($(this).parent().parent().find('.code').val()))+'" /></td><td '+assessment_style+'><input class="assessment" type="'+assessment_type+'" maxlength="5" size="5" value="1"/></td><td><input type="text" size="100" class="answer" value="'+htmlspecialchars(newansweroption_text)+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
        }
        else
        {
            inserthtml='<tr class="row_'+newposition+'" style="display:none;"><td>&nbsp;</td><td>&nbsp;</td><td><input type="text" size="100" class="answer" value="'+htmlspecialchars(newansweroption_text)+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
        }
        tablerow.after(inserthtml);
        tablerow.next().find('.btnaddanswer').click(addinput);
        tablerow.next().find('.btndelanswer').click(deleteinput);
        tablerow.next().find('.answer').focus(function(){
            if ($(this).val()==newansweroption_text)
            {
                $(this).val('');
            }
        });
        tablerow.next().find('.code').blur(updatecodes);
    }
    $('.row_'+newposition).fadeIn('slow');     
    $('.row_'+newposition).show(); //Workaround : IE does not show with fadeIn only
                                                                 
    if(languagecount>1)
    {
        
    }
    
    $('.tab-page:first .answertable tbody').sortable('refresh');
    updaterowproperties();
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
      var highlight=true;
      var rownumber=1;
      $(this).children('tr').each(function(){
          
         $(this).removeClass(); 
         if (highlight){
             $(this).addClass('highlight');
         }
         $(this).addClass('row_'+rownumber);
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
         highlight=!highlight;
         rownumber++;
      })
      $('#answercount_'+scale_id).val(rownumber);
  })  
}

function updatecodes()
{

}

function getNextCode(sourcecode)
{
    i=1; 
    found=true;
    foundnumber=-1;
    while (i<=sourcecode.length && found)   
    {
        found=is_numeric(sourcecode.substr(-i));
        if (found) 
        {
            foundnumber=sourcecode.substr(-i);
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
       result=sourcecode.substr(0,sourcecode.length-foundnumber.length)+foundnumber;
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

function code_duplicates_check()
{
    languages=langs.split(';');    
    var dupefound=false;
    $('#tabpage_'+languages[0]+' .answertable tbody').each(function(){
        var codearray=[];
        $(this).find('tr .code').each(function(){
           codearray.push($(this).val());  
        })
        if (arrHasDupes(codearray))
        {
            alert(duplicateanswercode);
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
    $('#labelsetbrowser').dialog( 'open' );
    surveyid=$('input[name=sid]').val();
/*    match=0;
    if ($('#languagefilter').attr('checked')==true)
    {
        match=1;
    }*/
    $.getJSON('admin.php?action=ajaxlabelsetpicker',{sid:surveyid, match:1},function(json){
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
            $('#btnlsreplace').addClass('ui-state-disabled');
            $('#btnlsinsert').addClass('ui-state-disabled');
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
    
   var lsid=$('#labelsets').selectedValues();
   surveyid=$('input[name=sid]').val();
   // check if this label set is already cached
   if (!isset(labelcache[lsid]))
   {
       $.ajax({
              url: 'admin.php?action=ajaxlabelsetdetails',
              dataType: 'json',
              data: {lid:lsid, sid:surveyid},
              cache: true,
              success: function(json){
                    $("#labelsetpreview").tabs('destroy');
                    $("#labelsetpreview").empty();
                    var tabindex=''; 
                    var tabbody=''; 
                    for ( x in json)
                    {

                        language=json[x];
                        for (y in language)
                        {
                            tabindex=tabindex+'<li><a href="#language_'+y+'">'+language[y][1]+'</a></li>';
                            tabbody=tabbody+"<div id='language_"+y+"'><table class='limetable'>";
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
                                tabbody=tabbody+'><td>'+lsrows[z].code+'</td><td>'+lsrows[z].title+'</td></tr><tbody>';
                            }
                            tabbody=tabbody+'<thead><tr><th>'+strcode+'</th><th>'+strlabel+'</th></tr></thead></table></div>';
                        }
                    }
                    $("#labelsetpreview").append('<ul>'+tabindex+'</ul>'+tabbody);
                    labelcache[lsid]='<ul>'+tabindex+'</ul>'+tabbody;
                    $("#labelsetpreview").tabs();
              }}
       );
   }
   else
   {
                    $("#labelsetpreview").tabs('destroy');
                    $("#labelsetpreview").empty();
                    $("#labelsetpreview").append(labelcache[lsid]);
                    $("#labelsetpreview").tabs();
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
    
    if(typeof(arr) == 'object') { //Array/Hashes/Objects 
        for(var item in arr) {
            var value = arr[item];
            
            if(typeof(value) == 'object') { //If it is an array,
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
   surveyid=$('input[name=sid]').val(); 
   if ($(this).attr('id')=='btnlsreplace')
   {
       var lsreplace=true;
   } 
   else
   {
       var lsreplace=false;
   }
   var lsid=$('#labelsets').selectedValues();
   $.ajax({
          url: 'admin.php?action=ajaxlabelsetdetails',
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
                                    tablerows=tablerows+'<tr class="row_'+k+'" ><td><img class="handle" src="../images/handle.png" /></td><td><input class="code" type="text" maxlength="5" size="5" value="'+htmlspecialchars(lsrows[k].code)+'" /></td><td '+assessment_style+'><input class="assessment" type="'+assessment_type+'" maxlength="5" size="5" value="1"/></td><td><input type="text" size="100" class="answer" value="'+htmlspecialchars(lsrows[k].title)+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
                                }
                                else
                                {
                                    tablerows=tablerows+'<tr class="row_'+k+'" ><td>&nbsp;</td><td>'+htmlspecialchars(lsrows[k].code)+'</td><td><input type="text" size="100" class="answer" value="'+htmlspecialchars(lsrows[k].title)+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
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
                            tablerows=tablerows+'<tr class="row_'+k+'" ><td>&nbsp;</td><td>'+htmlspecialchars(lsrows[k].code)+'</td><td><input type="text" size="100" class="answer" value="'+htmlspecialchars(lsrows[k].title)+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
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
                    $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
                    $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);
                    $('#answers_'+languages[x]+'_'+scale_id+' .answer').focus(function(){
                        if ($(this).val()==newansweroption_text)
                        {
                            $(this).val('');
                        }
                    });
                }
                $('#labelsetbrowser').dialog('close');
                $('.tab-page:first .answertable tbody').sortable('refresh');                       
                updaterowproperties(); 

          }}
   );
    
    
}



function quickaddlabels()
{
    if ($(this).attr('id')=='btnqareplace')
    {
       var lsreplace=true;
    } 
    else
    {
       var lsreplace=false;
    }

    languages=langs.split(';');   
    for (x in languages)
    {
        
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
        
        lsrows=$('#quickaddarea').val().split("\n");

        if (lsrows[0].indexOf("\t")==-1)
        {
            separatorchar=';'
        }
        else
        {
            separatorchar="\t";
        }
        tablerows='';
        for (k in lsrows)
        {
            thisrow=lsrows[k].splitCSV(separatorchar);
            if (thisrow.length==1)
            {
                thisrow[1]=thisrow[0];
                thisrow[0]=parseInt(k)+1;
            }
            else
            {
                thisrow[0]=thisrow[0].replace(/[^A-Za-z0-9]/g, "");
            }            
             
            if (x==0) {
                tablerows=tablerows+'<tr class="row_'+k+'" ><td><img class="handle" src="../images/handle.png" /></td><td><input class="code" type="text" maxlength="5" size="5" value="'+thisrow[0]+'" /></td><td '+assessment_style+'><input class="assessment" type="'+assessment_type+'" maxlength="5" size="5" value="1"/></td><td><input type="text" size="100" class="answer" value="'+thisrow[1]+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
            }
            else
            {
                tablerows=tablerows+'<tr class="row_'+k+'" ><td>&nbsp;</td><td>&nbsp;</td><td><input type="text" size="100" class="answer" value="'+thisrow[1]+'"></input><a class="editorLink"><img class="btneditanswerena" src="../images/edithtmlpopup.png" width="16" height="16" border="0" /><img class="btneditanswerdis" alt="Give focus to the HTML editor popup window" src="../images/edithtmlpopup_disabled.png" style="display: none;" width="16" height="16" align="top" border="0" /></a></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'

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
        $('#answers_'+languages[x]+'_'+scale_id+' .btnaddanswer').click(addinput);
        $('#answers_'+languages[x]+'_'+scale_id+' .btndelanswer').click(deleteinput);
        $('#answers_'+languages[x]+'_'+scale_id+' .answer').focus(function(){
            if ($(this).val()==newansweroption_text)
            {
                $(this).val('');
            }
        });
    }
    $('#quickadd').dialog('close');
    $('#quickaddarea').val('');
    $('.tab-page:first .answertable tbody').sortable('refresh');                       
    updaterowproperties(); 
}








function quickadddialog()
{
    scale_id=removechars($(this).attr('id'));
    $('#quickadd').dialog('open');    
}
