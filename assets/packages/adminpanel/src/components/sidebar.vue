<script>
import _ from "lodash";
import ajaxMixin from "../mixins/runAjax.js";
import Questionexplorer from "./subcomponents/_questionsgroups.vue";
import Sidemenu from "./subcomponents/_sidemenu.vue";
import Quickmenu from "./subcomponents/_quickmenu.vue";

export default {
    components: {
        questionexplorer: Questionexplorer,
        sidemenu: Sidemenu,
        quickmenu: Quickmenu
    },
    mixins: [ajaxMixin],
    props: {
        user: { type: Number },
        translate: { type: Object },
        getQuestionsUrl: { type: String },
        getMenuUrl: { type: String },
        createQuestionGroupLink: { type: String },
        createQuestionLink: { type: String },
        updateOrderLink: { type: String },
        isActive: {type: String},
        basemenus: {type: Object}
    },
    data: () => {
        return {
            currentTab: "settings",
            activeMenuIndex: 0,
            openSubpanelId: 0,
            questiongroups: [],
            menues: [],
            collapsed: false,
            sideBarWidth: "315",
            initialPos: { x: 0, y: 0 },
            isMouseDown: false,
            isMouseDownTimeOut: null,
            sidemenus: {},
            collapsedmenus: {},
            topmenus: {},
            bottommenus: {},
            sideBarHeight: "400px",
            showLoader: false
        };
    },
    computed: {
        getSideBarWidth() {
            return this.$store.state.isCollapsed ? "98" : this.sideBarWidth;
        },
        sortedMenus() {
            return _.orderBy(
                this.menues,
                a => {
                    return parseInt(a.order || 999999);
                },
                ["asc"]
            );
        },
        showSideMenu() {
            return (
                !this.$store.state.isCollapsed &&
                this.$store.state.currentTab == "settings"
            );
        },
        showQuestionTree() {
            return (
                !this.$store.state.isCollapsed &&
                this.$store.state.currentTab == "questiontree"
            );
        },
        calculateSideBarMenuHeight() {
            let currentSideBar = this.$store.state.sideBarHeight;
            return _.min(currentSideBar, Math.floor(screen.height * 2)) + "px";
        },
        getWindowHeight() {
            return screen.height * 2 + "px";
        },
        getloaderHeight() {
            return $("#sidebar").height();
        }
    },
    methods: {
        calculateHeight(self) {
            self.$store.commit(
                "changeSideBarHeight",
                $("#in_survey_common").height()
            );
        },
        changedQuestionGroupOrder() {
            const self = this;
            const onlyGroupsArray = _.map(
                this.$store.state.questiongroups,
                (questiongroup, count) => {
                    const questions = _.map(
                        questiongroup.questions,
                        (question, i) => {
                            return {
                                qid: question.qid,
                                question: question.question,
                                gid: question.gid,
                                question_order: question.question_order
                            };
                        }
                    );
                    return {
                        gid: questiongroup.gid,
                        group_name: questiongroup.group_name,
                        group_order: questiongroup.group_order,
                        questions: questions
                    };
                }
            );
            this.$log.log("QuestionGroup order changed");
            this.showLoader = true;
            this.post(this.updateOrderLink, {
                grouparray: onlyGroupsArray,
                surveyid: this.$store.surveyid
            }).then(
                result => {
                    self.$log.log("questiongroups updated");
                    self.getQuestions().then(() => {
                        self.showLoader = false;
                    });
                },
                error => {
                    self.$log.error("questiongroups updating error!");
                    this.post(this.updateOrderLink, {
                        surveyid: this.$store.surveyid
                    }).then(()=>{
                        self.getQuestions().then(() => {
                            self.showLoader = false;
                        });
                    });
                }
            );
        },
        controlActiveLink() {
            //get current location
            let currentUrl = window.location.href;

            //Check for corresponding menuItem
            let lastMenuItemObject = false;
            _.each(this.$store.state.sidemenus, (itm, i) => {
                _.each(itm.entries, (itmm, j) => {
                    lastMenuItemObject = _.endsWith(currentUrl, itmm.link)
                        ? itmm
                        : lastMenuItemObject;
                });
            });

            //check for quickmenu menuLinks
            let lastQuickMenuItemObject = false;
            _.each(this.$store.state.collapsedmenus, (itm, i) => {
                _.each(itm.entries, (itmm, j) => {
                    lastQuickMenuItemObject = _.endsWith(currentUrl, itmm.link)
                        ? itmm
                        : lastQuickMenuItemObject;
                });
            });

            //check for corresponding question group object
            let lastQuestionGroupObject = false;
            _.each(this.$store.state.questiongroups, (itm, i) => {
                let regTest = new RegExp(
                    "questiongroups/sa/edit/surveyid/" +
                        itm.sid +
                        "/gid/" +
                        itm.gid
                );
                lastQuestionGroupObject =
                    regTest.test(currentUrl) || _.endsWith(currentUrl, itm.link)
                        ? itm
                        : lastQuestionGroupObject;
            });

            //check for corresponding question group
            let lastQuestionObject = false;
            _.each(this.$store.state.questiongroups, (itm, i) => {
                _.each(itm.questions, (itmm, j) => {
                    let regTest = new RegExp(
                        "editquestion/surveyid/" +
                            itmm.sid +
                            "/gid/" +
                            itmm.gid +
                            "/qid/" +
                            itmm.qid
                    );
                    lastQuestionObject =
                        _.endsWith(currentUrl, itmm.link) ||
                        regTest.test(currentUrl)
                            ? itmm
                            : lastQuestionObject;
                });
            });

            //unload every selection
            this.$store.commit("closeAllMenus");
            // self.$log.debug('setMenuActive', {
            //     lastMenuItemObject : lastMenuItemObject,
            //     lastQuickMenuItemObject : lastQuickMenuItemObject,
            //     lastQuestionObject : lastQuestionObject,
            //     lastQuestionGroupObject : lastQuestionGroupObject
            // });
            //apply selection based on the url
            if (
                lastMenuItemObject != false &&
                this.$store.state.isCollapsed != true
            )
                this.$store.commit("lastMenuItemOpen", lastMenuItemObject);
            if (
                lastQuickMenuItemObject != false &&
                this.$store.state.isCollapsed == true
            )
                this.$store.commit("lastMenuItemOpen", lastQuickMenuItemObject);
            if (lastQuestionObject != false)
                this.$store.commit("lastQuestionOpen", lastQuestionObject);
            if (lastQuestionGroupObject != false) {
                this.$store.commit(
                    "lastQuestionGroupOpen",
                    lastQuestionGroupObject
                );
                this.$store.commit(
                    "addToQuestionGroupOpenArray",
                    lastQuestionGroupObject
                );
            }
        },
        editEntity() {
            this.setActiveMenuIndex(null, "question");
        },
        openEntity() {
            this.setActiveMenuIndex(null, "question");
        },
        changeTab(currentTab) {
            this.$store.commit("changeCurrentTab", currentTab);
            this.currentTab = currentTab;
        },
        activeTab(currentTab) {
            return this.$store.state.currentTab === currentTab;
        },
        setActiveMenuIndex(index) {
            this.$store.commit("lastMenuItemOpen", index);
            this.activeMenuIndex = index;
        },
        setOpenSubpanel(sId) {
            this.openSubpanelId = sId;
            this.$store.commit("lastMenuOpen", sId);
            this.$emit("menuselected", sId);
        },
        toggleCollapse() {
            this.$store.state.isCollapsed = !this.$store.state.isCollapsed;
            this.$store.commit(
                "changeIsCollapsed",
                this.$store.state.isCollapsed
            );
            if (this.$store.state.isCollapsed) {
                this.sideBarWidth = "98";
            } else {
                this.sideBarWidth = this.$store.state.sidebarwidth;
            }
        },
        mousedown(e) {
            this.isMouseDown = this.$store.state.isCollapsed ? false : true;
            $("#sidebar").removeClass("transition-animate-width");
            $("#pjax-content").removeClass("transition-animate-width");
        },
        mouseup(e) {
            if (this.isMouseDown) {
                this.isMouseDown = false;
                this.$store.state.isCollapsed = false;
                if (
                    parseInt(this.sideBarWidth) < 250 &&
                    !this.$store.state.isCollapsed
                ) {
                    this.toggleCollapse();
                    this.$store.commit("changeSidebarwidth", "340");
                } else {
                    this.$store.commit("changeSidebarwidth", this.sideBarWidth);
                }
                $("#sidebar").addClass("transition-animate-width");
                $("#pjax-content").removeClass("transition-animate-width");
            }
        },
        mouseleave(e) {
            if (this.isMouseDown) {
                const self = this;
                this.isMouseDownTimeOut = setTimeout(() => {
                    self.mouseup(e);
                }, 1000);
            }
        },
        mousemove(e, self) {
            if (this.isMouseDown) {
                // prevent to emit unwanted value on dragend
                if (e.screenX === 0 && e.screenY === 0) {
                    return;
                }
                if (e.clientX > screen.width / 2) {
                    this.$store.commit("maxSideBarWidth", true);
                    return;
                }
                self.sideBarWidth = e.pageX + 8 + "px";
                this.$store.commit("changeSidebarwidth", this.sideBarWidth);
                this.$store.commit("maxSideBarWidth", false);
                window.clearTimeout(self.isMouseDownTimeOut);
                self.isMouseDownTimeOut = null;
            }
        },
        getQuestions() {
            this.questiongroups = [];
            return this.get(this.getQuestionsUrl).then(result => {
                this.$log.log("Questions", result);
                this.questiongroups = result.data.groups;
                this.$store.commit("updateQuestiongroups", this.questiongroups);
                this.$forceUpdate();
                this.updatePjaxLinks();
            });
        },
        setBaseMenuPosition(entries, position){
            switch(position) {
                case 'side' : 
                    this.sidemenus = _.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateSidemenus", this.sidemenus);
                    break;
                case 'collapsed':
                    this.collapsedmenus = _.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateCollapsedmenus", this.collapsedmenus);
                    break;
                case 'top':
                    this.topmenus = _.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateTopmenus", this.topmenus);
                    break;
                case 'bottom':
                    this.bottommenus = _.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateBottommenus", this.bottommenus);
                    break;
            };
        },
        getSidemenus() {
            this.sidemenus = [];
            return this.get(this.getMenuUrl, { position: "side" }).then(
                result => {
                    this.$log.log("sidemenues", result);
                    this.sidemenus = _.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateSidemenus", this.sidemenus);
                    this.$forceUpdate();
                    this.updatePjaxLinks();
                }
            );
        },
        getCollapsedmenus() {
            this.collapsedmenus = [];
            return this.get(this.getMenuUrl, { position: "collapsed" }).then(
                result => {
                    this.$log.log("quickmenu", result);
                    this.collapsedmenus = _.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit(
                        "updateCollapsedmenus",
                        this.collapsedmenus
                    );
                    this.$forceUpdate();
                    this.updatePjaxLinks();
                }
            );
        },
        getTopmenus() {
            return this.get(this.getMenuUrl, { position: "top" }).then(
                result => {
                    this.$log.log("topmenus", result);
                    this.topmenus = _.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateTopmenus", this.topmenus);
                    this.$forceUpdate();
                    this.updatePjaxLinks();
                }
            );
        },
        getBottommenus() {
            return this.get(this.getMenuUrl, { position: "bottom" }).then(
                result => {
                    this.$log.log("bottommenus", result);
                    this.bottommenus = _.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    this.$store.commit("updateBottommenus", this.bottommenus);
                    this.$forceUpdate();
                    this.updatePjaxLinks();
                }
            );
        }
    },
    created() {
        const self = this;
        
        self.$store.commit('setSurveyActiveState', (parseInt(this.isActive)===1));
        // self.$log.debug(this.$store.state);
        this.currentTab = self.$store.state.currentTab;
        this.activeMenuIndex = this.$store.state.lastMenuOpen;
        if (this.$store.state.isCollapsed) {
            this.sideBarWidth = "98";
        } else {
            this.sideBarWidth = self.$store.state.sidebarwidth;
        }
        _.each(this.basemenus, this.setBaseMenuPosition)
        //retrieve the current menues via ajax
        this.getQuestions();
        this.getSidemenus();
        this.getCollapsedmenus();
        this.getTopmenus();
        this.getBottommenus();
    },
    mounted() {
        const self = this;

        $(document).trigger("sidebar:mounted");
        //Calculate the sidebar height and bin it to the resize event
        self.calculateHeight(self);
        window.addEventListener("resize", () => {
            self.calculateHeight(self);
        });
        

        $(document).on("vue-sidemenu-update-link", () => {
            this.controlActiveLink();
        });

        $(document).on("vue-reload-remote", () => {
            this.getQuestions();
            this.getSidemenus();
            this.getCollapsedmenus();
            this.getTopmenus();
            this.getBottommenus();
            this.$forceUpdate();
        });

        $(document).on("vue-redraw", () => {
            this.getQuestions();
            this.getSidemenus();
            this.getCollapsedmenus();
            this.getTopmenus();
            this.getBottommenus();
            this.$forceUpdate();
        });

        //control the active link
        this.controlActiveLink();

        this.$forceUpdate();
        this.updatePjaxLinks();
        $("body").on("mousemove", event => {
            self.mousemove(event, self);
        });
    }
};
</script>
<template>
    <div id="sidebar" class="ls-flex ls-ba ls-space padding left-0 col-md-4 hidden-xs nofloat transition-animate-width" :style="{'max-height': $store.state.inSurveyViewHeight, width : $store.getters.sideBarSize}" @mouseleave="mouseleave" @mouseup="mouseup">
        <div class="sidebar_loader" :style="{width: getSideBarWidth, height: getloaderHeight}" v-if="showLoader"><div class="ls-flex ls-flex-column fill align-content-center align-items-center"><i class="fa fa-circle-o-notch fa-2x fa-spin"></i></div></div>
        <div class="col-12 fill-height ls-space padding all-0" style="height: 100%">
            <div class="mainMenu container-fluid col-12 ls-space padding right-0 fill-height">
                <div class="ls-space margin bottom-15 top-5 col-12" style="height: 40px;">
                    <div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 right-10 bottom-0 top-0">
                        <transition name="fade">
                            <button class="btn btn-default ls-space padding left-15 right-15" v-if="!$store.state.isCollapsed" @click="toggleCollapse">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                        </transition>
                        <transition name="fade">
                            <div class="ls-flex-item grow-10 col-12" v-if="!$store.state.isCollapsed">
                                <div class="btn-group btn-group col-12">
                                    <button id="adminpanel__sidebar--selectorSettingsButton" class="btn col-6 force color white onhover tabbutton" :class="activeTab('settings') ? 'btn-primary' : 'btn-default'" @click="changeTab('settings')">{{translate.settings}}</button>
                                    <button id="adminpanel__sidebar--selectorStructureButton" class="btn col-6 force color white onhover tabbutton" :class="activeTab('questiontree') ? 'btn-primary' : 'btn-default'" @click="changeTab('questiontree')">{{translate.structure}}</button>
                                </div>
                            </div>
                        </transition>
                        <transition name="fade">
                            <button class="btn btn-default ls-space padding left-15 right-15" v-if="$store.state.isCollapsed" @click="toggleCollapse">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </transition>
                    </div>
                </div>
                <transition name="slide-fade">
                    <sidemenu :style="{'min-height': calculateSideBarMenuHeight}" v-show="showSideMenu"></sidemenu>
                </transition>
                <transition name="slide-fade">
                    <questionexplorer :style="{'min-height': calculateSideBarMenuHeight}" v-show="showQuestionTree" :create-question-group-link="createQuestionGroupLink" :create-question-link="createQuestionLink" :translate="translate" v-on:openentity="openEntity" v-on:questiongrouporder="changedQuestionGroupOrder"></questionexplorer>
                </transition>
                <transition name="slide-fade">
                    <quickmenu :style="{'min-height': calculateSideBarMenuHeight}" v-show="$store.state.isCollapsed"></quickmenu>
                </transition>
            </div>
        </div>
        <div class="resize-handle ls-flex-column" :style="{'height': calculateSideBarMenuHeight, 'max-height': getWindowHeight}">
            <button v-show="!$store.state.isCollapsed" class="btn btn-default" @mousedown="mousedown" @click.prevent="()=>{return false;}"><i class="fa fa-ellipsis-v"></i></button>
        </div>
    </div>
    
</template>
<style lang="scss" scoped>
.sidebar_loader {
    height: 100%;
    position: absolute;
    width: 100%;
    background: rgba(231, 231, 231, 0.3);
    z-index: 4501;
    box-shadow: 8px 0px 15px rgba(231, 231, 231, 0.3);
    top: 0;
}
</style>
