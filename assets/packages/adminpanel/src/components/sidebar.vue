<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMixin from '../mixins/runAjax.js'
import Questionexplorer from './questionsgroups.vue'

export default {
    components: {
        'questionexplorer': Questionexplorer
    },
    mixins: [ajaxMixin],
    props: {
        'translate': {type: Object},
        'getQuestionsUrl' : {type: String},
        'getMenuUrl' : {type: String}
    },
    data: () => {
        return {
            'currentTab': 'settings',
            'questiongroups': [],
            'menues' : []
        };
    },
    computed: {
        maxSideBarHeight(){
            let positionTop = $('#surveybarid').offset();
            let positionBottom = $('footer').offset();
            return (positionBottom.top - (positionTop.top+($('#surveybarid').height()))-15)+'px';
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
            console.log({
                locationUrl : locationUrl.pathname,                    
                checkUrl : checkUrl.pathname          
            });
            if(locationUrl.pathname == '/index.php'){
                return (locationUrl.search == checkUrl.search);
            } else {
                return ( locationUrl.pathname == checkUrl.pathname ); 
            }
        },
        sortedMenu(entries){
            let retVal = _.orderBy(entries,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);
            return retVal;
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
        })
    }
}
</script>
<template>
    <div id="sidebar" v-bind:style="{'height': maxSideBarHeight}" class="overflow-y-enabled">
        <div class="mainMenu container-fluid col-sm-12 fill-height">
            <div class="btn-group btn-group-justified ls-space margin bottom-15 top-5 ">
                <div class="btn-group" role="group">
                    <button class="btn force color white onhover" :class="activeTab('settings') ? 'btn-primary' : 'btn-default'" @click="changeTab('settings')">{{translate.settings}}</button>
                </div>
                <div class="btn-group" role="group">
                    <button class="btn force color white onhover" :class="activeTab('questiontree') ? 'btn-primary' : 'btn-default'" @click="changeTab('questiontree')">{{translate.structure}}</button>
                </div>
            </div>

            <ul class="list-group" v-for="menu in menues" :title="menu.title" v-bind:key="menu.title" > 
                <li v-for="(menuItem, index) in sortedMenu(menu.entries)" class="list-group-item" style="padding:0;"  v-show="activeTab('settings')" v-bind:key="menuItem.id">
                    <a :href="menuItem.link" :title="menuItem.menu_description" data-toggle="tooltip"  class="ls-flex-row nowrap align-item-center align-content-center pjax">
                        <div class="col-sm-10" v-bind:class=" checkIsActive(menuItem.link) ? 'selected' : ''" style="padding:15px 10px;" >
                                    <i class="fa" :class="'fa-'+menuItem.menu_icon">&nbsp;</i>&nbsp; 
                                    <span v-html="menuItem.menu_title"></span>
                        </div>
                        <div class="col-sm-2 text-center" :class="checkIsActive(menuItem.link) ? 'background white' : ''"  style="padding:15px 10px;">
                            <i class="fa fa-chevron-right">&nbsp;</i>
                        </div>
                    </a>
                </li>
            </ul>
            
            <div class="row fill-height ls-ba" v-show="activeTab('questiontree')">
                <questionexplorer v-on:editentity="editEntity" v-on:openentity="openEntity" :questiongroups="questiongroups"></questionexplorer>
            </div>
        </div>
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

.overflow-y-enabled{
    overflow-y: auto;
}
</style>