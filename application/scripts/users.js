// $Id: users.js 9648 2011-01-07 13:06:39Z c_schmitz $

$(document).ready(function(){
    $("#users").tablesorter({
                            widgets: ['zebra'],            
                            sortList: [[1,0]] });
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
