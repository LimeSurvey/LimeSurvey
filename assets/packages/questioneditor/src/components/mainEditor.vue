
<template>
    <div class="ls-flex grow-2">
        <transition name="slide-fade">
            <div class="col-12" v-show="!loading">
                <div
                    class="panel panel-default col-12 question-option-general-container"
                    key="mainPanel"
                >
                    <div class="panel-heading">{{"Text elements" | translate }}</div>
                    <div class="panel-body">
                        <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                            <div class="ls-flex-row">
                                <div class="ls-flex-item grow-2 text-left">
                                    <label class="col-sm-12">{{ 'Question' | translate }}:</label>
                                </div>
                                <div class="ls-flex-item text-right">
                                    <button
                                        class="btn btn-default btn-xs"
                                        @click.prevent="toggleSourceEditQuestion"
                                    >
                                        <i class="fa fa-file-code-o"></i>
                                        {{'Toggle source mode'|translate}}
                                    </button>
                                </div>
                            </div>
                            <lsckeditor
                                v-if="!questionEditSource"
                                v-model="currentQuestionQuestion"
                                :editor="editorQuestionObject"
                                :config="editorQuestionConfig"
                                v-on:input="runDebouncedChange"
                            ></lsckeditor>
                            <aceeditor
                                v-else
                                v-model="currentQuestionQuestion"
                                :showLangSelector="false"
                                :thisId="'questionEditSource'"
                                v-on:input="runDebouncedChange"
                            ></aceeditor>
                        </div>
                        <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                            <div class="ls-flex-row">
                                <div class="ls-flex-item grow-2 text-left">
                                    <label class="col-sm-12">{{ 'Help' | translate }}:</label>
                                </div>
                                <div class="ls-flex-item text-right">
                                    <button
                                        class="btn btn-default btn-xs"
                                        @click.prevent="toggleSourceEditHelp"
                                    >
                                        <i class="fa fa-file-code-o"></i>
                                        {{'Toggle source mode'|translate}}
                                    </button>
                                </div>
                            </div>
                            <lsckeditor
                                v-if="!helpEditSource"
                                :editor="editorHelpObject"
                                v-model="currentQuestionHelp"
                                v-on:input="runDebouncedChange"
                                :config="editorHelpConfig"
                            ></lsckeditor>
                            <aceeditor
                                v-else
                                v-model="currentQuestionHelp"
                                :showLangSelector="false"
                                :thisId="'helpEditSource'"
                                v-on:input="runDebouncedChange"
                            ></aceeditor>
                        </div>
                        <div
                            class="col-12 ls-space margin all-5 scope-contains-ckeditor"
                            v-if="!!$store.state.currentQuestionPermissions.script"
                        >
                            <label class="col-sm-6">
                                    {{ 'Script' | translate }}:
                            </label>
                            <div class="col-sm-6 text-right">
                                <input 
                                    type="checkbox" 
                                    name="selector--scriptForAllLanguages" 
                                    id="selector--scriptForAllLanguages"
                                    v-model="scriptForAllLanugages"
                                />&nbsp;
                                <label for="selector--scriptForAllLanguages">
                                    {{ 'Set for all languages' | translate }}
                                </label>
                            </div>
                            <aceeditor
                                v-model="currentQuestionScript"
                                :show-lang-selector="false"
                                base-lang="javascript"
                                :thisId="'helpEditScript'"
                                :showLangSelector="true"
                                v-on:input="runDebouncedChange"
                            ></aceeditor>
                            <p class="alert well">{{"__SCRIPTHELP"|translate}}</p>
                        </div>
                    </div>
                </div>
                <div class="row" key="divideRow">
                    <div class="col-sm-12 ls-space margin top-5 bottom-5">
                        <hr />
                    </div>
                </div>
            </div> 
        </transition>
        <transition name="slide-fade">
            <div class="row" v-if="loading">
                <loader-widget id="mainQuestionGroupEditorLoader" />
            </div>
        </transition>
    </div>
</template>

<script>
import debounce from "lodash/debounce";
import isEqual from "lodash/isEqual";
import merge from "lodash/merge";

import ClassicEditor from "../../../meta/LsCkeditor/src/LsCkEditorClassic.js";
import Aceeditor from "../helperComponents/AceEditor";

import runAjax from "../mixins/runAjax";
import eventChild from "../mixins/eventChild";

