<template>
    <div class="col-sm-12">
        <div class="container-fluid scoped-main-answeroptions-container">
            <div class="row" v-show="!readonly">
                <div class="col-sm-8">
                    <button class="btn btn-default col-3" @click.prevent="openQuickAdd">{{ "Quick add" | translate }}</button>
                </div>
                <div class="col-sm-4 text-right">
                    <button class="btn btn-danger col-5" @click.prevent="resetansweroptions(answeroptionscale)">{{ "Reset" | translate }}</button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <hr />
                </div>
            </div>
            <template v-for="answeroptionscale in answeroptionscales">
                <div 
                    :key="answeroptionscale+'answeroptions'"
                    class="row list-group scoped-answeroption-row-container"
                    @dragover.prevent="preventDisallowedCursor"
                >
                    <div class="list-group-item scoped-answeroption-block header-block">
                        <div class="scoped-move-block" v-show="!readonly">
                            <div>&nbsp;</div>
                        </div>
                        <div class="scoped-code-block">
                            <div>{{"Code" | translate}}</div>
                        </div>
                        <div class="scoped-assessments-block">
                            <div>{{"Assessment value" | translate}}</div>
                        </div>
                        <div class="scoped-content-block">
                            <div>{{"Answeroption" | translate}}</div>
                        </div>
                        <div class="scoped-actions-block" v-show="!readonly">
                            <div>&nbsp;</div>
                        </div>

                    </div>
                    <div 
                        class="list-group-item scoped-answeroption-block"
                        v-for="answeroption in currentDataSet[answeroptionscale]"
                        :key="answeroption.aid"
                        @dragenter.prevent="dragoverAnsweroption($event, answeroption, answeroptionscale)"
                        :class="(answeroptionDragging ? 'movement-active'+ ((answeroption.aid == draggedAnsweroption.aid) ? ' in-movement' : '') : '')"
                    >
                        <div class="scoped-move-block" v-show="!readonly">
                            <i 
                                class="fa fa-bars" 
                                :class="surveyActive ? ' disabled' : ' '"
                                :draggable="!surveyActive"
                                @dragstart="startDraggingAnsweroption($event, answeroption, answeroptionscale)"
                                @dragend="endDraggingAnsweroption($event, answeroption, answeroptionscale)" 
                            ></i>
                        </div>
                        <div class="scoped-code-block">
                            <input
                                type='text'
                                class="form-control"
                                maxlength='5'
                                size='5'
                                :class="surveyActive ? ' disabled' : ' '"
                                :name="'code_'+answeroption.sortorder+'_'+answeroptionscale" 
                                :readonly="readonly"
                                v-model="answeroption.code"
                                @keyup.enter.prevent="switchinput('assessment_'+answeroption.sortorder+'_'+answeroptionscale)"
                                @dblclick="toggleEditMode"
                            />
                        </div>
                        <div class="scoped-assessments-block">
                                <input
                                    type='numeric'
                                    class='assessment form-control input'
                                    :id="'assessment_'+answeroption.sortorder+'_'+answeroptionscale"
                                    :name="'assessment_'+answeroption.sortorder+'_'+answeroptionscale"
                                    :readonly="readonly"
                                    v-model="answeroption.assessment_value"
                                    maxlength='5'
                                    size='5'
                                    @keyup.enter.prevent='switchinput("answer_"+$store.state.activeLanguage+"_"+answeroption.aid+"_"+answeroptionscale)'
                                    @dblclick="toggleEditMode"
                                />
                        </div>
                        <div class="scoped-content-block">
                            <input
                                type='text'
                                size='20'
                                class='answer form-control input'
                                :id='"answer_"+$store.state.activeLanguage+"_"+answeroption.aid+"_"+answeroptionscale'
                                :name='"answer_"+$store.state.activeLanguage+"_"+answeroption.aid+"_"+answeroptionscale'
                                :placeholder='translate("Some example answer option")'
                                :value="getAnswerForCurrentLanguage(answeroption)"
                                :readonly="readonly"
                                @change="setAnswerForCurrentLanguage(answeroption,$event)"
                                @keyup.enter.prevent='switchinput(false, $event)'
                                @dblclick="toggleEditMode"
                            />
                        </div>
                        <div class="scoped-actions-block" v-show="!readonly">
                            <button 
                                v-if="!surveyActive" 
                                class="btn btn-default btn-small" 
                                data-toggle="tooltip" 
                                :title='translate("Delete")'
                                @click.prevent="deleteThisDataSet(answeroption, answeroptionscale)"
                            >
                                <i class="fa fa-trash text-danger"></i>
                                <span class="sr-only">{{ "Delete" | translate }}</span>
                            </button>
                            <button 
                                class="btn btn-default btn-small" 
                                data-toggle="tooltip" 
                                :title='translate("Open editor")'
                                @click.prevent="openPopUpEditor(answeroption, answeroptionscale)"
                            >
                                <i class="fa fa-edit"></i>
                                <span class="sr-only">{{ "Open editor" | translate }}</span>
                            </button>
                            <button 
                                v-if="!surveyActive" 
                                class="btn btn-default btn-small" 
                                data-toggle="tooltip" 
                                :title='translate("Duplicate")'
                                @click.prevent="duplicateThisDataSet(answeroption, answeroptionscale)"
                            >
                                <i class="fa fa-copy"></i>
                                <span class="sr-only">{{ "Duplicate" | translate }}</span>
                            </button>
                        </div>

                    </div>
                </div>
                <div class="row custom custom-margin bottom-15" :key="answeroptionscale+'addRow'" v-show="!readonly">
                    <div class="col-sm-6 text-left">
                        <button v-if="!surveyActive" class="btn btn-default" @click.prevent="openLabelSets(answeroptionscale)">{{ "Predefined label sets" | translate }}</button>
                        <button class="btn btn-default" @click.prevent="saveAsLabelSet(answeroptionscale)">{{ "Save as label set" | translate }}</button>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button v-if="!surveyActive" @click.prevent="addDataSet(answeroptionscale)" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                            {{ "Add answer option" | translate}}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>

