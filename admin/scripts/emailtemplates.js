// $Id: saved.js 9330 2010-10-24 22:23:56Z c_schmitz $

$(document).ready(function(){
          $('.tabsinner').tabs(
          {
               show: loadHTMLEditor
          });       
          
});

/**
* This function loads each FCKeditor only when the tab is clicked and only if it is not already loaded
*/
function loadHTMLEditor(event, ui) 
{ 
    var selected = $(this).tabs('option', 'selected');
   // var oEditor = FCKeditorAPI.GetInstance('html');
   if ($('#'+ui.panel.id+' iframe').size()==0)
   {
        sFCKEditorInstanceName='oFCKeditor_'+$('#'+ui.panel.id+' textarea').attr('id');
        eval("if (typeof "+sFCKEditorInstanceName+" != 'undefined')"+sFCKEditorInstanceName+".ReplaceTextarea();");
   }
}

function fillin(tofield, fromfield)
{
    if (confirm(sReplaceTextConfirmation)) 
    {
        if (document.getElementById(tofield).readOnly == false)
        {   
            $('#'+tofield).val($('#'+fromfield).val());    
        }
        updateFCKeditor(tofield,$('#'+fromfield).val());

    }
}