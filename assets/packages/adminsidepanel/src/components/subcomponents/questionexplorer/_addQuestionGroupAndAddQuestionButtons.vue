<template>
    <div id="AddGroupAndCreateQuestionButtons">
         <div 
           class="ls-flex-row wrap align-content-center align-items-center ls-space margin top-5 bottom-15 button-sub-bar">
            <div class="scoped-toolbuttons-left">
                <!-- Add Group Button -->
                <!-- Survey is not active -->
                <a v-if="!(isSurveyActive)"
                    id="adminsidepanel__sidebar--selectorCreateQuestionGroup"
                    class="btn btn-small btn-default ls-space margin right-5 pjax"
                    :href="createQuestionGroupLink">
                    <i class="fa fa-plus"></i>&nbsp;
                    {{"createPage"|translate}}
                </a>
                <!-- Survey is active -->
                <a v-else
                    id="adminsidepanel__sidebar--selectorCreateQuestionGroup"
                    :disabled="!(createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1)"
                    class="btn btn-small btn-default ls-space margin right-5 pjax"
                    data-toggle="tooltip"
                    :title="buttonDisabledTooltipGroups"
                >
                    <i class="fa fa-plus"></i>&nbsp;
                    {{"createPage"|translate}}
                </a>

                <!-- Create Question Button -->
                <!-- Survey is not active -->
                <a v-if="!(isSurveyActive)"
                    id="adminsidepanel__sidebar--selectorCreateQuestion"
                    :href="createFullQuestionLink()"
                    class="btn btn-small btn-primary ls-space margin right-10 pjax"
                >
                    <i class="fa fa-plus-circle"></i>&nbsp;
                    {{"createQuestion"|translate}}
                </a>
                <!-- Survey is active -->
                <a v-else
                    id="adminsidepanel__sidebar--selectorCreateQuestion"
                    class="btn btn-small btn-primary ls-space margin right-10 pjax"
                    :disabled="!isCreateQuestionAllowed"
                    data-toggle="tooltip"
                    :title="buttonDisabledTooltipQuestions">
                    <i class="fa fa-plus-circle"></i>&nbsp;
                    {{"createQuestion"|translate}}
                </a>
            </div>
            <div class="scoped-toolbuttons-right">
                <button
                    class="btn btn-default"
                    @click="toggleOrganizer"
                    :title="toggleOrganizerTitle"
                >
                    <i :class="allowOrganizer ? 'fa fa-unlock' : 'fa fa-lock'" />
                </button>
                <button
                    class="btn btn-default"
                    @click="collapseAll"
                    :title="translate(this.$store.state.SideMenuData, 'collapseAll')"
                >
                    <i class="fa fa-compress" />
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import translateMixins from '../../../mixins/translateMixins';
import EventBus from '../../../../eventbus.js';

export default {
    name: 'AddQuestionGroupAndAddQuestionButtons',
    mixins: [translateMixins],
    props: {
        isSurveyActive: Boolean,
        createQuestionGroupLink: String,
        isCreateQuestionAllowed: Boolean,
        createQuestionLink: String,
        allowOrganizer: Boolean,
    },
    data() {
        return {

        }
    },
    filters: {
        translate (string) {
            return string;
        }
    },
    computed: {
        buttonDisabledTooltipQuestions() {
            if (this.isSurveyActive) {
                return this.$store.state.SideMenuData.buttonDisabledTooltipQuestions;
            }
            return "";
        },
        buttonDisabledTooltipGroups() {
            if (this.isSurveyActive) {
                return this.$store.state.SideMenuData.buttonDisabledTooltipGroups;
            }
            return "";
        },
        toggleOrganizerTitle() {
            return this.translate(this.$store.state.SideMenuData, this.allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle')
        }
    },
    methods: {
        createFullQuestionLink() {
            if (this.isCreateQuestionAllowed) {
            if (LS.reparsedParameters().combined.gid) {
              return LS.createUrl(this.createQuestionLink, {gid: LS.reparsedParameters().combined.gid});
            }
            return LS.createUrl(this.createQuestionLink, {});
          }
          return "";
        },
        toggleOrganizer() {
            this.$store.dispatch('unlockOrganizer');
        },
        collapseAll() {
            EventBus.$emit('collapseAll', []);
        }
    },
}
</script>
