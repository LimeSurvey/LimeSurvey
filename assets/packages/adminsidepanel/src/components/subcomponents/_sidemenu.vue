<script>
import ajaxMethods from '../../mixins/runAjax.js';
import Menuicon from './_menuicon.vue';
import Submenu from './_submenu.vue';

export default {
    name: 'sidemenu',
    components : {
        'menuicon' : Menuicon,
        'submenu': Submenu
    },
    mixins: [ajaxMethods],
    props: {
        'openSubpanelId' : {type: Number},
        loading: {type: Boolean, default: false}
    },
    data(){
        return {
            menues : {},
        };
    },
    computed: {
        sortedMenues(){
            return LS.ld.orderBy(this.$store.state.sidemenus,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);
        },
        loadingState: {
            get() { return this.loading; },
            set(newState) { this.$emit('changeLoadingState', newState); }
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
            this.$store.commit('lastMenuOpen', menuItem)
            
        },
        setActiveMenuItemIndex(menuItem){
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
        this.$store.dispatch('getSidemenus')
        .then(
            (result) => {},
            this.$log.error
        )
        .finally(
            (result) => { this.loadingState = false }
        );
    },
    mounted(){
        const self = this;
        this.updatePjaxLinks();

        $(document).on('vue-reload-remote', ()=>{
            //this.$forceUpdate();
        });
    }
}
</script>
<template>
    <div class="ls-flex-column menu-pane overflow-enabled ls-space all-0 py-4 bg-white" >
        <div v-show="!loadingState"  :title="sortedMenues[0].title" :id="sortedMenues[0].id" class="ls-flex-row wrap ls-space padding all-0" v-bind:key="sortedMenues[0].id">
            <submenu :menu="sortedMenues[0]"></submenu>
        </div>

        <div v-show="!loadingState"  :title="sortedMenues[1].title" :id="sortedMenues[1].id" class="ls-flex-row wrap ls-space padding all-0" v-bind:key="sortedMenues[1].id">
            <label class="menu-label mt-3 p-2 ls-survey-menu-item">{{sortedMenues[1].title}}</label>
            <submenu :menu="sortedMenues[1]"></submenu>
        </div>
        <!-- <div v-show="!loadingState"  v-for="menu in sortedMenues" :title="menu.title" :id="menu.id" class="ls-flex-row wrap ls-space padding all-0" v-bind:key="menu.id">
            <submenu :menu="menu"></submenu>
        </div> -->
        <loader-widget v-if="loadingState" id="sidemenuLoaderWidget" />
    </div>
</template>
<style lang="scss">

</style>