import map from 'lodash/map';
import merge from 'lodash/merge';
import sortBy from 'lodash/sortBy';
import remove from 'lodash/remove';
import isEmpty from 'lodash/isEmpty';
import foreach from 'lodash/forEach';

import AbstractSubQuestionAndAnswerBase from '../abstracts/_abstractSubquestionAndAnswers'

export default {
    name: 'answeroptions',
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
            uniqueSelector: 'aid',
            type: 'answeroptions',
            orderAttribute: 'sortorder',
            typeDefininition: 'answer',
            typeDefininitionKey: 'code',
            answeroptionDragging: false,
            draggedAnsweroption: null
        };
    },
    computed: {
            /*
            Abstract base provides computed values: 
             - surveyActive
            */
        baseNonNumericPart() { return window.QuestionEditData.baseSQACode.answeroptions},
        answeroptionscales(){
            if(this.$store.state.currentQuestion.typeInformation.answerscales == 1) {
                return [0];
            } 
            if(this.$store.state.currentQuestion.typeInformation.answerscales == 2) {
                return [0,1];
            } 
            return [];
        },
        currentDataSet: {
            get() {
                return map(
                    this.$store.state.currentQuestionAnswerOptions, 
                    scale => sortBy(
                        scale, 
                        answeroption => parseInt(answeroption.sortorder)
                    )
                );
            },
            set(newValue) {
                this.$store.commit('setCurrentQuestionAnswerOptions', newValue);
            }
        },
    },
    methods: {
        /**
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
         *
         * @param {number} scaleId
         * @return {object}
         */
        getTemplate(scaleId = 0){
            let randomId = this.getRandomId();

            let answerOptionTemplate = {
                aid: randomId,
                qid: this.$store.state.currentQuestion.qid,
                code: this.getNewTitleFromCurrent(scaleId),
                sortorder: 0,
                scale_id: ''+scaleId,
                assessment_value: 0,
                };

            foreach(this.$store.state.languages, (lng, lngKey) => {
                answerOptionTemplate[lngKey] = {
                     id: null,
                     aid: randomId,
                     answer: "",
                     language: lngKey
                    }
            });

            return answerOptionTemplate;
        },
        resetansweroptions() {
            this.currentDataSet = this.$store.state.questionAnswerOptionsImmutable;
        },
        getAnswerForCurrentLanguage(answerOptionObject) {
            try {
                return answerOptionObject[this.$store.state.activeLanguage].answer;
            } catch(e){
                this.$log.error('PROBLEM GETTING LANGUAGE', answerOptionObject);
            }
            return '';
        },
        setAnswerForCurrentLanguage(answerOptionObject, $event) {
            this.$log.log('setAnswerOption',$event);
            if(!answerOptionObject[this.$store.state.activeLanguage]) {
                answerOptionObject[this.$store.state.activeLanguage] = {};
            }
            answerOptionObject[this.$store.state.activeLanguage].answer = $event.srcElement.value;
        },
        replaceByQuickAddObject(quickAddContent) {
            this.$_log.log({AOQuickAddContent: quickAddContent});
        },
        //dragevents questions
        startDraggingAnsweroption($event, answeroptionObject, scale) {
            this.$log.log("Dragging started", answeroptionObject);
            $event.dataTransfer.setData('application/node', $event.target.parentNode.parentNode);
            $event.dataTransfer.dropEffect = "move";
            $event.dataTransfer.setDragImage(document.createElement('span'), 0, 0)
            this.answeroptionDragging = true;
            this.draggedAnsweroption = answeroptionObject;
        },
        endDraggingAnsweroption($event, answeroptionObject, scale) {
            if (this.answeroptionDragging) {
                this.answeroptionDragging = false;
                this.draggedAnsweroption = null;
                this.reorderAnsweroptions(scale);
            }
        },
        dragoverAnsweroption($event, answeroptionObject, scale) {
            if (this.answeroptionDragging) {
                let orderSwap = answeroptionObject.sortorder;
                answeroptionObject.sortorder = this.draggedAnsweroption.sortorder;
                this.draggedAnsweroption.sortorder = orderSwap;
            }
        },
        reorderAnsweroptions(scale){
            let answeroptions = [];
            let last = 0;
            foreach(this.currentDataSet[scale], (answeroption, i) => {
                answeroption.sortorder = (i+1)
                answeroptions.push(answeroption);
            });
            this.$set(this.currentDataSet, scale, answeroptions);
        },
        toggleEditMode(){
            if(this.readonly) {
                this.triggerEvent({ target: 'lsnextquestioneditor', method: 'triggerEditQuestion', content: {} });
            }
        }
    },
    mounted() {
        if(isEmpty(this.$store.state.currentQuestionAnswerOptions)){
            // NB: 0 is scale id.
            this.$store.state.currentQuestionAnswerOptions = {"0": [this.getTemplate()]};
        };
        foreach(this.answeroptionscales, this.reorderAnsweroptions);
    }
}
</script>

<style lang="scss" scoped>
    .scoped-spacer{
        content: ' ';
        display: inline-block;
    }
    .scoped-main-answeroptions-container {
        margin: 1rem 0.2rem;
        padding-top: 0.2rem;
        min-height: 25vh;
    }
    .scoped-answeroption-block{
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        justify-content: space-evenly;
        &>div {
            flex-basis: auto;
            padding: 1px 2px;
            transition: all 1s ease-in-out;
            @media (min-width: 1279px) {
                white-space: nowrap;
            }
        }
        &.header-block>div {
            display: flex;
            &>div{
                width:100%;
                text-align: center;
            }
        }
    }
    
    .scoped-move-block {
        width:5%;
        text-align: center;
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
    .scoped-assessments-block {
        width:10%
    }
    .scoped-content-block {
        width:50%;
        flex-grow: 1;
    }
    .scoped-actions-block {
        width: 15%;
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
