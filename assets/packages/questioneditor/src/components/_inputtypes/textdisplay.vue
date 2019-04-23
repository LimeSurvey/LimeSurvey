<script>
    import empty from 'lodash/isEmpty';
    export default {
        name: 'setting-text',
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
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ')
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
        <div :class="getClasses" :name="elName || elId" :id="elId" v-html="curValue" />
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>