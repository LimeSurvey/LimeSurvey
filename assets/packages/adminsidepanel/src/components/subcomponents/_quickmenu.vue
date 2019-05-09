<script>
import ajaxMethods from '../../mixins/runAjax.js';

export default {
    mixins: [ajaxMethods],
    props: {
        'menuEntries' : {type: [Array,Object]},
        'activeMenuIndex': {type: String},
    },
    data(){
        return {
            loading : true,
        };
    },
    computed: {
        sortedMenues(){
            return LS.ld.orderBy(this.$store.state.collapsedmenus,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);
        }
    },
    methods:{
        sortedMenuEntries(entries) {
            const self = this;
            let orderedArray = LS.ld.orderBy(entries,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);            
            return orderedArray;
        },
        setActiveMenuIndex(menuItem){
            let activeMenuIndex = menuItem.id;
            this.$store.commit('lastMenuItemOpen', menuItem)
        },
        compileEntryClasses(menuItem){
            let classes = "";
            if(this.$store.state.lastMenuItemOpen == menuItem.id){
                classes+=' btn-primary ';
            } else {
                classes+=' btn-default ';
            }
            if(!menuItem.link_external){
                classes+=' pjax ';
            }
            return classes;
        }
    },
    created(){
        const self = this;
        this.$store.dispatch('getCollapsedmenus')
        .then(
            (result) => {},
            this.$log.error
        )
        .finally(
            (result) => { this.loading = false }
        );
    },
    mounted(){
    }
}
</script>
<template>
    <div class='ls-flex-column fill'>
        <div class="ls-space margin top-10" v-show="!loading"  v-for="menu in sortedMenues" :title="menu.title" v-bind:key="menu.title" >
            <div class="btn-group-vertical ls-space padding right-10">
                <a v-for="(menuItem) in sortedMenuEntries(menu.entries)" 
                @click="setActiveMenuIndex(menuItem)"
                v-bind:key="menuItem.id" 
                :href="menuItem.link" :title="menuItem.menu_description" 
                :target="menuItem.link_external ? '_blank' : '_self'"
                data-toggle="tooltip" 
                class="btn btn-icon"
                :class="compileEntryClasses(menuItem)"
                >
                    <template v-if="menuItem.menu_icon_type == 'fontawesome'">
                        <i class="quickmenuIcon fa" :class="'fa-'+menuItem.menu_icon"></i>
                    </template>
                    <template v-else-if="menuItem.menu_icon_type == 'image'">
                        <img width="32px" :src="menuItem.menu_icon" />
                    </template>
                    <template v-else-if="menuItem.menu_icon_type == 'iconclass'">
                        <i class="quickmenuIcon"  :class="menuItem.menu_icon" ></i>
                    </template>
                </a>
            </div>
        </div>
        <loader-widget v-if="loading" id="quickmenuLoadingIcon" extra-class="loader-quickmenu"/>
    </div>
</template>
<style lang="scss">
    .loader-adminpanel.loader-quickmenu .contain-pulse{
        width: 2em;
    }
</style>
