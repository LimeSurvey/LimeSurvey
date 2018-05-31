/**
 * Methods loaded on subquestions and answers page
 */
import _ from 'lodash';

const subquestionAndAnswersGlobalMethods = {
    removechars : (strtoconvert) => {
        return strtoconvert.replace(/[-a-zA-Z_]/g,"");
    },
    getUnique : (array) => {
        return _.uniq(array);
    }
};

 export {subquestionAndAnswersGlobalMethods};