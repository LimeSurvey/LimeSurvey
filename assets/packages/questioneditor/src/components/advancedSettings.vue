<script>
import SettingSwitch from './_inputtypes/switch.vue';
import SettingText from './_inputtypes/text.vue';
import SettingInteger from './_inputtypes/integer.vue';
import SettingSelect from './_inputtypes/select.vue';
import SettingTextdisplay from './_inputtypes/textdisplay.vue';
import SettingTextarea from './_inputtypes/textarea.vue';
import StubSet from './_inputtypes/stub.vue';

import keys from 'lodash/keys';
import filter from 'lodash/filter';
import sortBy from 'lodash/sortBy';

export default {
    name: 'AdvancedSettings',
    components: {
        'setting-switch': SettingSwitch,
        'setting-text': SettingTextdisplay,
        'setting-integer': SettingInteger,
        'setting-select': SettingSelect,
        'setting-textinput': SettingText,
        'setting-textarea': SettingTextarea,
        'stub-set' : StubSet
    },
    data() {
        return {
            aComponentArray : [
                'switch',
                'text',
                'integer',
                'select',
                'textinput',
                'textarea'
            ],
        };
    },
    computed: {
        organizedSettings(){
            return filter(this.$store.state.questionAdvancedSettings, (settingOptions, category) => category != 'debug');
        },
        currentSettingsTab(){
            let items =  filter(
                this.$store.state.questionAdvancedSettings[this.$store.state.questionAdvancedSettingsCategory],
                (item) => this.aComponentArray.indexOf((item.inputtype=='singleselect' ? 'select' : item.inputtype)) > -1 
            );
            return sortBy(
                items, 
                item=>item.aFormElementOptions.sortorder 
                );
        },
        tabs(){
            return filter(keys(this.$store.state.questionAdvancedSettings), (category) => category != 'debug');
        }
    },
    methods: {
        selectCurrentTab(categoryName) {
            this.$store.commit('setQuestionAdvancedSettingsCategory',categoryName);
        },
        getComponentName(componentRawName){
            
            componentRawName = componentRawName=='singleselect' ? 'select' : componentRawName;
            if(this.aComponentArray.indexOf(componentRawName) > -1 ){
                return 'setting-'+componentRawName;
            }
            return 'stub-set';
        },
        reactOnChange(newValue, oAdvancedSettingObject) {
            this.$store.commit('setQuestionAdvancedSetting', {newValue, settingName: oAdvancedSettingObject.name});   
        }
    },
    mounted(){
        this.selectCurrentTab(this.tabs[0]);
    }
}
</script>

<template>
    <div class="col-xs-12 scope-apply-base-style scope-min-height">
        <div class="container-fluid">
            <div class="row">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li 
                        v-for="advancedSettingCategory in tabs"
                        :key="'tablist-'+advancedSettingCategory"
                        :class="$store.state.questionAdvancedSettingsCategory == advancedSettingCategory ? 'active' : ''"
                    >
                        <a href="#" @click.prevent.stop="selectCurrentTab(advancedSettingCategory)" >{{advancedSettingCategory}}</a>
                    </li>
                </ul>
            </div>
            <div class="row scope-border-open-top">
                <div class="col-sm-12">
                    <div class="list-group scoped-custom-list-group">
                        <div 
                            class="list-group-item question-option-advanced-setting-block" 
                            v-for="advancedSetting in currentSettingsTab" 
                            :key="advancedSetting.name"
                        >
                            <component 
                            v-bind:is="getComponentName(advancedSetting.inputtype)" 
                            :elId="advancedSetting.formElementId"
                            :elName="advancedSetting.formElementName"
                            :elLabel="advancedSetting.title"
                            :elHelp="advancedSetting.formElementHelp"
                            :currentValue="advancedSetting.formElementValue"
                            :elOptions="advancedSetting.aFormElementOptions"
                            :debug="advancedSetting"
                            @change="reactOnChange($event, advancedSetting)"
                            ></component>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</template>

<style lang="scss" scoped>
.scoped-custom-list-group {
    display: flex;
    flex-wrap: wrap;
    align-content: space-between;
    align-items: flex-start;
    width: 100%;
    margin: 0;
    padding: 1rem 0;

    .list-group-item {
        width: 98%;
        display: inline-block;
        margin: 0.5% 1%;

    }

    @media(min-width: 992px) {
        .list-group-item {
            width: 48%;
            display: inline-block;
        }
    }
    
    .list-group-item:first-child,
    .list-group-item:last-child {
        border-radius: 0;
    }
}
.scope-min-height {
    min-height: 20vh;
}
.scope-apply-base-style {
    border-top: 1px solid #cfcfcf;
    margin-top: 1rem;
    padding-top: 1rem;
}
.scope-border-open-top {
    border-left: 1px solid #cfcfcf;
    border-right: 1px solid #cfcfcf;
    border-bottom: 1px solid #cfcfcf;
}
</style>