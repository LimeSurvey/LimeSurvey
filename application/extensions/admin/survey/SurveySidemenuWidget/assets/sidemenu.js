$('.btn-close-lime').on('click', function (e) {
    $('.side-panel').addClass('d-none')
    $('.sidebar-icon .btn-icon').removeClass('active')
})
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


$(document).on('ready  pjax:scriptcomplete', function(){
    $('#breadcrumb-container').hide()
    $(".ls-breadcrumb").append($(".side-menu-logo"));

});
$(window).on("load scroll", function () {
    if ($(window).scrollTop() > 0) {
        $(".ls-breadcrumb .side-menu-logo").removeClass('d-none');
        $(".topbar").css("background-color", "white");
    } else {
        $(".ls-breadcrumb .side-menu-logo").addClass('d-none');
        $(".topbar").css("background-color", "inherit");
    }
});
