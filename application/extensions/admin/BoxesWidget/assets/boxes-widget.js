let cardHeaderRedirection = function () {
    $(".box-widget-card-header, .box-widget-card-text, .box-widget-card.card-link").off('click.boxesWidget').on('click.boxesWidget', function (event) {
        // Links inside the card (e.g. the title link) navigate themselves
        if ($(event.target).closest('a, button').length) {
            return;
        }
        if ($(this).closest('.box-widget-card').attr("data-url")) {
            window.location.href = $(this).closest('.box-widget-card').attr("data-url");
        }
    })

    // Link cards are links themselves (role="link"): activate them with Enter
    $('.box-widget-card[data-url][role="link"]')
        .off('keydown.boxesWidget')
        .on('keydown.boxesWidget', function (event) {
            if (event.key !== 'Enter' || event.target !== this) {
                return
            }

            event.preventDefault()
            window.location.href = $(this).attr('data-url')
        })
};

let checkLoadMoreButton = function () {
    var count = parseInt($('#load-more').attr('data-max-count'))
    var numItems = $('.box-widget-card').length
    if (numItems >= count) {
        $('.box-widget-loadmore').hide()
    }
};

$("#load-more").click(function (event) {
    event.preventDefault();
    event.stopPropagation()

    var page = $(this).attr('data-page')
    var limit = $(this).attr('data-limit')

    // Works with both URL formats (get and path)
    var urlParams = {page: page, limit: limit};
    let params = Object.fromEntries(new URLSearchParams(location.search));
    if (params.active) {
        urlParams.active = encodeURIComponent(params.active)
    }
    var url = LS.createUrl('surveyAdministration/boxList', urlParams);

    $.ajax({
        url : url,
        type : 'GET',
        success: function (html, status) {
            if (html && html.includes("card")) {
                $("#load-more").attr('data-page', parseInt(page) + 1)
                var previousCount = $('.box-widget .box-widget-list .box-widget-card').length;
                $('.box-widget .box-widget-list').append(html);
                cardHeaderRedirection()
                // Move the focus to the first loaded survey, the "Load more" link may be hidden now
                $('.box-widget .box-widget-list .box-widget-card').eq(previousCount)
                    .find('.box-widget-card-title-link').trigger('focus');
            }
            checkLoadMoreButton()
        },
        error: function (requestObject, error, errorThrown) {
            console.log('error');
        }
    });
})


$(document).on('ready pjax:scriptcomplete', function () {
    cardHeaderRedirection()
    checkLoadMoreButton()
});
