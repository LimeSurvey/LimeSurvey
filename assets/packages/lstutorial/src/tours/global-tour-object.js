import _ from 'lodash';

const globalTourObject = function(){
    const getBasedUrls = (/index\.php\/?\?r=admin/.test(window.location.href)),
    
        combineParams = function(params){
            const getBasedUrls = false;
            if(params === false) return '';

            const returner = (getBasedUrls ? '?' :'/') + _.reduce(params, (urlParams, value, key)=>{ 
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
            
            const baseUrl = (getBasedUrls || forceGet) ? '/index.php?r=admin/' : '/index.php/admin/';
            
            const returnUrl = baseUrl+url+combineParams(params);

            return returnUrl;

        },
        _preparePath = function(path){
            if(typeof path === 'string')
                return path;
            
            return RegExp(path.join());
        },
        _prepareMethods = function(tutorialObject){
            'use strict';
            tutorialObject.steps = _.map(tutorialObject.steps, function(step,i){
                step.path    = _preparePath(step.path);
                step.onNext  = step.onNext  ? eval(step.onNext)  : undefined;
                step.onShow  = step.onShow  ? eval(step.onShow)  : undefined;
                step.onShown = step.onShown ? eval(step.onShown) : undefined;
                return step;
            });
            
            tutorialObject.onShown = tutorialObject.onShown ? eval(tutorialObject.onShown) : null;

            return tutorialObject;
        };

    return {
        get : function(tourName){
            return new Promise((res)=>{
                $.ajax({
                    url: filterUrl('/tutorial/sa/serveprebuilt', null, true),
                    data: {tutorialname: tourName},
                    success: (tutorialData)=>{
                        const tutorialObject = _prepareMethods(tutorialData.tutorial);
                        res(tutorialObject);
                    }
                });
            });
        }
    };

};

export default globalTourObject();
