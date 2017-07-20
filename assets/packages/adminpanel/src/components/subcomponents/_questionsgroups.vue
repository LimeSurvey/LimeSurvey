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
            active:[]
        };
    },
    computed: {
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            return (containerHeight - 100);
        }
    },
    methods: {
        isActive(index){
            const result =  (_.indexOf(this.active,index)!=-1);
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
        }
    },
    mounted(){
        this.active = this.$store.state.questionGroupOpenArray;
        this.updatePjaxLinks();
    }
}
</script>
<template>
    <div id="questionexplorer" class="ls-flex-column fill ls-ba">
        <div class="ls-flex-row wrap align-content-space-between align-items-space-between ls-space margin top-5 bottom-15">
            <a v-if="( createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1 )" :href="createQuestionGroupLink" class="btn btn-small btn-primary">
                <i class="fa fa-plus"></i>&nbsp;{{translate.createQuestionGroup}}</a>
            <a v-if="( createQuestionLink!=undefined && createQuestionLink.length>1 )" :href="createQuestionLink" class="btn btn-small btn-default">
                <i class="fa fa-plus-circle"></i>&nbsp;{{translate.createQuestion}}</a>
        </div>
        <ul class="list-group">
            <li v-for="questiongroup in $store.state.questiongroups" class="list-group-item ls-flex-column" v-bind:key="questiongroup.gid" v-bind:class="isActive(questiongroup.gid) ? 'selected' : ''" >
                <div class="col-12 ls-flex-row nowrap ls-space padding left-5 bottom-5">
                    <i class="fa fa-bars bigIcons" draggable="true">&nbsp;</i>
                    <a :href="questiongroup.link" @click.stop="openQuestionGroup(questiongroup)" class="col-12 pjax"> 
                        {{questiongroup.group_name}} 
                        <span class="badge pull-right ls-space margin right-5">{{questiongroup.questions.length}}</span>
                    </a>
                    <i class="fa bigIcons" v-bind:class="isActive(questiongroup.gid) ? 'fa-caret-up' : 'fa-caret-down'" @click.prevent="toggleActivation(questiongroup.gid)">&nbsp;</i>
                </div>
                <ul class="list-group background-muted padding-left" v-if="isActive(questiongroup.gid)">
                    <li v-for="question in questiongroup.questions" v-bind:key="question.qid" v-bind:class="($store.state.lastQuestionOpen == question.qid ? 'selected' : '')" class="list-group-item ls-flex-row align-itmes-flex-between">
                        <i class="fa fa-bars margin-right bigIcons" draggable="true">&nbsp;</i>
                        <a @click.stop="openQuestion(question)" :href="question.link" class="pjax" data-toggle="tootltip" :title="question.question"> <i>[{{question.title}}]</i> {{question.name_short}} </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>

<style lang="scss">
    .selected{
        padding-left: 20px;
        background: linear-gradient(to right, rgb(50, 134, 55) 0px, rgb(50, 134, 55) 13px, white 13px, white 100%);
    }
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