export default {
    name: "MainEditor",
    mixins: [runAjax, eventChild],
    components: 
    {
        Aceeditor
    },
    props: {
        loading: { type: Boolean, default: false }
    },
    data() {
        return {
            editorQuestionObject: ClassicEditor,
            editorQuestionData: "",
            editorQuestionConfig: {
                "lsExtension:fieldtype": "editquestion",
                "lsExtension:ajaxOptions": {
                    surveyid: this.$store.getters.surveyid,
                    qid: window.QuestionEditData.qid,
                    gid: window.QuestionEditData.gid,
                    action:
                        this.$store.state.currentQuestion.qid == null
                            ? "addquestion"
                            : "editquestion"
                },
                "lsExtension:currentFolder":
                    "upload/surveys/" + this.$store.getters.surveyid + "/"
            },
            editorHelpObject: ClassicEditor,
            editorHelpData: "",
            editorHelpConfig: {
                "lsExtension:fieldtype": "editquestion_help",
                "lsExtension:ajaxOptions": {
                    surveyid: this.$store.getters.surveyid,
                    qid: window.QuestionEditData.qid,
                    gid: window.QuestionEditData.gid,
                    action:
                        this.$store.state.currentQuestion.qid == null
                            ? "addquestion"
                            : "editquestion"
                },
                "lsExtension:currentFolder":
                    "upload/surveys/" + this.$store.getters.surveyid + "/"
            },
            questionEditSource: false,
            scriptForAllLanugages: false,
            helpEditSource: false,
            debug: false,
            firstStart: true,
            changeTriggered: debounce((content, event) => {
                this.$log.log("Debounced load triggered", { content, event });
            }, 3000)
        };
    },
    computed: {
        currentQuestionQuestion: {
            get() {
                return this.$store.state.currentQuestionI10N[
                    this.$store.state.activeLanguage
                ].question;
            },
            set(newValue) {
                this.$store.commit("updateCurrentQuestionI10NValue", {
                    value: "question",
                    newValue
                });
            }
        },
        currentQuestionHelp: {
            get() {
                return this.$store.state.currentQuestionI10N[
                    this.$store.state.activeLanguage
                ].help;
            },
            set(newValue) {
                this.$store.commit("updateCurrentQuestionI10NValue", {
                    value: "help",
                    newValue
                });
            }
        },
        currentQuestionScript: {
            get() {
                return this.$store.state.currentQuestionI10N[
                    this.$store.state.activeLanguage
                ].script;
            },
            set(newValue) {
                if(this.scriptForAllLanugages === true) {
                    this.$store.commit("updateFullQuestionI10NValue", {
                        value: "script",
                        newValue
                    });
                    return;
                }

                this.$store.commit("updateCurrentQuestionI10NValue", {
                    value: "script",
                    newValue
                });
            }
        },
        currentQuestionI10N() {
            return this.$store.state.currentQuestionI10N[
                this.$store.state.activeLanguage
            ];
        },
        questionImmutableI10NQuestion() {
            return this.$store.state.questionImmutableI10N[
                this.$store.state.activeLanguage
            ].question;
        },
        questionImmutableI10NHelp() {
            return this.$store.state.questionImmutableI10N[
                this.$store.state.activeLanguage
            ].help;
        }
    },
    methods: {
        changedParts() {
            let changed = {};
            this.$log.log("CHANGE!", {
                currentQuestionQuestion: this.currentQuestionQuestion,
                questionImmutableI10NQuestion: this
                    .questionImmutableI10NQuestion,
                currentQuestionHelp: this.currentQuestionHelp,
                questionImmutableI10NHelp: this.questionImmutableI10NHelp,
                questionEqal: isEqual(
                    this.currentQuestionQuestion,
                    this.questionImmutableI10NQuestion
                ),
                helpEqual: isEqual(
                    this.currentQuestionHelp,
                    this.questionImmutableI10NHelp
                )
            });
            if (
                !(
                    isEqual(
                        this.currentQuestionQuestion,
                        this.questionImmutableI10NQuestion
                    ) &&
                    isEqual(
                        this.currentQuestionHelp,
                        this.questionImmutableI10NHelp
                    )
                )
            ) {
                changed["changedText"] = this.currentQuestionI10N;
            }
            if (
                !isEqual(
                    this.$store.state.currentQuestion.type,
                    this.$store.state.questionImmutable.type
                )
            ) {
                changed["changedType"] = this.$store.state.currentQuestion.type;
            }
            this.$log.log("CHANGEOBJECT", changed);

            return merge(changed, window.LS.data.csrfTokenData);
        },
        runDebouncedChange(content, event) {
            this.changeTriggered(content, event);
        },
        toggleSourceEditQuestion() {
            this.questionEditSource = !this.questionEditSource;
        },
        toggleSourceEditHelp() {
            this.helpEditSource = !this.helpEditSource;
        }
    },
    created() {
        if (
            this.$store.state.currentQuestionPermissions.editorpreset ==
            "source"
        ) {
            this.questionEditSource = true;
            this.helpEditSource = true;
        }
    },
    mounted() {
    
    }
};
</script>

<style lang="scss" scoped>
.scope-set-min-height {
    min-height: 40vh;
}
.scope-border-simple {
    border: 1px solid #cfcfcf;
}
.scope-overflow-scroll {
    overflow: scroll;
    height: 100%;
    width: 100%;
}
.scope-preview {
    margin: 15px 5px;
    padding: 2rem;
    border: 3px double #dfdfdf;
    min-height: 20vh;
    resize: vertical;
    overflow: auto;
}
.scope-contains-ckeditor {
    min-height: 10rem;
}
</style>
