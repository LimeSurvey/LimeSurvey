$(document).on("ready pjax:scriptcomplete", function () {
    window.addEventListener("scroll", function () {
        if (
            document.body.scrollTop > 50 ||
            document.documentElement.scrollTop > 50
        ) {
            document.querySelector(".topbar").classList.add("scroll-shadow");
        } else {
            document.querySelector(".topbar").classList.remove("scroll-shadow");
        }
    });

    // when click export button, as sticky-top z-index is higher than modal, both are shown together
    // so remove that when click button

    $("#trigger_exportTypeSelector_button").on("click", function () {
        let $topbar = $(this).closest(".topbar");
        $topbar.removeClass("sticky-top");
    });

    // Add the "sticky-top" class to the top bar when the modal is closed
    $("#selector__exportTypeSelector-modal").on("hidden.bs.modal", function () {
        let $topbar = $(this).closest(".topbar");
        $topbar.addClass("sticky-top");
    });
});
