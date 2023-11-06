/**
 * Welcome page panels animations
 */
import LOG from './lslog';

export default function panelsAnimation(){
    setTimeout(() => {
        LOG.log('Triggering panel animation');
        /**
         * Panel shown one by one
         */
        $('.panel').each(function(i){
            $(this).delay((i++) * 200).animate({opacity: 1, top: '0px'}, 200);
        });

        /**
         * Rotate last survey/question
         */
        function rotateLast(){
        const $rotateShown = $('.rotateShown');
        const $rotateHidden = $('.rotateHidden');
        $rotateShown.hide('slide', { direction: 'left', easing: 'easeInOutQuint'}, 500, function(){
            $rotateHidden.show('slide', { direction: 'right', easing: 'easeInOutQuint' }, 1000);
        });

        $rotateShown.removeClass('rotateShown').addClass('rotateHidden');
        $rotateHidden.removeClass('rotateHidden').addClass('rotateShown');
        window.setTimeout( rotateLast, 5000 );

        }

        if ( $( "#last_question" ).length ) {
            $('.rotateHidden').hide();
            window.setTimeout( rotateLast, 2000 );
        }
    }, 350);
};
