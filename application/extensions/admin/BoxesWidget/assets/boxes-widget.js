let cardHeaderRedirection = function () {
    $(".box-widget-card-header, .box-widget-card-text").click(function (event) {
        if ($(this).closest('.box-widget-card').attr("data-url")) {
            window.location.href = $(this).closest('.box-widget-card').attr("data-url");
        }
    })
};

$("#load-more").click(function (event) {
    event.preventDefault();
    event.stopPropagation()

    var page = $(this).attr('data-page')
    var limit = $(this).attr('data-limit')

    var url = '/surveyAdministration/boxList?page=' + page + '&limit=' + limit;
    let params = Object.fromEntries(new URLSearchParams(location.search));
    if (params.active) {
        url += '&active=' + params.active
    }

    $.ajax({
        url : url,
        type : 'GET',
        success: function (html, status) {
            if (html && html.includes("card")) {
                $("#load-more").attr('data-page', parseInt(page) + 1)
                $('.box-widget .box-widget-list').append(html);
                cardHeaderRedirection()
            } else {
                $("#load-more").hide()
            }
        },
        error: function (requestObject, error, errorThrown) {
            console.log('error');
        }
    });
})


$(document).on('ready pjax:scriptcomplete', function () {
    cardHeaderRedirection()
});
