<script>
import _ from "lodash";
import ajaxMethods from "../../mixins/runAjax.js";

export default {
    mixins: [ajaxMethods],
    data(){
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
        allowOrganizer() {return this.$store.state.allowOrganizer===1},
        surveyIsActive() {return window.SideMenuData.isActive; },
        createQuestionGroupLink() {
            return window.SideMenuData.createQuestionGroupLink
        },
        createQuestionLink() {
            return window.SideMenuData.createQuestionLink
        },
        calculatedHeight() {
            let containerHeight = this.$store.state.maxHeight;
            return containerHeight - 100;
        },
        orderedQuestionGroups() {
            return LS.ld.orderBy(
                this.$store.state.questiongroups,
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
        toggleOrganizer(){
            this.$store.dispatch('unlockLockOrganizer');
        },
        collapseAll() {
            this.active = [];
        },
        createFullQuestionLink() {
            if (LS.reparsedParameters().combined.gid) {
                return this.createQuestionLink + '&gid=' + LS.reparsedParameters().combined.gid;
            } else {
                return this.createQuestionLink;
            }
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
            return LS.ld.orderBy(
                questionList,
                a => {
                    return parseInt(a.question_order || 999999);
                },
                ["asc"]
            );
        },
        isActive(gid) {
            return gid == this.$store.state.lastQuestionGroupOpen;
        },
        isOpen(index) {
            const result = LS.ld.indexOf(this.active, index) != -1;

            if (this.questiongroupDragging === true) return false;

            return result;
        },
        toggleActivation(index) {
            if (this.isOpen(index)) {
                let removed = LS.ld.remove(this.active, idx => {
                    return idx === index;
                });
            } else {
                this.active.push(index);
            }
            this.$store.commit("questionGroupOpenArray", this.active);
            this.updatePjaxLinks();
        },
        addActive(questionGroupId) {
            if (!this.isOpen(questionGroupId)) {
                this.active.push(questionGroupId);
            }
            this.$store.commit("questionGroupOpenArray", this.active);
        },
        openQuestionGroup(questionGroup) {
            this.addActive(questionGroup.gid);
            this.$store.commit("lastQuestionGroupOpen", questionGroup);
            this.updatePjaxLinks();
        },
        openQuestion(question) {
            this.addActive(question.gid);
            this.$store.commit("lastQuestionOpen", question);
            this.updatePjaxLinks();
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
                if(window.SideMenuData.isActive) {return;}
                this.addActive(questiongroupObject.gid);
                if (this.draggedQuestion.gid !== questiongroupObject.gid) {
                    const removedFromInital = LS.ld.remove(
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
                            LS.ld.each(
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
                if(this.questionDragging.gid !== questionObject.gid && window.SideMenuData.isActive) {return;}
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
            //this.$forceUpdate();
        });
    }
};
</script>
<template>
    <div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane ls-space padding left-0 top-0 bottom-0 right-5 margin top-5">
        <div
            class="ls-flex-row wrap align-content-center align-items-center ls-space margin top-5 bottom-15 button-sub-bar"
            v-if="createAllowance != ''"
        >
            <div class="scoped-toolbuttons-left">
                <a
                    id="adminsidepanel__sidebar--selectorCreateQuestionGroup"
                    v-if="( createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1 )"
                    :href="createQuestionGroupLink" class="btn btn-small btn-primary pjax"
                >
                    <!-- <i class="fa fa-plus"></i> -->
                    <i class="ri-add-fill"></i>
                    &nbsp;
                    {{"createPage"|translate}}
                </a>
                <a
                    id="adminsidepanel__sidebar--selectorCreateQuestion"
                    v-if="createQuestionAllowed"
                    :href="createFullQuestionLink()"
                    class="btn btn-small btn-outline-secondary ls-space margin right-10 pjax"
                >
                    <i class="ri-add-circle-fill"></i>
                    &nbsp;
                    {{"createQuestion"|translate}}
                </a>
            </div>
            <div class="scoped-toolbuttons-right">
                <button
                    class="btn btn-outline-secondary"
                    @click="toggleOrganizer"
                    :title="translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle')"
                >
                    <i :class="allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill'" />
                </button>
                <button
                    class="btn btn-outline-secondary"
                    @click="collapseAll"
                    :title="translate('collapseAll')"
                >
                    <i class="ri-fullscreen-exit-line" />
                </button>
            </div>
        </div>
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

                  <div class="d-flex nowrap ls-space padding right-5 bottom-5">
                    <i
                        v-if="!surveyIsActive"
                        class="ri-menu-fill bigIcons dragPointer"
                        :class=" allowOrganizer ? '' : 'disabled' "
                        :draggable="allowOrganizer"
                        @dragend="endDraggingGroup($event, questiongroup)"
                        @dragstart="startDraggingGroup($event, questiongroup)"
                        @click.stop.prevent="()=>false"
                    >
                      &nbsp;
                    </i>
                    <div class="w-100">
                      <a
                          class="d-flex pjax"
                          :href="questiongroup.link"
                      >
                        <span
                            class="question_text_ellipsize"
                            :style="{ 'max-width': itemWidth }"
                            @click.stop="openQuestionGroup(questiongroup)"
                        >
                        {{ questiongroup.group_name }}
                        </span>
                        <div class="ms-auto">
                          <span
                              class="badge reverse-color ls-space margin right-5"
                              @click.stop="openQuestionGroup(questiongroup)"
                          >
                                  {{ questiongroup.questions.length }}
                          </span>
                          <i class="fa bigIcons"
                             v-bind:class="isOpen(questiongroup.gid) ? 'black-caret fa-caret-up' : 'black-caret fa-caret-down'"
                             @click.prevent="toggleActivation(questiongroup.gid)">&nbsp;</i>
                        </div>
                      </a>

                    </div>
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
                                data-bs-toggle="tooltip"
                                class="list-group-item question-question-list-item ls-flex-row align-itmes-flex-start"
                                :data-is-hidden="question.hidden"
                                :data-questiontype="question.type"
                                :data-has-condition="questionHasCondition(question)"
                                :title="question.question_flat"
                                @dragenter="dragoverQuestion($event, question, questiongroup)"
                            >
                                    <i
                                        v-if="!$store.state.surveyActiveState"
                                        class="ri-menu-fill margin-right bigIcons dragPointer question-question-list-item-drag"
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
                                    class="col-9 pjax question-question-list-item-link display-as-container ls-text-underline-hover"
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
