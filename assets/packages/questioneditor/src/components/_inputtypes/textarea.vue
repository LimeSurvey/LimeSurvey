<script>
    import foreach from 'lodash/foreach';
    import empty from 'lodash/isEmpty';

    export default {
        name: 'setting-textarea',
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
                get() { return this.currentValue },
                set(newValue) { this.$emit('change', newValue)},
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
            <div class="input-group">
                <div v-if="hasPrefix" class="input-group-addon"> {{elOptions.inputGroup.prefix}} </div>
                <textarea :class="getClasses" :name="elName || elId" :id="elId" v-model="curValue" v-bind="elOptions.attributes" ></textarea>
                <div v-if="hasSuffix" class="input-group-addon"> {{elOptions.inputGroup.suffix}} </div>
            </div>
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>