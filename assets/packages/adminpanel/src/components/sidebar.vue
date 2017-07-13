<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMixin from '../mixins/runAjax.js'
import Questionexplorer from './questionsgroups.vue'
import Quickmenu from './quickmenu.vue'

export default {
    components: {
        'questionexplorer': Questionexplorer,
        'quickmenu': Quickmenu
    },
    mixins: [ajaxMixin],
    props: {
        'translate': {type: Object},
        'getQuestionsUrl' : {type: String},
        'getMenuUrl' : {type: String},
        'getQuickMenuUrl' : {type: String},
        'createQuestionGroupLink' : {type: String},
        'createQuestionLink' : ''
    },
    data: () => {
        return {
            'currentTab': 'settings',
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
        }
    },
    methods: {
        editEntity(){},
        openEntity(){},
        changeTab(currentTab){
            this.$localStorage.set('currentTab',currentTab);
            this.currentTab = currentTab;
        },
        activeTab(currentTab){
            return this.currentTab === currentTab;
        },
        checkIsActive(link){
            let locationUrl = document.createElement('a'); locationUrl.href = location.href;
            let checkUrl = document.createElement('a'); checkUrl.href=link;
            if(locationUrl.pathname == '/index.php' || locationUrl.pathname == '/'){
                return (locationUrl.search == checkUrl.search);
            } else {
                return ( locationUrl.pathname == checkUrl.pathname ); 
            }
        },
        sortedMenu(entries) {
            let retVal = _.orderBy(entries,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);
            return retVal;
        },
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
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
    mounted(){
        const self = this;
        //first load old settings from localStorage
        this.menues = JSON.parse(self.$localStorage.get('menues', JSON.stringify([])));
        this.questiongroups = JSON.parse(self.$localStorage.get('questiongroups', JSON.stringify([])));
        this.currentTab = self.$localStorage.get('currentTab','settings');
        //then retrieve the current menues via ajax
        this.get(this.getQuestionsUrl).then( (result) =>{
            console.log(result);
            self.questiongroups = result.data.groups;
            self.$localStorage.set('questiongroups', JSON.stringify(self.questiongroups));
            self.$forceUpdate();
        })
        this.get(this.getMenuUrl).then( (result) =>{
            console.log(result);
            self.menues =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$localStorage.set('menues', JSON.stringify(self.menues));
            self.$forceUpdate();
        });
        this.sideBarWidth = self.$localStorage.get('sidebarwidth', '380px');
        self.$forceUpdate();
        $('body').on('mousemove', (event) => {self.mousemove(event,self)});
    }
}
</script>
<template>
   
    <div id="sidebar" class="ls-flex col-md-4 hidden-xs nofloat nooverflow transition-animate-width" :style="{width : getSideBarWidth}" @mouseleave="mouseleave" @mouseup="mouseup">
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
                    <div class="no-padding no-margin" v-show="!isCollapsed">
                        <ul class="list-group" v-for="menu in menues" :title="menu.title" v-bind:key="menu.title">
                            <li v-for="(menuItem, index) in sortedMenu(menu.entries)" class="list-group-item" style="padding:0;" v-show="activeTab('settings')" v-bind:key="menuItem.id">
                                <a :href="menuItem.link" :title="menuItem.menu_description" data-toggle="tooltip" class="ls-flex-row nowrap align-item-center align-content-center pjax">
                                    <div class="col-sm-10 ls-space padding top-10 bottom-10" v-bind:class=" checkIsActive(menuItem.link) ? 'selected' : ''">
                                        <i class="fa" :class="'fa-'+menuItem.menu_icon">&nbsp;</i>&nbsp;
                                        <span v-html="menuItem.menu_title"></span>
                                    </div>
                                    <div class="col-sm-2 text-center ls-space padding top-10 bottom-10" :class="checkIsActive(menuItem.link) ? 'background white' : ''">
                                        <i class="fa fa-chevron-right">&nbsp;</i>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </transition>
    
                <transition name="slide-fade">
                    <div class="row fill-height ls-ba" v-show="activeTab('questiontree')">
                        <div class="ls-flex-row wrap align-content-space-between align-items-space-between ls-space margin top-5 bottom-15">
                            <a v-if="( createQuestionGroupLink!=undefined && createQuestionGroupLink.length>1 )" :href="createQuestionGroupLink" class="btn btn-small btn-primary">
                                <i class="fa fa-plus"></i>&nbsp;{{translate.createQuestionGroup}}</a>
                            <a v-if="( createQuestionLink!=undefined && createQuestionLink.length>1 )" :href="createQuestionLink" class="btn btn-small btn-default">
                                <i class="fa fa-plus-circle"></i>&nbsp;{{translate.createQuestion}}</a>
                        </div>
                        <questionexplorer v-on:editentity="editEntity" v-on:openentity="openEntity" :questiongroups="questiongroups"></questionexplorer>
                    </div>
                </transition>
                <transition name="slide-fade">
                    <div class="no-padding no-margin" v-show="isCollapsed">
                        <quickmenu :get-quick-menu-url='getQuickMenuUrl' main-title="Test if this really works"></quickmenu>
                    </div>
                </transition>
            </div>
        </div>
        <div draggable="true" @mousedown="mousedown" class="resize-handle"></div>
    </div>
</template>
<style lang="scss">
.selected{
    background-color: rgba(200,255,200,0.4);
    box-shadow: 1px2px 4px rgba(200,255,200,0.4) inset;
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
    box-shadow: 5px 5px 7px #0f3e12;
    cursor: w-resize;
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