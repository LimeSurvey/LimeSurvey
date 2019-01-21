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
            context.commit('unsetQuestionImmutable')
            context.commit('setQuestionImmutable',result.data.question)
            context.commit('unsetQuestionImmutableI10N')
            context.commit('setQuestionImmutableI10N', _.cloneDeep(result.data.i10n))
            context.commit('setLanguages',_.map(result.data.i10n, (value, language) => {
                return language;
            }));
            context.commit('setActiveLanguage', context.state.languages[0]);
        });
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getQuestionAttributeData', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setQuestionAttributes', result.data);
        });
    },
    getQuestionGeneralSettings: (context) => {
        ajax.methods.$_get(
            window.QuestionEditData.connectorBaseUrl+'/getGeneralOptions', 
            {'iQuestionId' : window.QuestionEditData.qid}
        ).then((result) => {
            context.commit('setQuestionGeneralSettings', result.data);
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
            context.commit('setQuestionGeneralSettings', result.data);
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