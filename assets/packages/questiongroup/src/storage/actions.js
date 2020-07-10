import ajax from '../mixins/runAjax.js';
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import {LOG} from '../mixins/logSystem.js'

export default {
    loadQuestionGroup: (context) => {
        return new Promise((resolve, reject) => {
            context.commit('setCurrentQuestionGroup', {});
            ajax.methods.$_get(
                LS.createUrl('questionGroupsAdministration/loadQuestionGroup', {
                    'surveyid' : window.QuestionGroupEditData.surveyid,
                    'iQuestionGroupId' : window.QuestionGroupEditData.gid
                })
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
            ajax.methods.$_get(
                LS.createUrl('questionGroupsAdministration/loadQuestionGroup', {
                    'surveyid' : window.QuestionGroupEditData.surveyid,
                    'iQuestionGroupId' : gid || context.state.currentQuestionGroup.gid
                })
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
            ajax.methods.$_get(
                LS.createUrl('questionGroupsAdministration/getQuestionsForGroup', {
                    'iQuestionGroupId' : window.QuestionGroupEditData.gid
                })
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
    saveQuestionGroupData: (context) => {
        if (context.state.inTransfer ) {
            return Promise.resolve(false);
        }
        
        let transferObject = merge({
            'questionGroup': context.state.currentQuestionGroup,
            'questionGroupI10N': context.state.currentQuestionGroupI10N
        }, window.LS.data.csrfTokenData);
        
        return new Promise((resolve, reject) => {
            context.commit('setInTransfer', true);
            LOG.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
            ajax.methods.$_post(
                LS.createUrl('questionGroupsAdministration/saveQuestionGroupData', {
                    'sid' : window.QuestionGroupEditData.surveyid
                }),
                transferObject
            ).then(
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
