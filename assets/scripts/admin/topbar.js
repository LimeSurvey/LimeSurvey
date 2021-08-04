$(document).on('ready pjax:scriptcomplete', function () {
    function makeTopbarSticky() {
        const topbar = $('.menubar:not(.surveymanagerbar)');
        if (topbar.length == 0) {
            return;
        }
        const topbarOffset = topbar.offset().top;

        // When the topbar is floated, the space it occupies is released, so the rest of the content "jumps" up.
        // To avoid that, a "placeholder" div is added, with the same height as the topbar.
        // The placeholder is only visible (by css) when the topbar has the 'sticky' class.
        topbar.after('<div id="sticky-topbar-placeholder" style="min-height: ' + topbar.outerHeight(true) + 'px;"></div>');

        $(window).on('scroll', () => {
            if (window.pageYOffset >= topbarOffset) {
                if (!topbar.hasClass('sticky')) {
                    topbar.addClass('sticky');
                    topbar.css('width', topbar.parent().width());
                }
            } else {
                topbar.removeClass('sticky');
                topbar.css('width', '');
            }
        });

        // Handle the topbar's parent element resizing.
        // Note that this resizing doesn't only happen when the window size changes, but also when the sidemenu is resized.
        const resizeObserver = new ResizeObserver(() => {
            // Adjust the topbar width if it's floated
            if (topbar.hasClass('sticky')) {
                topbar.css('width', topbar.parent().width());
            }
            // Also adjust the placeholder's height, because changing the topbar width can also affect it's height
            $('#sticky-topbar-placeholder').css('min-height', topbar.outerHeight(true) + "px");
        });

        // Observe the topbar parent
        resizeObserver.observe(topbar.parent().get(0));
    }

    makeTopbarSticky();
});