/**
 * Welcome page card animations
 * NB: Bootstrap 5 replaced panels with cards
 */
import LOG from './lslog';

export default function panelsAnimation() {
    setTimeout(() => {
        LOG.log('Triggering card animation');
        /**
         * Card shown one by one
         */
        /** 
         * @todo Added .welcome to restrict this to the welcome page (as it was intended), but the animation doesn't work
         *       anyway because the cards are already visible. On older versions, the panels (now cards) were hidden by
         *       the definition of ".welcome .panel" in adminbasics.css. We should either fix all the CSS related to these
         *       panels, or clean it and remove this CSS if the animation is not needed.
         */
        document.querySelectorAll(".welcome .card").forEach(function (e, i) {
            setTimeout(() => {
                e.animate({
                    top: '0px',
                    opacity: 1
                }, {
                    duration: 200,
                    fill: 'forwards'
                });
            }, i * 200);
        });

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
