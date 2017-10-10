<script>
export default {
    name : 'lspanel-parameter-popup',
    components : {},
    mixins : {},
    props : {
        'translate': {type: Object},
        'parameterRow': {type: Object},
        'questions' : {type: Array},
        'isNew' : {type: Boolean}
    },
    data(){
        return {};
    },
    computed : {
        popupTitle(){
            return this.isNew ? this.translate.popup.newParam : this.translate.popup.editParam ;
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
            sEllipsis = sEllipsis || '&hellip;';

            const cleanString = text;
            const iStrLen = cleanString.length;
            const sBegin = cleanString.substring(0, Math.floor(iMaxLength * fPosition));
            const sEnd = cleanString.substring(iStrLen-(iMaxLength-sBegin.length),iStrLen);
            return sBegin+sEllipsis+sEnd;

        },
        printQuestion(question){
            const questionText = this._ellipsize(question.question, 43, 0.75);
            const subquestionText = ` - ${this._ellipsize(question.squestion,30,.75)}`;
            const returnstring = `${question.title}: ${questionText}${subquestionText}`;
            return returnstring;
        },
        saveChangedParameter(){
            this.$emit('updateparam', {paramRow: this.parameterRow, isNew: this.isNew});
        },
        cancelEditParameter(){
            this.$emit('canceledit');

        },
        updateValues(){
            let qidSqid = this.parameterRow.targetQuestionText.split('-');
            this.parameterRow.qid = qidSqid[0];
            this.parameterRow.sqid = qidSqid[1] || '';
        }
    },
    created(){},
    mounted(){}  
}
</script>


<template>
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" @click.prevent="cancelEditParameter()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="exampleModalLabel">{{popupTitle}}</h4>
        </div>
        <div class="modal-body">
            <div class='row'>
                <div class='form-group'>
                    <label class='control-label ' for='paramname'>{{translate.popup.paramName}}</label>
                    <div class=''>
                        <input class='form-control' name='paramname' type='text' v-model="parameterRow.parameter" size='20' />
                    </div>
                </div>
                <div class='form-group'>
                    <label class='control-label ' for='targetquestion'>{{translate.popup.targetQuestion}}</label>
                    <div class=''>
                        <select class='form-control' name='targetquestion' size='1' v-model="parameterRow.targetQuestionText" @change="updateValues()">
                            <option value=''>{{translate.popup.noTargetQuestion}}</option>
                            <option v-for="question in questions" v-bind:key="question.qid" :value="question.qid+(question.sqid ? '-'+question.sqid : '')">
                                {{printQuestion(question)}} 
                            </option>
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
                            {{translate.popup.save}}
                        </button>
                        <button class='btn btn-danger' @click.prevent="cancelEditParameter()" >{{translate.popup.cancel}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
