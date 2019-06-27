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
        getNewTitleFromCurrent() {
            let nonNumericPart = this.baseNonNumericPart;
            if(this.getLength(this.currentDataSet) > 0) {
                nonNumericPart = (this.currentDataSet[0].title || this.currentDataSet[0].code).replace(/[0-9]/g,'');
            }
            let numericPart = reduce(this.currentDataSet,(prev, oDataSet) => {
                return max([prev, parseInt((oDataSet.title || oDataSet.code  ).replace(/[^0-9]/g,''))]);
            }, 0) + 1 ;
            this.$log.log('NewTitle', {nonNumericPart, numericPart});
            return nonNumericPart+''+numericPart;
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

        },
        addDataSet() {
            let tmpArray = merge([], this.currentDataSet);
            tmpArray.push(this.getTemplate());
            this.currentDataSet = tmpArray;
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
            this.currentDataSet = tempObject;
        },
        addToFromQuickAdd(contents){
            this.$log.log('addToFromQuickAdd triggered on: '+this.$options.name, contents);
            let tempObject = merge([], this.currentDataSet);
            foreach(contents, (lngSet, key) => {
                const newDataSetBlock = this.getTemplate();
                newDataSetBlock[this.typeDefininitionKey] = key;
                foreach(lngSet, (dataSetValue, lngKey) => { 
                    newDataSetBlock[lngKey][this.typeDefininition] = dataSetValue; 
                });
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
        }
    }
}