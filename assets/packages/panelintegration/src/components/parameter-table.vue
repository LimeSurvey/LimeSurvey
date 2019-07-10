<script>
    import ParameterPopup from './parameter-popup.vue'
    import Loader from '../helperComponents/loader.vue'

    export default {
        name: 'lspanelparametertable',
        components: {
            loaderWidget: Loader,
            parameterpopup: ParameterPopup
        },
        data () {
            return {
                showAllColumns: false,
                modalShown: false,
                isNew : false,
                toDeleteRow: null,
                loading: true
            };
        },
        computed: {
            currentParameter: {
                get(){ return this.$store.state.currentSelectedParameter; },
                set(newValue){ this.$store.commit('setCurrentSelectedParameter', newValue)},
            },
            parameterRows: {
                get() { return this.$store.state.rowdata },
                set(newValue) { this.$store.commit('setRowdata', newValue) }
            },
            combinedValues(){
                return JSON.stringify(this.parameterRows);
            },
            questions(){
                return this.$store.state.questionArray || [];
            }
        },
        methods: {
            paramUpdated(updateUbject){
                if(updateUbject.isNew === false){
                    let paramIdx = LS.ld.findIndex(this.parameterRows, (item)=>{return item.id === updateUbject.paramRow.id});
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
                    let tmpArray = LS.ld.filter(this.parameterRows, (item)=>{return item.id !== this.toDeleteRow.id});
                    if(tmpArray.length !== this.parameterRows.length ) {
                        this.parameterRows = tmpArray;
                    }
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
        created () { 
            this.$store.dispatch('getCurrentQuestionlist');
            this.$store.dispatch('getCurrentParameters').then(
                () => {this.loading = false;},
                (error)=>{
                    this.$log.trace(arguments);
                    this.loading = false;
                }
            );
         },
        mounted () { 
        }
    }
</script>


<template>
  <!-- Container -->
  <div class="container-fluid">
    <template v-if="!loading">
      <div class="row">
        <div class="col-sm-12">
          <table id="urlparams" class="table dataTable table-striped table-borders">
            <thead>
              <tr>
                <th v-show="showAllColumns">{{"Id" | translate}}</th>
                <th v-show="true">{{"Action" | translate}}</th>
                <th v-show="true">{{"Parameter" | translate}}</th>
                <th v-show="true">{{"Question" | translate}}</th>
                <th v-show="showAllColumns">{{"Survey ID" | translate}}</th>
                <th v-show="showAllColumns">{{"Question ID" | translate}}</th>
                <th v-show="showAllColumns">{{"Subquestion ID" | translate}}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="parameterRow in parameterRows"
                v-bind:key="parameterRow.id"
                :data-rowdata="JSON.stringify(parameterRow)"
              >
                <td v-show="showAllColumns">{{parameterRow.id}}</td>
                <td v-show="true">
                  <div>
                    <button class="btn btn-sm btn-default" @click.prevent="editRow(parameterRow)">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" @click.prevent="deleteRow(parameterRow)">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
                <td v-show="true">{{parameterRow.parameter }}</td>
                <td
                  v-show="true"
                  v-html="parameterRow.targetQuestionText+(parameterRow.sqid != '' ? ' => '+ parameterRow.targetSubQuestionText : '')"
                ></td>
                <td v-show="showAllColumns">{{parameterRow.sid }}</td>
                <td v-show="showAllColumns">{{parameterRow.qid }}</td>
                <td v-show="showAllColumns">{{parameterRow.sqid }}</td>
              </tr>
            </tbody>
          </table>
          <input type="hidden" id="allurlparams" name="allurlparams" :value="combinedValues" />
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 text-right">
          <button
            @click.prevent="addNewParam($event)"
            class="btn btn-primary"
          >{{"Add parameter" |translate}}</button>
        </div>
      </div>

      <div
        class="modal fade"
        :class="{ in: modalShown }"
        id="lspanelintegration-parameterPopup"
        role="dialog"
      >
        <div class="modal-dialog" role="document">
          <parameterpopup
            :is-new="isNew"
            v-on:updateparam="paramUpdated"
            v-on:canceledit="toggleModal"
          ></parameterpopup>
        </div>
      </div>

      <div class="modal fade" id="lspanelintegration-deletePopup" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-body">{{"Are you sure you want to delete this URL parameter?" | translate}}</div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-default"
                @click.prevent="cancelDelete"
              >{{"Cancel" | translate}}</button>
              <button
                type="button"
                class="btn btn-primary"
                @click.prevent="confirmDelete"
              >{{"Yes, delete" | translate}}</button>
            </div>
          </div>
        </div>
      </div>
    </template>
    <loader-widget v-if="loading" id="panelintegrationloader" />
  </div>
</template>
