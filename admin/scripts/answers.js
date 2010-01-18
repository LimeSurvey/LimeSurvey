// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
       $('.answertable tbody').sortable({containment:'parent',
                         distance:3});
       $('.btnaddanswer').click(addinput);
       $('.btndelanswer').click(deleteinput);                         
});


function deleteinput()
{

    // 1.) Check if there is at least one answe

     
    thisinputid=removechars(this.id); 
    thisquestionid=removechars($(this).parent().parent().attr('id'));
    ulparent=$(this).parents('ul');
    countanswers=$(ulparent[0]).children().length;
    if (countanswers>1)
    {
       // 2.) mark for deletion.
       if ($('#formquestionarea').data("deletedinputs")==undefined){  //add this ID to the "deletedinputs"  variable
         $('#formquestionarea').data("deletedinputs",thisinputid);
       }
       else {
            $('#formquestionarea').data("deletedinputs",$('#formquestionarea').data("deletedinputs")+" "+thisinputid);     
       }
       //3. Remove the answer li
       $("#editanswer_"+thisinputid).hide('fast',function(){
                $("#editanswer_"+thisinputid).remove();  
       });
       
       //4. Call the callback function in the question specific JS
       questiontype=$('#questioncontainer'+thisquestionid).data('questiontype');
       window[questiontype+"_deleteinput"] (thisquestionid,thisinputid);  
         
    }
    else
    {
       $.blockUI({message:"<p><br/>You cannot delete the last answer.</p>"});
       setTimeout(jQuery.unblockUI,1000);   
    }
}


function addinput()
{
    //todo: extract question id from parent element so inputelement creation is faster in cake
    this.src=imgpath+'ajax-loader-small.gif';
    thisinputid=removechars(this.id); 
    var addinput_questionid=removechars($(this).parent().parent().attr('id'));
    
    jQuery.getJSON( basepath+"builder/addinput/"+thisinputid,
                    function(data){
                        answerid=data.lastid;
                        afterid=data.afterid;  
                        $('#editanswer_'+afterid).after('<li id="editanswer_'+answerid+'" class="edititem"><input id="editanswerinput_'+answerid+'" value="New choice"></input><img id="btnaddanswer'+answerid+'" src="'+imgpath+'addanswer.png"><img id="btndelanswer'+answerid+'" src="'+imgpath+'deleteanswer.png"></li>');
                        $('#editanswer_'+afterid).next().show('normal');
                        $('#btnaddanswer'+answerid).click(addinput);
                        $('#btndelanswer'+answerid).click(deleteinput);
                        $('#btnaddanswer'+afterid).attr('src',imgpath+'addanswer.png');
                       //4. Call the callback function in the question specific JS
                       questiontype=$('#questioncontainer'+addinput_questionid).data('questiontype');
                       window[questiontype+"_addinput"] (addinput_questionid,answerid,'New choice');
                       $('#editanswerinput_'+answerid).change(changeinput);  
                    }
    );           
}

// This is a helper function to extract the question ID from a DOM ID element 
function removechars(strtoconvert){
  return strtoconvert.replace(/[a-zA-Z_]/g,"");
}
