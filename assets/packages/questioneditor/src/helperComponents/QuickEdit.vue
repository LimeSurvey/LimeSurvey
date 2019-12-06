
<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="pagetitle h3">{{'Quick edit' | translate}}</div>
            <div class="">
                <div class="ls-flex-row align-content-space-between wrap">
                    <div class="col-5">
                        <div class="ls-flex-row">
                            <label class="ls-flex col-6" :for="type+'--Select-Delimiter'">{{"Select delimiter" | translate}}</label> 
                            <select class="form-control ls-flex" :id="type+'--Select-Delimiter'" v-model="delimiter">
                                <option value=";">
                                    {{'Semicolon' | translate}} (;)
                                </option>
                                <option value=",">
                                    {{'Comma' | translate}} (,)
                                </option>
                                <option :value='"\t"'>
                                    {{'Tab' | translate}} (\t)
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-5 text-right">
                        <label :for="type+'--Toggle-Multilingual'">{{ 'Multilingual entry' | translate }} </label> 
                        <input :id="type+'--Toggle-Multilingual'" type="checkbox" v-model="multilanguage" >
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body ls-flex-column grow-1 fill">
            <div 
                class="ls-flex-column ls-space margin top-5 bottom-5"
                :class="'scoped-fix-height-1-' + scales.length"
                v-for="scale in scales"
                :key="scale"
            >
                <div class="ls-flex-row">
                    <h3>{{'Scale'|translate}} {{scale}}</h3>
                </div>
                <div class="ls-flex-colum grow-1">
                    <textarea 
                        class="scoped-textarea-class" 
                        v-model="unparsed[scale]"
                        @keydown.tab.exact="addTabAtCursor"
                        @paste.prevent="onPaste($event, scale)" 
                        @blur="parseContent(scale)"
                    />
                </div>
                <div class="ls-flex-row bg-info">
                    <div class="text-left">
                        {{'New rows' | translate }}: <span class="badge">{{parsed.length}}</span>
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
                <div class="ls-flex-item text-right">
                    <button class="btn btn-danger ls-space margin right-5" @click="resetContent" type="button">{{'Reset' | translate}}</button>
                    <button class="btn btn-danger ls-space margin right-5" @click="close" type="button">{{'Cancel' | translate}}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import keys from 'lodash/keys';
import foreach from 'lodash/forEach';
import slice from 'lodash/slice';
import debounce from 'lodash/debounce';
import he from 'he';

