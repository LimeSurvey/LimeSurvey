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
});
