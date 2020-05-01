<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" 
           @click="triggerShowHelp=!triggerShowHelp" 
           v-if="(elHelp.length>0) && !readonly" 
           :aria-expanded="!triggerShowHelp" 
           :aria-controls="'help-'+(elName || elId)"/>
        <label class="form-label" :for="elId">
            {{titleWithLanguage}}
        </label>
        <div :class="getClasses" 
             :name="elName || elId"
             :id="elId" 
             v-html="curValue" />
        <div class="question-option-help well"
             :id="'help-'+(elName || elId)"
             v-show="showHelp"
             v-html="elHelp"/>
    </div>
</template>
<script>
    import empty from 'lodash/isEmpty';
    import abstractBaseType from '../abstracts/_abstractInputType';

    export default {
        name: 'setting-text',
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
                get() { return this.currentValue },
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
