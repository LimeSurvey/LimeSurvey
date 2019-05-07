import forEach from 'lodash/forEach'

const $GET = {};

forEach(window.location.search.substring(1).split('&'), (value, index) => {
    try{
        const keyValueArray = value.split("=");
        $GET[keyValueArray[0]] = keyValueArray[1];
    } catch(e) {}
});

let key = null
const keyValuePairs = {};
forEach(window.location.search.substring(1).split('/'), (value, index) => {
    if(value == 'sa') {
        key = value;
    }
    if(key !== null) {
        if(key === false) {
            key=value;
        } else {
            keyValuePairs[key] = value;
        }
    }
});

export default {parameters : {$GET, keyValuePairs}};