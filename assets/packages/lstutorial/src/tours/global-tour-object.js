//import _ from 'lodash';
import {map as _map, reduce as _reduce} from 'lodash';


const globalTourObject = function(){
    const getBasedUrls = (/(\/index.php)?\?r=admin/.test(window.location.href)),
    
        combineParams = function(params){
            const getBasedUrls = false;
            if(params === false) return '';

            const returner = (getBasedUrls ? '?' :'/') + _reduce(params, (urlParams, value, key)=>{ 
                return urlParams + (
                    getBasedUrls ? 
                        (urlParams === '' ? '' : '&')+key+'='+value 
                        : (urlParams === '' ? '' : '/')+key+'/'+value
                );
            }, '');
            return returner;
        },
        filterUrl = function(url,params=false, forceGet=false){
            if(url.charAt(0) == '/')
                url = url.substring(1);
            
            const baseUrl = (getBasedUrls || forceGet) ? '?r=admin/' : '/admin/';
            const containsIndex = (/\/index.php\/?/.test(window.location.href));
            const returnUrl = window.LS.data.baseUrl+(containsIndex ? '/index.php' : '')+baseUrl+url+combineParams(params);

            return returnUrl;

        },
        _preparePath = function(path){
            if(typeof path === 'string')
                return path;
            
            return RegExp(path.join());
        },
        _prepareMethods = function(tutorialObject){
            'use strict';
            tutorialObject.steps = _map(tutorialObject.steps, function(step,i){
                step.path    = _preparePath(step.path);
                step.onNext  = step.onNext  ? eval(step.onNext)  : undefined;
                step.onShow  = step.onShow  ? eval(step.onShow)  : undefined;
                step.onShown = step.onShown ? eval(step.onShown) : undefined;
                step.onHide = step.onHide ? eval(step.onHide) : undefined;
                step.onHidden = step.onHidden ? eval(step.onHidden) : undefined;
                if(window.debugState.backend) { console.ls.log(step); }
                return step;
            });
            
            tutorialObject.onShown = tutorialObject.onShown ? eval(tutorialObject.onShown) : null;
            tutorialObject.onEnd = tutorialObject.onEnd ? eval(tutorialObject.onEnd) : null;
            tutorialObject.onStart = tutorialObject.onStart ? eval(tutorialObject.onStart) : null;
            return tutorialObject;
        };

    return {
        get : function(tourName){
            return new Promise((resolve, reject)=>{
                $.ajax({
                    url: filterUrl('/tutorial/sa/servertutorial'),
                    data: {tutorialname: tourName, ajax: true},
                    method: 'POST',
                    success: (tutorialData)=>{
                        const tutorialObject = _prepareMethods(tutorialData.tutorial);
                        resolve(tutorialObject);
                    },
                    error: (error)=>{
                        reject(error);
                    }
                });
            });
        }
    };

};

export default globalTourObject();
