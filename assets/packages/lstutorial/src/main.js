import Tour from '../lib/bootstrap-tour.js';
import globalTourObject from './tours/global-tour-object.js';

const TourLibrary = function () {
    'use strict';

    const _getIsTourActive = () => {
            let isTourActive = window.localStorage.getItem('lstutorial-is-tour-active') || false;
            return isTourActive;
        },
        _setTourActive = (tourName) => {
            window.localStorage.setItem('lstutorial-is-tour-active', tourName);
        },

        _setNoTourActive = () => {
            window.localStorage.removeItem('lstutorial-is-tour-active');
        },
        
        clearActiveTour = () => {
            if (typeof _actionActiveTour === 'function')
                _actionActiveTour.end();

            _setNoTourActive();
        },
        initTour = (tourName) => {
            return new Promise((resolve, reject) => {
                if ((_getIsTourActive() !== false) && (_getIsTourActive() !== tourName)) {
                    clearActiveTour();
                    reject();
                }
                globalTourObject.get(tourName).then((tourObject) => {
                    _setTourActive(tourName);

                    tourObject.onEnd = () => {
                        _setNoTourActive();
                    };

                    _actionActiveTour = new Tour(tourObject);
                    _actionActiveTour.init();

                    resolve(_actionActiveTour);
                }, console.log);
            });
        };

    let _activeTour = _getIsTourActive();
    let _actionActiveTour = null;

    if (_activeTour !== false && (typeof _actionActiveTour !== 'function')) {
        initTour(_activeTour);
    }

    return {
        clearActiveTour: clearActiveTour,
        initTour: initTour
    };
};


$(document).on('ready pjax:complete', function () {
    if(typeof window.tourLibrary !== 'object')
        window.tourLibrary = TourLibrary();

    $('#selector__welcome-modal--starttour').on('click', function (e) {
        $(e.currentTarget).closest('.modal').modal('hide');
        window.tourLibrary.clearActiveTour();
        window.tourLibrary.initTour('firstStartTour').then(
            (firstStartTour) => {
                if(firstStartTour.ended())
                    firstStartTour.restart();
                else
                    firstStartTour.start(true);
            },
            (err) => {
                console.log('Couldn\'t be loaded!');
                console.log(err);
            }
        );
    });
});
