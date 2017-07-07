<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../mixins/runAjax.js'
import Questions from './questions.vue'

export default {
    components:{
        questions: Questions
    },
    mixins: [ajaxMethods],
    props: {
        questiongroups: {type: Array}
    },
    data: () => {
        return {
            active:[]
        };
    },
    computed: {
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            console.log(this.$store);
            console.log('containerHeight',containerHeight);
            return (containerHeight - 100);
        }
    },
    methods: {
        onDragover(index){
            index = 'index_'+index;
            this.toggleActivation(index);
        },
        isActive(index){
            index = 'index_'+index;
            const result =  (_.indexOf(this.active,index)!=-1);
            return result;
        },
        toggleActivation(index){
            let sIndex = 'index_'+index;
            if(this.isActive(index)){
               let removed =  _.remove(this.active,(idx)=>{return idx===sIndex;});
            } else {
                this.active.push(sIndex);
            }
            this.$forceUpdate();
            this.$localStorage.set('active',JSON.stringify(this.active));
        },
        openQuestionGroup(questionGroup,index){
            this.toggleActivation(index)
            this.$emit('entityLoaded', questionGroup);
        }
    },
    mounted(){
        this.active = JSON.parse(this.$localStorage.get('active','[]'));
    }
}
</script>
<template>
    <div id="questionexplorer" class="ls-flex-column ls-ba " :style="{height: calculatedHeight+'px'}">
        <ul class="list-group">
            <li v-for="(questiongroup,index) in questiongroups" class="list-group-item ls-flex-column" v-bind:key="questiongroup.gid" v-bind:class="isActive(index) ? 'selected' : ''" >
                <div class="col-12 ls-flex-row nowrap margin-bottom">
                    <i class="fa fa-bars bigIcons" draggable="true">&nbsp;</i>
                    <a :href="questiongroup.link" @click="openQuestionGroup(questiongroup,index)" class="col-12"> 
                        {{questiongroup.group_name}} 
                        <span class="pull-right">({{questiongroup.questions.length}})</span>
                    </a>
                    <i class="fa bigIcons" v-bind:class="isActive(index) ? 'fa-caret-up' : 'fa-caret-down'" @click.prevent="toggleActivation(index)">&nbsp;</i>
                </div>
                <questions :questions="questiongroup.questions" v-if="isActive(index)"></questions>
            </li>
        </ul>
    </div>
</template>

<style lang="scss">
    .selected{
        background-color: #EEF6EF;
        box-shadow: 1px2px 4px #EEF6EF inset;
    }
    .bigIcons {
        font-size: 24px;
    }
    .border-bottom{
        border-bottom: 1px solid #323232;
    }
    .margin-bottom{
        padding-bottom: 5px;
    }
    #questionexplorer{
        overflow: auto;
    }
</style>