$(document).ready(function(){
    $('#create_survey,#configurator,#participant_panel,#create_user,#delete_user,#manage_template,#manage_label').click(function() {
        if($(this).attr('checked')==false)
        {
            $('#superadmin').attr('checked', false);
        }
    });    
    $("#users").tablesorter({
        widgets: ['zebra'],            
        sortList: [[2,0]]});
    $("#user-template-permissions").tablesorter({
        widgets: ['zebra'],            
        sortList: [[0,0]]});
    var tog=false;
    $("#checkall").click(function() {
        $("input[type=checkbox]").prop("checked",!tog);
        tog=!tog;
    });
});
