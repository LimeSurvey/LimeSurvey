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

$('#survey_active').change(function (e) {
    if ($(this).find(":selected").val()) {
        var filter = $(this).find(":selected").val()
        let url = window.location.href;

        if (location.href.indexOf("?") === -1) {
            window.location = location.href += "?state=" + filter;
        } else {
            const params = url.split('?')[1]
            const domain = url.split('?')[0]
            const searchParams = new URLSearchParams(params);
            searchParams.set('state', filter)
            window.location.href =  domain + '?' + searchParams.toString();
        }
    } else {
        let url = window.location.href;
        const params = new URLSearchParams(url.split('?')[1]);
        params.delete("state");
        console.log(params.toString())
    }
});
