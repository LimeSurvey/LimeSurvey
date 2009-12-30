// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
   $("#filterduplicatetoken").change(function(){
    if ($("#filterduplicatetoken").attr('checked')==true)
    {
        $("#lifilterduplicatefields").slideDown(); 
    }
    else
    {
        $("#lifilterduplicatefields").slideUp(); 
    }
   }) 
});
