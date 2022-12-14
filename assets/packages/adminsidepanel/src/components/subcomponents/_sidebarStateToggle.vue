<template>
    <div class="ls-space margin bottom-15 top-5 col-12" style="height: 40px;" :key="currentKey">
        <div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 right-10 bottom-0 top-0">
            <transition name="fade">
                <button class="btn btn-outline-secondary ls-space padding left-15 right-15" v-if="!$store.getters.isCollapsed" @click="$emit('collapse')">
                    <i :class="$store.getters.isRTL ? 'ri-arrow-right-s-line' : 'ri-arrow-left-s-line'"></i>
                </button>
            </transition>
            <transition name="fade">
                <div class="ls-flex-item grow-10 col-12" v-if="!$store.getters.isCollapsed">
                    <div class="btn-group btn-group col-12">
                        <button 
                            id="adminsidepanel__sidebar--selectorSettingsButton" 
                            class="btn col-6 force color white onhover tabbutton" 
                            :class="currentTab =='settings' ? 'btn-primary' : 'btn-outline-secondary'" 
                            @click="currentTab='settings'"
                        >
                            {{"settings"|translate}}
                        </button>
                        <button 
                            id="adminsidepanel__sidebar--selectorStructureButton" 
                            class="btn col-6 force color white onhover tabbutton" 
                            :class="currentTab =='questiontree' ? 'btn-primary' : 'btn-outline-secondary'" 
                            @click="currentTab='questiontree'"
                        >
                            {{"structure"|translate}}
                        </button>
                    </div>
                </div>
            </transition>
            <transition name="fade">
                <button class="btn btn-outline-secondary ls-space padding left-15 right-15" v-if="$store.getters.isCollapsed" @click="$emit('collapse')">
                    <i :class="$store.getters.isRTL ? 'ri-arrow-left-s-line' : 'ri-arrow-right-s-line'"></i>
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
            set(newVal) { this.$store.dispatch("changeCurrentTab", newVal); }
        },
    }
}
</script>

<style>

</style>
