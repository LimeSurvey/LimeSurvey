// $Id$

$(document).ready(function(){
 $('#btnDumpLabelSets').click(function(){
    if ($('#labelsets > option:selected').size()==0)
    {
        alert(strSelectLabelset);
        return false;
    }   
    else
    {
        return true;
    }
 });
});
