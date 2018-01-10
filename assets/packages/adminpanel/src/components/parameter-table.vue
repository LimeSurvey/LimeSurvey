<script>
    import _ from 'lodash';
    import ParameterPopup from './parameter-popup.vue'

    export default {
        name: 'lspanelparametertable',
        components: {
            parameterpopup: ParameterPopup
        },
        mixins: {},
        props: {
            translate: {type: Object},
            jsonUrl: {type:String},
            sid: {type: Number}
        },
        data () {
            return {
                showAllColumns: false,
                parameterRows: [],
                currentParameter: {},
                modalShown: false,
                isNew : false,
                toDeleteRow: null
            };
        },
        computed: {
            combinedValues(){
                return JSON.stringify(this.parameterRows);
            },
            questions(){
                return global.questionArray || [];
            }
        },
        methods: {
            getParameterRows () {
                let self = this;
                $.ajax({
                    url: this.jsonUrl,
                    dataType: 'json',
                    method: 'GET',
                    success: function (results) {
                        const dataSet = [];
                        _.forEach(results.rows, function (row, i) {
                            let rowArray = {
                                'id': row.id,
                                'parameter': row.parameter,
                                'targetQuestionText': row.questionTitle,
                                'targetSubQuestionText': row.subquestionTitle,
                                'sid': row.sid,
                                'qid': row.targetqid || '',
                                'sqid': row.targetsqid || ''
                            };
                            dataSet.push(rowArray);
                        });
                        self.parameterRows = dataSet;
                    }
                });
            },
            paramUpdated(updateUbject){
                if(updateUbject.isNew === false){
                    let paramIdx = _.findIndex(this.parameterRows, (item)=>{return item.id === updateUbject.paramRow.id});
                    if(paramIdx != -1)
                        this.parameterRows[paramIdx] = updateUbject.paramRow;
                } else {
                    this.parameterRows.push(updateUbject.paramRow);
                }

                this.toggleModal();
            },
            editRow(parameterRow){
                this.isNew = false;
                this.currentParameter = parameterRow;
                this.toggleModal();
            },
            addNewParam($event){
                this.isNew = true;
                this.currentParameter = {
                    id : this._guidGenerator(),
                    parameter : '',
                    targetQuestionText : '',
                    sid : this.sid,
                    qid : '',
                    sqid : ''
                };
                this.toggleModal();
            },
            deleteRow(parameterRow){
                this.toDeleteRow = parameterRow;
                $('#lspanelintegration-deletePopup').modal('toggle');
            },
            cancelDelete(){
                this.toDeleteRow = null;

            },
            confirmDelete(){
                if(this.toDeleteRow !== null){
                    let paramIdx = _.findIndex(this.parameterRows, (item)=>{return item.id === this.toDeleteRow.id});
                    if(paramIdx != -1)
                        this.parameterRows.splice(paramIdx,1);
                }
                $('#lspanelintegration-deletePopup').modal('toggle');

            },
            toggleModal(){
                this.modalShown = !this.modalShown;
                $('#lspanelintegration-parameterPopup').modal('toggle');
            },
            _guidGenerator() {
                const S4 = function() { return (((1+Math.random())*0x10000)|0).toString(16).substring(1); };
                return (S4()+S4()+'-'+S4()+'-'+S4()+'-'+S4()+'-'+S4()+S4()+S4());
            }
        },
        created () { },
        mounted () { 
            this.getParameterRows();
        }
    }
</script>


<template>
    <!-- Container -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <table id="urlparams" class='table dataTable table-striped table-borders'>
                    <thead>
                        <tr>
                            <th v-show="showAllColumns">{{translate.table.idColumn}}</th>
                            <th v-show="true">{{translate.table.actionColumn}}</th>
                            <th v-show="true">{{translate.table.parameterColumn}}</th>
                            <th v-show="true">{{translate.table.questionColumn}}</th>
                            <th v-show="showAllColumns">{{translate.table.sidColumn}}</th>
                            <th v-show="showAllColumns">{{translate.table.qidColumn}}</th>
                            <th v-show="showAllColumns">{{translate.table.sqidColumn}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="parameterRow in parameterRows" v-bind:key="parameterRow.id" :data-rowdata="JSON.stringify(parameterRow)">
                            <td v-show="showAllColumns"> {{parameterRow.id}} </td>
                            <td v-show="true"> 
                                <div>
                                    <button class="btn btn-sm btn-default" @click.prevent="editRow(parameterRow)"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" @click.prevent="deleteRow(parameterRow)"><i class="fa fa-trash"></i></button>
                                </div> 
                            </td>
                            <td v-show="true"> {{parameterRow.parameter }} </td>
                            <td v-show="true" v-html="parameterRow.targetQuestionText+(parameterRow.sqid != '' ? ' => '+ parameterRow.targetSubQuestionText : '')"> </td>
                            <td v-show="showAllColumns"> {{parameterRow.sid }} </td>
                            <td v-show="showAllColumns"> {{parameterRow.qid }} </td>
                            <td v-show="showAllColumns"> {{parameterRow.sqid }} </td>
                        </tr>
                    </tbody>
                </table>
                <input type='hidden' id='allurlparams' name='allurlparams' :value='combinedValues' />
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-right">
                <button @click.prevent="addNewParam($event)" class="btn btn-primary">{{translate.table.addParameter}}</button>
            </div>
        </div>

        <div class="modal fade" :class="{ in: modalShown }"  id="lspanelintegration-parameterPopup" role="dialog">
            <div class="modal-dialog" role="document">
                <parameterpopup :translate="translate" :questions="questions" :is-new="isNew" :parameter-row="currentParameter" v-on:updateparam="paramUpdated" v-on:canceledit="toggleModal" ></parameterpopup>
            </div>
        </div>

        <div class="modal fade" id="lspanelintegration-deletePopup" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        {{translate.popup.sureToDelete}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click.prevent="cancelDelete">{{translate.popup.deleteCancel}}</button>
                        <button type="button" class="btn btn-primary" @click.prevent="confirmDelete">{{translate.popup.deleteConfirm}}</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
