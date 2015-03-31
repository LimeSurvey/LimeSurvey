$(document).on("change","#bounceprocessing",function(){
    hideShowParameters();
});
$(document).ready(function() {
    hideShowParameters();
});
function hideShowParameters(){
    var disabled=$("#bounceprocessing").val()!="L";
    $("#bounceaccounttype").prop("disabled",disabled);
    $("#bounceaccounthost").prop("disabled",disabled);
    $("#bounceaccountuser").prop("disabled",disabled);
    $("#bounceaccountpass").prop("disabled",disabled);
    $("#bounceaccountencryption").prop("disabled",disabled);
}
