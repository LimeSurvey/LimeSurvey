<script>
import map from 'lodash/map';
import merge from 'lodash/merge';
import keys from 'lodash/keys';

import Autocomplete from './Autocomplete';
import AjaxMixin from '../mixins/runAjax';

export default {
    name: 'LabelSets',
    components: {Autocomplete},
    mixins: [AjaxMixin],
    props: {
        template: {type: Object, required: true},
        scaleId: {type: Number, required: true},
        type: {type: String, required: true},
        typedef: {type: String, required: true},
        typekey: {type: String, required: true},
    },
    data() {
        return {
            labelSets: [],
            isLoading: true,
            currentLabelSet: null
        };
    },
    computed: {
        labelSetToDataSet() {
            return map(this.currentLabelSet.labels, (label) => {
                const dataSet = merge({},this.template);
                dataSet[this.$store.state.activeLanguage][this.typedef] = this.currentLanguageValue(label);
                dataSet[this.typekey] = label.code;
                return dataSet;
            });
        }
    },
    methods: {
        compileLanguages(languageString) {
            return `(${languageString.split(' ').join(',')})`;
        },
        replaceCurrent() {
            this.$emit('modalEvent', {target: this.type, method: 'replaceFromLabelSets', content: {scaleId: this.scaleId ,data: this.labelSetToDataSet}});
            this.$emit('close');
        },
        addToCurrent() {
            this.$emit('modalEvent', {target: this.type, method: 'addToFromLabelSets', content: {scaleId: this.scaleId ,data: this.labelSetToDataSet}});
            this.$emit('close');
        },
        currentLanguageValue(label) {
            return label[this.$store.state.activeLanguage] != undefined ? label[this.$store.state.activeLanguage].title : '';
        }
    },
    created() {
        this.$_get(LS.createUrl('admin/labels/sa/getLabelSetsForQuestion'), {'languages': keys(this.$store.state.languages)}).then(
            (result) => {
                this.isLoading = false;
                this.labelSets = result.data;
            },
            (error) => {
                this.$log.error(error);
                this.isLoading = false;
            }
        )
    }
}
</script>

<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="row">
                <div class="pagetitle h3">{{'Label sets' | translate}}</div>
            </div>
            <div class="row">
                <label class="control-label col-xs-12 col-md-4 text-right"> 
                    <template v-if="isLoading">
                        <i class="fa fa-cog fa-spin"></i>
                    </template>
                    <template v-else>
                    {{"Select label set" | translate }} 
                    </template>
                </label>
                <div class="col-xs-12 col-md-8">
                    <autocomplete 
                        v-model="currentLabelSet" 
                        :data-list="labelSets"
                        :searchable-keys="['label_name']"
                        show-key="label_name"
                    />
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="container-fluid scoped-max-height-and-scrollabel" v-if="currentLabelSet!=null">
                <div class="row">
                    <div class="col-xs-12">
                        <h4> {{currentLabelSet.label_name }} </h4>
                    </div>
                </div>
                <div class="row">
                    <hr/>
                </div>
                <div class="row scoped-descriptionrow">
                    <div class="col-xs-3">
                        {{"Sortorder"|translate}}
                    </div>
                    <div class="col-xs-3">
                        {{typekey|translate}}
                    </div>
                    <div class="col-xs-3">
                        {{typedef|translate}}
                    </div>
                    <div class="col-xs-3" v-if="type=='answeroptions'">
                        {{ "Assessment value"|translate }}
                    </div>
                </div>
                <div class="row" v-for="label in currentLabelSet.labels" :key="label.id">
                    <div class="col-xs-3">
                        {{label.sortorder}}
                    </div>
                    <div class="col-xs-3">
                        {{label.code}}
                    </div>
                    <div class="col-xs-3">
                        {{ currentLanguageValue(label) }}
                    </div>
                    <div class="col-xs-3" v-if="type=='answeroptions'">
                        {{ label.assessment_value }}
                    </div>
                </div>
            </div>
            <div  class="container-fluid" v-else>
                <div class="row">
                    <div class="row">
                        <p class="text-center scoped-no-selection"> {{"No label set selected" | translate}} </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div class="ls-flex-row wrap">
                <div class="ls-flex-item">
                    <button class="btn btn-primary ls-space margin left-5" @click="replaceCurrent" type="button">{{'Replace' | translate}}</button>
                    <button class="btn btn-primary ls-space margin left-5" @click="addToCurrent" type="button">{{'Add' | translate}}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .scoped-max-height-and-scrollabel {
        max-height: 50vh;
        overflow-y: auto;
    }
</style>
