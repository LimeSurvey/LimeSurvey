<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMixin from '../mixins/runAjax.js';
import Questionexplorer from './subcomponents/_questionsgroups.vue';
import Sidemenu from './subcomponents/_sidemenu.vue';
import Quickmenu from './subcomponents/_quickmenu.vue';

export default {
    components: {
        'questionexplorer': Questionexplorer,
        'sidemenu': Sidemenu,
        'quickmenu': Quickmenu,
    },
    mixins: [ajaxMixin],
    props: {
        'user' : {type: Number},
        'translate': {type: Object},
        'getQuestionsUrl' : {type: String},
        'getMenuUrl' : {type: String},
        'createQuestionGroupLink' : {type: String},
        'createQuestionLink' : ''
    },
    data: () => {
        return {
            'currentTab': 'settings',
            'activeMenuIndex' : 0,
            'openSubpanelId' : 0,
            'questiongroups': [],
            'menues' : [],
            '$store.state.isCollapsed' : false,
            'sideBarWidth': '315px',
            'initialPos' : {x: 0, y: 0},
            'isMouseDown' : false,
            'isMouseDownTimeOut' : null,
            'sidemenus': {},
            'collapsedmenus': {},
            'topmenus': {},
            'bottommenus': {},
        };
    },
    computed: {
        getSideBarWidth(){
            return this.$store.state.isCollapsed ? '98px' : this.sideBarWidth;
        },
        sortedMenus() {
            return _.orderBy(this.menues,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);            
        },
        showSideMenu(){
            return (!this.$store.state.isCollapsed && this.$store.state.currentTab == 'settings');
        },
        showQuestionTree(){
            return (!this.$store.state.isCollapsed && this.$store.state.currentTab == 'questiontree');
        },
        calculateSideBarMenuHeight(){
            return (this.$store.state.generalContainerHeight-70)+'px';
        }
    },
    methods: {
        controlActiveLink(){
            //get current location
            let currentUrl = window.location.href;
            //Check for corresponding menuItem
            let lastMenuItemObject = false;
            _.each(this.$store.state.sidemenus, (itm,i)=>{
                _.each(itm.entries, (itmm,j)=>{
                    lastMenuItemObject =  _.endsWith(currentUrl,itmm.link) ? itmm : lastMenuItemObject;
                });
            });
            //check for quickmenu menuLinks
            let lastQuickMenuItemObject = false;
            _.each(this.$store.state.collapsedmenus, (itm,i)=>{
                _.each(itm.entries, (itmm,j)=>{
                    lastQuickMenuItemObject =  _.endsWith(currentUrl,itmm.link) ? itmm : lastQuickMenuItemObject;
                });
            });
            //check for corresponding question group object
            let lastQuestionGroupObject = false;
            _.each(this.$store.state.questiongroups, (itm,i)=>{
                let regTest = new RegExp('questiongroups/sa/edit/surveyid/'+itm.sid+'/gid/'+itm.gid);
                lastQuestionGroupObject =  (regTest.test(currentUrl) || _.endsWith(currentUrl,itm.link)) ? itm : lastQuestionGroupObject;
            });
            //check for corresponding question group
            let lastQuestionObject = false;
             _.each(this.$store.state.questiongroups, (itm,i)=>{
                _.each(itm.questions, (itmm,j)=>{
                    let regTest = new RegExp('editquestion/surveyid/'+itmm.sid+'/gid/'+itmm.gid+'/qid/'+itmm.qid);
                    lastQuestionObject =  (_.endsWith(currentUrl,itmm.link) || regTest.test(currentUrl)) ? itmm : lastQuestionObject;
                });
            });
            
            //unload every selection
            this.$store.commit('closeAllMenus');
            // self.$log.debug('setMenuActive', {
            //     lastMenuItemObject : lastMenuItemObject,
            //     lastQuickMenuItemObject : lastQuickMenuItemObject,
            //     lastQuestionObject : lastQuestionObject,
            //     lastQuestionGroupObject : lastQuestionGroupObject
            // });
            //apply selection based on the url
            if(lastMenuItemObject != false && this.$store.state.isCollapsed !=true)
                this.$store.commit('lastMenuItemOpen',lastMenuItemObject);
            if(lastQuickMenuItemObject != false && this.$store.state.isCollapsed ==true)
                this.$store.commit('lastMenuItemOpen',lastQuickMenuItemObject);
            if(lastQuestionObject != false )
                this.$store.commit('lastQuestionOpen',lastQuestionObject);
            if(lastQuestionGroupObject != false){
                this.$store.commit('lastQuestionGroupOpen',lastQuestionGroupObject);
                this.$store.commit('addToQuestionGroupOpenArray',lastQuestionGroupObject);
            }
        },
        editEntity(){
            this.setActiveMenuIndex(null,'question');
        },
        openEntity(){
            this.setActiveMenuIndex(null,'question');
        },
        changeTab(currentTab){
            this.$store.commit('changeCurrentTab', currentTab);
            this.currentTab = currentTab;
        },
        activeTab(currentTab){
            return this.$store.state.currentTab === currentTab;
        },
        setActiveMenuIndex(index){
            this.$store.commit('lastMenuItemOpen',index);
            this.activeMenuIndex = index;
        },
        setOpenSubpanel(sId){
            this.openSubpanelId = sId;
            this.$store.commit('lastMenuOpen',sId);
            this.$emit('menuselected', sId);
        },
        toggleCollapse() {
            this.$store.state.isCollapsed = !this.$store.state.isCollapsed;
            this.$store.commit('changeIsCollapsed',this.$store.state.isCollapsed);
            if(this.$store.state.isCollapsed){
                this.sideBarWidth = '98px';
            } else {
                this.sideBarWidth = this.$store.state.sidebarwidth;
            }
        },
        mousedown(e) {
            this.isMouseDown = this.$store.state.isCollapsed ? false : true;
            $('#sidebar').removeClass('transition-animate-width');
            $('#pjax-content').removeClass('transition-animate-width');
        },
        mouseup(e) {
            if(this.isMouseDown){
                this.isMouseDown = false;
                this.$store.state.isCollapsed = false;
                if(parseInt(this.sideBarWidth) < 335 && !this.$store.state.isCollapsed) {
                    this.toggleCollapse();
                    this.$store.commit('changeSidebarwidth', '340px');
                } else {
                    this.$store.commit('changeSidebarwidth', this.sideBarWidth);
                }
                $('#sidebar').addClass('transition-animate-width');
                $('#pjax-content').removeClass('transition-animate-width');
            }
        },
        mouseleave(e) {
            if(this.isMouseDown){
                const self = this;
                this.isMouseDownTimeOut = setTimeout(()=>{
                    self.mouseup(e);
                }, 1000);
                
            }
        },
        mousemove(e,self) {
            if(this.isMouseDown){
                // prevent to emit unwanted value on dragend
                if (e.screenX === 0 && e.screenY === 0) return;
                if(e.clientX > (screen.width/2)) return;
                self.sideBarWidth = (e.pageX+8)+'px';
                this.$store.commit('changeSidebarwidth', this.sideBarWidth);
                window.clearTimeout(self.isMouseDownTimeOut);
                self.isMouseDownTimeOut = null;
            }
        }
    },
    created(){
        const self = this;
        // self.$log.debug(this.$store.state);
        this.currentTab = self.$store.state.currentTab;
        this.activeMenuIndex = this.$store.state.lastMenuOpen; 
        if(this.$store.state.isCollapsed){ 
            this.sideBarWidth = '98px'; 
        } else {
            this.sideBarWidth = self.$store.state.sidebarwidth;
        }
    },
    mounted(){
        const self = this;


        //retrieve the current menues via ajax
        //questions
        this.get(this.getQuestionsUrl).then( (result) =>{
            // self.$log.debug(result);
            self.questiongroups = result.data.groups;
            self.$store.commit('updateQuestiongroups', self.questiongroups);
            self.$forceUpdate();
            this.updatePjaxLinks();
        });

        //sidemenus
        this.get(this.getMenuUrl, {position: 'side'}).then( (result) =>{
            self.$log.debug('sidemenues',result);
            self.sidemenus =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$store.commit('updateSidemenus', self.sidemenus);
            self.$forceUpdate();
            this.updatePjaxLinks();
        });

        //collapsedmenus
        this.get(this.getMenuUrl, {position: 'collapsed'}).then( (result) =>{
            self.$log.debug('quickmenu',result);
            self.collapsedmenus =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$store.commit( 'updateCollapsedmenus', self.collapsedmenus);
            self.$forceUpdate();
            this.updatePjaxLinks();
        });

        //topmenus
        this.get(this.getMenuUrl, {position: 'top'}).then( (result) =>{
            self.$log.debug('topmenus',result);
            self.topmenus =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$store.commit('updateTopmenus', self.topmenus);
            self.$forceUpdate();
            this.updatePjaxLinks();
        });

        //bottommenus
        this.get(this.getMenuUrl, {position: 'bottom'}).then( (result) =>{
            self.$log.debug('bottommenus',result);
            self.bottommenus =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$store.commit('updateBottommenus', self.bottommenus);
            self.$forceUpdate();
            this.updatePjaxLinks();
        });

        //control the active link
        this.controlActiveLink();

        self.$forceUpdate();
        this.updatePjaxLinks();
        $('body').on('mousemove', (event) => {self.mousemove(event,self)});
    }
}
</script>
<template>
    <div id="sidebar" class="ls-flex ls-ba ls-space padding left-0 col-md-4 hidden-xs nofloat transition-animate-width fill-height" :style="{width : sideBarWidth}" @mouseleave="mouseleave" @mouseup="mouseup">
        <div class="col-12 fill-height ls-space padding all-0" v-bind:style="{'height': $store.state.inSurveyViewHeight}">
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
                    <sidemenu :style="{height: calculateSideBarMenuHeight}" v-show="showSideMenu"></sidemenu>
                </transition>
                <transition name="slide-fade">
                    <questionexplorer :style="{height: calculateSideBarMenuHeight}" v-show="showQuestionTree" :create-question-group-link="createQuestionGroupLink" :create-question-link="createQuestionLink" :translate="translate" v-on:openentity="openEntity" ></questionexplorer>
                </transition>
                <transition name="slide-fade">
                    <quickmenu :style="{height: calculateSideBarMenuHeight}" v-show="$store.state.isCollapsed"></quickmenu>
                </transition>
            </div>
        </div>
        <div class="resize-handle" v-bind:style="{'height': $store.state.inSurveyViewHeight}">
            <button v-show="!$store.state.isCollapsed" class="btn btn-default" @mousedown="mousedown" @click.prevent="()=>{return false;}"><i class="fa fa-ellipsis-v"></i></button>
        </div>
    </div>
