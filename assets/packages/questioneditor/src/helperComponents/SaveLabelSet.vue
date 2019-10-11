
<script>
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import foreach from 'lodash/forEach';

import AjaxMixin from '../mixins/runAjax';

export default {
    name: 'SaveAsLabelSet',
    mixins: [AjaxMixin],
    props: {
        dataSet: {type: Object, required: true},
        scaleId: {type: Number, required: true},
        type: {type: String, required: true},
        typedef: {type: String, required: true},
        typekey: {type: String, required: true},
    },
    data() {
        return {
            labelName: '',

        }
    },
    methods: {
        confirm() {
            const dataSetTosend = merge({}, this.dataSet);

            delete dataSetTosend[this.typekey];
            dataSetTosend.code = this.dataSet[this.typekey];

            foreach(this.$store.state.languages, (language, lngKey) => {

                let tmpLangObj = merge({}, this.dataSet[lngKey]);
                tmpLangObj.title = tmpLangObj[this.typedef];
                delete tmpLangObj[this.typedef];
                dataSetTosend[lngKey] = tmpLangObj;

            });
            const payload = {
                label_name: this.labelName,
                labels: dataSetTosend,
                languages: keys(this.$store.state.languages).join(' ')
            }
            this.$store.dispatch('saveAsLabelSet', payload).then(
                (result) => { LS.LsGlobalNotifier.create(result.data.message, result.data.classes || 'well backgroung-success '); this.$emit('close'); },
                error => {this.$log.error(error); this.$emit('close'); }
            );
        }
    }
}
</script>

<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{'Save as labelset' | translate}}</h3>
        </div>
        <div class="panel-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="form-group">
                        <label for="exampleInputEmail1">{{ "Name for label set" | translate}}</label>
                        <input type="text" class="form-control" v-model="labelName">
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body text-right">
            <button @click.prevent="confirm" class="btn btn-success">{{ 'Confirm' | translate }}</button>
            <button @click.prevent="$emit('close')" class="btn btn-error">{{"Cancel" | translate }}</button>
        </div>
    </div>
</template>

<style>

</style>
