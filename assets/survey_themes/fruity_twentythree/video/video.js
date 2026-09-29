export var Video = function () {
    let fixVideoHeight = function () {
        $(document).on('ready pjax:scriptcomplete', function () {
            if (/iPad/i.test(navigator.userAgent)) {
                let videoElements = document.getElementsByTagName('video');
                for (let video of videoElements) {
                    video.classList.add('video-ipad'); // Replace "my-video-class" with your desired class name
                }
            }
        });
    };
    return {
        fixVideoHeight: fixVideoHeight
    };
};

window.video = new Video();
video.fixVideoHeight();