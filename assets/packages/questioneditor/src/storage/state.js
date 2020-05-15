/**
 * @name State
 * @type {object}
 * @property {object} currentQuestionSubquestions
 * @property {array} currentQuestionSubquestions.0 - Subquestions for scale id 0
 * @property {array} currentQuestionSubquestions.1 - Subquestions for scale id 1
 * @property {object} currentQuestionAnswerOptions
 * @property {array} currentQuestionAnswerOptions.0 - Answer options for scale id 0
 * @property {array} currentQuestionAnswerOptions.1 - Answer options for scale id 1
 * @todo Fill in all properties.
 * @see https://www.npmjs.com/package/jsdoc-vuex-plugin#the-state
 */
export default {
    currentQuestion: {},
    currentQuestionGroupInfo: {},
    currentQuestionSubquestions: {},
    currentQuestionAnswerOptions: {},
    currentQuestionI10N: {},
    currentQuestionPermissions: {},
    currentQuestionGeneralSettings: [],
    currentQuestionAdvancedSettings: {},
    questionAttributesImmutable: {},
    questionGeneralSettingsImmutable: [],
    questionAdvancedSettingsImmutable: {},
    questionImmutable: {},
    questionImmutableI10N: {},
    questionSubquestionsImmutable: {},
    questionAnswerOptionsImmutable: {},
    languages: [],
    surveyInfo: {},
    debugMode: false,
    questionTypes: window.aQuestionTypes,
    questionAdvancedSettingsCategory: '',
    collapsedGeneralSettings: false,
    activeLanguage: '',
    inTransfer: false,
    alerts: [],
    storedEvent: null,
    initCopy: false,
    copySubquestions: true,
    copyAnswerOptions: true,
    copyDefaultAnswers: true,
    copyAdvancedOptions: true,
};
