// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
    $("#users").tablesorter({sortList: [[1,0]] });
	var tog=false;
	$("#checkall").click(function() {
	    $("input[type=checkbox]").attr("checked",!tog);
		tog=!tog;
});
$('#create_survey,#configurator,#create_user,#delete_user,#manage_template,#manage_label').click(function() {
  if($(this).attr('checked')==false)
  {
  $('#superadmin').attr('checked', false);
  }
});
});
