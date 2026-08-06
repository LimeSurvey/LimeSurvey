function doBootstrapRadioOther() {
    $("input:radio.button-item.btn-check").on('change', function () {
        var name = $(this).attr('name');
        // conditionaly show or hide "other" input field
        if ($(this).val() === '-oth-') {
            var value = $("#answer" + name + "othertextaux").val();
            $("#div" + name + "other").removeClass('ls-js-hidden');
            $("#answer" + name + "othertext").val(value).trigger("change");
            copyOtherInputToHiddenField(name);
        } else {
            $("#div" + name + "other").addClass('ls-js-hidden');
            $("#answer" + name + "othertext").val("");
            $("#answer" + name + "othertextaux").val('');
        }
    });

    function copyOtherInputToHiddenField(name) {
        $("#answer" + name + "othertext").on('change keyup paste', function () {
            if ($(this).val()) {
                $("#answer" + name + "othertextaux").val($(this).val());
            }
            checkconditions(this.value, this.name, this.type);
        });
    }
}

function attachSuffixFocusBehavior(containerId, inputId) {
    var div    = document.getElementById(containerId);
    var suffix = div ? div.querySelector('.othertext-suffix-label') : null;
    if (!suffix) { return; }
    suffix.style.cursor = 'pointer';
    suffix.addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById(inputId).focus();
    });
}