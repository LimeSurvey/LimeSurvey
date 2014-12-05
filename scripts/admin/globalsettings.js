$(document).on('click',"[data-copy] :submit",function(){
    $("form :input[value='"+$(this).val()+"']").click();
});
$(document).ready(function(){
    $("[data-copy]").each(function(){
        $(this).html($("#"+$(this).data('copy')).html());
    });
    $('[type=password]').attr('autocomplete', 'off');
    $('#btnRemove').click(removeLanguages);
    $('#btnAdd').click(addLanguages);
    $("#frmglobalsettings").submit(UpdateRestrictedLanguages);
});

function removeLanguages(ui,evt)
{
   $('#includedLanguages').copyOptions('#excludedLanguages');
   $("#excludedLanguages").sortOptions();
   $("#includedLanguages").removeOption(/./,true);
}

function addLanguages(ui,evt)
{
   $('#excludedLanguages').copyOptions('#includedLanguages');
   $("#includedLanguages").sortOptions();
   $("#excludedLanguages").removeOption(/./,true);
}

function UpdateRestrictedLanguages(){
    aString='';
    $("#includedLanguages option").each(function(){
       aString=aString+' '+$(this).val();
    });
    $('#restrictToLanguages').val($.trim(aString));
}

