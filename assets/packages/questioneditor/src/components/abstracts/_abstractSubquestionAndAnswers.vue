<script>
import max from 'lodash/max';
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import uniqBy from 'lodash/uniqBy';
import remove from 'lodash/remove';
import reduce from 'lodash/reduce';
import foreach from 'lodash/forEach';
import sortBy from 'lodash/sortBy';
import findIndex from 'lodash/findIndex';
import isArrayLike from 'lodash/isArrayLike';
import isObjectLike from 'lodash/isObjectLike';

import QuickEdit from '../../helperComponents/QuickEdit.vue';
import LabelSets from '../../helperComponents/LabelSets.vue';
import SaveLabelSet from '../../helperComponents/SaveLabelSet.vue';
import SimplePopUpEditor from '../../helperComponents/SimplePopUpEditor.vue';

import EventChild from '../../mixins/eventChild.js';

export default {
    components: {QuickEdit, SimplePopUpEditor, LabelSets},
    mixins: [EventChild],
    props: {
        readonly : {type: Boolean, default: false}
    },
    data(){
        return {
            uniqueSelector: 'id',
            type: 'xxx',
            orderAttribute: 'sortorder',
            typeDefininition: 'xx',
            typeDefininitionKey: 'xx',
        };
    },
    computed: {
        surveyActive() {
            return this.$store.getters.surveyObject.active =='Y'
        }
    },
    methods: {
        getLength(arrayOrObject) {
            if(isArrayLike(arrayOrObject)) {
                return arrayOrObject.length;
            }
            if(isObjectLike(arrayOrObject)) {
                return keys(arrayOrObject).length;
            }
            return 0;
        },
        getNewTitleFromCurrent(scaleId, relativeObject = null) {
            let nonNumericPart = this.baseNonNumericPart;
            relativeObject = relativeObject || this.currentDataSet[scaleId];
            if(this.getLength(relativeObject) > 0) {
                nonNumericPart = (relativeObject[0][this.typeDefininitionKey]).replace(/[0-9]/g,'');
            }

            let numericPart = reduce(relativeObject, (prev, oDataSet) => {
                return max([prev, parseInt((oDataSet[this.typeDefininitionKey]).replace(/[^0-9]/g,''))]);
            }, 0);

            numericPart = numericPart+1;

            this.$log.log('relativeObject', relativeObject);
            this.$log.log('NewTitle', {nonNumericPart, numericPart});

            return nonNumericPart+String(numericPart).padStart(2,'0');
        },
        getRandomId(){
            return 'random'+Math.random().toString(36).substr(2, 7);
        },
        deleteThisDataSet(oDataSet, scaleId) {
            let tmpArray = merge([], this.currentDataSet);
            tmpArray[scaleId] = remove(tmpArray[scaleId], (oDataSetIterator) => oDataSetIterator[this.uniqueSelector] != oDataSet[this.uniqueSelector]);
            this.currentDataSet = tmpArray;
        },
        duplicateThisDataSet(oDataSet, scaleId) {
            let tmpArray = merge([], this.currentDataSet);
            let newDataSet = merge({}, oDataSet);
            newDataSet[this.uniqueSelector] = this.getRandomId();
            newDataSet[this.typeDefininitionKey] = this.getNewTitleFromCurrent(scaleId);
            //newDataSet[this.orderAttribute];
            tmpArray[scaleId].push(newDataSet);
            
            this.currentDataSet = this.reorder(tmpArray);
        },
        addDataSet(scaleId) {
            let tmpArray = merge([], this.currentDataSet);
            tmpArray[scaleId] = tmpArray[scaleId] || new Array();
            const newDataSet = this.getTemplate(scaleId);
            newDataSet[this.orderAttribute] = (tmpArray[scaleId].length+1);
            tmpArray[scaleId].push(newDataSet);
            this.currentDataSet = this.reorder(tmpArray);
        },
        openLabelSets(scaleId) {
            this.$modal.show(LabelSets, {
                scaleId,
                template: this.getTemplate(scaleId),
                type : this.type,
                typedef : this.typeDefininition,
                typekey : this.typeDefininitionKey
              }, {
                width: '75%',
                height: '75%',
                scrollable: true,
                resizable: true
              }
            )
        },
        openQuickAdd() {
            this.$modal.show(QuickEdit, {
                current : this.currentDataSet,
                type : this.type,
                typedef : this.typeDefininition,
                typekey : this.typeDefininitionKey
              }, {
                width: '75%',
                height: '75%',
                scrollable: true,
                resizable: true
              }
            )
        },
        openPopUpEditor(dataSetObject, scaleId) {
            this.$modal.show(
                SimplePopUpEditor, 
                { 
                    target: this.type,
                    dataSetObject: dataSetObject,
                    typeDef: this.typeDefininition,
                    typeDefKey: this.typeDefininitionKey
                },
                {
                    width: '75%',
                    height: '75%',
                    scrollable: true,
                    resizable: true
                },
                {
                    'closed': (event, payload) => { 
                        this.$log.log('MODAL CLOSED', event, payload);
                        if(event.save == true) {
                            dataSetObject[this.$store.state.activeLanguage][this.typeDefininition] = event.value;
                        }
                    },
                    'change': (event, payload) => { 
                        this.$log.log('CHANGE IN MODAL', event, payload);
                        if(event.save == true) {
                            dataSetObject[this.$store.state.activeLanguage][this.typeDefininition] = event.value;
                        }
                    }
                }
            )
        },
        switchinput(newTarget, $event = null) {
            if(newTarget == false) {
                this.$log.log($event);
                return;
            }
            $('#'+newTarget).focus();
        }, 
        replaceFromQuickAdd(contents){
            this.$log.log('replaceFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge({}, this.currentDataSet);
            let orderCount = 0;
            foreach(contents, (scaleObject, scale) => {
                tempObject[scale] = [];
                foreach(scaleObject, (lngSet, key) => {
                    const newDataSetBlock = this.getTemplate(scale);
                    newDataSetBlock[this.typeDefininitionKey] = key;
                    foreach(lngSet, (dataSetValue, lngKey) => { 
                        newDataSetBlock[lngKey][this.typeDefininition] = dataSetValue; 
                    });
                    newDataSetBlock[this.orderAttribute] = ++orderCount;
                    tempObject[scale].push(newDataSetBlock);
                });
            });
            this.reorder(tempObject);
            this.currentDataSet = tempObject;
        },
        addToFromQuickAdd(contents){
            this.$log.log('addToFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge({}, this.currentDataSet);
            foreach(contents, (scaleObject, scale) => {
                let orderCount = scaleObject.length;
                const currentKeys = uniqBy(tempObject[scale], this.typeDefininitionKey);
                foreach(scaleObject, (lngSet, key) => {
                    const newDataSetBlock = this.getTemplate(scale);
                    if(currentKeys.indexOf(key) != -1) {
                        newDataSetBlock[this.typeDefininitionKey] = this.getNewTitleFromCurrent(scale, tempObject)
                    } else {
                        newDataSetBlock[this.typeDefininitionKey] = key;
                    }
                    foreach(lngSet, (dataSetValue, lngKey) => { 
                        newDataSetBlock[lngKey][this.typeDefininition] = dataSetValue; 
                    });
                    newDataSetBlock[this.orderAttribute] = ++orderCount;
                    tempObject[scale].push(newDataSetBlock);
                });
            });
            this.currentDataSet = tempObject;
        },
        replaceFromLabelSets(contents){
            this.$log.log('replaceFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge({}, this.currentDataSet);
            tempObject[contents.scaleId] = [];
            foreach(contents.data, (dataSet) => { 
                dataSet[this.uniqueSelector] = this.getRandomId();
                tempObject[contents.scaleId].push(dataSet) 
            });
            this.currentDataSet = tempObject;
        },
        addToFromLabelSets(contents){
            this.$log.log('addToFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge({}, this.currentDataSet);
            foreach(contents.data, (dataSet, i) => { 
                dataSet[this.uniqueSelector] = this.getRandomId();
                tempObject[contents.scaleId].push(dataSet) 
            })
            this.currentDataSet = tempObject;
        },
        saveAsLabelSet(scaleId) {
            const dataSet = merge({}, this.currentDataSet[scaleId]);
            this.$modal.show(
                SaveLabelSet, 
                { 
                    scaleId,
                    dataSet,
                    type: this.type,
                    typedef: this.typeDefininition,
                    typekey: this.typeDefininitionKey
                },
                {
                    width: '75%',
                }
            )
        },
        editFromSimplePopupEditor(contents){
            this.$log.log('Event editFromSimplePopupEditor', contents);
            const tempFullObject = merge({}, this.currentDataSet);
            let identifier = findIndex(tempFullObject[contents.scale_id], (dataSetObject,i) => 
            dataSetObject[this.typeDefininitionKey] === contents[this.typeDefininitionKey] 
            );
            tempFullObject[contents.scale_id][identifier] = contents;
            this.$log.log('Event editFromSimplePopupEditor result', {identifier, tempFullObject});
            this.currentDataSet = tempFullObject;
        },
        reorder(dataSet) {
            foreach(dataSet, (scaleArray, scaleId) => {
                scaleArray.sort((a,b) => (
                    a[this.orderAttribute] < b[this.orderAttribute] 
                    ? -1 
                    : (a[this.orderAttribute] > b[this.orderAttribute] 
                        ? 1 
                        : 0
                    )));
                let currentOrder = 1;
                let maxOrder = scaleArray.length;
                for(;currentOrder<=maxOrder ; currentOrder++) {
                    scaleArray[(currentOrder-1)][this.orderAttribute] = currentOrder;
                }
                dataSet[scaleId] = scaleArray;
            });

            return dataSet;
        },
        preventDisallowedCursor($event) {
            $event.dataTransfer.dropEffect = "move";
            return;
        }
    }
}
</script>