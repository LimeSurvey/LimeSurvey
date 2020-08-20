
<template>
    <div 
        class="ls-flex scope-set-min-height scoped-general-settings" 
        :class="collapsedMenu ? 'collapsed' : 'non-collapsed'" 
        @dblclick="toggleEditMode"
    >
        <transition name="slide-fade">
            <div class="panel panel-default question-option-general-container col-12" id="uncollapsed-general-settings" v-if="!loading && !collapsedMenu">
                <div class="panel-heading"> 
                    {{"General Settings" | translate }}
                    <button class="pull-right btn btn-default btn-xs" @click="collapsedMenu=true">
                        <i class="fa fa-chevron-right" />
                    </button>
                </div>
                <div class="panel-body">
                    <div class="list-group">
                        <div class="list-group-item question-option-general-setting-block" 
                             v-for="generalSetting in generalSettingOptions" 
                             :key="generalSetting.name">
                            <component 
                                v-bind:is="getComponentName(generalSetting.inputtype)" 
                                :elId="generalSetting.formElementId"
                                :elName="generalSetting.formElementName"
                                :elLabel="generalSetting.title"
                                :elHelp="generalSetting.formElementHelp"
                                :currentValue="generalSetting.formElementValue"
                                :elOptions="generalSetting.formElementOptions"
                                :debug="generalSetting"
                                :readonly="isReadonly(generalSetting)"
                                @change="reactOnChange($event, generalSetting)">
                            </component>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
        <transition name="slide-fade">
            <button v-if="!loading && collapsedMenu" class="btn btn-default scoped--special-collapse"  id="collapsed-general-settings" @click="collapsedMenu=false">
                <i class="fa fa-chevron-left" />
                <div class="special-collapse-text">
                    {{"General Settings" | translate }}
                </div>
            </button>
        </transition>
        <transition name="slide-fade">
            <div class="row" v-if="loading">
                <loader-widget id="generalSettingsLoader" />
            </div>
        </transition>
    </div>
</template>

<script>
import filter from 'lodash/filter';

import SettingSwitch from './_inputtypes/switch.vue';
import SettingText from './_inputtypes/text.vue';
import SettingSelect from './_inputtypes/select.vue';
import SettingTextarea from './_inputtypes/textarea.vue';
import SettingButtongroup from './_inputtypes/buttongroup.vue';
import SettingQuestiontheme from './_inputtypes/questiontheme.vue';
import SettingQuestiongroup from './_inputtypes/questiongroup.vue';
import SettingColumns from './_inputtypes/columns.vue';
import StubSet from './_inputtypes/stub.vue';

import eventChild from '../mixins/eventChild.js';

export default {
    name: 'GeneralSettings',
    mixins: [eventChild],
    components: {
        'setting-questiontheme': SettingQuestiontheme,
        'setting-questiongroup': SettingQuestiongroup,
        'setting-switch': SettingSwitch,
        'setting-select': SettingSelect,
        'setting-text': SettingText,
        'setting-textarea': SettingTextarea,
        'setting-buttongroup': SettingButtongroup,
        'setting-columns': SettingColumns,
        'stub-set' : StubSet,
    },
    props: {
        readonly : {type: Boolean, default: false}
    },
    data() {
        return {
            loading: true,
        };
    },
    computed: {
        generalSettingOptions(){
            return filter(this.$store.state.currentQuestionGeneralSettings, (questionSetting) => {
                return (questionSetting.inputtype != undefined)
            });
        },
        surveyActive() {
            return this.$store.getters.surveyObject.active =='Y'
        },
        collapsedMenu: {
            get() { return this.$store.state.collapsedGeneralSettings },
            set(nV) { return this.$store.commit('setCollapsedGeneralSettings', nV); }
        }
    },
    methods: {
        getComponentName(componentRawName){
            let name = '';
            if(componentRawName != undefined) {
                name = 'setting-' + componentRawName;
            } else {
                name = 'stub-set';
            }
            return name;
        },
        reactOnChange(newValue, oSettingObject) {
            this.$store.commit('setQuestionGeneralSetting', {newValue, settingName: oSettingObject.formElementId});   
        },
        toggleLoading(force=null){
            if(force===null) {
                this.loading = !this.loading;
                return;    
            }
            this.loading = force;
        },
        isReadonly(setting){
            return this.readonly || (setting.disableInActive && this.surveyActive);
        },
        toggleEditMode(){
            if(this.readonly) {
                this.triggerEvent({ target: 'lsnextquestioneditor', method: 'triggerEditQuestion', content: {} });
            }
        }
    },
    created(){
        this.$store.dispatch('getQuestionGeneralSettings').then(()=>{
            this.loading = false;
        });
    }
}
</script>

<style lang="scss" scoped>
.scoped--special-collapse {
    width: 50px;
    >.special-collapse-text {
        text-orientation: upright;
        writing-mode: vertical-lr;
        margin-top: 0.5rem;
    }
}
.scoped-general-settings {
    position: relative;
    min-width: 30%;
    &.collapsed {
        min-width: 0%;
        width: 75px;
    }
}
.scope-general-setting-block {
    margin: 1rem  0.1rem;
}
.scope-set-min-height {
    min-height: 280px;
}
</style>
