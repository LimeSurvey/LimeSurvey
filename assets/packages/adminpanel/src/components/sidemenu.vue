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
            let activeMenuIndex = 'sidemenu_'+idx;
            this.$emit('selectedmenu', activeMenuIndex);
        }
    },
    created(){
        const self = this;
        //first load old settings from localStorage
        this.menues = JSON.parse(self.$localStorage.get('sidemenues', JSON.stringify([])));
    },
    mounted(){
        const self = this;
        this.get(this.getMenuUrl, {position: 'side'}).then( (result) =>{
            console.log('sidemenues',result);
            self.menues =  _.orderBy(result.data.menues,(a)=>{return parseInt((a.order || 999999))},['desc']);
            self.$localStorage.set('sidemenues', JSON.stringify(self.menues));
            self.$forceUpdate();
        });
    }
}
</script>
<template>
    <div class="ls-flex-column fill" >
        <ul class="list-group" v-for="menu in sortedMenues" :title="menu.title" v-bind:key="menu.title" >
            <li v-for="(menuItem, index) in sortedMenuEntries(menu.entries)" class="list-group-item" style="padding:0;" @click="setActiveMenuIndex(index)"  v-bind:key="menuItem.id">
                <a :href="menuItem.link" :title="menuItem.menu_description"  data-toggle="tooltip" class="ls-flex-row nowrap align-item-center align-content-center pjax">
                    <div class="ls-space padding top-10 bottom-10" v-bind:class="'sidemenu_'+index==activeMenuIndex ? 'col-sm-10 selected' : 'col-sm-12' ">
                        <template v-if="menuItem.menu_icon_type == 'fontawesome'">
                            <i class="fa" :class="'fa-'+menuItem.menu_icon">&nbsp;</i>
                        </template>
                        <template v-else-if="menuItem.menu_icon_type == 'image'">
                            <img width="32px" :src="menuItem.menu_icon" ></img>
                        </template>
                        <span v-html="menuItem.menu_title"></span>
                    </div>
                    <div class="col-sm-2 text-center ls-space padding top-10 bottom-10 background white" v-show="'sidemenu_'+index==activeMenuIndex">
                        <i class="fa fa-chevron-right">&nbsp;</i>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>
<style lang="scss">

</style>
