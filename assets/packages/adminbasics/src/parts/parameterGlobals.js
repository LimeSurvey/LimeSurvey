import forEach from 'lodash/forEach'

const $GET = {};
forEach(window.location.search.substring(1).split('&'), (value, index) => {
    try{
        const keyValueArray = value.split("=");
        $GET[keyValueArray[0]] = keyValueArray[1];
    } catch(e) {}
});

export default {parameters : $GET};