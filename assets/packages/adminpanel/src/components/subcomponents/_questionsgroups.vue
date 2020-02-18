<script>
import _ from "lodash";
import ajaxMethods from "../../mixins/runAjax.js";

export default {
    mixins: [ajaxMethods],
    props: {
        createQuestionGroupLink: { type: String },
        createQuestionLink: { type: String },
        translate: { type: Object }
    },
    data: () => {
        return {
            active: [],
            questiongroupDragging: false,
            draggedQuestionGroup: null,
            questionDragging: false,
            draggedQuestion: null,
            draggedQuestionsGroup: null
        };
    },
    computed: {
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            return containerHeight - 100;
        },
        orderedQuestionGroups() {
            return _.orderBy(
                this.$store.state.questiongroups,
                a => {
                    return parseInt(a.group_order >= 0 && a.group_order !== null ? a.group_order : 999999);
                },
                ["asc"]
            );
        },
        createQuestionAllowed() {
            return (
                this.$store.state.questiongroups.length > 0 &&
                (this.createQuestionLink != undefined &&
                    this.createQuestionLink.length > 1)
            );
        },
        createAllowance() {
            let createGroupAllowed =
                this.createQuestionGroupLink != undefined &&
                this.createQuestionGroupLink.length > 1
                    ? "g"
                    : "";
            let createQuestionAllowed = this.createQuestionAllowed ? "q" : "";
            return createGroupAllowed + createQuestionAllowed;
        },
        itemWidth() {
            return parseInt(this.$store.state.sidebarwidth) - 95 + "px";
        }
    },
    methods: {
        questionItemClasses(question) {
            let classes = "";
            classes +=
                this.$store.state.lastQuestionOpen === question.qid
                    ? "selected"
                    : " ";

            if (this.draggedQuestion !== null)
                classes +=
                    this.draggedQuestion.qid === question.qid
                        ? " dragged"
                        : " ";

            return classes;
        },
        questionGroupItemClasses(questionGroup) {
            let classes = "";
            classes += this.isActive(questionGroup.gid) ? "selected" : " ";

            if (this.draggedQuestionGroup !== null)
                classes +=
                    this.draggedQuestionGroup.gid === questionGroup.gid
                        ? " dragged"
                        : " ";

            return classes;
        },
        orderQuestions(questionList) {
            return _.orderBy(
                questionList,
                a => {
                    return parseInt(a.group_order >= 0 && a.group_order !== null ? a.group_order : 999999);
                },
                ["asc"]
            );
        },
        isActive(index) {
            const result = _.indexOf(this.active, index) != -1;

            if (this.questiongroupDragging === true) return false;

            return result;
        },
        toggleActivation(index) {
            if (this.isActive(index)) {
                let removed = _.remove(this.active, idx => {
                    return idx === index;
                });
            } else {
                this.active.push(index);
            }
            this.$forceUpdate();
            this.$store.commit("questionGroupOpenArray", this.active);
            this.updatePjaxLinks();
        },
        addActive(questionGroupId) {
            if (!this.isActive(questionGroupId)) {
                this.active.push(questionGroupId);
            }
            this.$store.commit("questionGroupOpenArray", this.active);
        },
        openQuestionGroup(questionGroup) {
            this.addActive(questionGroup.gid);
            this.$store.commit("lastQuestionGroupOpen", questionGroup);
            this.$forceUpdate();
            this.updatePjaxLinks();
        },
        openQuestion(question) {
            this.addActive(question.gid);
            this.$store.commit("lastQuestionOpen", question);
            this.$forceUpdate();
            $(document).trigger("pjax:load", { url: question.link });
        },
        //dragevents questiongroups
        startDraggingGroup($event, questiongroupObject) {
            this.draggedQuestionGroup = questiongroupObject;
            this.questiongroupDragging = true;
            $event.dataTransfer.setData("text/plain", "node");
        },
        endDraggingGroup($event, questiongroupObject) {
            if (this.draggedQuestionGroup !== null) {
                this.draggedQuestionGroup = null;
                this.questiongroupDragging = false;
                this.$emit("questiongrouporder");
            }
        },
        dragoverQuestiongroup($event, questiongroupObject) {
            if(this.draggedQuestion == undefined || this.draggedQuestion == null) {
                this.$log.log({
                    this: this, 
                    questiongroupObject: questiongroupObject,
                    draggedQuestion: this.draggedQuestion
                    });
            }
                
            if (this.questiongroupDragging) {
                const targetPosition = parseInt(questiongroupObject.group_order);
                const currentPosition = parseInt(this.draggedQuestionGroup.group_order);
                if(Math.abs(parseInt(targetPosition)-parseInt(currentPosition)) == 1){
                    questiongroupObject.group_order = currentPosition;
                    this.draggedQuestionGroup.group_order = targetPosition
                } 
                
            } else {
                this.addActive(questiongroupObject.gid);
                if (this.draggedQuestion.gid !== questiongroupObject.gid) {
                    const removedFromInital = _.remove(
                        this.draggedQuestionsGroup.questions,
                        (question, i) => {
                            return question.qid === this.draggedQuestion.qid;
                        }
                    );
                    if (removedFromInital.length > 0) {
                        this.draggedQuestion.question_order = null;
                        questiongroupObject.questions.push(
                            this.draggedQuestion
                        );
                        this.draggedQuestion.gid = questiongroupObject.gid;

                        if (
                            questiongroupObject.group_order >
                            this.draggedQuestionsGroup.group_order
                        ) {
                            this.draggedQuestion.question_order = 0;
                            _.each(
                                questiongroupObject.questions,
                                (question, i) => {
                                    question.question_order =
                                        parseInt(question.question_order) + 1;
                                }
                            );
                        } else {
                            this.draggedQuestion.question_order =
                                this.draggedQuestionsGroup.questions.length + 1;
                        }

                        this.draggedQuestionsGroup = questiongroupObject;
                    }
                }
            }
        },
        //dragevents questions
        startDraggingQuestion($event, questionObject, questionGroupObject) {
            this.$log.log("Dragging started", questionObject);
            $event.dataTransfer.setData('application/node', this);
            this.questionDragging = true;
            this.draggedQuestion = questionObject;
            this.draggedQuestionsGroup = questionGroupObject;
        },
        endDraggingQuestion($event, question) {
            if (this.questionDragging) {
                this.questionDragging = false;
                this.draggedQuestion = null;
                this.draggedQuestionsGroup = null;
                this.$emit("questiongrouporder");
            }
        },
        dragoverQuestion($event, questionObject, questionGroupObject) {
            if (this.questionDragging) {
                let orderSwap = questionObject.question_order;
                questionObject.question_order = this.draggedQuestion.question_order;
                this.draggedQuestion.question_order = orderSwap;
            }
        }
    },
    mounted() {
        this.active = this.$store.state.questionGroupOpenArray;
        this.updatePjaxLinks();

        $(document).on("vue-reload-remote", () => {
            this.$forceUpdate();
        });
    }
};
</script>
<template>
    <div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane ls-space padding all-0 margin top-5">
        <div 
            class="ls-flex-row wrap align-content-space-between align-items-space-between ls-space margin top-5 bottom-15 button-sub-bar" 
            v-if="createAllowance != ''"
        >
            <a 
                id="adminpanel__sidebar--selectorCreateQuestionGroup" 
                v-if="( createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1 )" 
                :href="createQuestionGroupLink" class="btn btn-small btn-primary pjax"
            >
                <i class="fa fa-plus"></i>&nbsp;
                {{translate.createQuestionGroup}}
            </a>
            <a 
                id="adminpanel__sidebar--selectorCreateQuestion" 
                v-if="createQuestionAllowed" 
                :href="createQuestionLink" 
                class="btn btn-small btn-default ls-space margin right-10 pjax"
            >
                <i class="fa fa-plus-circle"></i>&nbsp;
                {{translate.createQuestion}}
            </a>
        </div>
        <div class="ls-flex-row ls-space padding all-0">
            <ul 
                class="list-group col-12"  
                @drop="dropQuestionGroup($event, questiongroup)"
            >
                <li 
                    v-for="questiongroup in orderedQuestionGroups" 
                    v-bind:key="questiongroup.gid" 
                    class="list-group-item ls-flex-column" 
                    v-bind:class="questionGroupItemClasses(questiongroup)" 
                    @dragenter="dragoverQuestiongroup($event, questiongroup)"
                >
                    <div class="col-12 ls-flex-row nowrap ls-space padding left-5 bottom-5">
                        <i 
                            v-if="!$store.state.surveyActiveState"
                            class="fa fa-bars bigIcons dragPointer" 
                            draggable="true"
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
                                class="question_text_ellipsize pull-left" 
                                :style="{ 'max-width': itemWidth }"
                            >
                                {{questiongroup.group_name}} 
                            </span>
                            <span class="badge pull-right ls-space margin right-5">{{questiongroup.questions.length}}</span>
                        </a>
                        <i class="fa bigIcons" v-bind:class="isActive(questiongroup.gid) ? 'fa-caret-up' : 'fa-caret-down'" @click.prevent="toggleActivation(questiongroup.gid)">&nbsp;</i>
                    </div>
                    <transition name="slide-fade-down">
                        <ul 
                            class="list-group background-muted padding-left question-question-list" 
                            v-if="isActive(questiongroup.gid)" 
                            @drop="dropQuestion($event, question)"
                        >
                            <div 
                                v-for="question in orderQuestions(questiongroup.questions)" 
                                v-bind:key="question.qid" 
                                v-bind:class="questionItemClasses(question)" 
                                data-toggle="tootltip" 
                                class="list-group-item question-question-list-item ls-flex-row align-itmes-flex-between" 
                                :title="question.question_flat"
                                @dragenter="dragoverQuestion($event, question, questiongroup)"
                            >
                                    <i 
                                        v-if="!$store.state.surveyActiveState"
                                        class="fa fa-bars margin-right bigIcons dragPointer question-question-list-item-drag" 
                                        draggable="true"
                                        @dragend="endDraggingQuestion($event, question)" 
                                        @dragstart="startDraggingQuestion($event, question, questiongroup)"
                                        @click.stop.prevent="()=>false"
                                    >
                                        &nbsp;
                                    </i>
                                <a
                                    :href="question.link"  
                                    class="col-12 pjax question-question-list-item-link display-as-container" 
                                    @click.stop.prevent="openQuestion(question)" 
                                > 
                                    <span class="question_text_ellipsize" :style="{ width: itemWidth }">
                                        [{{question.title}}] &rsaquo; {{ question.question_flat }} 
                                    </span> 
                                </a>
                            </div>
                        </ul>
                    </transition>
                </li>
            </ul>
        </div>
    </div>
</template>

<style lang="scss">
.display-as-container{
    display: block;
}
#questionexplorer {
    overflow: auto;
}
</style>
