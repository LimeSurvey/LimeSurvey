<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../../mixins/runAjax.js';
import Menuicon from './_menuicon.vue';

export default {
    name: 'sidemenu',
    components : {
        'menuicon' : Menuicon
    },
    mixins: [ajaxMethods],
    props: {
        'openSubpanelId' : {type: Number},
    },
    data(){
        return {
            menues : {},
        };
    },
    computed: {
        sortedMenues(){
            return _.orderBy(this.$store.state.sidemenus,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);
        }
    },
    methods:{
        sortedMenuEntries(entries) {
            const self = this;
            let orderedArray = _.orderBy(entries,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);            
            return orderedArray;
        },
        setActiveMenuIndex(menuItem){
            let activeMenuIndex = menuItem.id;
            this.$store.commit('lastMenuItemOpen', menuItem)
            
        },
        setOpenSubpanel(sId){
            this.openSubpanelId = sId;
            this.$emit('menuselected', sId);
        },
        debugOut(obj){
            return JSON.stringify(obj);
        }
    },
    created(){
        const self = this;
        //first load old settings from localStorage
        
    },
    mounted(){
        const self = this;
        this.updatePjaxLinks();
        // this.get(this.getMenuUrl, {position: 'side'}).then( (result) =>{
        //     console.log('sidemenues',result);
        //     self.menues =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
        //     self.$localStorage.set('sidemenues', JSON.stringify(self.menues));
        //     self.$forceUpdate();
        // });
    }
}
</script>
<template>
    <div class="ls-flex-column fill menu-pane overflow-auto" >
        <ul class="list-group" v-for="menu in sortedMenues" :title="menu.title" v-bind:key="menu.title" :id="menu.id">
            <li v-if="(menu.submenus.length>0)" class="list-group-item">
                <ul class="list-group">
                    <li v-for="(submenu, index) in menuItem.submenus" class="list-group-item" style="padding:0;"  v-bind:key="submenu.id">
                        <a href="#" :title="submenu.description" @click="setOpenSubpanel(submenu.id)"  data-toggle="tooltip" class="ls-flex-row nowrap align-item-center align-content-center">
                            <div class="ls-space padding top-10 bottom-10" v-bind:class="openSubpanelId==submenu.id ? 'col-sm-10 selected' : 'col-sm-10' ">
                                <menuicon :icon-type="menuItem.menu_icon_type" :icon="menuItem.menu_icon"></menuicon>
                                <span v-html="menuItem.menu_title"></span>
                            </div>
                            <div class="col-sm-2 text-center ls-space padding top-10 bottom-10" v-bind:class="(openSubpanelId==submenu.id  ? 'background white' : '')">
                                <i class="fa fa-level-down">&nbsp;</i>
                            </div>
                        </a>
                        <div class="subpanel" :class="'level-'+(submenu.level+1)" v-if="openSubpanelId == submenu.id">
                            <sidemenu v-show="$store.state.lastMenuOpen == submenu.id":open-subpanel-id="openSubpanelId" :menu-entries="submenu"></sidemenu>
                        </div>
                    </li>
                </ul>
            </li>
            <li v-for="(menuItem, index) in sortedMenuEntries(menu.entries)" class="list-group-item" style="padding:0;" @click="setActiveMenuIndex(menuItem)"  v-bind:key="menuItem.id">
                <a :href="menuItem.link" :title="menuItem.menu_description"  data-toggle="tooltip" class="ls-flex-row nowrap align-item-center align-content-center pjax">
                    <div class="ls-space padding top-10 bottom-10" v-bind:class="$store.state.lastMenuItemOpen == menuItem.id ? 'col-sm-10 selected' : 'col-sm-12' ">
                        <menuicon :icon-type="menuItem.menu_icon_type" :icon="menuItem.menu_icon"></menuicon>
                        <span v-html="menuItem.menu_title"></span>
                    </div>
                    <div class="col-sm-2 text-center ls-space padding top-10 bottom-10 background white" v-show="$store.state.lastMenuItemOpen == menuItem.id">
                        <i class="fa fa-chevron-right">&nbsp;</i>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>
<style lang="scss">

</style>
