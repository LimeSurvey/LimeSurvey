<script>
import max from 'lodash/max';
import merge from 'lodash/merge';
import remove from 'lodash/remove';
import isEmpty from 'lodash/isEmpty';
import foreach from 'lodash/foreach';

export default {
    name: 'answeroptions',
    data(){
        return {
            tmpansweroptions: []
        };
    },
    computed: {
        surveyActive(){
            return false;
        },
        answeroptionscales(){
            if(this.$store.state.currentQuestion.typeInformation.answerscales == 1) {
                return [0];
            } 
            if(this.$store.state.currentQuestion.typeInformation.answerscales == 2) {
                return [0,1];
            } 
            return [];
        },
        currentansweroptions() {
            return this.$store.state.currentQuestionAnswerOptions.map((scaleArray, scaleId) => {
                    if(!isEmpty(this.tmpansweroptions[scaleId])) {
                        return scaleArray.concat(this.tmpansweroptions[scaleId]);
                    }
                    return scaleArray;
                }
            );
        },
    },
    methods: {
        getNewCodeFromCurrent(scaleId) {
            let nonNumericPart = this.currentansweroptions[scaleId][0].code.replace(/[0-9]/,'');
            let numericPart = this.currentansweroptions[scaleId].reduce((prev, oAnswerOption) => {
                return parseInt(max([prev,oAnswerOption.code.replace(/[^0-9]/,'')]));
            }, 0) + 1 ;
            return nonNumericPart+''+numericPart;
        },
        getRandomId(){
            return 'random'+Math.random().toString(36).substr(2, 7);
        },
        getAnsweroptionTemplate(scaleId = 0){
            let randomId = this.getRandomId();

            let answerOptionTemplate = {
                aid: randomId,
                qid: this.$store.state.currentQuestion.qid,
                code: this.getNewCodeFromCurrent(scaleId),
                sortorder: (this.currentansweroptions.length + 1),
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
        openLabelSets() {},
        openQuickAdd() {},
        saveAsLabelSet() {},
        resetansweroptions() {},
        saveansweroptions() {},
        getAnswerForCurrentLanguage(answerOptionObject) {
            try {
                return answerOptionObject[this.$store.state.activeLanguage].answer;
            } catch(e){
                this.$log.error('PROBLEM GETTING LANGUAGE', answerOptionObject);
            }
            return JSON.stringify(answeroptionObject);
        },
        deleteThisAnsweroption(oAnsweroption, scaleId) {
            if(/^random.*$/.test(oAnsweroption.qid)) {
                let tmpArray = merge([], this.tmpansweroptions);
                tmpArray[scaleId] = remove(tmpArray[scaleId], (oAnsweroptionIterator) => oAnsweroptionIterator.qid == oAnsweroption.qid);
                this.tmpansweroptions = tmpArray;
            }
        },
        openEditorForThisAnsweroption(oAnsweroption, scaleId) {

        },
        duplicateThisAnsweroption(oAnsweroption, scaleId) {

        },
        addAnsweroption(scaleId) {
            let tmpArray = merge([], this.tmpansweroptions);
            tmpArray[scaleId] = tmpArray[scaleId] || new Array();
            tmpArray[scaleId].push(this.getAnsweroptionTemplate(scaleId));
            this.tmpansweroptions = tmpArray;
        },
    }
}
</script>

<template>
    <div class="col-sm-12">
        <div class="container-fluid scoped-main-answeroptions-container">
            <div class="row">
                <div class="col-sm-8">
                    <button class="btn btn-default col-3" @click.prevent="openQuickAdd">{{ "Quick add" | translate }}</button>
                    <span class="scoped-spacer col-1" />
                    <button class="btn btn-default" @click.prevent="openLabelSets">{{ "Predefined label sets" | translate }}</button>
                    <button class="btn btn-default" @click.prevent="saveAsLabelSet">{{ "Save as label set" | translate }}</button>
                </div>
                <div class="col-sm-4 text-right">
                    <button class="btn btn-danger col-5" @click.prevent="resetansweroptions">{{ "Reset" | translate }}</button>
                    <button class="btn btn-primary col-6" @click.prevent="saveansweroptions">{{ "Save" | translate }}</button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <hr />
                </div>
            </div>
            <template
                v-for="answeroptionscale in answeroptionscales"
            >
                <div 
                    :key="answeroptionscale+'answeroptions'"
                    class="row list-group scoped-answeroption-row-container"
                >
                    <div 
                        class="list-group-item scoped-answeroption-block"
                        v-for="answeroption in currentansweroptions[answeroptionscale]"
                        :key="answeroption.aid"
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
                                :name="'code_'+answeroption.sortorder+'_'+answeroptionscale" 
                                :value="answeroption.code"
                            />
                        </div>
                        <div class="scoped-assessments-block">
                                <input
                                    type='numeric'
                                    class='assessment form-control input'
                                    :id="'assessment_'+answeroption.sortorder+'_'+answeroptionscale"
                                    :name="'assessment_'+answeroption.sortorder+'_'+answeroptionscale"
                                    :value="answeroption.assessment_value"
                                    maxlength='5'
                                    size='5'
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
                            />
                        </div>
                        <div class="scoped-actions-block">
                            <button class="btn btn-default btn-small" @click.prevent="deleteThisAnsweroption(answeroption, answeroptionscale)">
                                <i class="fa fa-trash text-danger"></i>
                                {{ "Delete" | translate }}
                            </button>
                            <button class="btn btn-default btn-small" @click.prevent="openEditorForThisAnsweroption(answeroption, answeroptionscale)">
                                <i class="fa fa-edit"></i>
                                {{ "Open editor" | translate }}
                            </button>
                            <button class="btn btn-default btn-small" @click.prevent="duplicateThisAnsweroption(answeroption, answeroptionscale)">
                                <i class="fa fa-copy"></i>
                                {{ "Duplicate" | translate }}
                            </button>
                        </div>

                    </div>
                </div>
                <div class="row" :key="answeroptionscale+'addRow'">
                    <div class="col-sm-12 text-right">
                        <button @click.prevent="addAnsweroption(answeroptionscale)" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                            {{ "Add answeroption" | translate}}
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
    .scoped-assessments-block {
        flex-grow: 2;
    }
    .scoped-actions-block {
        flex-grow: 2;
    }

</style>
