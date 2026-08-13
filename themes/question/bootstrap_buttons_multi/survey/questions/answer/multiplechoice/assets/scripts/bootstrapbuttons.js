/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 *
 * This javascript updates values of input fields, shows "other" input field when "Other" checkbox button is checked
 * and triggers validation
 */


$(document).ready(function () {
    // OTHER
    $(".button-checkbox-other-value").each(function (index, element) {
        var myfname = $(this).data('name');
        var baseName = myfname.replace(/_Cother$/, '');
        if ($(this).val()) {
            // "other" input field
            $("#answer" + baseName + "other").val($(this).val());
        }
        // the "Other" button can be checked without a value, so also rely on its state
        if ($(this).val() || $("#answer" + myfname + "cbox").is(':checked')) {
            $("#" + baseName + "other-div").removeClass('d-none');
        }
        // execute validation
        checkconditions($("#answer" + baseName + "other").val(), myfname, this.type);
    });

    $(".bootstrap-checkbox-other").change(function () {
        var name = $(this).data('name');
        // conditionally show or hide "other" input field
        if ($(this).is(':checked')) {
            $("#" + name + "-div").removeClass('d-none');
        } else {
            $("#" + name + "-div").addClass('d-none');
            $("#answer" + name).val('');
        }
    });

    $(".bootstrap-other-input").on('change keyup paste', function () {
        var name = $(this).data('name');
        if (!$(this).val()) {
            $("#java" + $(this).attr('name')).val('');
        }
        checkconditions(this.value, this.name, this.type);
    });

});

/**
 * Makes an other-text suffix label focus its associated input when clicked.
 * @param {string} containerId - The ID of the container holding the suffix label.
 * @param {string} inputId - The ID of the input to focus.
 */
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