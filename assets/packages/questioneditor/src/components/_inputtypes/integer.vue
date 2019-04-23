<script>
    import empty from 'lodash/isEmpty';
    export default {
        name: 'setting-integer',
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
        computed: {
            curValue: {
                get() { return this.currentValue || this.elOptions.default || '' },
                set(newValue) { 
                    this.$emit('change', newValue);
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
            hasPrefix(){
                if(!empty(this.elOptions.inputGroup)){
                    return !empty(this.elOptions.inputGroup.prefix);
                }
                return false;
            },
            hasSuffix(){
                if(!empty(this.elOptions.inputGroup)){
                    return !empty(this.elOptions.inputGroup.suffix);
                }
                return false;
            },
        }
    };
</script>

<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" @click="triggerShowHelp=!triggerShowHelp" v-if="(elHelp.length>0)" />
        <label class="form-label" :for="elId"> {{elLabel}} </label>
            <div class="input-group col-12">
                <div v-if="hasPrefix" class="input-group-addon"> {{elOptions.inputGroup.prefix}} </div>
                <input 
                    type="number" 
                    v-model="curValue" 
                    :class="getClasses" 
                    :name="elName || elId" 
                    :id="elId" 
                    :max="elOptions.max || ''"
                    :min="elOptions.min || ''"
                />
                <div v-if="hasSuffix" class="input-group-addon"> {{elOptions.inputGroup.suffix}} </div>
            </div>
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>