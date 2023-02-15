<template>
    <div class="ls-space col-12"  :key="currentKey">
        <div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 bottom-0 top-0">
          
            <transition name="fade">
                <div class="ls-flex-item grow-10 col-12" v-if="!$store.getters.isCollapsed">
                    <ul class="nav nav-tabs" id="surveysystem" role="tablist">
                     <li class="nav-item"><a id="adminsidepanel__sidebar--selectorSettingsButton" :class="currentTab == 'settings' ? 'nav-link active' : 'nav-link'" href="#settings" aria-controls="settings" role="tab" data-bs-toggle="tab"  @click="currentTab='settings'"> {{"settings"|translate}} </a></li>
                     <li class="nav-item"><a id="adminsidepanel__sidebar--selectorStructureButton" :class="currentTab == 'questiontree' ? 'nav-link active' : 'nav-link'" href="#structure" aria-controls="structure" role="tab" data-bs-toggle="tab"   @click="currentTab='questiontree'"> {{"structure"|translate}} </a></li>
                    </ul>
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
