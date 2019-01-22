<script>
    import merge from 'lodash/merge';
    import empty from 'lodash/isEmpty';
    export default {
        name: 'setting-checkboxswitch',
        props: {
            elId: {type: String, required: true},
            elName: {type: [String, Boolean], default: ''},
            elLabel: {type: String, default: ''},
            elHelp: {type: String, default: ''},
            currentValue: {default: ''},
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
                    this.$emit('change', this.$$el.prop('checked'))
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ')
                }
                return '';
            },
            dataAttributes(){
                return this.elOptions.switchData;
            },
            $$el() {
                return jQuery('input#'+this.elId);
            }
        },
        mounted() {
            let curSwitchOptions = {};
            curSwitchOptions = merge(curSwitchOptions, this.dataAttributes);
            curSwitchOptions.onSwitchChange = () => { 
                this.$$el.prop('checked', !this.$$el.prop('checked')); 
                this.$emit('change', this.$$el.prop('checked'))
                this.changed();
            };

            this.$log.log('BOOTSTRAP SWITCH OPTIONS for '+this.elId, curSwitchOptions, this.dataAttributes);
            this.$$el.bootstrapSwitch(curSwitchOptions);

            if (this.disabled) { 
                this.$$el.bootstrapToggle('disable'); 
            }
        },
        beforeDestroy() {
            this.$$el.bootstrapToggle('destroy');
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