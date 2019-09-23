import forEach from 'lodash/forEach'
import merge from 'lodash/merge'

const parseParameters = function(){
    const $GET = {};
    const keyValuePairs = {};

    forEach(window.location.search.substring(1).split('&'), (value, index) => {
        try{
            const keyValueArray = value.split("=");
            $GET[keyValueArray[0]] = keyValueArray[1];
        } catch(e) {}
    });
    
    let key = null
    window.location.href.substring((window.location.href.indexOf('admin')-1)).split('/').forEach((value, index) => {
        if(value == 'sa') {
            key = false;
        }
        if(key !== null) {
            if(key === false) {
                key=value;
            } else {
                if(key=='surveyid') {
                    keyValuePairs['sid'] = value;
                }
                keyValuePairs[key] = value;
                key=false;
            }
        }
    });

    const combined = merge($GET, keyValuePairs);
    
    return {$GET, keyValuePairs, combined};
}


export default {parameters: parseParameters(), reparsedParameters: parseParameters };
