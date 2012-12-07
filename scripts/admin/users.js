$(document).ready(function(){
  $('.withadmin').click(function() {
  if(!$(this).is(':checked'))
    {
    $('#superadmin').attr('checked', false);
    }
  });
  $('#superadmin').click(function() {
  if($(this).is(':checked'))
    {
    $('.withadmin').attr('checked', true);
    }
  });
// Seems deprecated
$("#users").tablesorter({
                        widgets: ['zebra'],
                        sortList: [[1,0]]});
var tog=false;
$("#checkall").click(function() {
  $("input[type=checkbox]").attr("checked",!tog);
tog=!tog;
});
});
