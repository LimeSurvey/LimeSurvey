<script>
import _ from "lodash";
import ajaxMethods from "../../mixins/runAjax.js";

const parseIntOr999999 = (val) => {
    const intVal = parseInt(val);

    if(isNaN(intVal)) {
        return 999999;
    }

    return intVal;
}

export default {

    mixins: [ajaxMethods],
    data(){
        return {
            active: [],
            questiongroupDragging: false,
            draggedQuestionGroup: null,
            questionDragging: false,
            draggedQuestion: null,
            draggedQuestionsGroup: null,
            hoveredQuestion : null,
            hoveredQuestionGroup : null,

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
                a => parseIntOr999999(a.group_order),
                ["asc"]
            );
        },
		createQuestionAllowed() {
			return (
					this.$store.state.questiongroups.length > 0
					&& (this.createQuestionLink != undefined
							&& this.createQuestionLink.length > 1
					)
			);
		},
 
		createQuestionAllowedClass() {
			if (this.createQuestionAllowed) {
				return '';
			} else {
				return 'disabled';
			}
		},
		createQuestionGroupAllowedClass() {
			if (this.createQuestionGroupLink != undefined
					&& this.createQuestionGroupLink.length > 1) {
				return '';
			} else {
				return 'disabled';
			}
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
            return parseInt(this.$store.state.sidebarwidth) - 120 + "px";
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

        itemActivated(question){
            return  this.$store.state.lastQuestionOpen === question.qid;
        },
        groupActivated(questionGroup) {
            return this.$store.state.lastQuestionGroupOpen === questionGroup.gid;
        },
        questionItemClasses(question) {
            let classes = "";
            classes +=
                this.$store.state.lastQuestionOpen === question.qid
                    ? "selected activated"
                    : "selected ";

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
                a => parseIntOr999999(a.question_order),
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
        toggleQuestionGroup(questionGroup) {
            if (!this.isOpen(questionGroup.gid)) {
                this.addActive(questionGroup.gid);
                this.$store.commit("lastQuestionGroupOpen", questionGroup);
                this.updatePjaxLinks();
            } else {
                // collapse opened question group
                const newActive = this.active.filter((gid)=>gid !== questionGroup.gid);
                this.active = [...newActive];
                this.$store.commit("questionGroupOpenArray", this.active);
            }
 
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
                if(window.SideMenuData.isActive && this.draggedQuestion.gid != questionObject.gid) {return;}
                let orderSwap = questionObject.question_order;
                questionObject.question_order = this.draggedQuestion.question_order;
                this.draggedQuestion.question_order = orderSwap;
            }
        },
        onMouseOverQuestionGroup($event, group){
            this.hoveredQuestionGroup = group;
        },

        onMouseOverQuestion($event, question){
            this.hoveredQuestion = question;
        },
        onMouseLeave(){
            this.hoveredQuestion = null;
            this.hoveredQuestionGroup = null;
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
    <div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane h-100 pt-2">
        <div class="ls-flex-row button-sub-bar mb-2">
          <div class="scoped-toolbuttons-right me-2">
            <button
                class="btn btn-sm btn-outline-secondary"
                @click="toggleOrganizer"
                :title="translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle')"
            >
              <i :class="allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill'" />
            </button>
            <button
                class="btn btn-sm btn-outline-secondary me-2"
                @click="collapseAll"
                :title="translate('collapseAll')"
            >
              <i class="ri-link-unlink" />
            </button>
          </div>
        </div>
		<div class="ls-flex-row wrap align-content-center align-items-center button-sub-bar">
			<div class="scoped-toolbuttons-left mb-2 d-flex align-items-center">
                <div class="create-question px-3" data-bs-toggle="tooltip" data-bs-placement="top" :data-bs-original-title="createQuestionAllowed ? '' : translate('deactivateSurvey')" :title="createQuestionAllowed ? '' : translate('deactivateSurvey')">
                    <a id="adminsidepanel__sidebar--selectorCreateQuestion" :href="createFullQuestionLink()"
                        class="btn btn-primary pjax" v-bind:class="createQuestionAllowedClass">
                        <i class="ri-add-circle-fill"></i>
                        &nbsp;
                        {{ 'createQuestion' | translate }}
                    </a>
                </div>

                <div data-bs-toggle="tooltip" data-bs-placement="top" :data-bs-original-title="createQuestionAllowed ? '' : translate('deactivateSurvey')" :title="createQuestionAllowed ? '' : translate('deactivateSurvey')">
                    <a id="adminsidepanel__sidebar--selectorCreateQuestionGroup" v-bind:class="createQuestionGroupAllowedClass"
                        :href="createQuestionGroupLink" class="btn btn-secondary pjax">
                        <!-- <i class="ri-add-line"></i> -->
                        {{ "createPage" | translate }}
                    </a>
                </div>  
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

                  <div class="q-group d-flex nowrap ls-space padding right-5 bottom-5 bg-white ms-2 p-2"
                       v-on:mouseover="onMouseOverQuestionGroup($event, questiongroup)"
                       v-on:mouseleave ="onMouseLeave"

                  >
                    <div
                        class="bigIcons dragPointer me-1"
                        :class=" allowOrganizer ? '' : 'disabled' "
                        :draggable="allowOrganizer"
                        @dragend="endDraggingGroup($event, questiongroup)"
                        @dragstart="startDraggingGroup($event, questiongroup)"
                        @click.stop.prevent="()=>false"
                    >
                      <svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>
                      </svg>
                    </div>
                    <div class="cursor-pointer me-1" @click="toggleQuestionGroup(questiongroup)" 
                         :style="isOpen(questiongroup.gid) ? 'transform: rotate(90deg)' : 'transform: rotate(0deg)'">
                         <i class="ri-arrow-right-s-fill"></i>
                    </div>
                    <div class="w-100 position-relative">
                        <div class="cursor-pointer">
                            <a
                                class="d-flex pjax"
                                :href="questiongroup.link"
                            >
                                <span class="question_text_ellipsize" :style="{ 'max-width': itemWidth }">
                                    {{ questiongroup.group_name }}
                                </span>
                            </a>
                        </div>
                        <div class="dropdown position-absolute top-0 d-flex align-items-center" style="right:5px">
                            <div class=""  @click="toggleQuestionGroup(questiongroup)">
                                <span class="badge reverse-color ls-space margin right-5">
                                    {{ questiongroup.questions.length }}
                                </span>
                            </div>

                            <div v-if="groupActivated(questiongroup) || (hoveredQuestionGroup && hoveredQuestionGroup.gid === questiongroup.gid)">
                            <div class="ls-questiongroup-tools cursor-pointer" id="dropdownMenuButton1"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill"></i>
                            </div>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li v-if="key !== 'delete'" v-for="(value, key) in questiongroup.groupDropdown"
                                    :key="key">
                                    <a class="dropdown-item" :id="value.id" :href="value.url">
                                        <span :class="value.icon"></span>
                                        {{ value.label }}
                                    </a>

                                </li>

                                <li v-else-if="key === 'delete'" :class="value.disabled ? 'disabled' : ''">
                                    <a v-if="!value.disabled" href="#" onclick="return false;" class="dropdown-item"
                                        data-bs-toggle="modal" data-bs-target="#confirmation-modal"
                                        data-btnclass="btn-danger" :data-title="value.dataTitle"
                                        :data-btntext="value.dataBtnText" :data-onclick="value.dataOnclick"
                                        :data-message="value.dataMessage">
                                        <span :class="value.icon"></span>
                                        {{ value.label }}
                                    </a>
                                    <a v-else-if="value.disabled" href="#" onclick="return false;" class="dropdown-item"
                                        data-btnclass="btn-danger" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        :title="value.title">
                                        <span :class="value.icon"></span>
                                        {{ value.label }}
                                    </a>

                                </li>
                            </ul>
                            </div>

                        </div>
                    </div>
                  </div>
                    <transition name="slide-fade-down">
                        <ul
                            class="list-group background-muted padding-left question-question-list"
                            style="padding-right:15px"
                            v-if="isOpen(questiongroup.gid)"
                            @drop="dropQuestion($event, question)"
                        >
                            <li
                                v-for="question in orderQuestions(questiongroup.questions)"
                                v-bind:key="question.qid"
                                v-bind:class="questionItemClasses(question)"
                                data-bs-toggle="tooltip"
                                v-on:mouseover="onMouseOverQuestion($event, question)"
                                v-on:mouseleave ="onMouseLeave"


                                class="list-group-item question-question-list-item ls-flex-row align-itmes-flex-start"
                                :data-is-hidden="question.hidden"
                                :data-questiontype="question.type"
                                :data-has-condition="questionHasCondition(question)"
                                :title="question.question_flat"
                                @dragenter="dragoverQuestion($event, question, questiongroup)"
                            >
                                    <div
                                        v-if="!$store.state.surveyActiveState"
                                        class="margin-right bigIcons dragPointer question-question-list-item-drag"
                                        :class=" allowOrganizer ? '' : 'disabled' "
                                        :draggable="allowOrganizer"
                                        @dragend="endDraggingQuestion($event, question)"
                                        @dragstart="startDraggingQuestion($event, question, questiongroup)"
                                        @click.stop.prevent="()=>false"
                                    >
                                        <svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>
                                        </svg>

                                    </div>
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
                                <div v-if="itemActivated(question)||(hoveredQuestion && hoveredQuestion.qid === question.qid)" class="dropdown position-absolute" style="right:10px" >
                                    <div class="ls-question-tools ms-auto position-relative cursor-pointer" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                        <i class="ri-more-fill"></i>
                                    </div>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li  v-if="key !== 'delete' && !(key === 'language' && Array.isArray(value))"  v-for="(value, key) in question.questionDropdown" :key="key">
                                          <a   class="dropdown-item" :id="value.id" :href="key == 'editDefault' && value.active == 0 ? '#' : value.url" :class=" key == 'editDefault' &&  value.active == 0 ? 'disabled' : '' ">
                                            <span :class="value.icon"></span>
                                            {{value.label}}
                                          </a>

                                        </li>

                                        <li v-else-if="key === 'delete'"  :class=" value.disabled ? 'disabled' : '' ">
                                            <a
                                               v-if="!value.disabled"
                                                href="#"
                                                onclick="return false;"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmation-modal"
                                                data-btnclass="btn-danger"
                                                :data-title="value.dataTitle"
                                                :data-btntext="value.dataBtnText"
                                                :data-onclick="value.dataOnclick"
                                                :data-message="value.dataMessage"
                                            >
                                                <span :class="value.icon"></span>
                                                {{value.label}}
                                            </a>
                                            <a
                                               v-else-if="value.disabled"
                                                href="#"
                                                onclick="return false;"
                                                class="dropdown-item"
                                                data-btnclass="btn-danger"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="bottom"
                                                :title="value.title"

                                            >
                                                <span :class="value.icon"></span>
                                                {{value.label}}
                                            </a>

                                        </li>
                                        <div v-else-if="key === 'language' && Array.isArray(value)">
                                            <li role="separator" class="dropdown-divider"  ></li>
                                            <li class="dropdown-header">Survey logic overview</li>
                                            <li v-for="language in value" >
                                                <a class="dropdown-item" :id="language.id" :href="language.url">
                                                  <span :class="language.icon"></span>
                                                    {{language.label}}
                                                </a>
                                            </li>
                                        </div>

                                    </ul>

                                </div>
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
    background-color: #fff;
    /* position: relative; */
    /* z-index: 2; */
    min-height: 100vh;
}
.question-question-list-item .dropdown-menu li a.disabled {
  opacity: 0.5;
}
</style>
