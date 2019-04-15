import ajax from '../mixins/runAjax.js';
import cloneDeep from 'lodash/cloneDeep';
import merge from 'lodash/merge';
import {LOG} from '../mixins/logSystem.js'

export default {
    loadQuestionGroup: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+'/loadQuestionGroup', 
                {'iQuestionGroupId' : window.QuestionGroupEditData.gid }
            ).then((result) => {
                context.commit('setCurrentQuestionGroup', result.data.questionGroup);
                context.commit('setCurrentQuestionGroupI10N', result.data.questonGroupI10N);
                context.commit('setLanguages', result.data.languages);
                context.commit('setActiveLanguage', result.data.mainLanguage);
                resolve(true);
            },
            (rejectAnswer) => {
                reject(rejectAnswer);
            });
        });
    },
    getQuestionsForGroup: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+'/getQuestionsForGroup', 
                {
                    'iQuestionGroupId' : window.QuestionGroupEditData.gid,
                }
            ).then((result) => {
                context.commit('setQuestionList', result.data);
                resolve(true);
            },
            (rejectAnswer) => {
                reject(rejectAnswer);
            });
        });
    },
    saveQuestionGroupData: (context) => {
        
        let transferObject = merge({
            'questionGroup': context.state.currentQuestionGroup,
            'questionGroupI10N': context.state.currentQuestionGroupI10N
        }, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
        return ajax.methods.$_post(window.QuestionGroupEditData.connectorBaseUrl+'/saveQuestionGroupData', transferObject)
    }
};