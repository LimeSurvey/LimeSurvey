<script>
import merge from 'lodash/merge';
import remove from 'lodash/remove';
import isEmpty from 'lodash/isEmpty';
import foreach from 'lodash/foreach';

export default {
    name: 'subquestions',
    data(){
        return {
            tmpSubquestions: []
        };
    },
    computed: {
        surveyActive(){
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
        currentSubquestions() {
            return this.$store.state.currentQuestionSubquestions.map((scaleArray, scaleId) => {
                    if(!isEmpty(this.tmpSubquestions[scaleId])) {
                        return scaleArray.concat(this.tmpSubquestions[scaleId]);
                    }
                    return scaleArray;
                }
            );
        },
    },
    methods: {
        getRandomId(){
            return 'random'+Math.random().toString(36).substr(2, 7);
        },
        getSubquestionTemplate(scaleId = 0){
            let randomId = this.getRandomId();

            let subQuestionTemplate = {
                qid: randomId,
                parent_qid: this.$store.state.currentQuestion.qid,
                sid: this.$store.state.currentQuestion.sid,
                gid: this.$store.state.currentQuestion.gid,
                type: "F",
                title: "",
                preg: null,
                other: "N",
                mandatory: "N",
                question_order: (this.currentSubquestions.length + 1),
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
        openLabelSets() {},
        openQuickAdd() {},
        saveAsLabelSet() {},
        resetSubquestions() {},
        saveSubquestions() {},
        getQuestionForCurrentLanguage(subquestionObject) {
            try {
                return subquestionObject[this.$store.state.activeLanguage].question;
            } catch(e){
                this.$log.error('PROBLEM GETTING LANGUAGE', subquestionObject);
            }
            return JSON.stringify(subquestionObject);
        },
        deleteThisSubquestion(oSubquestion, scaleId) {
            if(/^random.*$/.test(oSubquestion.qid)) {
                let tmpArray = merge([], this.tmpSubquestions);
                tmpArray[scaleId] = remove(tmpArray[scaleId], (oSubquestionIterator) => oSubquestionIterator.qid == oSubquestion.qid);
                this.tmpSubquestions = tmpArray;
            }
        },
        openEditorForThisSubquestion(oSubquestion, scaleId) {

        },
        duplicateThisSubquestion(oSubquestion, scaleId) {

        },
        addSubquestion(scaleId) {
            let tmpArray = merge([], this.tmpSubquestions);
            tmpArray[scaleId] = tmpArray[scaleId] || new Array();
            tmpArray[scaleId].push(this.getSubquestionTemplate(scaleId));
            this.tmpSubquestions = tmpArray;
        },
    }
}
</script>

<template>
    <div class="col-sm-12">
        <div class="container-fluid scoped-main-subquestions-container">
            <div class="row">
                <div class="col-sm-8">
                    <button class="btn btn-default col-3" @click.prevent="openQuickAdd">{{ "Quick add" | translate }}</button>
                    <span class="scoped-spacer col-1" />
                    <button class="btn btn-default" @click.prevent="openLabelSets">{{ "Predefined label sets" | translate }}</button>
                    <button class="btn btn-default" @click.prevent="saveAsLabelSet">{{ "Save as label set" | translate }}</button>
                </div>
                <div class="col-sm-4 text-right">
                    <button class="btn btn-danger col-5" @click.prevent="resetSubquestions">{{ "Reset" | translate }}</button>
                    <button class="btn btn-primary col-6" @click.prevent="saveSubquestions">{{ "Save" | translate }}</button>
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
                <div 
                    :key="subquestionscale+'subquestions'"
                    class="row list-group scoped-subquestion-row-container"
                >
                    <div 
                        class="list-group-item scoped-subquestion-block"
                        v-for="subquestion in currentSubquestions[subquestionscale]"
                        :key="subquestion.qid"
                    >
                        <div class="scoped-move-block">
                            <i class="fa fa-bars" :class="surveyActive ? ' disabled' : ' '"></i>
                        </div>
                        <div class="scoped-code-block">
                            <input
                                type='text'
                                class="form-control"
                                maxlength='20'
                                size='5'
                                :class="surveyActive ? ' disabled' : ' '"
                                :name="'code_'+subquestion.question_order+'_'+subquestionscale" 
                                :value="subquestion.title"
                            />
                        </div>
                        <div class="scoped-content-block">
                            <input
                                type='text'
                                size='20'
                                class='answer form-control input'
                                :id='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                :name='"answer_"+$store.state.activeLanguage+"_"+subquestion.qid+"_"+subquestionscale'
                                :placeholder='translate("Some example subquestion")'
                                :value="getQuestionForCurrentLanguage(subquestion)"
                            />
                        </div>
                        <div class="scoped-relevance-block">
                            <input 
                                type='text' 
                                class='relevance form-control input' 
                                :id='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                :name='"relevance_"+subquestion.qid+"_"+subquestionscale'
                                :value="subquestion.relevance" 
                            />
                        </div>
                        <div class="scoped-actions-block">
                            <button class="btn btn-default btn-small" @click.prevent="deleteThisSubquestion(subquestion, subquestionscale)">
                                <i class="fa fa-trash text-danger"></i>
                                {{ "Delete" | translate }}
                            </button>
                            <button class="btn btn-default btn-small" @click.prevent="openEditorForThisSubquestion(subquestion, subquestionscale)">
                                <i class="fa fa-edit"></i>
                                {{ "Open editor" | translate }}
                            </button>
                            <button class="btn btn-default btn-small" @click.prevent="duplicateThisSubquestion(subquestion, subquestionscale)">
                                <i class="fa fa-copy"></i>
                                {{ "Duplicate" | translate }}
                            </button>
                        </div>

                    </div>
                </div>
                <div class="row" :key="subquestionscale+'addRow'">
                    <div class="col-sm-12 text-right">
                        <button @click.prevent="addSubquestion(subquestionscale)" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                            {{ "Add subquestion" | translate}}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

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
        justify-content: space-evenly;
        &>div {
            flex-basis: auto;
            padding: 1px 2px;
        }
    }
    
    .scoped-move-block {
        flex-grow: 1;
        text-align: center;
        &>i {
            font-size: 28px;
            line-height: 32px;
            &:after{
                content: ' |';
                font-size: 24px;
                vertical-align: text-bottom;
            }
        }
    }
    .scoped-content-block {
        flex-grow: 6;
    }
    .scoped-relevance-block {
        flex-grow: 4;
    }
    .scoped-actions-block {
        flex-grow: 2;
    }

</style>
