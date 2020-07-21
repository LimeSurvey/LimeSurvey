<script>
import eventChild from '../mixins/eventChild.js';
import sortBy from 'lodash/sortBy';
import reverse from 'lodash/reverse';

export default {
    name: 'questionlist',
    mixin: [eventChild],
    data() {
        return {
            sortBy: 'question_order',
            sortReverse: false
        }
    },
    computed: {
        columns(){
            return  [
                {label: 'Order', field: 'question_order'},
                {label: 'Question code', field: 'title'},
                {label: 'Question', field: 'question', useFunction: row => this.getQuestionI10N(row)},
                {label: 'QuestionType', field: 'type'},
                {label: 'Mandatory', field: 'mandatory'},
                {label: 'Encrypted', field: 'encrypted'},
                {label: 'Actions', field: 'actions', useFunction: row => this.getQuestionActions(row), insortable: true},
            ];
        },
        sortedQuestionList(){
            let sorted = sortBy(this.$store.state.questionList, question => {
                let intValue = parseInt(question[this.sortBy]);
                return isNaN(intValue) ? question[this.sortBy] : intValue;
            });
            if(this.sortReverse) {
                reverse(sorted);
            }
            return sorted;
        },
        createQuestionUrl(){
            return window.QuestionGroupEditData.createQuestionUrl;
        }
    },
    methods: {
        getQuestionI10N(question) {
            if(question[this.$store.state.activeLanguage] !== undefined) {
                return question[this.$store.state.activeLanguage].question;
            }
            return '---';
        },
        getFieldForColumn(column, row) {
            if(column.useFunction != undefined) {
                return column.useFunction(row);
            }
            return `<div> ${(row[column.field] || '--')} </div>`;
        },
        selectForSort(column) {
            if(this.sortBy == column.field) {
                this.sortReverse = !this.sortReverse;
                return;
            }
            this.sortBy = column.field;
            this.sortReverse = false;
        },
        getQuestionActions(row) {
            return `<a href="${window.QuestionGroupEditData.openQuestionUrl}${row.qid}" class="btn btn-default btn-xs">
                <i class="fa fa-external-link"> </i>
            </a>`;
        }
    }
}
</script>

<template>
<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="ls-flex-row">
                <div class="ls-flex-item text-left">
                    {{'Question list'|translate}}
                </div>
                <div class="ls-flex-item text-right" v-if="$store.state.currentQuestionGroup.gid != null">
                    <a :href="createQuestionUrl" class="btn btn-sm btn-default pull-right clear pjax" >
                        <i class="fa fa-plus"></i>
                        {{'Create question'|translate}}
                    </a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.field+'-thead'" @click="selectForSort(column)">
                            <b>{{column.label | translate}}</b>
                            <i 
                                :class="sortBy == column.field ?(sortReverse?'fa fa-sort-desc':'fa fa-sort-asc'):'fa fa-sort'" 
                                v-if="!column.insortable"
                            /> 
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="question in sortedQuestionList" :key="question.qid">
                        <td v-for="column in columns" :key="column.field+'-tbody'" v-html="getFieldForColumn(column, question)" />
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>

<style lang="scss" scoped>
</style>
