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

    $("#user_type").change(UsertypeChange);
    UsertypeChange();
});


function UsertypeChange(ui,evt)
{
    ldap_user=($("#user_type").val()=='LDAP');
    if (ldap_user==true) {ldap_user='disabled';}
    else {ldap_user='';}
    $("#new_email").prop('disabled',ldap_user);
    $("#new_full_name").prop('disabled',ldap_user);
}