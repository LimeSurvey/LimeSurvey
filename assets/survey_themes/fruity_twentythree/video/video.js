export var Video = function () {
    return {
        fixVideoHeight: function () {
            if (/iPad/i.test(navigator.userAgent)) {
                let videoElements = document.getElementsByName('video');
                videoElements.forEach(element => {
                    element.classList.add('os-based-height');
                });
            }
        }
    };
};


$(document).on('ready pjax:scriptcomplete', function () {
    Video.fixVideoHeight();
});
