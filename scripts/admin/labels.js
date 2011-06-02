// $Id: labels.js 8649 2010-04-28 21:38:53Z c_schmitz $

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
