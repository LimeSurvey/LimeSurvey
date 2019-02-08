import ajax from '../mixins/runAjax.js';
import _ from 'lodash';

export default {
    loadQuestion: (context) => {
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getQuestionData', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setCurrentQuestion', result.data.question);
            context.commit('setCurrentQuestionI10N', result.data.i10n);
            context.commit('unsetQuestionImmutable');
            context.commit('setQuestionImmutable', _.cloneDeep(result.data.question));
            context.commit('unsetQuestionImmutableI10N');
            context.commit('setQuestionImmutableI10N', _.cloneDeep(result.data.i10n));
            
            context.commit('setCurrentQuestionSubquestions', result.data.subquestions);
            context.commit('setCurrentQuestionAnswerOptions', result.data.answerOptions);
            context.commit('unsetQuestionSubquestionsImmutable')
            context.commit('setQuestionSubquestionsImmutable',  _.cloneDeep(result.data.subquestions));
            context.commit('unsetQuestionAnswerOptionsImmutable')
            context.commit('setQuestionAnswerOptionsImmutable', _.cloneDeep(result.data.answerOptions))

            context.commit('setLanguages', result.data.languages);
            context.commit('setActiveLanguage', result.data.mainLanguage);
        });
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getQuestionAttributeData', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setCurrentQuestionAttributes', result.data);
            context.commit('unsetImmutableQuestionAttributes', result.data);
            context.commit('setImmutableQuestionAttributes', result.data);
        });
    },
    getQuestionGeneralSettings: (context) => {
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getGeneralOptions', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setCurrentQuestionGeneralSettings', result.data);
            context.commit('unsetImmutableQuestionGeneralSettings', result.data);
            context.commit('setImmutableQuestionGeneralSettings', result.data);
        });
    },
    getQuestionGeneralSettingsWithType: (context) => {
        context.commit('setQuestionGeneralSettings', []);
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getGeneralOptions', 
            {
                'iQuestionId' : window.QuestionEditData.qid,
                'sQuestionType' : context.store.currentQuestion.type
            }
        ).then((result) => {
            context.commit('setCurrentQuestionGeneralSettings', result.data);
            context.commit('unsetImmutableQuestionGeneralSettings', result.data);
            context.commit('setImmutableQuestionGeneralSettings', result.data);
        });
    },
    getQuestionAdvancedSettings: (context) => {
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getAdvancedOptions', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setCurrentQuestionAdvancedSettings', result.data);
            context.commit('unsetImmutableQuestionAdvancedSettings', result.data);
            context.commit('setImmutableQuestionAdvancedSettings', result.data);
        });
    },
    getQuestionAdvancedSettingsWithType: (context) => {
        context.commit('setQuestionGeneralSettings', []);
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getAdvancedOptions', 
            {
                'iQuestionId' : window.QuestionEditData.qid,
                'sQuestionType' : context.store.currentQuestion.type
            }
        ).then((result) => {
            context.commit('setCurrentQuestionAdvancedSettings', result.data);
            context.commit('unsetImmutableQuestionAdvancedSettings', result.data);
            context.commit('setImmutableQuestionAdvancedSettings', result.data);
        });
    },
    getQuestionTypes: (context) => {
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getQuestionTypeList'
        ).then((result) => {
            context.commit('setQuestionTypeList', result.data);
        });
    }
};