import Tour from '../lib/bootstrap-tour.js';
import firstStartTourObject from './tours/first-start-tour.js';

class TourLibrary {
    constructor(){
        this.activeTour = this._getIsTourActive();

        if( this.activeTour !== false){
            const fnActiveTour = this[this.activeTour];
            $(document).on('ready pjax:complete', ()=>{
                fnActiveTour(false);
            });    
        }    
    }

    _getIsTourActive() {
        let isTourActive = window.localStorage.getItem('lstutorial-is-tour-active') || false; 
        return isTourActive;
    }
    _setTourActive(tourName) {
        window.localStorage.setItem('lstutorial-is-tour-active',tourName);
    }
    
    _setNoTourActive(){
        window.localStorage.removeItem('lstutorial-is-tour-active');
    }

    firstStartTour(force=true) {
        
        firstStartTourObject.onEnd = ()=>{
            this._setNoTourActive();
        };

        const actionFirstStartTour = new Tour(firstStartTourObject);
        actionFirstStartTour.init();
        this._setTourActive('firstStartTour');

        if(force === true) actionFirstStartTour.setCurrentStep(0);
        
        actionFirstStartTour.start();
    }
};

window.tourLibrary = new TourLibrary();

$(document).on('ready pjax:complete', function(e){
    $('#selector__welcome-modal--starttour').on('click', function(e){
        $(e.currentTarget).closest('.modal').modal('hide');
        window.tourLibrary.firstStartTour();
    });
});
