<script>
import empty from 'lodash/isEmpty'
import filter from 'lodash/filter'
export default {
    name: 'lsautocomplete',
    props: {
        dataList: {type: Array, required: true},
        searchableKeys: {type: Array, default: ['name','title']},
        showKey: {type: String, default: 'name'},
        valueKey: {type: String|Boolean, default: false},
        matchType: {type: String, default: 'fuzzy'},
        itemClass: {type: String, default: ''},
        inputClass:  {type: String, default: ''},
        value: {default: null},
    },
    data(){
        return {
            input: '',
            showDropdown: false,
            selectedIndex: 0,
        }
    },
    computed: {
        filteredList() {
            return filter(this.dataList, (listItem) => {
                if(empty(this.input)) { return true; }
                return this.searchableKeys.reduce((coll, key) => {
                    if(listItem[key] == undefined) { return coll; }
                    return (coll || this.match(listItem[key]));
                }, false);
            });
        },
        currentItemsHeight() {
            (this.filteredList.length*28)+'px';
        }
    },
    methods: {
        processKeyPress($event) {
            this.showDropdown=true;
            if($event.key.toLowerCase() == 'arrowdown') {
                if(this.selectedIndex < this.filteredList.length) {
                    this.selectedIndex = this.selectedIndex+1
                }
            }
            if($event.key.toLowerCase() == 'arrowup') {
                if(this.selectedIndex > 0) {
                    this.selectedIndex = this.selectedIndex-1
                }
            }
            if($event.key.toLowerCase() == 'enter') {
                this.itemSelected(this.filteredList[this.selectedIndex]);
            }

        },
        itemSelected(item) {
            const result = this.valueKey===false ? item : item[this.valueKey];
            this.input = item[this.showKey];
            this.$emit('input', result);
            this.showDropdown=false;
        },
        match(comparable) {
            this.$log.log(`Matching ${comparable} to ${this.input} with ${this.matchType}-Method`);
            let result = true;
            switch(this.matchType) {
                case 'fuzzy': return this._fuzzy(comparable);
                case 'exact': return this._exact(comparable);
                case 'start': return this._start(comparable);
            }
        },
        _fuzzy(comparable) {
            const regExp = new RegExp(".*"+this.input+".*",'i');
            return regExp.test(comparable);
        },
        _exact(comparable) {
            const regExp = new RegExp(this.input,'i');
            return regExp.test(comparable);
        },
        _start(comparable) {
            const regExp = new RegExp(this.input+".*",'i');
            return regExp.test(comparable);
        },
        lazy(comparable) {
            return (comparable.toLowerCase().indexOf(this.input.toLowerCase()) > -1);
        },
    },
    mounted() {
        if(this.value != '') {
            this.input = this.value;
        }
    }
}
</script>

<template>
    <div class="scoped-autocomplete-input-container" :class="itemClass">
        <input type="text" class="form-control" :class="inputClass" v-model="input" @keydown="processKeyPress" @focus="showDropdown=true"/>
        <ul class="scoped-autocomplete-list" v-show="showDropdown" :style="{height: currentItemsHeight}">
            <li 
                v-for="(item,i) in filteredList" 
                :key="'autocomplete-'+i"
                @click="itemSelected(item)"
                @mouseover="selectedIndex=i"
                class="scoped-autocomplete-list-item"
                :class="selectedIndex == i ? 'selected':''"
            >
                {{item[showKey]}}
            </li>
        </ul>
    </div>
</template>

<style lang="scss" scoped>
    .scoped-autocomplete-input-container {
        position: relative
    }
    .scoped-autocomplete-list {
        position: absolute;
        top: 34px;
        left:0;
        width: 100%;
        padding:0;
        margin: 0px;
        overflow: auto;
        border: 1px solid #212121;
        background-color: #fff;
        list-style: none;
    }
    .scoped-autocomplete-list-item {
        &:hover,&.selected { background-color: #dedede; }
        padding: 4px 6px;
        border-bottom: 1px solid #929292;
        &:last-of-type {
            border-bottom: none;
        }
    }
</style>
