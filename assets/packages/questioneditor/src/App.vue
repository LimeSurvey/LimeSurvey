<script>
import MainEditor from './components/mainEditor.vue';
import GeneralSettings from './components/generalSettings.vue';
import AdvancedSettings from './components/advancedSettings.vue';

export default {
    name: 'lsnextquestioneditor',
    data() {
        return {}
    },
    components: {
        'maineditor' : MainEditor,
        'generalsettings' : GeneralSettings,
        'advancedsettings' : AdvancedSettings
    },
    mounted() {
        this.toggleLoading(false);
    },
    created(){
        this.$store.dispatch('loadQuestion');
    }
}
</script>

<template>
    <div class="container-center">
        <template v-if="$store.getters.fullyLoaded">
            <maineditor></maineditor>
            <generalsettings></generalsettings>
            <advancedsettings></advancedsettings>
        </template>
    </div>
</template>

<style scoped>
.loading-back-greyed {
    background-color: rgba(200,200,200,0.4);
    width: 100%;
    height: 100%;
    min-height: 60vh;
}
</style>
