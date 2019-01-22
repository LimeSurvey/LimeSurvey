<script>
import filter from 'lodash/filter';

import SettingCheckboxswitch from './_inputtypes/checkboxswitch.vue';
import SettingText from './_inputtypes/text.vue';
import SettingSelect from './_inputtypes/select.vue';
import SettingTextinput from './_inputtypes/textinput.vue';
import SettingTextarea from './_inputtypes/textarea.vue';
import StubSet from './_inputtypes/stub.vue';

export default {
    name: 'GeneralSettings',
    components: {
        'setting-checkboxswitch': SettingCheckboxswitch,
        'setting-text': SettingText,
        'setting-select': SettingSelect,
        'setting-textinput': SettingTextinput,
        'setting-textarea': SettingTextarea,
        'stub-set' : StubSet
    },
    data() {
        return {};
    },
    computed: {
        generalSettingOptions(){
            return filter(this.$store.state.questionGeneralSettings, (questionSetting) => {
                return (questionSetting.formElement != undefined)
            });
        }
    },
    methods: {
        getComponentName(componentRawName){
            if(componentRawName != undefined)
                return 'setting-'+componentRawName;
            return 'stub-set';
        },
        reactOnChange(updateObject) {
            
        }
    }
}
</script>

<template>
    <div class="col-sm-4 col-xs-12 scope-border-simple scope-set-min-height">
        <div class="panel panel-default question-option-general-container">
            <div class="panel-heading"> {{"General Settings" | translate }}</div>
            <div class="panel-body">
                <div class="list-group">
                    <div class="list-group-item question-option-general-setting-block" v-for="generalSetting in generalSettingOptions" :key="generalSetting.name">
                        <component 
                        v-bind:is="getComponentName(generalSetting.formElement)" 
                        :elId="generalSetting.formElementId"
                        :elName="generalSetting.formElementName"
                        :elLabel="generalSetting.title"
                        :elHelp="generalSetting.formElementHelp"
                        :currentValue="generalSetting.formElementValue"
                        :elOptions="generalSetting.formElementOptions"
                        :debug="generalSetting"
                        @change="reactOnChange"
                        ></component>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.scope-general-setting-block {
    margin: 1rem  0.1rem;
}
.scope-set-min-height {
    min-height: 40vh;
}
</style>
