<script>
    import empty from 'lodash/isEmpty';

    export default {
        name: 'setting-select',
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
                triggerShowHelp: false
            };
        },
        methods: {
            simpleValue(value) {
                if(value == []) {
                    return null;
                }
                return value;
            },
        },
        computed: {
            curValue: {
                get() { return this.currentValue },
                set(newValue) { 
                    this.$emit('change', {value: newValue, 'element': elId});
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ');
                }
                return '';
            }
        }
    };
</script>

<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" @click="triggerShowHelp=!triggerShowHelp" v-if="(elHelp.length>0)" />
        <label class="form-label" :for="elId"> {{elLabel}} </label>
        <select 
            v-model="curValue"
            :class="getClasses" 
            :name="elName || elId" 
            :id="elId" 
        >
            <option 
                v-for="(optionObject, i) in elOptions.options"
                :key="i"
                :value="simpleValue(optionObject.value)"
            >
                {{optionObject.text}}
            </option>
        </select>
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>