$(document).on('ready pjax:scriptcomplete', function () {
    window.addEventListener("scroll", function() {
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            document.querySelector(".topbar").classList.add("scroll-shadow");
        } else {
            document.querySelector(".topbar").classList.remove("scroll-shadow");
        }
    });

    // topbar should not have editor class when left sidebar is closed
    if ($('#left-sidebar').is(':visible') == false) {
        $('.topbar').removeClass('editor')
    }
});
