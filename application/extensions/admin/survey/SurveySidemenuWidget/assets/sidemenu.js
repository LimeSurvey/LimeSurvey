$('.btn-close-lime').on('click', function (e) {
    $('.side-panel').addClass('d-none');
    $('.sidebar-icon .btn-icon').removeClass('active');
})
$('.sidebar-icon').on('click', function (e) {
    // Check if the element is disabled
    if ($(this).hasClass('disabled')) {
        e.preventDefault();
        return false;
    }

    $('.side-panel').addClass('d-none');

    if ($(this).find('.btn-icon').hasClass('active')) {
        $(this).find('.btn-icon').removeClass('active');
    } else {
        $($(this).attr('data-target')).removeClass('d-none');
        $('.sidebar-icon .btn-icon').removeClass('active');
        $(this).find('.btn-icon').addClass('active');
    }
});


$(document).on('ready  pjax:scriptcomplete', function(){
    $('.ls-breadcrumb').hide();
    $('.topbar.editor .container-fluid .row').prepend($('.side-menu-logo'));
});
$(window).on("load scroll", function () {
    if ($(window).scrollTop() > 61) {
        $('.side-menu-logo').removeClass('d-none');
        $('.topbar.editor').addClass('ms-0');
        // $(".topbar").css("background-color", "white");
    } else {
        $('.side-menu-logo').addClass('d-none');
        $('.topbar.editor').removeClass('ms-0');
        // $(".topbar").css("background-color", "inherit");
    }
});
