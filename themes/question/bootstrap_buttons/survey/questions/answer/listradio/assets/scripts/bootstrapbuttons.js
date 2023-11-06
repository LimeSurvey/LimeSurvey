function doBootstrapRadio(){
    $(document).off('ready.bbothersetup').on('ready.bbothersetup', function() {

        // Setup the change event for bootstrap button's "other" option
        $(".button-item .bootstrap-radio").on('change.bbother', function() {
            name = $(this).attr('name');
            // conditionaly show or hide "other" input field
            if ($(this).val() === '-oth-') {
                var value =  $("#answer" + name + "othertextaux").val();
                $("#div" + name + "other").removeClass('ls-js-hidden');
                $("#answer" + name + "othertext").val(value).trigger("change");
            } else {
                $("#div" + name + "other").addClass('ls-js-hidden');
                $("#answer" + name + "othertext").val('').trigger("change");
                $("#answer" + name + "othertextaux").val('');
            }
        });

        // Trigger the change event for the checked bootstrap buttons
        $(".button-item .bootstrap-radio:checked").trigger("change");

        // Unbind this setup event
        $(document).off('ready.bbothersetup');
    });
}