<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMixin from '../mixins/runAjax.js'
import Questionexplorer from './questionsgroups.vue'
import Sidemenu from './sidemenu.vue'
import Quickmenu from './quickmenu.vue'

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
            'questiongroups': [],
            'menues' : [],
            'isCollapsed' : false,
            'sideBarWidth': '315px',
            'initialPos' : {x: 0, y: 0},
            'isMouseDown' : false,
            'isMouseDownTimeOut' : null,
        };
    },
    computed: {
        maxSideBarHeight(){
            let positionTop = $('#surveybarid').offset();
            let positionBottom = $('footer').offset();
            return (positionBottom.top - (positionTop.top+($('#surveybarid').height()))-15)+'px';
        },
        getSideBarWidth(){
            return this.isCollapsed ? '98px' : this.sideBarWidth;
        },
        sortedMenus() {
            return _.orderBy(this.menues,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);            
        },
        showSideMenu(){
            return (!this.isCollapsed && this.activeTab('settings'));
        }
    },
    methods: {
        editEntity(){
            this.setActiveMenuIndex(null,'question');
        },
        openEntity(){
            this.setActiveMenuIndex(null,'question');
        },
        changeTab(currentTab){
            this.$localStorage.set('currentTab',currentTab);
            this.currentTab = currentTab;
        },
        activeTab(currentTab){
            return this.currentTab === currentTab;
        },
        setActiveMenuIndex(index){
            this.$localStorage.set('activeMenuIndex',index);
            this.activeMenuIndex = index;
        },
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
            this.$localStorage.set('iscollapsed', this.isCollapsed);
            if(this.isCollapsed){
                this.sideBarWidth = '98px';
            } else {
                this.sideBarWidth = this.$localStorage.get('sidebarwidth', '380px');
            }
        },
        mousedown(e) {
            this.isMouseDown = this.isCollapsed ? false : true;
            $('#sidebar').removeClass('transition-animate-width');
        },
        mouseup(e) {
            if(this.isMouseDown){
                this.isMouseDown = false;
                this.isCollapsed = false;
                if(this.sideBarWidth < 315 && !this.isCollapsed) {
                    this.isCollapsed = true;
                }
                this.$localStorage.set('sidebarwidth', this.sideBarWidth);
                $('#sidebar').addClass('transition-animate-width');
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
                window.clearTimeout(self.isMouseDownTimeOut);
                self.isMouseDownTimeOut = null;
            }
        }
    },
    created(){
        const self = this;
        //first load old settings from localStorage
        this.questiongroups = JSON.parse(self.$localStorage.get('questiongroups', JSON.stringify([])));
        this.currentTab = self.$localStorage.get('currentTab','settings');
        this.isCollapsed = self.$localStorage.get('iscollapsed', 'false') == 'true';
        this.activeMenuIndex = this.$localStorage.get('activeMenuIndex', null); 
        this.setActiveMenuIndex(this.activeMenuIndex);
        if(this.isCollapsed){ 
            this.sideBarWidth = '98px'; 
        } else {
            this.sideBarWidth = self.$localStorage.get('sidebarwidth', '380px');
        }
    },
    mounted(){
        const self = this;
        //then retrieve the current menues via ajax
        this.get(this.getQuestionsUrl).then( (result) =>{
            console.log(result);
            self.questiongroups = result.data.groups;
            self.$localStorage.set('questiongroups', JSON.stringify(self.questiongroups));
            self.$forceUpdate();
        });
        self.$forceUpdate();
        $('body').on('mousemove', (event) => {self.mousemove(event,self)});
    }
}
</script>
<template>
   
    <div id="sidebar" class="ls-flex col-md-4 hidden-xs nofloat nooverflow transition-animate-width" :style="{width : sideBarWidth}" @mouseleave="mouseleave" @mouseup="mouseup">
        <div class="col-12" v-bind:style="{'height': maxSideBarHeight}">
            <div class="mainMenu container-fluid col-sm-12 fill-height">
                <div class="ls-flex-row align-content-space-between align-items-space-between ls-space margin bottom-5 top-5 ">
                    <transition name="fade">
                        <div class="btn-group ls-space padding right-5" v-if="!isCollapsed" role="group">
                            <button class="btn btn-default" @click="toggleCollapse">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                        </div>
                    </transition>
                    <transition name="fade">
                        <div class="ls-flex-item col-12" v-if="!isCollapsed">
                            <div class="btn-group btn-group-justified">
                                <div class="btn-group" role="group">
                                    <button class="btn force color white onhover" :class="activeTab('settings') ? 'btn-primary' : 'btn-default'" @click="changeTab('settings')">{{translate.settings}}</button>
                                </div>
                                <div class="btn-group" role="group">
                                    <button class="btn force color white onhover" :class="activeTab('questiontree') ? 'btn-primary' : 'btn-default'" @click="changeTab('questiontree')">{{translate.structure}}</button>
                                </div>
                            </div>
                        </div>
                    </transition>
                    <transition name="fade">
                        <div class="btn-group ls-space padding right-5" v-if="isCollapsed" role="group">
                            <button class="btn btn-default" @click="toggleCollapse">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </transition>
                </div>
                <transition name="slide-fade">
                    <sidemenu :active-menu-index="activeMenuIndex" v-on:selectedmenu="setActiveMenuIndex" v-show="showSideMenu" :get-menu-url='getMenuUrl'></sidemenu>
                </transition>
    
                <transition name="slide-fade">
                    <template v-show="!isCollapsed">
                        <div class="row fill-height ls-ba" v-show="activeTab('questiontree')">
                            <questionexplorer :create-question-group-link="createQuestionGroupLink" :create-question-link="createQuestionLink" :translate="translate" v-on:openentity="openEntity" :questiongroups="questiongroups"></questionexplorer>
                        </div>
                    </template>
                </transition>
                <transition name="slide-fade">
                    <quickmenu v-show="isCollapsed" :active-menu-index="activeMenuIndex" v-on:selectedmenu="setActiveMenuIndex" :get-menu-url='getMenuUrl'></quickmenu>
                </transition>
            </div>
        </div>
        <div draggable="true" @mousedown="mousedown" class="resize-handle"></div>
    </div>
</template>
<style lang="scss">
    .selected{
        background-color: rgba(200,255,200,0.4);
        box-shadow: 1px 2px 4px rgba(200,255,200,0.4) inset;
    }

    .background.white{
        background-color: rgba(255,255,255,1);
        box-shadow: none;
    }

    .resize-handle{
        position: absolute;
        right: -4px;
        top: 0;
        bottom: 0;
        height:100%;
        width: 8px;
        box-shadow: 0px 5px 9px #0f3e12;
        cursor: col-resize;
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
    -moz-transition: all 0.2s ease;
    -webkit-transition: all 0.2s ease;
    -ms-transition: all 0.2s ease;
    transition: all 0.2s ease;
    }
    .slide-fade-leave-active {
    -moz-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    -webkit-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    -ms-transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    transition: all 0.1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
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