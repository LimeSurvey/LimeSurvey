import ajax from '../mixins/runAjax.js';
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import {LOG} from '../mixins/logSystem.js'

export default {
    loadQuestionGroup: (context) => {
        return new Promise((resolve, reject) => {
            context.commit('setCurrentQuestionGroup', {});
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+'/loadQuestionGroup', 
                {'iQuestionGroupId' : window.QuestionGroupEditData.gid }
            ).then((result) => {
                context.commit('setLanguages', result.data.languages);
                context.commit('setActiveLanguage', keys(result.data.languages)[0]);

                context.commit('setPermissions', result.data.permissions);
                context.commit('setCurrentQuestionGroup', result.data.questionGroup);
                context.commit('setCurrentQuestionGroupI10N', result.data.questonGroupI10N);
                context.commit('setInTransfer', false);
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
        if(context.state.inTransfer ) {
            return Promise.resolve(false);
        }
        
        let transferObject = merge({
            'questionGroup': context.state.currentQuestionGroup,
            'questionGroupI10N': context.state.currentQuestionGroupI10N
        }, window.LS.data.csrfTokenData);
        
        return new Promise((resolve, reject) => {
            context.commit('setInTransfer', true);
            LOG.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
            ajax.methods.$_post(window.QuestionGroupEditData.connectorBaseUrl+'/saveQuestionGroupData', transferObject)
                .then(
                    (result) => {
                        context.commit('setInTransfer', false);
                        resolve(result);
                    },
                    reject
                )
        });
    }
};