
<script>
    import filter from 'lodash/filter';
    import sortBy from 'lodash/sortBy';

    import SettingSwitch from '../_inputtypes/switch.vue';
    import SettingText from '../_inputtypes/text.vue';
    import SettingInteger from '../_inputtypes/integer.vue';
    import SettingSelect from '../_inputtypes/select.vue';
    import SettingTextdisplay from '../_inputtypes/textdisplay.vue';
    import SettingTextarea from '../_inputtypes/textarea.vue';
    import StubSet from '../_inputtypes/stub.vue';

    export default {
        name: "settings-tab",
        data(){
            return {
                aComponentArray : [
                    'switch',
                    'text',
                    'integer',
                    'select',
                    'textinput',
                    'textarea'
                ],
            }
        },
        components: {
            'setting-switch': SettingSwitch,
            'setting-text': SettingTextdisplay,
            'setting-integer': SettingInteger,
            'setting-select': SettingSelect,
            'setting-textinput': SettingText,
            'setting-textarea': SettingTextarea,
            'stub-set' : StubSet
        },
        computed: {
            currentSettingsTab(){
                let items =  filter(
                    this.$store.state.currentQuestionAdvancedSettings[this.$store.state.questionAdvancedSettingsCategory],
                    (item) => this.aComponentArray.indexOf((item.inputtype=='singleselect' ? 'select' : item.inputtype)) > -1 
                );
                return sortBy(
                    items, 
                    item=>item.aFormElementOptions.sortorder 
                    );
            },
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
