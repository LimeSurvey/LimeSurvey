$(".card-header").click(function (event) {
    console.log($(this).parent().attr("data-url"))
    if ($(this).parent().attr("data-url")) {
        window.location.href = $(this).parent().attr("data-url");
    }
})



$("#load-more").click(function (event) {
    event.preventDefault();
    event.stopPropagation()

    var page = $(this).attr('data-page')
    var limit = $(this).attr('data-limit')


    var url = '/surveyAdministration/boxList?page=' + page + '&limit=' + limit;
    let params = new URLSearchParams(window.location.href.split('?')[1]);
    if (params.get('state')) {
        url += '&state=' + params.get('state')
    }
    console.log(url)

    $.ajax({
        url : url,
        type : 'GET',
        success: function (html, status) {
            if (html && html.includes("card")) {
                $("#load-more").attr('data-page', parseInt(page) + 1)
                $('.box-widget .row').append(html);
            } else {
                $("#load-more").hide()
            }
        },
        error: function (requestObject, error, errorThrown) {
            console.log('error');
        }
    });
})

