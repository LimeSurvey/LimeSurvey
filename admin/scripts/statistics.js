function hide(element) {
    $('#'+element).slideUp('normal');
}
function show(element) {
    $('#'+element).slideDown('normal');
}

$(document).ready(function(){
     $('#filterinc').change(function(){
         if ($('#filterinc').val()=="filter") {
            $('#noncompleted').attr("checked", "");
             $('#vertical_slide').slideUp('normal'); 
         }
         else
         {
             $('#vertical_slide').slideDown('normal'); 
         }
     })
     $('#usegraph').click( function(){
        if ($('#grapherror').length>0)
        { 
            $('#grapherror').show();
            $('#usegraph').attr('checked',false);
        }
     })
});

function showhidefilters(value) {
 if(value == true) {
   hide('filterchoices');
 } else {
   show('filterchoices');
 }
}