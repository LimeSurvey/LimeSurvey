<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" 
            @click="triggerShowHelp=!triggerShowHelp" 
            v-if="(elHelp.length>0) && !readonly" 
            :aria-expanded="!triggerShowHelp" 
            :aria-controls="'help-'+(elName || elId)"
        />
        <label class="form-label" :for="elId"> {{titleWithLanguage}} </label>
        <div class="input-group col-12">
            <div v-if="hasPrefix" class="input-group-addon"> {{elOptions.inputGroup.prefix}}</div>
            <input
                    type="text"
                    v-model="curValue"
                    :pattern="elOptions.elInputPattern"
                    :class="getClasses"
                    :name="elName || elId"
                    :id="elId"
                    :readonly="readonly"/>
            <div v-if="hasSuffix" class="input-group-addon"> {{elOptions.inputGroup.suffix}}</div>
        </div>
        <div 
            class="question-option-help well"
            :id="'help-'+(elName || elId)"
            v-show="showHelp"
            v-html="elHelp"
        />
    </div>
</template>
<script>
    import empty from 'lodash/isEmpty';
    import abstractBaseType from '../abstracts/_abstractInputType';

    export default {
        name: 'setting-input',
        extends: abstractBaseType,
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
        /*
        Abstract base provides data: 
         - triggerShowHelp
        */
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
                get() { 
                    if(typeof this.currentValue !== 'string') {
                        return this.currentValue[this.$store.state.activeLanguage];
                    }
                    return this.currentValue
                },
                set(newValue) { 
                    if(typeof this.currentValue !== 'string') {
                        let tmpCurrentValue = this.currentValue;
                        tmpCurrentValue[this.$store.state.activeLanguage] = newValue;
                        this.$emit('change', tmpCurrentValue);
                        return;
                    }
                    this.$emit('change', newValue);
                },
            },
            titleWithLanguage() {
                if (typeof this.currentValue !== 'string') {
                    return this.elLabel + ' (' + this.$store.state.languages[this.$store.state.activeLanguage] + ')';
                }
                return this.elLabel
            }
        }
    };
</script>