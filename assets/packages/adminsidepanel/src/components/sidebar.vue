<script>
import _ from "lodash";
import ajaxMixin from "../mixins/runAjax.js";
import Questionexplorer from "./subcomponents/_questionsgroups.vue";
import SidebarStateToggle from "./subcomponents/_sidebarStateToggle.vue";
import Sidemenu from "./subcomponents/_sidemenu.vue";
import Quickmenu from "./subcomponents/_quickmenu.vue";

export default {
    components: {
        questionexplorer: Questionexplorer,
        sidemenu: Sidemenu,
        quickmenu: Quickmenu,
        SidebarStateToggle
    },
    mixins: [ajaxMixin],
    data: () => {
        return {
            activeMenuIndex: 0,
            openSubpanelId: 0,
            menues: [],
            collapsed: false,
            sideBarWidth: "315",
            initialPos: { x: 0, y: 0 },
            isMouseDown: false,
            isMouseDownTimeOut: null,
            sideBarHeight: "400px",
            showLoader: false
        };
    },
    computed: {
        isActive(){ return window.SideMenuData.isActive; },
        questiongroups() { return this.$store.state.questiongroups },
        sidemenus: {
            get(){return this.$store.state.sidemenus; },
            set(newValue) { this.$store.commit("updateSidemenus", newValue); }
        },
        collapsedmenus: {
            get(){return this.$store.state.collapsedmenus; },
            set(newValue) { this.$store.commit("updateCollapsedmenus", newValue); }
        },
        // topmenus: {
        //     get(){return this.$store.state.topmenus; },
        //     set(newValue) { this.$store.commit("updateTopmenus", newValue); }
        // },
        // bottommenus: {
        //     get(){return this.$store.state.bottommenus; },
        //     set(newValue) { this.$store.commit("updateBottommenus", newValue); }
        // },
        currentTab() { return this.$store.state.currentTab; },
        getSideBarWidth() {
            return this.$store.state.isCollapsed ? "98" : this.sideBarWidth;
        },
        sortedMenus() {
            return LS.ld.orderBy(
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
                this.currentTab == "settings"
            );
        },
        showQuestionTree() {
            return (
                !this.$store.state.isCollapsed &&
                this.currentTab == "questiontree"
            );
        },
        calculateSideBarMenuHeight() {
            let currentSideBar = this.$store.state.sideBarHeight;
            return LS.ld.min(currentSideBar, Math.floor(screen.height * 2)) + "px";
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
            const onlyGroupsArray = LS.ld.map(
                this.questiongroups,
                (questiongroup, count) => {
                    const questions = LS.ld.map(
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
            this.post(window.SideMenuData.updateOrderLink, {
                grouparray: onlyGroupsArray,
                surveyid: this.$store.surveyid
            }).then(
                result => {
                    self.$log.log("questiongroups updated");
                    self.$store.dispatch('getQuestions').then(() => {
                        self.showLoader = false;
                    });
                },
                error => {
                    self.$log.error("questiongroups updating error!");
                    this.post(window.SideMenuData.updateOrderLink, {
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
            LS.ld.each(this.sidemenus, (itm, i) => {
                LS.ld.each(itm.entries, (itmm, j) => {
                    lastMenuItemObject = LS.ld.endsWith(currentUrl, itmm.link)
                        ? itmm
                        : lastMenuItemObject;
                });
            });

            //check for quickmenu menuLinks
            let lastQuickMenuItemObject = false;
            LS.ld.each(this.collapsedmenus, (itm, i) => {
                LS.ld.each(itm.entries, (itmm, j) => {
                    lastQuickMenuItemObject = LS.ld.endsWith(currentUrl, itmm.link)
                        ? itmm
                        : lastQuickMenuItemObject;
                });
            });

            //check for corresponding question group object
            let lastQuestionGroupObject = false;
            LS.ld.each(this.questiongroups, (itm, i) => {
                let regTest = new RegExp(
                    "questiongroups/sa/edit/surveyid/" +
                        itm.sid +
                        "/gid/" +
                        itm.gid
                );
                lastQuestionGroupObject =
                    regTest.test(currentUrl) || LS.ld.endsWith(currentUrl, itm.link)
                        ? itm
                        : lastQuestionGroupObject;
            });

            //check for corresponding question group
            let lastQuestionObject = false;
            LS.ld.each(this.questiongroups, (itm, i) => {
                LS.ld.each(itm.questions, (itmm, j) => {
                    let regTest = new RegExp(
                        "editquestion/surveyid/" +
                            itmm.sid +
                            "/gid/" +
                            itmm.gid +
                            "/qid/" +
                            itmm.qid
                    );
                    lastQuestionObject =
                        LS.ld.endsWith(currentUrl, itmm.link) ||
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
        setBaseMenuPosition(entries, position){
            switch(position) {
                case 'side' : 
                    this.sidemenus = LS.ld.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    break;
                case 'collapsed':
                    this.collapsedmenus = LS.ld.orderBy(
                        entries,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    break;
                // case 'top':
                //     this.topmenus = LS.ld.orderBy(
                //         entries,
                //         a => {
                //             return parseInt(a.order || 999999);
                //         },
                //         ["desc"]
                //     );
                //     break;
                // case 'bottom':
                //     this.bottommenus = LS.ld.orderBy(
                //         entries,
                //         a => {
                //             return parseInt(a.order || 999999);
                //         },
                //         ["desc"]
                //     );
                //     break;
            };
        },
    },
    created() {
        const self = this;
        
        self.$store.commit('setSurveyActiveState', (parseInt(this.isActive)===1));
        // self.$log.debug(this.$store.state);
        this.activeMenuIndex = this.$store.state.lastMenuOpen;
        if (this.$store.state.isCollapsed) {
            this.sideBarWidth = "98";
        } else {
            this.sideBarWidth = self.$store.state.sidebarwidth;
        }
        LS.ld.each(window.SideMenuData.basemenus, this.setBaseMenuPosition)
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
            this.$log.log('vue-reload-remote');
            this.$store.dispatch('getQuestions');
            this.$store.dispatch('collectMenus');
            this.updatePjaxLinks();
        });

        $(document).on("vue-redraw", () => {
            this.$log.log('vue-redraw');
            this.$store.dispatch('getQuestions');
            this.$store.dispatch('collectMenus');
        });

        //control the active link
        this.controlActiveLink();
        this.updatePjaxLinks();
        $("body").on("mousemove", event => {
            self.mousemove(event, self);
        });
    }
};
</script>
<template>
    <div id="sidebar" class="ls-flex ls-ba ls-space padding left-0 col-md-4 hidden-xs nofloat transition-animate-width" :style="{'max-height': $store.state.inSurveyViewHeight}" @mouseleave="mouseleave" @mouseup="mouseup">
        <div class="sidebar_loader" :style="{width: getSideBarWidth, height: getloaderHeight}" v-if="showLoader"><div class="ls-flex ls-flex-column fill align-content-center align-items-center"><i class="fa fa-circle-o-notch fa-2x fa-spin"></i></div></div>
        <div class="col-12 fill-height ls-space padding all-0" style="height: 100%">
            <div class="mainMenu container-fluid col-12 ls-space padding right-0 fill-height">
                <sidebar-state-toggle @collapse="toggleCollapse"/>
                <transition name="slide-fade">
                    <sidemenu :style="{'min-height': calculateSideBarMenuHeight}" v-show="showSideMenu"></sidemenu>
                </transition>
                <transition name="slide-fade">
                    <questionexplorer :style="{'min-height': calculateSideBarMenuHeight}" v-show="showQuestionTree" v-on:openentity="openEntity" v-on:questiongrouporder="changedQuestionGroupOrder"></questionexplorer>
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
