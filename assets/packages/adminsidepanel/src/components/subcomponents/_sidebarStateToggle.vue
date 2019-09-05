<template>
    <div class="ls-space margin bottom-15 top-5 col-12" style="height: 40px;" :key="currentKey">
        <div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 right-10 bottom-0 top-0">
            <transition name="fade">
                <button class="btn btn-default ls-space padding left-15 right-15" v-if="!$store.state.isCollapsed" @click="$emit('collapse')">
                    <i :class="$store.getters.isRTL ? 'fa fa-chevron-right' : 'fa fa-chevron-left'"></i>
                </button>
            </transition>
            <transition name="fade">
                <div class="ls-flex-item grow-10 col-12" v-if="!$store.state.isCollapsed">
                    <div class="btn-group btn-group col-12">
                        <button 
                            id="adminsidepanel__sidebar--selectorSettingsButton" 
                            class="btn col-6 force color white onhover tabbutton" 
                            :class="currentTab =='settings' ? 'btn-primary' : 'btn-default'" 
                            @click="currentTab='settings'"
                        >
                            {{"settings"|translate}}
                        </button>
                        <button 
                            id="adminsidepanel__sidebar--selectorStructureButton" 
                            class="btn col-6 force color white onhover tabbutton" 
                            :class="currentTab =='questiontree' ? 'btn-primary' : 'btn-default'" 
                            @click="currentTab='questiontree'"
                        >
                            {{"structure"|translate}}
                        </button>
                    </div>
                </div>
            </transition>
            <transition name="fade">
                <button class="btn btn-default ls-space padding left-15 right-15" v-if="$store.state.isCollapsed" @click="$emit('collapse')">
                    <i :class="$store.getters.isRTL ? 'fa fa-chevron-left' : 'fa fa-chevron-right'"></i>
                </button>
            </transition>
        </div>
    </div>
</template>

<script>
export default {
    name: 'sidebar-state-toggle',
    computed: {
        currentKey() { return this.$store.state.toggleKey; },
        currentTab: {
            get() { return this.$store.state.currentTab; },
            set(newVal) { this.$store.commit("changeCurrentTab", newVal); }
        },
    }
}
</script>

<style>

</style>
