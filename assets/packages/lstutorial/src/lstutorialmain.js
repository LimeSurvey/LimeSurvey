import 'popper.js';
import '../scss/main.scss';

import Tour from '../lib/bootstrap-tourist.js';
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

        _setNoTourActive = (tid) => {
            window.localStorage.removeItem('lstutorial-is-tour-active');
            
            if(tid !== undefined) {
                $.post(LS.data.baseUrl+(LS.data.urlFormat == 'path' ? '/admin/tutorial/sa/triggerfinished/tid/' : '?r=admin/tutorial/sa/triggerfinished/tid/')+tid);
            }
        },
        
        clearActiveTour = () => {
            if (typeof _actionActiveTour === 'object' && _actionActiveTour !== null) {
                _actionActiveTour.end();
            }

            _setNoTourActive();
        },
        getCurrentStep = () => {
            if ((_getIsTourActive() !== false)) {
                return globalTourObject.getCurrentStep();
            }
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
                        _setNoTourActive(tourObject.tid);
                    };
                    
                    tourObject.debug = window.debugState.backend;
                    tourObject.framework = "bootstrap3";

                    _actionActiveTour = new Tour(tourObject);
                    window.addEventListener('resize', ()=>{
                        _actionActiveTour.redraw();
                    });
                    
                    resolve(_actionActiveTour);
                }, console.ls.err);
            });
        },
        triggerTourStart = (tutorialName) => {
            clearActiveTour();
            initTour(tutorialName).then(
                (startedTutorial) => {
                    if(startedTutorial.ended())
                        startedTutorial.restart();
                    else
                        startedTutorial.start(true);
                },
                (err) => {
                    console.ls.log('Couldn\'t be loaded!');
                    console.ls.error(err);
                }
            );
        };

    let _activeTour = _getIsTourActive();
    let _actionActiveTour = null;

    if (_activeTour !== false && (typeof _actionActiveTour !== 'function')) {
        initTour(_activeTour).then(
            (startedTutorial) => {
                if(startedTutorial.ended()){
                    startedTutorial.restart();
                } else {
                    setTimeout(function(){ startedTutorial.start();}, 1);
                }
            },
            (err) => {
                console.ls.log('Couldn\'t be loaded!');
                console.ls.error(err);
            }
        );
    }

    return {
        triggerTourStart: triggerTourStart,
        clearActiveTour: clearActiveTour,
        initTour: initTour,
        _actionActiveTour: _actionActiveTour,
        getCurrentStep: getCurrentStep
    };
};


$(document).on('ready pjax:scriptcomplete', function () {
    if(typeof window.tourLibrary === 'undefined'){
        window.tourLibrary = TourLibrary();
    }

    $('#selector__welcome-modal--starttour').on('click', function (e) {
        $(e.currentTarget).closest('.modal').modal('hide');
        window.tourLibrary.triggerTourStart('firstStartTour');
    });
});
