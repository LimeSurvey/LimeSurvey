$('.text-option-inherit').on('change', function (e) {
    var newValue = $(this).find('.btn-check:checked').val();
    var parent = $(this).parent().parent();
    var inheritValue = parent.find('.inherit-edit').data('inherit-value');
    var savedValue = parent.find('.inherit-edit').data('saved-value');
    if (newValue === 'Y') {
        parent.find('.inherit-edit').addClass('d-none').removeClass('show').val(inheritValue);
        parent.find('.inherit-readonly').addClass('show').removeClass('d-none');
    } else {
        var inputValue = (savedValue === inheritValue) ? "" : savedValue;
        parent.find('.inherit-edit').addClass('show').removeClass('d-none').val(inputValue);
        parent.find('.inherit-readonly').addClass('d-none').removeClass('show');
    }
});