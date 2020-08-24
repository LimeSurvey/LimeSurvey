function doBootstrapRadio(){
    $(".button-item .bootstrap-radio").change(function(){
        name = $(this).attr('name');
        // conditionaly show or hide "other" input field
        if ($(this).val() === '-oth-'){
            $("#div" + name + "other").removeClass('ls-js-hidden');
        } else {
            $("#div" + name + "other").addClass('ls-js-hidden');
            $("#answer" + name + "othertext").val('').trigger("change");
        }
    });
}