export default {
    name: 'quickedit',
    props: {
        current: {type: [Array,Object], required: true},
        type: {type: String, required: true},
        typedef: {type: String, required: true},
        typekey: {type: String, required: true},
    },
    data() {
        return {
            unparsed: [''],
            parsed: [{}],
            delimiter: ";",
            multilanguage: false
        }
    },
    computed: {
        multiscale() {
            return this.current.length>1;
        },
        scales(){
            return keys(this.current);
        },
        baseNonNumericPart() {
            return this.type == 'answeroptions' 
                ? window.QuestionEditData.baseSQACode.subquestions 
                : window.QuestionEditData.baseSQACode.answeroptions
        },
    },
    watch: {
        delimiter(newDelimiter, oldDelimiter) {
            this.unparseContent();
        }
    },
    methods: {
        tabDecode(string) {
            return string.replace(/\\t/, /\t/);
        },
        parseContent(scale) {
            scale = scale || 0;
            const rows = this.unparsed[scale].split(/\r?\n/);
            const newBlockObject = {};
            this.$log.log({rows});

            rows.forEach((element,rowCount) => {
                const blocks = element.split(this.delimiter);
                let newBlock = {};
                this.$log.log({blocks});
                
                if(blocks.length == 1) {               
                    newBlock[this.$store.state.activeLanguage] = blocks[0];     
                    newBlockObject[this.baseNonNumericPart+String((rowCount)).padStart(2,'0')] = newBlock;
                    return;
                } 

                if(this.multilanguage === true) {
                    keys(this.$store.state.languages).forEach((lng,i) => {
                        if(blocks[i+1] != undefined) {
                            newBlock[lng] = blocks[i+1];
                            return;
                        }
                        newBlock[lng] = blocks[1];
                    });

                } else {
                    newBlock[this.$store.state.activeLanguage] = blocks[1];
                }

                newBlockObject[blocks[0]] = newBlock;
            });

            this.$set(this.parsed, scale, newBlockObject);
            this.$log.log({parsed: this.parsed});
        },
        onPaste($event, scale) {
            const field = $event.target;
            const startPos = field.selectionStart;
            const endPos = field.selectionEnd;
            const oClipboardData = ($event.clipboardData || window.clipboardData);
            let paste = oClipboardData.getData('text') //Get the text representation of the clipboard
            
            const oldValue =  $event.target.value;
            const newValue = `${oldValue.substring(0,startPos)}${paste}${oldValue.substring(endPos, oldValue.length)}`;
            $event.target.value = newValue;
            this.$set(this.unparsed, scale, $event.target.value);
            this.delimiter = this.parseForMostProbableDelimiter(newValue);
            this.parseContent(scale);
        },
        addTabAtCursor($event) {
            const field = $event.target;
            const start = String($event.target.value).substring(0,field.selectionStart);
            const end = String($event.target.value).substring(field.selectionEnd,$event.target.value.length);
            $event.target.value = start + "\t" + end;
            this.$set(this.unparsed, scale, $event.target.value);
        },
        unparseContent(delimiter=null) {
            delimiter = delimiter || this.delimiter;
            foreach(this.parsed, (scaleArray, scale) => {
                this.$set(this.unparsed, scale, '');
                let rows = [];
                foreach(scaleArray, (rowContent, key) => {
                    let row = key+''+this.delimiter;
                    row+=(LS.ld.values(rowContent).join(this.delimiter)).replace(/\\t/,/\t/);
                    rows.push(row);
                });
                this.$set(this.unparsed, scale, rows.join("\n"));
            });
        },
        resetContent() {
            foreach(this.current, (scaleArray, scale) => {
                this.$set(this.unparsed, scale, '');
                let rows = [];
                scaleArray.forEach(rowObject => {
                    let row = rowObject[this.typekey]+''+this.delimiter;
                    if(this.multilanguage === true) {
                        keys(this.$stores.state.languages).forEach((lng,i) => {
                            row += rowObject[lng][this.typedef]+''+this.delimiter;
                        });
                        row = row.substring(0,row.length-1);
                    } else {
                        row += rowObject[this.$store.state.activeLanguage][this.typedef];
                    }
                    rows.push(row);
                });
                this.$set(this.unparsed, scale, rows.join("\n"));
                this.parseContent(scale);
            });
        },
        close() {
            this.$emit('close');
        },
        replaceCurrent() {
            this.$emit('modalEvent', {target: this.type, method: 'replaceFromQuickAdd', content: this.parsed});
            this.$emit('close');
        },
        addToCurrent() {
            this.$emit('modalEvent', {target: this.type, method: 'addToFromQuickAdd', content: this.parsed});
            this.$emit('close');
        },
        parseForMostProbableDelimiter(pasteText) {
            const firstLine = pasteText.split(/\r?\n/);
            if(firstLine.length < 2) {
                return this.delimiter;
            }
            const delimiter = [
                [';', firstLine.shift().split(';').length],
                [',', firstLine.shift().split(',').length],
                ['\t', firstLine.shift().split(/\t|\\t/).length]
            ]
            delimiter.sort((a,b) => {
                return b[1]-a[1];
            });
            return (delimiter[0][0]);
            
        }
    },
    mounted(){
        this.resetContent();
        this.scales.forEach(scale => {
            this.parseContent(scale);
        });
    }
}
</script>

<style lang="scss" scoped>
.scoped-textarea-class {
    height: 100%;
    width: 100%;
}
.scoped-fix-height-1-1 {
    margin-top: 1%;
    height: 99%;
}
.scoped-fix-height-1-2 {
    height: 48%;
    margin-top: 1%;
}
</style>
