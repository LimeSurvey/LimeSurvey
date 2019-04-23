<script>
    import merge from 'lodash/merge';
    import empty from 'lodash/isEmpty';
    import filter from 'lodash/filter';

    export default {
        name: 'setting-checkboxswitch',
        props: {
            elId: {type: String, required: true},
            elName: {type: [String, Boolean], default: ''},
            elLabel: {type: String, default: ''},
            elHelp: {type: String, default: ''},
            currentValue: {default: false},
            elOptions: {type: Object, default: {}},
            debug: {type: [Object, Boolean]}
        },
        data(){
            return {
                triggerShowHelp: false,
                defaults: {},
            };
        },
        computed: {
            curValue: {
                get() { return this.currentValue },
                set(newValue) { 
                    this.$emit('change', newValue);
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
            $$el() {
                return jQuery('input#'+this.elId);
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
        methods: {
            changed(){
                const newValue = this.$$el.prop('checked') ? this.onValue : this.offValue;
                this.curValue  = newValue;
                this.$log.log('SwitchChange ->', newValue);
            }
        },
        mounted() {
            let curSwitchOptions = {
                onColor: "primary",
                offColor: "warning",
                size: "small",
                onText : this.onText,
                offText : this.offText
            };

            curSwitchOptions = merge(curSwitchOptions, this.dataAttributes);
            curSwitchOptions.onSwitchChange = () => { 
                //this.$emit('change', !this.$$el.prop('checked'))
                //this.$$el.prop('checked', !this.$$el.prop('checked')); 
                this.changed();
            };

            this.$log.log('BOOTSTRAP SWITCH OPTIONS for '+this.elId, curSwitchOptions, this.dataAttributes);
            this.$$el.bootstrapSwitch(curSwitchOptions);

            if (this.disabled) { 
                this.$$el.bootstrapSwitch('disable'); 
            }
        },
        beforeDestroy() {
            this.$$el.bootstrapSwitch('destroy');
        }
    };
</script>

<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" @click="triggerShowHelp=!triggerShowHelp" v-if="(elHelp.length>0)" />
        <label class="form-label" :for="elId"> {{elLabel}} </label>
        <div :class="getClasses">
            <input type="checkbox" :name="elName || elId" :id="elId" v-model="curValue"/>
        </div> 
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>