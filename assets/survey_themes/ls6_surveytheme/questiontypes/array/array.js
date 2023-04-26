export var ArrayScripts = function () {
    var addEntryMarker = function addEntryMarker(element) {
        element.addClass('success-border');
    };

    var removeEntryMarker = function removeEntryMarker(element) {
        element.removeClass('success-border');
    };

    var initArrayEvents = function () {
        $(document).on(
            "change",
            '.array-multi-flexi .multiflexitext.text-item, .array-multi-flexi .answer-item .form-select, .array-flexible-dual-scale .answer-item .form-select, .array-multi-flexi-text input.form-control',
            function () {
                var enteredValue = $(this).val();
                if (enteredValue !== undefined && enteredValue !== '') {
                    addEntryMarker($(this));
                } else {
                    removeEntryMarker($(this));
                }
            });
    }

    return {
        initArrayEvents: initArrayEvents,
    };
}
// register to global scope
window.ArrayScripts = ArrayScripts;