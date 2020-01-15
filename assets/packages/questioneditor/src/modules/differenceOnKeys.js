import uniq from 'lodash/uniq';

const diffOnKeys = function(object1, object2){
    const differingKeys = [];
    Object.keys(object1).forEach(key => {
        if(object2[key] == undefined) {
            differingKeys.push(key);
        }
    });

    Object.keys(object2).forEach(key => {
        if(object1[key] == undefined) {
            differingKeys.push(key);
        }
    });

    return uniq(differingKeys);
}

export default diffOnKeys;