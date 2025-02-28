/**
 * Welcome page animations
 * NB: Bootstrap 5 replaced panels with cards
 */
export default function panelsAnimation() {
    setTimeout(() => {
        /**
         * Rotate last survey/question
         */
        function rotateLast() {
            const $rotateShown = $('.rotateShown');
            const $rotateHidden = $('.rotateHidden');
            $rotateShown.hide('slide', {direction: 'left', easing: 'easeInOutQuint'}, 500, function () {
                $rotateHidden.show('slide', {direction: 'right', easing: 'easeInOutQuint'}, 1000);
            });

            $rotateShown.removeClass('rotateShown').addClass('rotateHidden');
            $rotateHidden.removeClass('rotateHidden').addClass('rotateShown');
            window.setTimeout(rotateLast, 5000);

        }

        if ($("#last_question").length) {
            $('.rotateHidden').hide();
            window.setTimeout(rotateLast, 2000);
        }
    }, 350);
}
