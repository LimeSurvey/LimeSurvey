<template>
    <div id="ListOfAllQuestionGroupsWithQuestions">
        <div class="ls-flex-row ls-space padding all-0">
            <ul 
                class="list-group col-12 questiongroup-list-group"  
                @drop="dropQuestionGroup($event, questiongroup)"
            >
                <li 
                    v-for="questiongroup in orderedQuestionGroups" 
                    v-bind:key="questiongroup.gid" 
                    class="list-group-item ls-flex-column" 
                    v-bind:class="questionGroupItemClasses(questiongroup)" 
                    @dragenter="dragoverQuestiongroup($event, questiongroup)"
                >
                    <div class="col-12 ls-flex-row nowrap ls-space padding right-5 bottom-5">
                        <i 
                            v-if="!isSurveyActive"
                            class="fa fa-bars bigIcons dragPointer" 
                            :class=" allowOrganizer ? '' : 'disabled' "
                            :draggable="allowOrganizer"
                            @dragend="endDraggingGroup($event, questiongroup)" 
                            @dragstart="startDraggingGroup($event, questiongroup)"
                            @click.stop.prevent="()=>false"
                        >
                            &nbsp;
                        </i>
                        <a 
                            class="col-12 pjax"
                            :href="questiongroup.link" 
                            @click.stop="openQuestionGroup(questiongroup)" 
                        > 
                            <span 
                                :class="$store.getters.isRTL ? 'question_text_ellipsize pull-right' : 'question_text_ellipsize pull-left'"
                                :style="{ 'max-width': itemWidth }"
                            >
                                {{questiongroup.group_name}} 
                            </span>
                            <span 
                                :class="$store.getters.isRTL ? 'badge ls-space margin right-5 pull-left' : 'badge ls-space margin right-5 pull-right'"
                            >
                                {{questiongroup.questions.length}}
                            </span>
                        </a>
                        <i class="fa bigIcons" v-bind:class="isOpen(questiongroup.gid) ? 'fa-caret-up' : 'fa-caret-down'" @click.prevent="toggleActivation(questiongroup.gid)">&nbsp;</i>
                    </div>
                    <transition name="slide-fade-down">
                        <ul 
                            class="list-group background-muted padding-left question-question-list" 
                            v-if="isOpen(questiongroup.gid)" 
                            @drop="dropQuestion($event, question)"
                        >
                            <li 
                                v-for="question in orderQuestions(questiongroup.questions)" 
                                v-bind:key="question.qid" 
                                v-bind:class="questionItemClasses(question)" 
                                data-toggle="tootltip" 
                                class="list-group-item question-question-list-item ls-flex-row align-itmes-flex-start" 
                                :data-is-hidden="question.hidden"
                                :data-questiontype="question.type"
                                :data-has-condition="questionHasCondition(question)"
                                :title="question.question_flat"
                                @dragenter="dragoverQuestion($event, question, questiongroup)"
                            >
                                    <i 
                                        v-if="!$store.state.surveyActiveState"
                                        class="fa fa-bars margin-right bigIcons dragPointer question-question-list-item-drag" 
                                        :class=" allowOrganizer ? '' : 'disabled' "
                                        :draggable="allowOrganizer"
                                        @dragend="endDraggingQuestion($event, question)" 
                                        @dragstart="startDraggingQuestion($event, question, questiongroup)"
                                        @click.stop.prevent="()=>false"
                                    >
                                        &nbsp;
                                    </i>
                                <a
                                    :href="question.link"  
                                    class="col-9 pjax question-question-list-item-link display-as-container" 
                                    @click.stop.prevent="openQuestion(question)" 
                                > 
                                    <span 
                                        class="question_text_ellipsize" 
                                        :class="{'question-hidden' : question.hidden}" 
                                        :style="{ width: itemWidth }"
                                    >
                                        [{{question.title}}] &rsaquo; {{ question.question_flat }} 
                                    </span> 
                                </a>
                            </li>
                        </ul>
                    </transition>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
import _ from 'lodash';

import EventBus from '../../../../eventbus.js';

export default {
    name: 'ListOfAllQuestionGroupsWithQuestions',
    props: {
        orderedQuestionGroups: Array,
        isSurveyActive: Boolean,
        allowOrganizer: Boolean,
        currentlyDraggingQuestionGroups: Boolean,
    },
    data() {
        return {
            openQuestionGroups: [],
        }
    },
    methods: {
        isOpen(index) {
            const result = _.indexOf(this.openQuestionGroups, index) != -1;
            if (this.currentlyDraggingQuestionGroups) {
                result = false;
            }
            return result;
        },
        endDraggingGroup($event, questionGroup) {
            if (this.draggedQuestionGroup !== null) {
                this.draggedQuestionGroup = null;
                this.currentlyDraggingQuestionGroups = false;
                EventBus.$emit('questiongrouporder', (payload) => {
                    
                });
            }
        }
    },
    mounted() {
        EventBus.$on('updateOpenQuestions', (payload) => {
            this.openQuestionGroups = payload;
        });
    }
}
</script>
