<template>
    <div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane ls-space padding left-0 top-0 bottom-0 right-5 margin top-5">
        <!-- Add Question Group and Add Question Buttons -->
        <add-question-group-and-add-question-buttons
            :isSurveyActive="this.surveyIsActive" 
            :createQuestionGroupLink="this.createQuestionGroupLinkString" 
            :createQuestionLink="this.createQuestionLinkString" 
            :isCreateQuestionAllowed="this.createQuestionAllowed"
            :allowOrganizer="this.allowOrganizer" />
        <!-- List of all Question Groups with Questions -->
        <list-of-all-question-groups-with-questions
            :orderedQuestionGroups="this.questionGroups"
            :isSurveyActive="this.surveyIsActive"
            :allowOrganizer="this.allowOrganizer"
            :currentlyDraggingQuestionGroups="this.currentlyDraggingQuestionGroups" />
    </div>
</template>
<script>
import _ from "lodash";
import pjaxMixins from "../../../mixins/pjaxMixins.js";
import translateMixins from "../../../mixins/translateMixins.js";
import AddQuestionGroupAndAddQuestionButtons from './_addQuestionGroupAndAddQuestionButtons.vue';
import ListOfAllQuestionGroupsWithQuestions from './_listOfAllQuestionGroupsWithQuestions.vue';
import EventBus from '../../../../eventbus.js';

export default {
    name: 'QuestionExplorer',
    mixins: [pjaxMixins, translateMixins],
    filters: {
        translate: function(value) {
            return value;
        }
    },
    componets: {
        'add-question-group-and-add-question-buttons': AddQuestionGroupAndAddQuestionButtons,
        'list-of-all-question-groups-with-questions': ListOfAllQuestionGroupsWithQuestions,
    },
    data() {
        return {
            openQuestionGroups: [],
            currentlyDraggingQuestionGroups: false,
            draggedQuestionGroup: null,
            questionDragging: false,
            draggedQuestion: null,
            draggedQuestionsGroup: null,
            questionGroups: [],
            lastQuestionGroupOpened: false,
            isSurveyActive: false,
            createQuestionGroupLinkString: '',
            createQuestionLinkString: '',
            sideMenuData: [],
        };
    },
    computed: {
        allowOrganizer() {
            return this.$store.state.allowOrganizer === 1;
        },
        surveyIsActive() {
            return this.isSurveyActive;
        },
        createQuestionGroupLink() { 
            return this.createQuestionGroupLinkString;
        },
        createQuestionLink() { 
            return this.createQuestionLinkString;
        },
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            return containerHeight - 100;
        },
        orderedQuestionGroups() {
            return _.orderBy(
                this.questionGroups,
                a => {
                    return parseInt(a.group_order || 999999);
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
        collapseAll() {
            this.openQuestionGroups = [];
        },
        questionHasCondition(question) {
            return question.relevance !== '1';
        },
        questionItemClasses(question) {
            let classes = "";
            classes +=
                this.$store.state.lastQuestionOpen === question.qid
                    ? "selected activated"
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
            classes += this.isOpen(questionGroup.gid) ? " selected " : " ";
            classes += this.isActive(questionGroup.gid) ? " activated " : " ";

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
                    return parseInt(a.question_order || 999999);
                },
                ["asc"]
            );
        },
        isActive(gid) {
            return gid == this.lastQuestionGroupOpened;
        },
        toggleActivation(index) {
            if (this.isOpen(index)) {
                let removed = _.remove(this.openQuestionGroups, idx => {
                    return idx === index;
                });
            } else {
                this.openQuestionGroups.push(index);
            }
            this.$store.commit("questionGroupOpenArray", this.openQuestionGroups);
            this.updatePjaxLinks(this.$store);
        },
        addActive(questionGroupId) {
            if (!this.isOpen(questionGroupId)) {
                this.openQuestionGroups.push(questionGroupId);
            }
            this.$store.commit("questionGroupOpenArray", this.openQuestionGroups);
        },
        openQuestionGroup(questionGroup) {
            this.addActive(questionGroup.gid);
            this.$store.commit("lastQuestionGroupOpen", questionGroup);
            this.updatePjaxLinks(this.$store);
        },
        openQuestion(question) {
            this.addActive(question.gid);
            this.$store.commit("lastQuestionOpen", question);
            this.updatePjaxLinks(this.$store);
            $(document).trigger("pjax:load", { url: question.link });
        },
        //dragevents questiongroups
        startDraggingGroup($event, questiongroupObject) {
            this.draggedQuestionGroup = questiongroupObject;
            this.currentlyDraggingQuestionGroups = true;
            $event.dataTransfer.setData("text/plain", "node");
        },
        endDraggingGroup($event, questiongroupObject) {
            if (this.draggedQuestionGroup !== null) {
                this.draggedQuestionGroup = null;
                this.currentlyDraggingQuestionGroups = false;
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
                
            if (this.currentlyDraggingQuestionGroups) {
                const targetPosition = parseInt(questiongroupObject.group_order);
                const currentPosition = parseInt(this.draggedQuestionGroup.group_order);
                if(Math.abs(parseInt(targetPosition)-parseInt(currentPosition)) == 1){
                    questiongroupObject.group_order = currentPosition;
                    this.draggedQuestionGroup.group_order = targetPosition
                } 
                
            } else {
                if (this.$store.state.SideMenuData.isActive) {
                    return;
                }
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
                            this.draggedQuestion.question_order = this.draggedQuestionsGroup.questions.length + 1;
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
                if (this.questionDragging.gid !== questionObject.gid && this.$store.state.SideMenuData.isActive) {
                    return;
                }
                let orderSwap = questionObject.question_order;
                questionObject.question_order = this.draggedQuestion.question_order;
                this.draggedQuestion.question_order = orderSwap;
            }
        }
    },
    created() {
        this.questionGroups = this.$store.state.questiongroups;
        this.lastQuestionGroupOpened = this.$store.state.lastQuestionGroupOpen;
        this.sideMenuData = this.$store.state.SideMenuData;
    },
    mounted() {
        this.openQuestionGroups = this.$store.state.questionGroupOpenArray;
        this.updatePjaxLinks(this.$store);
        this.isSurveyActive = this.$store.state.SideMenuData.isActive;
        this.createQuestionGroupLinkString = this.$store.state.SideMenuData.createQuestionGroupLink;
        this.createQuestionLinkString = this.$store.state.SideMenuData.createQuestionLink;

        EventBus.$on('collapseAll', (payload) => {
            this.openQuestionGroups = $payload;
        });
    }
};
</script>
<style lang="scss">
.scoped-bottom-bar {
    align-self: flex-end;
}
.scoped-toolbuttons-left {
    flex: 3 0 auto;
    align-self: flex-start;
    .btn {
        flex: 1;
    }
}
.scoped-toolbuttons-right {
    flex: 2 1 auto;
    align-self: flex-end;
    white-space: nowrap;
    .btn {
        float: right;
    }
}
.list-group-item.question-question-list-item .editIcon {
    margin: 10px 10px 10px 5px;
}
.display-as-container{
    display: block;
}
#questionexplorer {
    overflow: auto;
}

</style>
