$(document).on('ready pjax:scriptcomplete', function () {
    function makeTopbarSticky() {
        const topbar = $('#pjax-content > .menubar');
        const editor = $('#in_survey_common');
        const topbarOffset = topbar.offset().top;
    
        $(window).on('scroll', () => {
            if (window.pageYOffset >= topbarOffset) {
                topbar.addClass('sticky');
                topbar.css('width', topbar.parent().width());
                editor.css('padding-top', topbar.outerHeight(true));
            } else {
                topbar.removeClass('sticky');
                topbar.css('width', '');
                editor.css('padding-top', '');
            }
        });
    }

    makeTopbarSticky();
});