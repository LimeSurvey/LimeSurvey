import ajax from "../mixins/runAjax.js";
import cloneDeep from "lodash/cloneDeep";
import merge from "lodash/merge";
import { LOG } from "../mixins/logSystem.js";

export default {
    updateObjects: (context, newObjectBlock) => {
        context.commit("setCurrentQuestion", newObjectBlock.question);
        context.commit("unsetQuestionImmutable");
        context.commit(
            "setQuestionImmutable",
            cloneDeep(newObjectBlock.question)
        );

        context.commit("setCurrentQuestionI10N", newObjectBlock.questionI10N);
        context.commit("unsetQuestionImmutableI10N");
        context.commit(
            "setQuestionImmutableI10N",
            cloneDeep(newObjectBlock.questionI10N)
        );

        context.commit(
            "setCurrentQuestionSubquestions",
            newObjectBlock.scaledSubquestions
        );
        context.commit("unsetQuestionSubquestionsImmutable");
        context.commit(
            "setQuestionSubquestionsImmutable",
            cloneDeep(newObjectBlock.scaledSubquestions)
        );

        context.commit(
            "setCurrentQuestionAnswerOptions",
            newObjectBlock.scaledAnswerOptions
        );
        context.commit("unsetQuestionAnswerOptionsImmutable");
        context.commit(
            "setQuestionAnswerOptionsImmutable",
            cloneDeep(newObjectBlock.scaledAnswerOptions)
        );

        context.commit(
            "setCurrentQuestionGeneralSettings",
            newObjectBlock.generalSettings
        );
        context.commit("unsetImmutableQuestionGeneralSettings");
        context.commit(
            "setImmutableQuestionGeneralSettings",
            cloneDeep(newObjectBlock.generalSettings)
        );

        context.commit(
            "setCurrentQuestionAdvancedSettings",
            newObjectBlock.advancedSettings
        );
        context.commit("unsetImmutableQuestionAdvancedSettings");
        context.commit(
            "setImmutableQuestionAdvancedSettings",
            cloneDeep(newObjectBlock.advancedSettings)
        );

        context.commit(
            "setCurrentQuestionGroupInfo",
            newObjectBlock.questiongroup
        );
    },
    loadQuestion: context => {
        return Promise.all([
            new Promise((resolve, reject) => {
                const postUrl = LS.createUrl(
                    "questionEditor/getQuestionData",
                    {
                        sid: context.getters.surveyid
                    }
                );

                ajax.methods
                    .$_get(postUrl, {
                        iQuestionId: window.QuestionEditData.qid,
                        gid: window.QuestionEditData.gid || null,
                        type: window.QuestionEditData.startType
                    })
                    .then(result => {
                        context.commit(
                            "setCurrentQuestion",
                            result.data.question
                        );
                        context.commit("unsetQuestionImmutable");
                        context.commit(
                            "setQuestionImmutable",
                            cloneDeep(result.data.question)
                        );

                        context.commit(
                            "setCurrentQuestionI10N",
                            result.data.i10n
                        );
                        context.commit("unsetQuestionImmutableI10N");
                        context.commit(
                            "setQuestionImmutableI10N",
                            cloneDeep(result.data.i10n)
                        );

                        context.commit(
                            "setCurrentQuestionSubquestions",
                            result.data.subquestions
                        );
                        context.commit("unsetQuestionSubquestionsImmutable");
                        context.commit(
                            "setQuestionSubquestionsImmutable",
                            cloneDeep(result.data.subquestions)
                        );

                        context.commit(
                            "setCurrentQuestionAnswerOptions",
                            result.data.answerOptions
                        );
                        context.commit("unsetQuestionAnswerOptionsImmutable");
                        context.commit(
                            "setQuestionAnswerOptionsImmutable",
                            cloneDeep(result.data.answerOptions)
                        );

                        context.commit(
                            "setCurrentQuestionGroupInfo",
                            result.data.questiongroup
                        );
                        context.commit("setSurveyInfo", result.data.surveyInfo);
                        context.commit("setLanguages", result.data.languages);
                        context.commit(
                            "setActiveLanguage",
                            result.data.mainLanguage
                        );
                        context.commit("setInTransfer", false);
                        resolve(true);
                    })
                    .catch(error => {
                        context.commit("setInTransfer", false);
                        reject(error);
                    });
            }),
            new Promise((resolve, reject) => {
                const postUrl = LS.createUrl(
                    "questionEditor/getQuestionPermissions",
                    {
                        sid: context.getters.surveyid
                    }
                );
                ajax.methods
                    .$_get(postUrl, {
                        gid: window.QuestionEditData.gid || null,
                        iQuestionId: window.QuestionEditData.qid
                    })
                    .then(result => {
                        context.commit(
                            "setCurrentQuestionPermissions",
                            result.data
                        );
                        resolve(true);
                    })
                    .catch(error => {
                        context.commit("setInTransfer", false);
                        reject(error);
                    });
            })
        ]);
    },
    getQuestionGeneralSettings: (context, questionTheme = "core") => {
        return new Promise((resolve, reject) => {
            const postUrl = LS.createUrl(
                "questionEditor/getGeneralOptions",
                {
                    sid: context.getters.surveyid
                }
            );

            const parameters = {
                gid: window.QuestionEditData.gid || null,
                sQuestionType:
                    context.state.currentQuestion.type ||
                    window.QuestionEditData.startType,
                question_template: questionTheme
            };

            if (
                context.state.currentQuestionGeneralSettings
                    .question_template != undefined
            ) {
                parameters["question_template"] =
                    context.state.currentQuestionGeneralSettings.question_template.formElementValue;
            }

            if (window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid;
            }
            ajax.methods
                .$_get(postUrl, parameters)
                .then(result => {
                    context.commit(
                        "setCurrentQuestionGeneralSettings",
                        result.data
                    );
                    context.commit(
                        "unsetImmutableQuestionGeneralSettings",
                        result.data
                    );
                    context.commit(
                        "setImmutableQuestionGeneralSettings",
                        result.data
                    );
                    resolve(true);
                })
                .catch(error => {
                    context.commit("setInTransfer", false);
                    reject(error);
                });
        });
    },
    getQuestionAdvancedSettings: context => {
        return new Promise((resolve, reject) => {
            const postUrl = LS.createUrl(
                "questionEditor/getAdvancedOptions",
                {
                    sid: context.getters.surveyid
                }
            );
            const parameters = {
                sQuestionType:
                    context.state.currentQuestion.type ||
                    window.QuestionEditData.startType
            };

            if (
                context.state.currentQuestionGeneralSettings
                    .question_template != undefined
            ) {
                parameters["question_template"] =
                    context.state.currentQuestionGeneralSettings.question_template.formElementValue;
            }

            if (window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid;
            }

            ajax.methods
                .$_get(postUrl, parameters)
                .then(result => {
                    context.commit(
                        "setCurrentQuestionAdvancedSettings",
                        result.data.advancedSettings
                    );
                    context.commit(
                        "unsetImmutableQuestionAdvancedSettings",
                        result.data.advancedSettings
                    );
                    context.commit(
                        "setImmutableQuestionAdvancedSettings",
                        result.data.advancedSettings
                    );
                    context.commit(
                        "setQuestionTypeDefinition",
                        result.data.questionTypeDefinition
                    );
                    resolve(true);
                })
                .catch(error => {
                    context.commit("setInTransfer", false);
                    reject(error);
                });
        });
    },
    getQuestionTypes: context => {
        const postUrl = LS.createUrl(
            "questionEditor/getQuestionTypeList",
            {
                sid: context.getters.surveyid
            }
            );

        ajax.methods
            .$_get(postUrl)
            .then(result => {
                context.commit("setQuestionTypeList", result.data);
            })
            .catch(error => {
                context.commit("setInTransfer", false);
                reject(error);
            });
    },
    reloadQuestion: context => {
        return new Promise((resolve, reject) => {
            const postUrl = LS.createUrl(
                "questionEditor/reloadQuestionData",
                {
                    sid: context.getters.surveyid
                }
            );

            const parameters = {
                gid: window.QuestionEditData.gid || null,
                type:
                    context.state.currentQuestion.type ||
                    window.QuestionEditData.startType,
                question_template:
                    context.state.currentQuestionGeneralSettings
                        .question_template.formElementValue || "core"
            };

            if (window.QuestionEditData.qid != null) {
                parameters.iQuestionId = window.QuestionEditData.qid;
            }

            ajax.methods
                .$_get(postUrl, parameters)
                .then(result => {
                    context.commit(
                        "updateCurrentQuestion",
                        result.data.question
                    );
                    context.commit(
                        "updateCurrentQuestionSubquestions",
                        result.data.scaledSubquestions
                    );
                    context.commit(
                        "updateCurrentQuestionAnswerOptions",
                        result.data.scaledAnswerOptions
                    );
                    context.commit(
                        "updateCurrentQuestionGeneralSettings",
                        result.data.generalSettings
                    );
                    context.commit(
                        "updateCurrentQuestionAdvancedSettings",
                        result.data.advancedSettings
                    );
                    context.commit(
                        "setCurrentQuestionGroupInfo",
                        result.data.questiongroup
                    );
                    resolve();
                })
                .catch(error => {
                    context.commit("setInTransfer", false);
                    reject(error);
                });
        });
    },
    saveQuestionData: context => {
        if (context.state.inTransfer) {
            return Promise.reject({
                data: {
                    message: "Transfer in progress",
                    error: "Transfer in progress"
                }
            });
        }

        return new Promise((resolve, reject) => {
            let transferObject = merge(
                {
                    questionData: {
                        question: context.state.currentQuestion,
                        scaledSubquestions:
                            context.state.currentQuestionSubquestions || [],
                        scaledAnswerOptions:
                            context.state.currentQuestionAnswerOptions || [],
                        questionI10N: context.state.currentQuestionI10N,
                        generalSettings:
                            context.state.currentQuestionGeneralSettings,
                        advancedSettings:
                            context.state.currentQuestionAdvancedSettings
                    }
                },
              window.LS.data.csrfTokenData
            );

            if (context.state.initCopy == true) {
                transferObject.questionCopy = true;
                transferObject.copySettings = {
                    copySubquestions: context.state.copySubquestions ? 1 : 0,
                    copyAnswerOptions: context.state.copyAnswerOptions ? 1 : 0,
                    copyDefaultAnswers: context.state.copyDefaultAnswers ? 1 : 0,
                    copyAdvancedOptions: context.state.copyAdvancedOptions ? 1 : 0
                };
            }

            LOG.log("OBJECT TO BE TRANSFERRED: ", {
                questionData: transferObject
            });

            const postUrl = LS.createUrl(
                "questionEditor/saveQuestionData",
                {
                    gid: context.state.currentQuestion.gid,
                    sid: context.getters.surveyid
                }
            );
            context.commit("setInTransfer", true);

            ajax.methods
                .$_post(postUrl, transferObject)
                .then(result => {
                    context.commit("setInTransfer", false);
                    resolve(result);
                })
                .catch(error => {
                    context.commit("setInTransfer", false);
                    reject(error);
                });
        });
    },
    saveAsLabelSet: (context, payload) => {
        let transferObject = merge(
            { labelSet: payload },
            window.LS.data.csrfTokenData
        );
        LOG.log("OBJECT TO BE TRANSFERRED: ", {
            "transferData ": transferObject
        });
        return new Promise((resolve, reject) => {
            ajax.methods
                .$_post(
                    LS.createUrl(
                        "admin/labels/sa/newLabelSetFromQuestionEditor"
                    ),
                    transferObject
                )
                .then(result => {
                    resolve(result);
                })
                .catch(error => {
                    context.commit("setInTransfer", false);
                    reject(error);
                });
        });
    },
    questionTypeChange: (context, payload) => {
        context.commit("updateCurrentQuestionType", payload.type);
        context.commit("setQuestionGeneralSetting", {
            settingName: "question_template",
            newValue: payload.name
        });
        context.commit("setStoredEvent", {
            target: "GeneralSettings",
            method: "toggleLoading",
            content: true,
            chain: "AdvancedSettings"
        });
        
        Promise.all([
            context.dispatch("getQuestionGeneralSettings", payload.name),
            context.dispatch("getQuestionAdvancedSettings")
        ]).finally(() => {
            context.commit("setStoredEvent", {
                target: "GeneralSettings",
                method: "toggleLoading",
                content: false,
                chain: "AdvancedSettings"
            });
        });
    }
};
