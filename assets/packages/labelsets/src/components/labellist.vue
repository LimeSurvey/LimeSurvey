<script>
import max from 'lodash/max';
import merge from 'lodash/merge';
import remove from 'lodash/remove';
import isEmpty from 'lodash/isEmpty';
import foreach from 'lodash/forEach';
import map from 'lodash/map';
import sortBy from 'lodash/sortBy';

import abstractLabelsets from '../mixins/abstractLabelsets.js';
import eventChild from '../mixins/eventChild.js';

export default {
    name: 'labellist',
    mixins: [abstractLabelsets, eventChild],
    data(){
        return {
            uniqueSelector: 'id',
            baseNonNumericPart : "L",
            type: 'labellist',
            typeDefininition: 'title',
            typeDefininitionKey: 'code',
            labelDragging: false,
            draggedLabel: null
        };
    },
    computed: {
        currentDataSet: {
            get() {
                return sortBy(this.$store.state.labels, ['label','sortorder']);
            },
            set(newValue) {
                this.$store.commit('setLabels', newValue);
            }
        },
    },
    methods: {
        getTemplate(){
            let randomId = this.getRandomId();

            let labelTemplate = {
                id: randomId,
                lid: window.LabelSetData.lid,
                code: '',
                sortorder: 0,
                assessment_value: 0
                };

            foreach(this.$store.state.languages, (lng, lngKey) => {
                labelTemplate[lngKey] = {
                     id: null,
                     label_id: randomId,
                     title: "",
                     language: lngKey
                    }
            });

            return labelTemplate;
        },
        getLabelI10NForCurrentLanguage(labelObject) {
            try {
                return labelObject[this.$store.state.activeLanguage].title;
            } catch(e){
                this.$log.error('PROBLEM GETTING LANGUAGE', labelObject);
            }
            return '';
        },
        setLabelI10NForCurrentLanguage(labelObject, $event) {
            labelObject[this.$store.state.activeLanguage] = labelObject[this.$store.state.activeLanguage] || {};
            this.$set( labelObject[this.$store.state.activeLanguage], 'title', $event.srcElement.value);
        },
        //dragevents questions
        startDraggingLabel($event, labelObject) {
            this.$log.log("Dragging started", labelObject);
            $event.dataTransfer.setData('application/node', this);
            this.labelDragging = true;
            this.draggedLabel = labelObject;
        },
        endDraggingLabel($event, labelObject) {
            if (this.labelDragging) {
                this.labelDragging = false;
                this.draggedLabel = null;
                this.reorderLabels();
            }
        },
        dragoverLabel($event, labelObject) {
            if (this.labelDragging) {
                let orderSwap = labelObject.sortorder;
                labelObject.sortorder = this.draggedLabel.sortorder;
                this.draggedLabel.sortorder = orderSwap;
            }
        },
        reorderLabels(){
            let labels = [];
            let last = 0;
            foreach(this.currentDataSet, (label, i) => {
                label.sortorder = (i+1)
                labels.push(label);
            });
            this.$set(this.currentDataSet, labels);
        },
    },
    mounted() {
        if(isEmpty(this.$store.state.labels)){
            this.$store.state.labels = {"0": [this.getTemplate()]};
        };
        this.reorderLabels();
    }
}
</script>

<template>
    <div class="col-sm-12">
        <div class="container-fluid scoped-main-labelsets-container">
            <div class="row" v-show="!readonly">
                <div class="col-sm-8">
                    <button class="btn btn-default col-3" @click.prevent="openQuickAdd">{{ "Quick add" | translate }}</button>
                </div>
                <div class="col-sm-4 text-right">
                    <button class="btn btn-danger col-5" @click.prevent="resetSubquestions">{{ "Reset" | translate }}</button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <hr />
                </div>
            </div>
            <div class="row list-group scoped-label-row-container">
                <div 
                    class="list-group-item scoped-label-block"
                    v-for="label in currentDataSet"
                    :key="label.id"
                    @dragenter="dragoverLabel($event, label)"
                    :class="(labelDragging ? 'movement-active'+ ((label.id == draggedLabel.id) ? ' in-movement' : '') : '')"
                >
                    <div class="scoped-move-block" v-show="!readonly">
                        <i 
                            class="fa fa-bars" 
                            :draggable="true"
                            @dragstart="startDraggingLabel($event, label)"
                            @dragend="endDraggingLabel($event, label)" 
                        ></i>
                    </div>
                    <div class="scoped-code-block   ">
                        <input
                            type='text'
                            class="form-control"
                            maxlength='5'
                            size='5'
                            :name="'code_'+label.sortorder" 
                            :readonly="readonly"
                            v-model="label.code"
                            @keyup.enter.prevent='switchinput("answer_"+$store.state.activeLanguage+"_"+label.id)'
                        />
                    </div>
                    <div class="scoped-content-block   ">
                        <input
                            type='text'
                            size='20'
                            class='answer form-control input'
                            :id='"answer_"+$store.state.activeLanguage+"_"+label.id'
                            :name='"answer_"+$store.state.activeLanguage+"_"+label.id'
                            :placeholder='translate("Some example label")'
                            :value="getLabelI10NForCurrentLanguage(label)"
                            :readonly="readonly"
                            @change="setLabelI10NForCurrentLanguage(label,$event, arguments)"
                            @keyup.enter.prevent='switchinput("relevance_"+label.id)'
                        />
                    </div>
                    <div class="scoped-actions-block" v-show="!readonly">
                        <button class="btn btn-default btn-small" @click.prevent="deleteThisDataSet(label)">
                            <i class="fa fa-trash text-danger"></i>
                            {{ "Delete" | translate }}
                        </button>
                        <button class="btn btn-default btn-small" @click.prevent="openPopUpEditor(label)">
                            <i class="fa fa-edit"></i>
                            {{ "Open editor" | translate }}
                        </button>
                        <button class="btn btn-default btn-small" @click.prevent="duplicateThisDataSet(label)">
                            <i class="fa fa-copy"></i>
                            {{ "Duplicate" | translate }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="row" v-show="!readonly">
                <div class="col-sm-12 text-right">
                    <button @click.prevent="addDataSet" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        {{ "Add Label" | translate}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .scoped-spacer{
        content: ' ';
        display: inline-block;
    }
    .scoped-main-label-container {
        margin: 1rem 0.2rem;
        padding-top: 0.2rem;
        min-height: 25vh;
    }
    .scoped-label-block{
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        justify-content: flex-start;
        &>div {
            flex-basis: 10rem;
            padding: 1px 2px;
            transition: all 1s ease-in-out;
            white-space: nowrap;
        }
    }
    
    .scoped-move-block {
        text-align: center;
        width: 64px;
        &>i {
            font-size: 28px;
            line-height: 32px;
            &:after{
                content: ' |';
                font-size: 24px;
                vertical-align: text-bottom;
            }
        }
    }
    .scoped-content-block {
        flex-grow: 8;
    }
    .scoped-relevance-block {
        flex-grow: 1;
        max-width: 10rem;
    }
    .scoped-actions-block {
        flex-grow: 2;
    }
    
    .movement-active {
        background-color: hsla(0,0,90,0.8);
        &.in-movement {
            background-color: hsla(0,0,60,1);
        }
    }
</style>
