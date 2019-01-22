<script>
import SettingSwitch from './_inputtypes/switch.vue';
import SettingText from './_inputtypes/text.vue';
import SettingSelect from './_inputtypes/select.vue';
import SettingTextdisplay from './_inputtypes/textdisplay.vue';
import SettingTextarea from './_inputtypes/textarea.vue';
import StubSet from './_inputtypes/stub.vue';

import keys from 'lodash/keys';

export default {
    name: 'AdvancedSettings',
    components: {
        'setting-switch': SettingSwitch,
        'setting-text': SettingTextdisplay,
        'setting-select': SettingSelect,
        'setting-textinput': SettingText,
        'setting-textarea': SettingTextarea,
        'stub-set' : StubSet
    },
    data() {
        return {};
    },
    computed: {
        organizedSettings(){
            return this.$store.state.questionAdvancedSettings;
        },
        tabs(){
            return keys(this.$store.state.questionAdvancedSettings);
        }
    },
    methods: {
        getComponentName(componentRawName){
            if(componentRawName != undefined)
                return 'setting-'+componentRawName;
            return 'stub-set';
        },
    },
    mounted(){}
}
</script>

<template>
    <div class="col-xs-12 scope-border-simple scope-min-height">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li 
                class="active"
                v-for="advancedSettingCategory in tabs"
                :key="'tablist-'+advancedSettingCategory"
            >
                <a :href="'#tab-'+advancedSettingCategory" role="tab" data-toggle="tab">{{advancedSettingCategory}}</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div 
                class="tab-pane active" 
                v-for="(advancedSettingCategory,advancedSettingOptionsBlock) in organizedSettings"
                :key="'tab-'+advancedSettingCategory"
                :id="'tab-'+advancedSettingCategory"
            >
                <div class="panel panel-default question-option-advanced-container">
                    <div class="panel-heading">{{advancedSettingCategory}} </div>
                    <div class="panel-body">
                        <div class="list-group">
                            <div 
                                class="list-group-item question-option-advanced-setting-block" 
                                v-for="advancedSetting in advancedSettingOptionsBlock" 
                                :key="advancedSetting.name"
                            >
                                <component 
                                v-bind:is="getComponentName(advancedSetting.inputtype)" 
                                :elId="advancedSetting.formElementId"
                                :elName="advancedSetting.formElementName"
                                :elLabel="advancedSetting.title"
                                :elHelp="advancedSetting.formElementHelp"
                                :currentValue="advancedSetting.formElementValue"
                                :elOptions="advancedSetting.formElementOptions"
                                :debug="advancedSetting"
                                @change="reactOnChange"
                                ></component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.scope-min-height {
    height: 10vh;
}
.scope-border-simple {
    border: 1px solid #cfcfcf;
}
</style>