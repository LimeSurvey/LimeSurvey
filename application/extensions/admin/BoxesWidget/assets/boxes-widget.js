$(".card-header").click(function(event){
    console.log($(this).parent().attr("data-url"))
    if ($(this).parent().attr("data-url")) {
        window.location.href = $(this).parent().attr("data-url");
    }
})

$("#load-more").click(function(event){
    event.preventDefault();
    event.stopPropagation()

    var page = $(this).attr('data-page')
    var limit = $(this).attr('data-limit')

    $.ajax({
        url : '/surveyAdministration/boxList?page=' + page + '&limit=' + limit,
        type : 'GET',
        success: function(html, status){
            console.log(html, status)
            if (html) {
                $("#load-more").attr('data-page', parseInt(page) + 1 )
                $('.box-widget .row').append(html);

            }
            else
                $("#load-more").hide()
        },
        error: function(requestObject, error, errorThrown){
            console.log(error);
        }
    });
})
