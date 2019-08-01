
<script>
    
    import keys from 'lodash/keys';
    import foreach from 'lodash/forEach';
    import reduce from 'lodash/reduce';
    import filter from 'lodash/filter';
    import sortBy from 'lodash/sortBy';
    import isEmpty from 'lodash/isEmpty';
    import isObject from 'lodash/isObject';

    import SettingSwitch from '../_inputtypes/switch.vue';
    import SettingText from '../_inputtypes/text.vue';
    import SettingInteger from '../_inputtypes/integer.vue';
    import SettingSelect from '../_inputtypes/select.vue';
    import SettingTextdisplay from '../_inputtypes/textdisplay.vue';
    import SettingTextarea from '../_inputtypes/textarea.vue';
    import SettingButtongroup from '../_inputtypes/buttongroup.vue';
import SettingQuestiontheme from '../_inputtypes/questiontheme.vue';
    import StubSet from '../_inputtypes/stub.vue';

    export default {
        name: "settings-tab",
        props: {
            readonly : {type: Boolean, default: false}
        },
        data(){
            return {
                aComponentArray : [
                    'switch',
                    'text',
                    'integer',
                    'select',
                    'singleselect',
                    'textinput',
                    'buttongroup',
                    'textarea'
                ],
            }
        },
        components: {
            'setting-switch': SettingSwitch,
            'setting-questiontheme': SettingQuestiontheme,
            'setting-text': SettingText,
            'setting-integer': SettingInteger,
            'setting-select': SettingSelect,
            'setting-singleselect': SettingSelect,
            'setting-textdisplay': SettingTextdisplay,
            'setting-textarea': SettingTextarea,
            'setting-buttongroup': SettingButtongroup,
            'stub-set' : StubSet
        },
        computed: {
            
            currentAdvancedSettingsList() {
                return filter(this.$store.state.currentQuestionAdvancedSettings[this.currentTab], (settingOption) => {
                    return !isEmpty(this.parseForLocalizedOption(settingOption.formElementValue));
                });
            },
            currentSettingsTab(){
                let items =  filter(
                    this.$store.state.currentQuestionAdvancedSettings[this.$store.state.questionAdvancedSettingsCategory],
                    (item) => {
                        if(this.aComponentArray.indexOf((item.inputtype)) == -1){ return false; }
                        if(this.readonly) { return !isEmpty(this.parseForLocalizedOption(item.formElementValue)); }
                        return true;
                    }
                );
                return sortBy(
                    items, 
                    item=>item.aFormElementOptions.sortorder 
                    );
            },
            surveyActive() {
                return this.$store.getters.surveyObject.active =='Y'
            }
        },
        methods: {
            reactOnChange(newValue, oAdvancedSettingObject) {
                this.$store.commit('setQuestionAdvancedSetting', {newValue, settingName: oAdvancedSettingObject.formElementId});   
            },
            getComponentName(componentRawName){
                
                componentRawName = componentRawName=='singleselect' ? 'select' : componentRawName;
                if(this.aComponentArray.indexOf(componentRawName) > -1 ){
                    return 'setting-'+componentRawName;
                }
                return 'stub-set';
            },
            parseForLocalizedOption(value) {
                if(isObject(value) && value[this.$store.state.activeLanguage] != undefined) {
                    return value[this.$store.state.activeLanguage];
                }
                return value;
            },
            isReadonly(setting) {
                return (this.readonly || (setting.disableInActive && this.surveyActive));
            }
        }
    }
</script>

<template>
    <div class="col-sm-12">
        <div class="list-group scoped-custom-list-group">
            <div 
                class="list-group-item question-option-advanced-setting-block" 
                v-for="advancedSetting in currentSettingsTab" 
                :key="advancedSetting.name"
            >
                <!-- Here be debug information -->
                <pre v-show="$store.debugMode === true">{{ advancedSetting }}</pre>
                <component 
                v-bind:is="getComponentName(advancedSetting.inputtype)" 
                :elId="advancedSetting.formElementId"
                :elName="advancedSetting.formElementName"
                :elLabel="advancedSetting.title"
                :elHelp="advancedSetting.formElementHelp"
                :currentValue="advancedSetting.formElementValue"
                :elOptions="advancedSetting.aFormElementOptions"
                :debug="advancedSetting"
                :readonly="isReadonly(advancedSetting)"
                @change="reactOnChange($event, advancedSetting)"
                ></component>
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
</style>
