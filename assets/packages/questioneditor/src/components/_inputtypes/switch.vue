<script>
    import merge from 'lodash/merge';
    import empty from 'lodash/isEmpty';
    import filter from 'lodash/filter';
        
    import BootstrapToggle from 'vue-bootstrap-toggle'

    export default {
        name: 'setting-checkboxswitch',
        components: {BootstrapToggle},
        props: {
            elId: {type: String, required: true},
            elName: {type: [String, Boolean], default: ''},
            elLabel: {type: String, default: ''},
            elHelp: {type: String, default: ''},
            currentValue: {default: false},
            elOptions: {type: Object, default: {}},
            debug: {type: [Object, Boolean]},
            disabled: {type: Boolean, default: false}
        },
        data(){
            return {
                triggerShowHelp: false,
                defaults: {},
            };
        },
        computed: {
            curValue: {
                get() { return this.currentValue == this.onValue },
                set(newValue) { 
                    this.$emit('change', (newValue ? this.onValue : this.offValue));
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
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
                    size: "small",
                    on : this.onText,
                    off : this.offText
                };
                return merge(curSwitchOptions, this.dataAttributes);
            },
            onText() {
                return this.elOptions.options.option[0].text;
            },
            onValue() {
                return this.elOptions.options.option[0].value;
            },
            offText() {
                return this.elOptions.options.option[1].text;
            },
            offValue() {
                return this.elOptions.options.option[1].value;
            },
        },
    };
</script>

<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" @click="triggerShowHelp=!triggerShowHelp" v-if="(elHelp.length>0)" />
        <label class="form-label" :for="elId"> {{elLabel}} </label>
        <div :class="getClasses">
            <bootstrap-toggle v-model="curValue" :options="switchOptions" :disabled="disabled" />
            <!-- <input type="checkbox" :name="elName || elId" :id="elId" v-model="curValue"/> -->
        </div> 
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>