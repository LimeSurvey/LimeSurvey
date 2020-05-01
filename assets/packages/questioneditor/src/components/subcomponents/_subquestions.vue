<template>
    <div class="col-sm-12">
        <div class="container-fluid scoped-main-subquestions-container">
            <div class="row" v-show="!readonly">

                <div class="col-sm-8" v-if="!isSurveyActive">
                    <button class="btn btn-default col-3" @click.prevent="openQuickAdd()">{{ "Quick add" | translate }}</button>
                </div>

                <div class="col-sm-4 text-right" v-if="!isSurveyActive">
                    <button class="btn btn-danger col-5" @click.prevent="resetSubquestions()">{{ "Reset" | translate }}</button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <hr />
                </div>
            </div>
            <template
                v-for="subquestionscale in subquestionScales"
            >
                <!-- Y-Scale -->
                <div v-if="subquestionscale == 0" :key="subquestionscale" class="panel panel-default">
                        <div class="panel-heading">
                            {{'Y-Scale (lines)'|translate}}
                        </div>
                    <div class="panel-body">
                        <div
                            :key="subquestionscale+'subquestions'"
                            class="row list-group scoped-subquestion-row-container"
                            @dragover.prevent="preventDisallowedCursor"
                        >
                            <div class="list-group-item scoped-subquestion-block header-block">
                                <div class="scoped-move-block" v-show="!readonly">
                                    <div>&nbsp;</div>
                                </div>
                                <div class="scoped-code-block">
                                    {{'Title'|translate}}
                                </div>
                                <div class="scoped-content-block">
                                    {{'Subquestion'|translate}}
                                </div>
                                <div class="scoped-relevance-block">
                                    {{'Condition'}}
                                </div>
                                <div class="scoped-actions-block" v-show="!readonly">
                                    <div>&nbsp;</div>
                                </div>
                            </div>
                            <div
                                class="list-group-item scoped-subquestion-block"
                                v-for="subquestion in currentDataSet[subquestionscale]"
                                :key="subquestion.qid"
                                @dragenter.prevent="dragoverSubQuestion($event, subquestion, subquestionscale)"
                                :class="(subQuestionDragging ? 'movement-active'+ ((subquestion.qid == draggedSubQuestion.qid) ? ' in-movement' : '') : '')"
                            >
                                <div class="scoped-move-block" v-show="!readonly">
                                    <i
                                        class="fa fa-bars"
                                        :class="surveyActive ? ' disabled' : ' '"
                                        :draggable="!surveyActive"
                                        @dragstart="startDraggingSubQuestion($event, subquestion, subquestionscale)"
                                        @dragend="endDraggingSubQuestion($event, subquestion, subquestionscale)"
                                    ></i>
                                </div>
                                <div class="scoped-code-block   ">
                                    <input
                                        type='text'
                                        class="form-control"
                                        maxlength='20'
                                        size='5'
                                        :class="surveyActive ? ' disabled' : ' '"
                                        :disabled="surveyActive"
                                        :name="'code_'+subquestion.question_order+'_'+subquestionscale"
                                        :readonly="readonly"
                                        v-model="subquestion.title"
                                        @dblclick="toggleEditMode"
                                        @keyup.enter.prevent='switchinput("answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale)'
                                    />
                                </div>
                                <div class="scoped-content-block   ">
                                    <input
                                        type='text'
                                        size='20'
                                        class='answer form-control input'
                                        :id='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                        :name='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                        :placeholder='translate("Some example subquestion")'
                                        :value="getQuestionForCurrentLanguage(subquestion)"
                                        :readonly="readonly"
                                        @change="setQuestionForCurrentLanguage(subquestion,$event, arguments)"
                                        @keyup.enter.prevent='switchinput("relevance_"+subquestion.qid+"_"+subquestionscale)'
                                        @dblclick="toggleEditMode"
                                    />
                                </div>
                                <div class="scoped-relevance-block   ">
                                    <div class="input-group">
                                        <div class="input-group-addon">{</div>
                                        <input
                                            type='text'
                                            class='relevance_input_field form-control input'
                                            :id='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                            :name='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                            :readonly="readonly"
                                            v-model="subquestion.relevance"
                                            @dblclick="toggleEditMode"
                                            @keyup.enter.prevent='switchinput(false,$event)'
                                            @focus='triggerScale'
                                            @blur='untriggerScale'
                                        />
                                        <div class="input-group-addon">}</div>
                                    </div>
                                </div>
                                <div class="scoped-actions-block" v-show="!readonly">
                                    <button
                                        v-if="!surveyActive"
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Delete")'
                                        @click.prevent="deleteThisDataSet(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-trash text-danger"></i>
                                        <span class="sr-only">{{ "Delete" | translate }}</span>
                                    </button>
                                        <button
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Open editor")'
                                        @click.prevent="openPopUpEditor(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-edit"></i>
                                        <span class="sr-only">{{ "Open editor" | translate }}</span>
                                    </button>
                                    <button
                                        v-if="!surveyActive"
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Duplicate")'
                                        @click.prevent="duplicateThisDataSet(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-copy"></i>
                                        <span class="sr-only">{{ "Duplicate" | translate }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Buttons Predifined label sets Save as label set and Add Subquestion -->
                        <div class="row custom custom-margin bottom-15" :key="subquestionscale+'metaSettings'" v-show="!readonly">
                            <div class="col-sm-6 text-left">
                                <button v-if="!isSurveyActive" class="btn btn-default" @click.prevent="openLabelSets(subquestionscale)">{{ "Predefined label sets" | translate }}</button>
                                <button class="btn btn-default" @click.prevent="saveAsLabelSet(subquestionscale)">{{ "Save as label set" | translate }}</button>
                            </div>
                            <div class="col-sm-6 text-right">
                                <button @click.prevent="addDataSet(subquestionscale)" class="btn btn-primary" v-if="!surveyActive">
                                    <i class="fa fa-plus"></i>
                                    {{ "Add subquestion" | translate}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- X-Scale -->
                <div v-if="subquestionscale == 1" :key="subquestionscale"  class="panel panel-default">
                    <div class="panel-heading">
                        {{'X-Scale (columns)'|translate}}
                    </div>
                    <div class="panel-body">
                        <div
                            :key="subquestionscale+'subquestions'"
                            class="row list-group scoped-subquestion-row-container"
                            @dragover.prevent="preventDisallowedCursor"
                        >
                            <div class="list-group-item scoped-subquestion-block header-block">
                                <div class="scoped-move-block" v-show="!readonly">
                                    <div>&nbsp;</div>
                                </div>
                                <div class="scoped-code-block">
                                    {{'Title'|translate}}
                                </div>
                                <div class="scoped-content-block">
                                    {{'Subquestion'|translate}}
                                </div>
                                <div class="scoped-relevance-block">
                                    {{'Condition'}}
                                </div>
                                <div class="scoped-actions-block" v-show="!readonly">
                                    <div>&nbsp;</div>
                                </div>
                            </div>
                            <div
                                class="list-group-item scoped-subquestion-block"
                                v-for="subquestion in currentDataSet[subquestionscale]"
                                :key="subquestion.qid"
                                @dragenter.prevent="dragoverSubQuestion($event, subquestion, subquestionscale)"
                                :class="(subQuestionDragging ? 'movement-active'+ ((subquestion.qid == draggedSubQuestion.qid) ? ' in-movement' : '') : '')"
                            >
                                <div class="scoped-move-block" v-show="!readonly">
                                    <i
                                        class="fa fa-bars"
                                        :class="surveyActive ? ' disabled' : ' '"
                                        :draggable="!surveyActive"
                                        @dragstart="startDraggingSubQuestion($event, subquestion, subquestionscale)"
                                        @dragend="endDraggingSubQuestion($event, subquestion, subquestionscale)"
                                    ></i>
                                </div>
                                <div class="scoped-code-block   ">
                                    <input
                                        type='text'
                                        class="form-control"
                                        maxlength='20'
                                        size='5'
                                        :class="surveyActive ? ' disabled' : ' '"
                                        :disabled="surveyActive"
                                        :name="'code_'+subquestion.question_order+'_'+subquestionscale"
                                        :readonly="readonly"
                                        v-model="subquestion.title"
                                        @dblclick="toggleEditMode"
                                        @keyup.enter.prevent='switchinput("answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale)'
                                    />
                                </div>
                                <div class="scoped-content-block   ">
                                    <input
                                        type='text'
                                        size='20'
                                        class='answer form-control input'
                                        :id='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                        :name='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                        :placeholder='translate("Some example subquestion")'
                                        :value="getQuestionForCurrentLanguage(subquestion)"
                                        :readonly="readonly"
                                        @change="setQuestionForCurrentLanguage(subquestion,$event, arguments)"
                                        @keyup.enter.prevent='switchinput("relevance_"+subquestion.qid+"_"+subquestionscale)'
                                        @dblclick="toggleEditMode"
                                    />
                                </div>
                                <div class="scoped-relevance-block   ">
                                    <div class="input-group">
                                        <div class="input-group-addon">{</div>
                                        <input
                                            type='text'
                                            class='relevance_input_field form-control input'
                                            :id='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                            :name='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                            :readonly="readonly"
                                            v-model="subquestion.relevance"
                                            @dblclick="toggleEditMode"
                                            @keyup.enter.prevent='switchinput(false,$event)'
                                            @focus='triggerScale'
                                            @blur='untriggerScale'
                                        />
                                        <div class="input-group-addon">}</div>
                                    </div>
                                </div>
                                <div class="scoped-actions-block" v-show="!readonly">
                                    <button
                                        v-if="!surveyActive"
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Delete")'
                                        @click.prevent="deleteThisDataSet(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-trash text-danger"></i>
                                        <span class="sr-only">{{ "Delete" | translate }}</span>
                                    </button>
                                    <button
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Open editor")'
                                        @click.prevent="openPopUpEditor(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-edit"></i>
                                        <span class="sr-only">{{ "Open editor" | translate }}</span>
                                    </button>
                                    <button
                                        v-if="!surveyActive"
                                        class="btn btn-default btn-small"
                                        data-toggle="tooltip"
                                        :title='translate("Duplicate")'
                                        @click.prevent="duplicateThisDataSet(subquestion, subquestionscale)"
                                    >
                                        <i class="fa fa-copy"></i>
                                        <span class="sr-only">{{ "Duplicate" | translate }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Buttons Predifined label sets Save as label set and Add Subquestion -->
                        <div class="row custom custom-margin bottom-15" :key="subquestionscale+'metaSettings'" v-show="!readonly">
                            <div class="col-sm-6 text-left">
                                <button v-if="!isSurveyActive" class="btn btn-default" @click.prevent="openLabelSets(subquestionscale)">{{ "Predefined label sets" | translate }}</button>
                                <button class="btn btn-default" @click.prevent="saveAsLabelSet(subquestionscale)">{{ "Save as label set" | translate }}</button>
                            </div>
                            <div class="col-sm-6 text-right">
                                <button @click.prevent="addDataSet(subquestionscale)" class="btn btn-primary" v-if="!surveyActive">
                                    <i class="fa fa-plus"></i>
                                    {{ "Add subquestion" | translate}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import max from 'lodash/max';
import merge from 'lodash/merge';
import remove from 'lodash/remove';
import isEmpty from 'lodash/isEmpty';
import foreach from 'lodash/forEach';
import map from 'lodash/map';
import sortBy from 'lodash/sortBy';

import AbstractSubQuestionAndAnswerBase from '../abstracts/_abstractSubquestionAndAnswers'

export default {
    name: 'subquestions',
    extends: AbstractSubQuestionAndAnswerBase,
    data(){
        /*
        Abstract base provides data: 
         - uniqueSelector
         - type
         - orderAttribute
         - typeDefininition
         - typeDefininitionKey
        */
        return {
            uniqueSelector: 'qid',
            type: 'subquestions',
            orderAttribute: 'question_order',
            typeDefininition: 'question',
            typeDefininitionKey: 'title',
            subQuestionDragging: false,
            draggedSubQuestion: null
        };
    },
    computed: {
        /*
        Abstract base provides computed values: 
         - surveyActive
        */
        baseNonNumericPart() { return window.QuestionEditData.baseSQACode.subquestions},
        isSurveyActive() {
            if (this.$store.getters.surveyObject.active == "Y") {
                return true;
            }
            return false;
        },
        subquestionScales(){
            if(this.$store.state.currentQuestion.typeInformation.subquestions == 1) {
                return [0];
            }
            if(this.$store.state.currentQuestion.typeInformation.subquestions == 2) {
                return [0,1];
            }
            return [];
        },
        currentDataSet: {
            get() {
                return map(this.$store.state.currentQuestionSubquestions, 
                    subquestionscale => sortBy(subquestionscale, subquestion => parseInt(subquestion.question_order)));
            },
            set(newValue) {
                this.$store.commit('setCurrentQuestionSubquestions', newValue);
            }
        },
    },
    methods: {
        /*
        Abstract base provides methods: 
         - getLength
         - getNewTitleFromCurrent
         - getRandomId
         - deleteThisDataSet
         - duplicateThisDataSet
         - addDataSet
         - openLabelSets
         - openQuickAdd
         - openPopUpEditor
         - switchinput
         - replaceFromQuickAdd
         - addToFromQuickAdd
         - replaceFromLabelSets
         - addToFromLabelSets
         - saveAsLabelSet
         - editFromSimplePopupEditor
         - reorder
         - preventDisallowedCursor
        */
        getTemplate(scaleId = 0){
            let randomId = this.getRandomId();

            let subQuestionTemplate = {
                qid: randomId,
                parent_qid: this.$store.state.currentQuestion.qid,
                sid: this.$store.state.currentQuestion.sid,
                gid: this.$store.state.currentQuestion.gid,
                type: "F",
                title: this.getNewTitleFromCurrent(scaleId),
                preg: null,
                other: "N",
                mandatory: "N",
                question_order: 0,
                scale_id: ''+scaleId,
                same_default: "0",
                relevance: "1",
                modulename: null,
                };

            foreach(this.$store.state.languages, (lng, lngKey) => {
                subQuestionTemplate[lngKey] = {
                     id: null,
                     qid: randomId,
                     question: "",
                     help:"",
                     language: lngKey
                    }
            });

            return subQuestionTemplate;
        },
        resetSubquestions() {
            this.currentDataSet = this.$store.state.questionSubquestionsImmutable;
        },
        getQuestionForCurrentLanguage(subquestionObject) {
            try {
                return subquestionObject[this.$store.state.activeLanguage].question;
            } catch(e){
                this.$log.error('PROBLEM GETTING LANGUAGE', subquestionObject);
            }
            return '';
        },
        setQuestionForCurrentLanguage(subquestionObject, $event) {
            subquestionObject[this.$store.state.activeLanguage].question = $event.srcElement.value;
        },
        triggerScale($event) {
            $('.scoped-relevance-block').css({'flex-grow': 4, 'max-width': 'initial'});
        },
        untriggerScale($event) {
            $('.scoped-relevance-block').css({'flex-grow': 4, 'max-width': ''});
        },
        //dragevents questions
        startDraggingSubQuestion($event, subQuestionObject, scale) {
            this.$log.log("Dragging started", {$event, subQuestionObject});
            $event.dataTransfer.setData('application/node', $event.target.parentNode.parentNode);
            $event.dataTransfer.setDragImage(document.createElement('span'), 0, 0)
            this.subQuestionDragging = true;
            this.draggedSubQuestion = subQuestionObject;
        },
        endDraggingSubQuestion($event, subQuestionObject, scale) {
            if (this.subQuestionDragging) {
                this.subQuestionDragging = false;
                this.draggedSubQuestion = null;
                this.reorderSubquestions(scale);
            }
        },
        dragoverSubQuestion($event, subQuestionObject, scale) {
            if (this.subQuestionDragging) {
                let orderSwap = subQuestionObject.question_order;
                subQuestionObject.question_order = this.draggedSubQuestion.question_order;
                this.draggedSubQuestion.question_order = orderSwap;
            }
        },
        reorderSubquestions(scale){
            let subquestions = [];
            let last = 0;
            foreach(this.currentDataSet[scale], (subquestion, i) => {
                subquestion.question_order = (i+1)
                subquestions.push(subquestion);
            });
            this.$set(this.currentDataSet, scale, subquestions);
        },
        toggleEditMode(){
            if(this.readonly) {
                this.triggerEvent({ target: 'lsnextquestioneditor', method: 'triggerEditQuestion', content: {} });
            }
        }
    },
    mounted() {
        if(isEmpty(this.$store.state.currentQuestionSubquestions)){
            this.$store.state.currentQuestionSubquestions = {"0": [this.getTemplate()]};
        };
        foreach(this.subquestionScales, this.reorderSubquestions);
    }
}
</script>

<style lang="scss" scoped>
    .scoped-spacer{
        content: ' ';
        display: inline-block;
    }
    .scoped-main-subquestions-container {
        margin: 1rem 0.2rem;
        padding-top: 0.2rem;
        min-height: 25vh;
    }
    .scoped-subquestion-block{
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        justify-content: flex-start;
        &>div {
            flex-basis: auto;
            padding: 1px 2px;
            transition: all 1s ease-in-out;
            @media (min-width: 1279px) {
                white-space: nowrap;
            }
        }
        &.header-block {
            text-align: center;
        }
    }

    .scoped-move-block {
        text-align: center;
        width: 5%;
        cursor: move;
        &:active {
            cursor: grabbing;
        }
        &>i {
            font-size: 28px;
            line-height: 32px;
            @media (min-width: 1279px) {
                &:after{
                    content: ' |';
                    font-size: 24px;
                    vertical-align: text-bottom;
                }
            }
        }
    }
    .scoped-code-block {
        width:10%;
    }
    .scoped-content-block {
        @media (min-width: 1279px) {
            width:30%;
        }
        @media (max-width: 1279px) {
            width:20%;
        }
        flex-grow: 1;
    }
    .scoped-relevance-block {
        min-width:125px;
        @media (min-width: 1279px) {
            width:10%;
            max-width: 20%;
        }
        @media (max-width: 1279px) {
            width:20%;
            max-width: 25%;
        }
    }
    .scoped-actions-block {
        width:15%;
        padding-left: 1rem;
    }

    .movement-active {
        background-color: hsla(0,0,90,0.8);
        &.in-movement {
            background-color: hsla(0,0,60,1);
            width:102%;
            margin-left: -1%;
        }
    }
</style>
