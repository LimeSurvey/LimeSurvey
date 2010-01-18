// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
       $('.answertable tbody').sortable({   containment:'parent',
                                            update:updaterowproperties,
                                            distance:3});
       $('.btnaddanswer').click(addinput);
       $('.btndelanswer').click(deleteinput);                         
});


function deleteinput()
{

    // 1.) Check if there is at least one answe
     
    countanswers=$(this).parent().parent().parent().children().length;
    if (countanswers>1)
    {
       // 2.) Remove the table row
       position=$(this).closest('tr').attr('id').substr(4);
       info=$(this).closest('table').attr('id').split("_"); 
       language=info[1];
       scale_id=info[2];
       languages=langs.split(';');
       var x;
       for (x in languages)
       {
            tablerow=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' #row_'+position);
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
       $.blockUI({message:"<p><br/>You cannot delete the last answer.</p>"});
       setTimeout(jQuery.unblockUI,1000);   
    }
    updaterowproperties();     
}


function addinput()
{
    position=$(this).closest('tr').attr('id').substr(4);
    info=$(this).closest('table').attr('id').split("_"); 
    language=info[1];
    scale_id=info[2];
    newposition=Number(position)+1;
    languages=langs.split(';');
    var x;
    for (x in languages)
    {
        tablerow=$('#tabpage_'+languages[x]).find('#answers_'+languages[x]+'_'+scale_id+' #row_'+position);
        if (x==0) {
            inserthtml='<tr id="row_'+newposition+'" style="display:none;"><td><img src="../images/handle.png" /></td><td><input class="code" type="text" maxlength="5" size="5" value="'+getNextCode($('#tabpage_'+languages[x]).find('#row_'+position+' .code').val())+'" /></td><td><input type="text" size="80" class="answer" value="New answer option"></input><img src="../images/edithtmlpopup.png" class="btnaddanswer" /></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
        }
        else
        {
            inserthtml='<tr id="row_'+newposition+'" style="display:none;"><td>&nbsp;</td><td>&nbsp;</td><td><input type="text" size="80" class="answer" value="New answer option"></input><img src="../images/edithtmlpopup.png" class="btnaddanswer" /></td><td><img src="../images/addanswer.png" class="btnaddanswer" /><img src="../images/deleteanswer.png" class="btndelanswer" /></td></tr>'
        }
        tablerow.after(inserthtml);
        tablerow.next().find('.btnaddanswer').click(addinput);
        tablerow.next().find('.btndelanswer').click(deleteinput);
        tablerow.next().fadeIn(800);
        tablerow.next().find('.code').blur(updatecodes);
    }
    
    if(languagecount>1)
    {
        
    }
    
    $('.answertable tbody').sortable('refresh');
    updaterowproperties();
}

// This function adjust the alternating table rows and IDs and names
function updaterowproperties()
{
  $('.answertable tbody').each(function(){
      info=$(this).closest('table').attr('id').split("_"); 
      language=info[1];
      scale_id=info[2];
      var highlight=true;
      var rownumber=0;
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
         $(this).find('.assessment').attr('id','assessment_'+language+'_'+rownumber+'_'+scale_id);
         $(this).find('.assessment').attr('name','assessment_'+language+'_'+rownumber+'_'+scale_id);
         highlight=!highlight;
         rownumber++;
      })
      $('#answercount_'+scale_id).val(rownumber);
  })  
}

// This is a helper function to extract the question ID from a DOM ID element 
function removechars(strtoconvert){
  return strtoconvert.replace(/[a-zA-Z_]/g,"");
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