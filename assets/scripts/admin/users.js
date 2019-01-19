
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){

    bindButtons();

    $("#user_type").change(UsertypeChange);
    UsertypeChange();
});


function triggerRunAction(el){
    return function(){
        runAction(el);
    }
}

function runAction(el){
    var url = $(el).data('url'),
            action = $(el).data('action'),
            user = $(el).data('user'),
            uid = $(el).data('uid');
        var form = $('<form></form>');
        form.attr('method','post');
        form.attr('action',url);
        form.append('<input type="hidden" name="uid" value="'+uid+'" />');
        form.append('<input type="hidden" name="action" value="'+action+'" />');
        form.append('<input type="hidden" name="user" value="'+user+'" />');
        form.append('<input type="hidden" name="'+LS.data.csrfTokenName+'" value="'+LS.data.csrfToken+'" />');
        form.appendTo('body');
        form.submit();
}

function bindButtons(){
    $('.action_usercontrol_button').on('click', function(){
        runAction(this);
    });
    $('input[name="alltemplates"]').on('switchChange.bootstrapSwitch', function(event, state) {
        $('input[id$="_use"]').prop('checked',state).trigger('change');
    });

}

function UsertypeChange(ui,evt)
{
    ldap_user=($("#user_type").val()=='LDAP');
    if (ldap_user==true) {ldap_user='disabled';}
    else {ldap_user='';}
    $("#new_email").prop('disabled',ldap_user);
    $("#new_full_name").prop('disabled',ldap_user);
}
