<script>
    import empty from 'lodash/isEmpty';

    export default {
        name: 'setting-buttongroup',
        props: {
            elId: {type: String, required: true},
            elName: {type: [String, Boolean], default: ''},
            elLabel: {type: String, default: ''},
            elHelp: {type: String, default: ''},
            currentValue: {default: ''},
            elOptions: {type: Object, default: {}},
            debug: {type: [Object, Boolean]},
            readonly: {type: Boolean, default: false}
        },
        data(){
            return {
                triggerShowHelp: false
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
        },
        methods: {
            simpleValue(value) {
                if(value == []) {
                    return null;
                }
                return value;
            },
             getHTMLClasses(value) {
                let classes = 'btn btn-default ';
                if(!empty(this.elOptions.classes)) {
                    classes += this.elOptions.classes.join(' ');
                }
                
                classes += (value == this.curValue ? 'active ' : '');

                return classes;
            }
        },
    };
</script>

<template>
    <div class="form-row">
        <i class="fa fa-question pull-right" @click="triggerShowHelp=!triggerShowHelp" v-if="(elHelp.length>0) && !readonly" />
        <label class="form-label"> {{elLabel}} </label>
        <div class="btn-group col-12">
            <label 
                v-for="(optionObject, i) in elOptions.options"
                :key="i"
                type="button" 
                :for="'input-'+(elName || elId)+'_'+i" 
                :class="getHTMLClasses(optionObject.value)" 
                :disabled="readonly"
            >
                <input 
                    v-if="!readonly"
                    type="radio" 
                    :id="'input-'+(elName || elId)+'_'+i" 
                    v-model="curValue"
                    :name="elName || elId" 
                    :value="simpleValue(optionObject.value)"
                />
                {{optionObject.text}}
            </label>
        </div>
        <div 
            class="question-option-help alert alert-info"
            v-if="showHelp"
            v-html="elHelp"
        />
    </div>
</template>