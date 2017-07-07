<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../mixins/runAjax.js'

export default {
    mixins: [ajaxMethods],
    props: {
        questions: {type: Array}
    },
    data: () => {
        return {
            active: false
        };
    },
    computed: {

    },
    methods: {
        isLast(index){ return (index==this.questions.length);},
        openQuestion(question){ this.$emit('openEntity', {type: 'question', model: question}); location.href=question.link;}
    }
}
</script>
<template>
    <ul class="list-group background-muted padding-left">
        <li v-for="(question,index) in questions" class="list-group-item ls-flex-row align-itmes-flex-between">
            <i class="fa fa-bars margin-right bigIcons" draggable="true">&nbsp;</i>
            <a :href="question.link" @click="openQuestion(question)" data-toggle="tootltip" :title="question.question"> <i>[{{question.title}}]</i> {{question.name_short}} </a>
        </li>
    </ul>
</template>
<style lang="scss">
    .margin-right {
        margin-right: 5px;
    }
    .padding-left{
     padding-left: 10px;
    }
</style>