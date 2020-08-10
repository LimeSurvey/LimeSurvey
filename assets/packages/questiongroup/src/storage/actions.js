import ajax from '../mixins/runAjax.js';
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import {LOG} from '../mixins/logSystem.js'

export default {
    loadQuestionGroup: (context) => {
        return new Promise((resolve, reject) => {
            context.commit('setCurrentQuestionGroup', {});
            const subAction = window.QuestionGroupEditData.connectorBaseUrl.slice(-1) == '=' ? 'loadQuestionGroup' : '/loadQuestionGroup';
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+subAction, 
                {'iQuestionGroupId' : window.QuestionGroupEditData.gid }
            ).then((result) => {
                context.commit('setLanguages', result.data.languages);
                context.commit('setActiveLanguage', keys(result.data.languages)[0]);

                context.commit('setPermissions', result.data.permissions);
                context.commit('setCurrentQuestionGroup', result.data.questionGroup);
                context.commit('setCurrentQuestionGroupI10N', result.data.questonGroupI10N);
                context.commit('setInTransfer', false);
                resolve(true);
            })
            .catch((error) => {
                context.commit('setInTransfer', false);
                reject(error);
            });
        });
    },
    reloadQuestionGroup: (context, gid=false) => {
        LOG.log('Reloading questionGroup with gid -> ', gid || context.state.currentQuestionGroup.gid);
        return new Promise((resolve, reject) => {
            context.commit('setCurrentQuestionGroup', {});
            const subAction = window.QuestionGroupEditData.connectorBaseUrl.slice(-1) == '=' ? 'loadQuestionGroup' : '/loadQuestionGroup';
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+subAction, 
                {'iQuestionGroupId' : gid || context.state.currentQuestionGroup.gid }
            ).then((result) => {
                context.commit('setLanguages', result.data.languages);
                context.commit('setActiveLanguage', keys(result.data.languages)[0]);

                context.commit('setPermissions', result.data.permissions);
                context.commit('setCurrentQuestionGroup', result.data.questionGroup);
                context.commit('setCurrentQuestionGroupI10N', result.data.questonGroupI10N);
                context.commit('setInTransfer', false);
                resolve(true);
            })
            .catch((error) => {
                context.commit('setInTransfer', false);
                reject(error);
            });
        });
    },
    getQuestionsForGroup: (context) => {
        return new Promise((resolve, reject) => {
            const subAction = window.QuestionGroupEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionsForGroup' : '/getQuestionsForGroup';
            ajax.methods.$_get(
                window.QuestionGroupEditData.connectorBaseUrl+subAction, 
                {
                    'iQuestionGroupId' : window.QuestionGroupEditData.gid,
                }
            ).then((result) => {
                context.commit('setQuestionList', result.data.questions);
                resolve(true);
            })
            .catch((error) => {
                context.commit('setInTransfer', false);
                reject(error);
            });
        });
    },
    saveQuestionGroupData: (context, scenario='') => {
        if(context.state.inTransfer ) {
            return Promise.resolve(false);
        }
        
        let transferObject = merge({
            'questionGroup': context.state.currentQuestionGroup,
            'questionGroupI10N': context.state.currentQuestionGroupI10N,
            'scenario': scenario
        }, window.LS.data.csrfTokenData);
        
        return new Promise((resolve, reject) => {
            context.commit('setInTransfer', true);
            LOG.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
            const subAction = window.QuestionGroupEditData.connectorBaseUrl.slice(-1) == '=' ? 'saveQuestionGroupData' : '/saveQuestionGroupData';
            ajax.methods.$_post(window.QuestionGroupEditData.connectorBaseUrl+subAction, transferObject)
                .then(
                    (result) => {
                        LOG.log("Result data -> ", result.data);
                        LOG.log("Result data questiongroupData-> ", result.data.questiongroupData);
                        context.commit('setCurrentQuestionGroup', result.data.questiongroupData);
                        context.commit('setInTransfer', false);
                        resolve(result);
                    }
                )
                .catch((error) => {
                    context.commit('setInTransfer', false);
                    reject(error);
                });
        });
    }
};
