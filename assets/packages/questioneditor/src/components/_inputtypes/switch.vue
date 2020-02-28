<script>
    import merge from 'lodash/merge';
    import empty from 'lodash/isEmpty';
    import filter from 'lodash/filter';
    import BootstrapToggle from 'vue-bootstrap-toggle'

    import abstractBaseType from '../abstracts/_abstractInputType';

    export default {
        name: 'setting-checkboxswitch',
        extends: abstractBaseType,
        components: {BootstrapToggle},
        props: {
            /*
            Abstract base provides props: 
             - elId
             - elName
             - elLabel
             - elHelp
             - currentValue
             - elOptions
             - readonly
             - debug
            */
            disabled: {type: Boolean, default: false},
        },
        data(){
            /*
            Abstract base provides data: 
             - triggerShowHelp
            */
            return {
                defaults: {},
            };
        },
        computed: {
            /*
            Abstract base provides computed values: 
             - curValue
             - getClasses
             - showHelp
             - hasPrefix
             - hasSuffix
            */
            curValue: {
                get() { return this.currentValue == this.onValue },
                set(newValue) { 
                    this.$emit('change', (newValue ? this.onValue : this.offValue));
                },
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return filter(this.elOptions.classes, sClass => sClass !== 'form-control').join(' ')
                }
                return '';
            },
            dataAttributes(){
                return this.elOptions.switchData;
            },
            switchOptions() {
                let curSwitchOptions = {
                    onstyle: "primary",
                    offstyle: "warning",
                    size: "normal",
                    on : this.onText,
                    off : this.offText
                };
                return merge(curSwitchOptions, this.dataAttributes);
            },
            onText() {
                return this.elOptions.options.option[1].text;
            },
            onValue() {
                return this.elOptions.options.option[1].value;
            },
            offText() {
                return this.elOptions.options.option[0].text;
            },
            offValue() {
                return this.elOptions.options.option[0].value;
            },
        },
    };
</script>

<template>
    <div class="form-row">
        <i 
            class="fa fa-question pull-right" 
            @click="triggerShowHelp=!triggerShowHelp" 
            v-if="(elHelp.length>0) && !readonly"
            :aria-expanded="!triggerShowHelp" 
            :aria-controls="'help-'+(elName || elId)"
        ></i>
        <label class="form-label" :for="elId"> {{elLabel}} </label>
        <div class="inputtype--toggle-container" :class="getClasses">
            <bootstrap-toggle v-model="curValue" :options="switchOptions" :disabled="disabled || readonly" />
            <!-- <input type="checkbox" :name="elName || elId" :id="elId" v-model="curValue"/> -->
        </div> 
        <transition name="fade">
            <div 
                class="question-option-help well"
                :id="'help-'+(elName || elId)"
                v-show="showHelp"
                v-html="elHelp"
            />
        </transition>
    </div>
</template>

<style lang="scss">
    .inputtype--toggle-container {
        .toggle[disabled] {
            .toggle-group {
                label {
                    cursor: not-allowed;
                }
            }
        }
    }
</style>
