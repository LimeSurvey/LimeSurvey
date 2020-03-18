<script>
    import foreach from 'lodash/forEach';
    import empty from 'lodash/isEmpty';

    import abstractBaseType from '../abstracts/_abstractInputType';

    export default {
        name: 'setting-textarea',
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
                get() { return this.decode(this.currentValue) },
                set(newValue) { this.$emit('change', this.encode(newValue))},
            },
            parsedAttributes(){
                if(typeof this.elOptions.attributes == Object) {
                    let attributeString = '';
                    foreach(this.elOptions.attributes, (attributeValue, attributeKey) => {
                        attributeString += ` ${attributeKey}=${attributeValue} `;
                    });
                    return attributeString;
                }
                return '';
            },
            titleWithLanguage() {
                if (typeof this.currentValue !== 'string') {
                    return this.elLabel + ' (' + this.$store.state.languages[this.$store.state.activeLanguage] + ')';
                }
                return this.elLabel
            }
        },
        methods: {
            decode(value) {
                if(typeof value == 'string') {
                    return value;
                }
                if(typeof value == 'object') {
                    return value[this.$store.state.activeLanguage];
                }
            },
            encode(value) {
                if(typeof this.currentValue == 'object') {
                    this.currentValue[this.$store.state.activeLanguage] = value;
                    return this.currentValue;
                } 
                return value;

            },
        }
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
        />
        <label class="form-label" :for="elId"> {{titleWithLanguage}} </label>
        <div class="input-group col-12">
            <div v-if="hasPrefix" class="input-group-addon"> {{elOptions.inputGroup.prefix}}</div>
            <textarea :class="getClasses" :name="elName || elId" :id="elId" v-model="curValue" :readonly="readonly"
                      v-bind="elOptions.attributes"></textarea>
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
