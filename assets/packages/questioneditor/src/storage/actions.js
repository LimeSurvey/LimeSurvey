import ajax from '../mixins/runAjax.js';
import cloneDeep from 'lodash/cloneDeep';
import merge from 'lodash/merge';
import {LOG} from '../mixins/logSystem.js'

export default {
    updateObjects: (context, newObjectBlock) => {
        context.commit('setCurrentQuestion', newObjectBlock.question);
        context.commit('unsetQuestionImmutable');
        context.commit('setQuestionImmutable', cloneDeep(newObjectBlock.question));

        context.commit('setCurrentQuestionI10N', newObjectBlock.questionI10N);
        context.commit('unsetQuestionImmutableI10N');
        context.commit('setQuestionImmutableI10N', cloneDeep(newObjectBlock.questionI10N));

        context.commit('setCurrentQuestionSubquestions', newObjectBlock.scaledSubquestions);
        context.commit('unsetQuestionSubquestionsImmutable')
        context.commit('setQuestionSubquestionsImmutable',  cloneDeep(newObjectBlock.scaledSubquestions));

        context.commit('setCurrentQuestionAnswerOptions', newObjectBlock.scaledAnswerOptions);
        context.commit('unsetQuestionAnswerOptionsImmutable')
        context.commit('setQuestionAnswerOptionsImmutable', cloneDeep(newObjectBlock.scaledAnswerOptions))

        context.commit('setCurrentQuestionGeneralSettings', newObjectBlock.generalSettings);
        context.commit('unsetImmutableQuestionGeneralSettings');
        context.commit('setImmutableQuestionGeneralSettings', cloneDeep(newObjectBlock.generalSettings));

        context.commit('setCurrentQuestionAdvancedSettings', newObjectBlock.advancedSettings);
        context.commit('unsetImmutableQuestionAdvancedSettings');
        context.commit('setImmutableQuestionAdvancedSettings', cloneDeep(newObjectBlock.advancedSettings));

        context.commit('setCurrentQuestionGroupInfo', newObjectBlock.questiongroup);
    },
    loadQuestion: (context) => {
        return Promise.all([
            (new Promise((resolve, reject) => {
                const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionData' : '/getQuestionData';
                ajax.methods.$_get(
                    window.QuestionEditData.connectorBaseUrl+subAction, 
                    {'iQuestionId' : window.QuestionEditData.qid, type: window.QuestionEditData.startType}
                ).then((result) => {
                    context.commit('setCurrentQuestion', result.data.question);
                    context.commit('unsetQuestionImmutable');
                    context.commit('setQuestionImmutable', cloneDeep(result.data.question));

                    context.commit('setCurrentQuestionI10N', result.data.i10n);
                    context.commit('unsetQuestionImmutableI10N');
                    context.commit('setQuestionImmutableI10N', cloneDeep(result.data.i10n));
                    
                    context.commit('setCurrentQuestionSubquestions', result.data.subquestions);
                    context.commit('unsetQuestionSubquestionsImmutable')
                    context.commit('setQuestionSubquestionsImmutable',  cloneDeep(result.data.subquestions));

                    context.commit('setCurrentQuestionAnswerOptions', result.data.answerOptions);
                    context.commit('unsetQuestionAnswerOptionsImmutable')
                    context.commit('setQuestionAnswerOptionsImmutable', cloneDeep(result.data.answerOptions))

                    context.commit('setCurrentQuestionGroupInfo', result.data.questiongroup);
                    context.commit('setLanguages', result.data.languages);
                    context.commit('setActiveLanguage', result.data.mainLanguage);
                    context.commit('setInTransfer', false);
                    resolve(true);
                },
                (rejectAnswer) => {
                    reject(rejectAnswer);
                })
            })),
            (new Promise((resolve, reject) => {
                const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionPermissions' : '/getQuestionPermissions';
                ajax.methods.$_get(
                    window.QuestionEditData.connectorBaseUrl+subAction, 
                    {'iQuestionId' : window.QuestionEditData.qid }
                ).then((result) => {
                    context.commit('setCurrentQuestionPermissions', result.data);
                    resolve(true);
                },
                (rejectAnswer) => {
                    reject(rejectAnswer);
                });
            }))
        ]);
    },
    getQuestionGeneralSettings: (context) => {
        return new Promise((resolve, reject) => {
            const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getGeneralOptions' : '/getGeneralOptions';
            const parameters = {
                sQuestionType: context.state.currentQuestion.type || window.QuestionEditData.startType,
            };

            if(context.state.currentQuestionGeneralSettings.question_template != undefined) {
                parameters['question_template'] = context.state.currentQuestionGeneralSettings.question_template.formElementValue;
            }
            
            if( window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid
            }
            ajax.methods.$_get(
                window.QuestionEditData.connectorBaseUrl+subAction, 
                parameters
            ).then((result) => {
                context.commit('setCurrentQuestionGeneralSettings', result.data);
                context.commit('unsetImmutableQuestionGeneralSettings', result.data);
                context.commit('setImmutableQuestionGeneralSettings', result.data);
                resolve(true);
            },
            (rejectAnswer) => {
                reject(rejectAnswer);
            });
        });
    },
    getQuestionAdvancedSettings: (context) => {
        return new Promise((resolve, reject) => {
            const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getAdvancedOptions' : '/getAdvancedOptions';
            const parameters = {
                sQuestionType: context.state.currentQuestion.type || window.QuestionEditData.startType,
            };
            
            if(context.state.currentQuestionGeneralSettings.question_template != undefined) {
                parameters['question_template'] = context.state.currentQuestionGeneralSettings.question_template.formElementValue;
            }
            
            if( window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid
            }

            ajax.methods.$_get(
                window.QuestionEditData.connectorBaseUrl+subAction, 
                parameters
            ).then((result) => {
                context.commit('setCurrentQuestionAdvancedSettings', result.data);
                context.commit('unsetImmutableQuestionAdvancedSettings', result.data);
                context.commit('setImmutableQuestionAdvancedSettings', result.data);
                resolve(true);
            },
            (rejectAnswer) => {
                reject(rejectAnswer);
            });
        });
    },
    getQuestionTypes: (context) => {
        const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'getQuestionTypeList' : '/getQuestionTypeList';
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+subAction
        ).then((result) => {
            context.commit('setQuestionTypeList', result.data);
        });
    },
    reloadQuestion: (context) => {
        return new Promise((resolve,reject) => {
            const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'reloadQuestionData' : '/reloadQuestionData';
            const parameters = {
                type: context.state.currentQuestion.type || window.QuestionEditData.startType,
                question_template: context.state.currentQuestionGeneralSettings.question_template.formElementValue || 'core'
            };
            
            if( window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid
            }

            ajax.methods.$_get(
                window.QuestionEditData.connectorBaseUrl+subAction, 
                parameters
            ).then((result) => {
                context.commit('updateCurrentQuestion', result.data.question);
                context.commit('updateCurrentQuestionSubquestions', result.data.scaledSubquestions);
                context.commit('updateCurrentQuestionAnswerOptions', result.data.scaledAnswerOptions);
                context.commit('updateCurrentQuestionGeneralSettings', result.data.generalSettings);
                context.commit('updateCurrentQuestionAdvancedSettings', result.data.advancedSettings);
                context.commit('setCurrentQuestionGroupInfo', result.data.questiongroup);
                resolve();
            }, reject);
        });
    },
    saveQuestionData: (context) => {
        if(context.state.inTransfer) {
            return Promise.resolve(false);
        }

        let transferObject = merge({
            'questionData': {
            question: context.state.currentQuestion,
            scaledSubquestions: context.state.currentQuestionSubquestions,
            scaledAnswerOptions: context.state.currentQuestionAnswerOptions,
            questionI10N: context.state.currentQuestionI10N,
            generalSettings: context.state.currentQuestionGeneralSettings,
            advancedSettings: context.state.currentQuestionAdvancedSettings,
        }}, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'questionData': transferObject});
        return new Promise((resolve, reject) => {
            const subAction = window.QuestionEditData.connectorBaseUrl.slice(-1) == '=' ? 'saveQuestionData' : '/saveQuestionData';
            context.commit('setInTransfer', true);
            ajax.methods.$_post(window.QuestionEditData.connectorBaseUrl+subAction, transferObject)
                .then(
                    (result) => {
                        context.commit('setInTransfer', false);
                        resolve(result);
                    },
                    reject
                )
        });
    },
    saveAsLabelSet: (context, payload) => {
        let transferObject = merge({'labelSet': payload}, window.LS.data.csrfTokenData);
        LOG.log('OBJECT TO BE TRANSFERRED: ', {'transferData ': transferObject});
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(LS.createUrl('admin/labels/sa/newLabelSetFromQuestionEditor'), transferObject)
            .then(
                (result) => {resolve(result);},
                reject
            );
        });
    }
};