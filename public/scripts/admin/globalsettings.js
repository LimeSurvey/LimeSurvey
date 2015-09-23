$(document).on('click',"[data-copy] :submit",function(){
    $("form :input[value='"+$(this).val()+"']").click();
});
$(document).on('submit',"#frmglobalsettings",function(){
    $('#frmglobalsettings').attr('action',$('#frmglobalsettings').attr('action')+location.hash);// Maybe validate before ?
});

$(document).ready(function(){
    $("[data-copy]").each(function(){
        $(this).html($("#"+$(this).data('copy')).html());
    });
    $('[type=password]').attr('autocomplete', 'off');
});
