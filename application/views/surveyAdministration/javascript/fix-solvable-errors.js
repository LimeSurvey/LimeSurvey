$('#ajaxAllConsistency').on('click', function (e) {
    e.preventDefault();
    var items = $('.selector__fixConsistencyProblem').map(function (i, item) {
        return function () {
            return $.ajax({
                url: $(item).attr('href'),
                beforeSend: function () {
                    $(item).prop('disabled', true).append('<i class=\"ri-loader-2-fill remix-pulse\"></i>');
                },
                complete: function (jqXHR, status) {
                    if (status == 'success')
                        $(item).remove();
                    else
                        console.log(jqXHR);
                }
            });
        };
    });
    var runIteration = function (arrayOfLinks, iterator) {
        iterator = iterator || 0;
        if (iterator < arrayOfLinks.length) {
            arrayOfLinks[iterator]().then(function () {
                iterator++;
                runIteration(arrayOfLinks, iterator);
            });
        }
    };
    runIteration(items);
});
