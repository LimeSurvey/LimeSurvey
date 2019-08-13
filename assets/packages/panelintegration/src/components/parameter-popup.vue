<script>
export default {
    name : 'lspanel-parameter-popup',
    components : {},
    mixins : {},
    props : {
        'isNew' : {type: Boolean},
    },
    computed : {
        availableQuestions() { return this.$store.state.questionArray},
        currentQuestion: {
            get() { return this.$store.state.currentSelectedQuestion; },
            set(newValue) { this.$store.commit('setCurrentSelectedQuestion', newValue)}
        },
        parameterRow: {
            get(){ return this.$store.state.currentSelectedParameter; },
            set(newValue){ this.$store.commit('setCurrentSelectedParameter', newValue)},
        },
        popupTitle(){
            return this.isNew ? this.translate('New parameter') : this.translate('Edit parameter') ;
        },
        isValid(){
            return !(
                this.parameterRow.parameter==''                                     // can't be empty
                || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(this.parameterRow.parameter)    // cannot contain something else than letters and numbers
                || this.parameterRow.parameter=='sid'                               // cannot be 'sid'
                || this.parameterRow.parameter=='newtest'                           // cannot be 'newtest' 
                || this.parameterRow.parameter=='token'                             // cannot be 'token'
                || this.parameterRow.parameter=='lang'                              // cannot be 'lang'
            );
        }
    },
    methods : {
        _ellipsize(text, iMaxLength, fPosition, sEllipsis){
            text = text || '';
            fPosition = fPosition || 1;
            sEllipsis = sEllipsis || '...';

            const cleanString = text;
            const iStrLen = cleanString.length;
            if(iStrLen>iMaxLength){
                const sBegin = cleanString.substring(0, Math.floor(iMaxLength * fPosition));
                const sEnd = cleanString.substring(iStrLen-(iMaxLength-sBegin.length),iStrLen);
                return sBegin+sEllipsis+sEnd;
            }
            return text;

        },
        printQuestion(question){
            const questionText = this._ellipsize(question.question, 43, 0.75);
            const subquestionText = ` - ${this._ellipsize(question.sqquestion,30,.75)}`;
            const returnstring = `${question.title}: ${questionText}${(subquestionText.length > 3 ? subquestionText : '')}`;
            return returnstring;
        },
        saveChangedParameter(){
            this.$emit('updateparam', {paramRow: this.parameterRow, isNew: this.isNew});
        },
        cancelEditParameter(){
            this.$emit('canceledit');

        },
        updateValues($event){
            this.currentQuestion = this.availableQuestions[$event.target.value];
            this.parameterRow.qid = this.currentQuestion.qid;
            this.parameterRow.sqid = this.currentQuestion.sqid || '';
            this.parameterRow.targetQuestionText = this.printQuestion(this.currentQuestion);
        }
    },
    created(){
        this.currentQuestion = LS.ld.find( 
            this.availableQuestions, 
            (item,i)=>{ 
                return ( 
                    this.parameterRow.qid == item.qid
                    && ( item.sqid == null || item.sqid == this.parameterRow.sqid)
                ); 
            }
        );
    },
    mounted(){}  
}
</script>


<template>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLabel">{{popupTitle}}</h4>
        </div>
        <div class="modal-body">
            <div class='row'>
                <div class='form-group'>
                    <label class='control-label' for='paramname'>{{'Parameter name' | translate}}</label>
                    <div class=''>
                        <input class='form-control' name='paramname' type='text' v-model="parameterRow.parameter" size='20' />
                    </div>
                </div>
                <div class='form-group'>
                    <label class='control-label ' for='targetquestion'>{{'Target question' | translate}}</label>
                    <div class=''>
                        <select class='form-control' name='targetquestion' size='1' @change="updateValues($event)">
                            <option value=''>{{'No target question'|translate}}</option>
                            <option 
                                v-for="(question, index) in availableQuestions" 
                                v-bind:key="question.qid" 
                                :value="index" 
                                :selected="question.qid==parameterRow.qid"
                                v-html="printQuestion(question)" 
                            />
                        </select>
                    </div>
                </div>
                <div>
                    <input type="hidden" v-model="parameterRow.qid" />
                    <input type="hidden" v-model="parameterRow.sqid" />
                </div>
                <div class='form-group'>
                    <div class='col-sm-12 text-center'>
                        <button class='btn btn-success' v-show="isValid" @click.prevent="saveChangedParameter()">
                            <span class="fa fa-floppy-o"></span>
                            {{'Save'|translate}}
                        </button>
                        <button class='btn btn-danger' @click.prevent="cancelEditParameter()" >{{'Cancel'|translate}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
