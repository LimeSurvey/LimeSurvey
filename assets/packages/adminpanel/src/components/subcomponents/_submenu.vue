<script>
import _ from 'lodash';
import ajaxMethods from '../../mixins/runAjax.js';
import Menuicon from './_menuicon.vue';

export default {
    name: 'submenu',
    components : {
        'menuicon' : Menuicon
    },
    mixins: [ajaxMethods],
    props: {
        'menu' : {type: [Object,Array], required: true},
    },
    data(){
        return {
            menues : {},
        };
    },
    computed: {
        sortedMenuEntries() {
            return _.orderBy(this.menu.entries,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);
        },
    },
    methods:{
        setActiveMenuItemIndex(menuItem){
            let activeMenuIndex = menuItem.id;
            this.$store.commit('lastMenuItemOpen', menuItem);
            this.$log.log('Opened Menuitem', menuItem);
            return true;
        },
        checkIsOpen(toCheckMenu){
            let directSelect = this.$store.state.lastMenuOpen == toCheckMenu.id;
            let childSelected = false;
            _.each(toCheckMenu.submenus, (submenu,i)=>{
                childSelected = (this.$store.state.lastMenuOpen == submenu.id) || childSelected;
            });
            return (directSelect || childSelected || false);
        },
        setActiveMenuIndex(menu){
            let activeMenuIndex = menu.id;
            this.$store.commit('lastMenuOpen', menu);
        },
        setOpenSubpanel(sId){
            this.openSubpanelId = sId;
            this.$emit('menuselected', sId);
        },
        debugOut(obj){
            return JSON.stringify(obj);
        },
        getLinkClass(menuItem){
            let classes = "ls-flex-row nowrap ";
            classes += (menuItem.pjax ? 'pjax ' : ' ');
            classes += (this.$store.state.lastMenuItemOpen==menuItem.id ? 'selected ' : ' ' );
            return classes;
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
        //     self.$log.debug('sidemenues',result);
        //     self.menues =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
        //     self.$localStorage.set('sidemenues', JSON.stringify(self.menues));
        //     self.$forceUpdate();
        // });
    }
}
</script>
<template>
    <ul class="list-group subpanel col-12" :class="'level-'+(menu.level)">        
        <a  v-for="(menuItem, index) in sortedMenuEntries" 
            v-bind:key="menuItem.id" 
            v-on:click="setActiveMenuItemIndex(menuItem)"  
            :href="menuItem.link" 
            :id="'sidemenu_'+menu.id+'_'+menuItem.id" 
            class="list-group-item"
            :class="getLinkClass(menuItem)" >

            <div class="col-12" :class="menuItem.menu_class" 
            :title="menuItem.menu_description"  
            data-toggle="tooltip" >
                <div class="ls-space padding all-0" v-bind:class="$store.state.lastMenuItemOpen == menuItem.id ? 'col-sm-10' : 'col-sm-12' ">
                    <menuicon :icon-type="menuItem.menu_icon_type" :icon="menuItem.menu_icon"></menuicon>
                    <span v-html="menuItem.menu_title"></span>
                </div>
                <div class="col-sm-2 text-center ls-space padding all-0 background white" v-show="$store.state.lastMenuItemOpen == menuItem.id">
                    <i class="fa fa-chevron-right">&nbsp;</i>
                </div>
            </div>
        </a>
        <li v-for="(submenu, index) in menu.submenus" class="list-group-item" v-bind:key="submenu.id" v-bind:class="checkIsOpen(submenu) ? 'menu-selected' : '' " @click.capture="setActiveMenuIndex(submenu)" >
            <a href="#" :title="submenu.description"   data-toggle="tooltip" class="ls-flex-row nowrap align-item-center align-content-center" :class="checkIsOpen(submenu) ? 'ls-space margin bottom-5' : ''">
                <div class="ls-space col-sm-10 padding all-0">
                    <menuicon icon-type="fontawesome" icon="arrow-right"></menuicon>
                    <span v-html="submenu.title"></span>
                </div>
                <div class="col-sm-2 text-center ls-space padding all-0"  v-bind:class="(checkIsOpen(submenu) ? 'menu-open' : '')">
                    <i class="fa fa-level-down"></i>
                </div>
            </a>
            <transition name="slide-fade-down">
            <submenu v-if="checkIsOpen(submenu)" :menu="submenu"></submenu>
            </transition>
        </li>
    </ul>
</template>
<style lang="scss">

</style>
