$('.sidebar-icon').on('click', function (e) {
    $('.side-panel').addClass('d-none')

    if ($(this).find('.btn-icon').hasClass('active')) {
        $(this).find('.btn-icon').removeClass('active')
    } else {
        $($(this).attr('data-target')).removeClass('d-none')
        $('.sidebar-icon .btn-icon').removeClass('active')
        $(this).find('.btn-icon').addClass('active')
    }
})

$('.btn-close-lime').on('click', function (e) {
    $('.side-panel').addClass('d-none')
    $('.sidebar-icon .btn-icon').removeClass('active')
})

$('#breadcrumb-container, #ls-activate-survey, #preview_survey_button')
    .hide()

$(".ls-breadcrumb").append($(".side-menu-logo"));
$(window).on("load scroll", function () {
    if ($(window).scrollTop() > 65) {
        $(".ls-breadcrumb .side-menu-logo").removeClass('d-none');
    } else {
        $(".ls-breadcrumb .side-menu-logo").addClass('d-none');
    }
});
