import max from 'lodash/max';
import merge from 'lodash/merge';
import remove from 'lodash/remove';

export default {
    methods: {
        getNewTitleFromCurrent(scaleId) {
            let nonNumericPart = this.baseNonNumericPart;
            if(this.currentDataSet[scaleId].length > 0) {
                nonNumericPart = (this.currentDataSet[scaleId][0].title || this.currentDataSet[scaleId][0].code).replace(/[0-9]/g,'');
            }
            let numericPart = this.currentDataSet[scaleId].reduce((prev, oDataSet) => {
                return max([prev, parseInt((oDataSet.title || oDataSet.code  ).replace(/[^0-9]/g,''))]);
            }, 0) + 1 ;
            this.$log.log('NewTitle', {nonNumericPart, numericPart});
            return nonNumericPart+''+numericPart;
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

        },
        addDataSet(scaleId) {
            let tmpArray = merge([], this.currentDataSet);
            tmpArray[scaleId] = tmpArray[scaleId] || new Array();
            tmpArray[scaleId].push(this.getTemplate(scaleId));
            this.currentDataSet = tmpArray;
        },
        openLabelSets() {},
        openQuickAdd() {},
        saveAsLabelSet() {},
        switchinput(newTarget, $event = null) {
            if(newTarget == false) {
                this.$log.log($event);
                return;
            }
            $('#'+newTarget).focus();
        }
    }
}