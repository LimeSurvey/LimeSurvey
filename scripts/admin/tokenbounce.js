function hideShowParameters(){
    var bouncedisabled=$("#bounceprocessing").val()!="L";
    $("#bounceaccounttype").prop("disabled",bouncedisabled).select2({ disabled: bouncedisabled });// Must work without, but : work on ready, but no onchange ?
    $("#bounceaccounthost").prop("disabled",bouncedisabled);
    $("#bounceaccountuser").prop("disabled",bouncedisabled);
    $("#bounceaccountpass").prop("disabled",bouncedisabled);
    $("#bounceaccountencryption").prop("disabled",bouncedisabled).select2({ disabled: bouncedisabled });
}
