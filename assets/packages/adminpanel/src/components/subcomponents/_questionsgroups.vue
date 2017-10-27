<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../../mixins/runAjax.js'

export default {
    mixins: [ajaxMethods],
    props: {
        createQuestionGroupLink : {type: String},
        createQuestionLink : {type: String},
        translate : {type: Object},
    },
    data: () => {
        return {
            active:[],
            questiongroupDragging: false,
            draggedQuestionGroup: null,
            questionDragging: false,
            draggedQuestion: null,
        };
    },
    computed: {
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            return (containerHeight - 100);
        },
        orderedQuestionGroups(){
            return _.orderBy(this.$store.state.questiongroups,(a)=>{return parseInt((a.group_order || 999999)) }, ['asc']);
        }
    },
    methods: {
        orderQuestions(questionList){
            return _.orderBy(questionList,(a)=>{return parseInt((a.question_order || 999999)) }, ['asc']);
        },
        isActive(index){
            const result =  (_.indexOf(this.active,index)!=-1);
            
            if(this.questiongroupDragging===true)
                return false;

            if(this.questionDragging===true)
                return true;
            
            return result;
        },
        toggleActivation(index){
            if(this.isActive(index)){
               let removed =  _.remove(this.active,(idx)=>{return idx===index;});
            } else {
                this.active.push(index);
            }
            this.$forceUpdate();
            this.$store.commit('questionGroupOpenArray',this.active);
            this.updatePjaxLinks();
        },
        addActive(questionGroupId){
            if(!this.isActive(questionGroupId)){
               this.active.push(questionGroupId);
            }  
            this.$store.commit('questionGroupOpenArray',this.active);
        },
        openQuestionGroup(questionGroup){
            this.addActive(questionGroup.gid)
            this.$store.commit('lastQuestionGroupOpen', questionGroup);
            this.$forceUpdate();
            this.updatePjaxLinks();
        },
        openQuestion(question){
            this.addActive(question.gid);
            this.$store.commit('lastQuestionOpen', question);
            this.$forceUpdate();
            this.updatePjaxLinks();
        },
        //dragevents questiongroups
        startDraggingGroup($event, questiongroupObject){
            $event.target.parentElement.parentElement.style.opacity = 0.5;
            this.draggedQuestionGroup = questiongroupObject;
            this.questiongroupDragging = true;
        },
        endDraggingGroup($event, questiongroupObject){
            $event.target.parentElement.parentElement.style.opacity = 1;
            this.draggedQuestionGroup = null;
            this.questiongroupDragging = false;
            this.$emit('questiongrouporder');
        },
        dragoverQuestiongroup($event, questiongroupObject){
            const orderSwap = questiongroupObject.group_order;
            questiongroupObject.group_order = this.draggedQuestionGroup.group_order;
            this.draggedQuestionGroup.group_order = orderSwap;
        },
        //dragevents questions
        startDraggingQuestion($event, questionObject){
            $event.target.parentElement.parentElement.style.opacity = 0.5;
            this.$log.log("Dragging started", questionObject);
            this.questionDragging = true;
        },
        endDraggingQuestion($event, question){
            $event.target.parentElement.parentElement.style.opacity = 1;            
            this.questionDragging = false;
        },
        dragoverQuestion($event, questionObject){
            
        },
    },
    mounted(){
        this.active = this.$store.state.questionGroupOpenArray;
        this.updatePjaxLinks();
    }
}
</script>
<template>
    <div id="questionexplorer" class="ls-flex-column fill ls-ba">
        <div class="ls-flex-row wrap align-content-space-between align-items-space-between ls-space margin top-5 bottom-15 button-sub-bar">
            <a id="adminpanel__sidebar--selectorCreateQuestionGroup" v-if="( createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1 )" :href="createQuestionGroupLink" class="btn btn-small btn-primary">
                <i class="fa fa-plus"></i>&nbsp;{{translate.createQuestionGroup}}</a>
            <a id="adminpanel__sidebar--selectorCreateQuestion" v-if="( createQuestionLink!=undefined && createQuestionLink.length>1 )" :href="createQuestionLink" class="btn btn-small btn-default ls-space margin right-10">
                <i class="fa fa-plus-circle"></i>&nbsp;{{translate.createQuestion}}</a>
        </div>
        <ul class="list-group"  @drop="dropQuestionGroup($event, questiongroup)">
            <li v-for="questiongroup in orderedQuestionGroups" class="list-group-item ls-flex-column" v-bind:key="questiongroup.gid" v-bind:class="isActive(questiongroup.gid) ? 'selected' : ''" @dragenter="dragoverQuestiongroup($event, questiongroup)">
                <div class="col-12 ls-flex-row nowrap ls-space padding left-5 bottom-5">
                    <i class="fa fa-bars bigIcons" draggable="true" @dragend="endDraggingGroup($event, questiongroup)" @dragstart="startDraggingGroup($event, questiongroup)">&nbsp;</i>
                    <a :href="questiongroup.link" @click.stop="openQuestionGroup(questiongroup)" class="col-12 pjax"> 
                        {{questiongroup.group_name}} 
                        <span class="badge pull-right ls-space margin right-5">{{questiongroup.questions.length}}</span>
                    </a>
                    <i class="fa bigIcons" v-bind:class="isActive(questiongroup.gid) ? 'fa-caret-up' : 'fa-caret-down'" @click.prevent="toggleActivation(questiongroup.gid)">&nbsp;</i>
                </div>
                <transition name="slide-fade-down">
                    <ul class="list-group background-muted padding-left" v-if="isActive(questiongroup.gid)" @drop="dropQuestion($event, question)">
                        <li v-for="question in orderQuestions(questiongroup.questions)" v-bind:key="question.qid" v-bind:class="($store.state.lastQuestionOpen == question.qid ? 'selected' : '')" class="list-group-item ls-flex-row align-itmes-flex-between" @dragenter="dragoverQuestion($event, question)">
                            <i class="fa fa-bars margin-right bigIcons" draggable="true" @dragend="endDraggingQuestion($event, question)" @dragstart="startDraggingQuestion($event, question)">&nbsp;</i>
                            <a @click.stop="openQuestion(question)" :href="question.link" class="pjax" data-toggle="tootltip" :title="question.question"> <i>[{{question.title}}]</i> {{question.name_short}} </a>
                        </li>
                    </ul>
                </transition>
            </li>
        </ul>
    </div>
</template>

<style lang="scss">
    .bigIcons {
        font-size: 18px;
        line-height: 21px;
    }
    .border-bottom{
        border-bottom: 1px solid transparent;
    }
    .margin-bottom{
        padding-bottom: 5px;
    }
    #questionexplorer{
        overflow: auto;
    }
</style>
