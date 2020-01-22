import max from 'lodash/max';
import keys from 'lodash/keys';
import merge from 'lodash/merge';
import remove from 'lodash/remove';
import reduce from 'lodash/reduce';
import foreach from 'lodash/forEach';
import findIndex from 'lodash/findIndex';
import isArrayLike from 'lodash/isArrayLike';
import isObjectLike from 'lodash/isObjectLike';

import QuickEdit from '../helperComponents/QuickEdit.vue';
import SimplePopUpEditor from '../helperComponents/SimplePopUpEditor.vue';

export default {
    components: {QuickEdit, SimplePopUpEditor},
    props: {
        readonly : {type: Boolean, default: false}
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
        getNewTitleFromCurrent(relativeObject = null) {
            let nonNumericPart = this.baseNonNumericPart;
            relativeObject = relativeObject || this.currentDataSet;
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
        deleteThisDataSet(oDataSet) {
            let tmpArray = merge([], this.currentDataSet);
            tmpArray = remove(tmpArray, (oDataSetIterator) => oDataSetIterator[this.uniqueSelector] != oDataSet[this.uniqueSelector]);
            this.currentDataSet = tmpArray;
        },
        duplicateThisDataSet(oDataSet) {
            let tmpArray = merge([], this.currentDataSet);
            let newDataSet = merge({}, oDataSet);
            newDataSet[this.uniqueSelector] = this.getRandomId();
            newDataSet[this.typeDefininitionKey] = this.getNewTitleFromCurrent();
            //newDataSet[this.orderAttribute];
            tmpArray.push(newDataSet);
            
            this.currentDataSet = this.reorder(tmpArray);
        },
        addDataSet() {
            let tmpArray = merge([], this.currentDataSet);
            const newLabel = this.getTemplate();
            newLabel.sortorder = this.currentDataSet.length+1;
            tmpArray.push(newLabel);
            this.currentDataSet = this.reorder(tmpArray);
        },
        openLabelSets() {},
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
        openPopUpEditor(dataSetObject) {
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
        saveAsLabelSet() {},
        switchinput(newTarget, $event = null) {
            if(newTarget == false) {
                this.$log.log($event);
                return;
            }
            $('#'+newTarget).focus();
        }, 
        replaceFromQuickAdd(contents){
            this.$log.log('replaceFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = [];
            foreach(contents, (lngSet, key) => {
                const newDataSetBlock = this.getTemplate();
                newDataSetBlock[this.typeDefininitionKey] = key;
                foreach(lngSet, (dataSetValue, lngKey) => { 
                    newDataSetBlock[lngKey][this.typeDefininition] = dataSetValue; 
                });
                tempObject.push(newDataSetBlock);
            });
            this.reorder(tempObject);
            this.currentDataSet = tempObject;
        },
        addToFromQuickAdd(contents){
            this.$log.log('addToFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge([], this.currentDataSet);
            let orderCount = tempObject.length;
            foreach(contents, (lngSet, key) => {
                const newDataSetBlock = this.getTemplate();
                if(tempObject.indexOf(key) !== -1) {
                    newDataSetBlock[this.typeDefininitionKey] = this.getNewTitleFromCurrent(tempObject);
                } else {
                    newDataSetBlock[this.typeDefininitionKey] = key;
                }
                foreach(lngSet, (dataSetValue, lngKey) => { 
                    newDataSetBlock[lngKey][this.typeDefininition] = dataSetValue; 
                });
                newDataSetBlock[this.orderAttribute] = ++orderCount;
                tempObject.push(newDataSetBlock);
            });
            this.currentDataSet = tempObject;
        },
        editFromSimplePopupEditor(contents){
            this.$log.log('Event editFromSimplePopupEditor', contents);
            const tempFullObject = merge([], this.currentDataSet);
            let identifier = findIndex(tempFullObject, (dataSetObject,i) => 
                dataSetObject[this.typeDefininitionKey] === contents[this.typeDefininitionKey] 
            );
            tempFullObject[identifier] = contents;
            this.$log.log('Event editFromSimplePopupEditor result', {identifier, tempFullObject});
            this.currentDataSet = tempFullObject;
        },
        reorder(dataSet) {
            dataSet.sort((a,b) => (
                a[this.orderAttribute] < b[this.orderAttribute] 
                ? -1 
                : (a[this.orderAttribute] > b[this.orderAttribute] 
                    ? 1 
                    : 0
                )));
            let currentOrder = 1;
            let maxOrder = dataSet.length;
            for(;currentOrder<=maxOrder ; currentOrder++) {
                dataSet[(currentOrder-1)].sortorder = currentOrder;
            }

            return dataSet;
        },
        preventDisallowedCursor($event) {
            $event.dataTransfer.dropEffect = "move";
            return;
        }
    }
}