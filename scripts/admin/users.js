// $Id: users.js 9330 2010-10-24 22:23:56Z c_schmitz $

$(document).ready(function(){
$('#create_survey,#configurator,#participant_panel,#create_user,#delete_user,#manage_template,#manage_label').click(function() {
   if($(this).attr('checked')==false)
    {
        $('#superadmin').attr('checked', false);
    }
});    
    $("#users").tablesorter({
                            widgets: ['zebra'],            
                            sortList: [[1,0]]});
	var tog=false;
	$("#checkall").click(function() {
	    $("input[type=checkbox]").attr("checked",!tog);
		tog=!tog;
});
});
