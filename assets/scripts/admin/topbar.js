$(document).on('ready pjax:scriptcomplete', function () {
    window.addEventListener("scroll", function() {
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            document.getElementById("ls-topbar").classList.add("scroll-shadow");
        } else {
            document.getElementById("ls-topbar").classList.remove("scroll-shadow");
        }
    });
});