</template>
<style lang="scss">
    .tabbutton.btn-primary{
        outline: none;
        &:hover, &:focus, &:active{
            &:after{
                color: #246128;
            }
        }
        &:after{
            position: absolute;
            left: 45%;
            bottom: -12px;
            font: normal normal normal 14px/1 FontAwesome;
            font-size: 28px;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            content: "\F078";
            color: #328637;
        }
    }

    .background.white{
        background-color: rgba(255,255,255,1);
        box-shadow: none;
    }

    .overflow-auto{
        overflow-x: hidden;
        overflow-y: auto;
    }

    .resize-handle{
        position: absolute;
        right: 14px;
        top: 0;
        bottom: 0;
        height:100%;
        width: 4px;
        //box-shadow: 0px 5px 9px #0f3e12;
        cursor: col-resize;
        button{
            outline:0;
            &:focus,&:active,&:hover {outline:0 !important; background-color: transparent !important;}
            cursor: col-resize;
            width:100%;
            height:100%;
            text-align: left;
            border-radius: 0;
            padding: 0px 7px 0px 4px;
            i{
                font-size: 12px;
                width:5px;
            }
        }
    }

    .transition-animate-width {
        -moz-transition: width 0.5s ease;
        -webkit-transition: width 0.5s ease;
        -ms-transition: width 0.5s ease;
        transition: width 0.5s ease;
    }

    .fade-enter-active {
        -moz-transition: all 0.8s ease;
        -webkit-transition: all 0.8s ease;
        -ms-transition: all 0.8s ease;
        transition: all 0.8s ease;
    }
    .fade-leave-active {
        -moz-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        -webkit-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        -ms-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .fade-enter, .fade-leave-to{
        -moz-transform: translateY(10px);
        -webkit-transform: translateY(10px);
        -ms-transform: translateY(10px);
        transform: translateY(10px);
        opacity: 0;
    }
    .slide-fade-enter-active {
        -moz-transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        -ms-transition: all 0.3s ease;
        transition: all 0.3s ease;
    }
    .slide-fade-leave-active {
        -moz-transition: all 0.2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        -webkit-transition: all 0.2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        -ms-transition: all 0.2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
        transition: all 0.2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .slide-fade-enter, .slide-fade-leave-to {
        -moz-transform: rotateY(90);
        -webkit-transform: rotateY(90);
        -ms-transform: rotateY(90);
        transform: rotateY(90);
        -moz-transform-origin: left;
        -webkit-transform-origin: left;
        -ms-transform-origin: left;
        transform-origin: left;
        opacity: 0;
    }

</style>
