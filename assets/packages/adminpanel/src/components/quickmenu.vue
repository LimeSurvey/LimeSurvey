<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../mixins/runAjax.js';

export default {
    mixins: [ajaxMethods],
    props: {
        'getMenuUrl' : {type: String},
        'activeMenuIndex': {type: String},
    },
    data(){
        return {
            menues : {},
        };
    },
    computed: {
        sortedMenues(){
            return _.orderBy(this.menues,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);
        }
    },
    methods:{
        sortedMenuEntries(entries) {
            const self = this;
            let orderedArray = _.orderBy(entries,(a)=>{return parseInt((a.order || 999999)) }, ['asc']);            
            return orderedArray;
        },
        setActiveMenuIndex(idx){
            let activeMenuIndex = 'quichmenu_'+idx;
            this.$emit('selectedmenu', activeMenuIndex);
        }
    },
    created(){
        const self = this;
        //first load old settings from localStorage
        this.menues = JSON.parse(self.$localStorage.get('collapsedmenues', JSON.stringify([])));
    },
    mounted(){
        const self = this;
        this.get(this.getMenuUrl, {position: 'collapsed'}).then( (result) =>{
            console.log('quickmenu',result);
            self.menues =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$localStorage.set('collapsedmenues', JSON.stringify(self.menues));
            self.$forceUpdate();
        });
    }
}
</script>
<template>
    <div class='ls-column fill'>
        <div class="btn-group-vertical"  v-for="menu in sortedMenues" :title="menu.title" v-bind:key="menu.title" >
            <a v-for="(menuItem, index) in sortedMenuEntries(menu.entries)" 
            @click="setActiveMenuIndex(index)"
             v-bind:key="menuItem.id" 
            :href="menuItem.link" :title="menuItem.menu_description" 
            :target="menuItem.linkExternal ? '_blank' : '_self'"
            data-toggle="tooltip" 
            class="btn btn-default btn-icon pjax"
            v-bind:class="('quickmenu_'+index)==activeMenuIndex ? 'selected' : ''"
            >
                <template v-if="menuItem.menu_icon_type == 'fontawesome'">
                    <i class="quickmenuIcon fa" :class="'fa-'+menuItem.menu_icon"></i>
                </template>
                <template v-else-if="menuItem.menu_icon_type == 'image'">
                    <img width="32px" :src="menuItem.menu_icon" ></img>
                </template>
                <template v-else-if="menuItem.menu_icon_type == 'iconclass'">
                    <i class="quickmenuIcon"  :class="menuItem.menu_icon" ></i>
                </template>
            </a>
        </div>
    </div>
</template>
<style lang="scss">
    .quickmenuIcon{
        font-size:"28px";
    }
</style>
