<template>
    <div class="panel panel-default scope-attach-padding ls-flex ls-flex-column fill" :id="'question_type_selector_' + id">
      <div class="panel-heading">
        <button type="button" @click="closeButton" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="panel-title">{{title}}</h4>
      </div>
      <div class="panel-body scoped-overflow-scroll">
        <div class="container-fluid">
          <div class="row">
            <div class="col-xs-4 ls-ba">
              <div class="panel-group" :id="'accordion_question_type_selector_' + id" role="tablist" aria-multiselectable="true">
                  <div v-for="(groupStructureArray, groupTitle) in structureArray" :key="groupTitle" class="panel panel-default">
                    <div class="panel-heading" role="tab" :id="'heading_'+groupTitle">
                      <h4 class="panel-title">
                        <a 
                            role="button" 
                            data-toggle="collapse" 
                            :href="'#collapsible_'+groupTitle"
                            :data-parent="'#accordion_question_type_selector_' + id" 
                            @click="groupStructureArray.uncollapsed = !groupStructureArray.uncollapsed"
                        >
                            {{groupStructureArray.questionGroupName}}
                        </a>
                      </h4>
                    </div>
                    <div 
                        :id="'collapsible_'+groupTitle" 
                        class="panel-collapse" 
                        :class="groupStructureArray.collapsed ? 'collapse' : ''" 
                        role="tabpanel" 
                        :aria-labelledby="groupTitle"
                    >
                      <div class="panel-body ls-space padding all-0">
                        <div class="list-group ls-space margin all-0">
                            <a
                                v-for="questionType in groupStructureArray.questionTypes"
                                :key="questionType.type+questionType.name"
                                href="#"
                                class="list-group-item"
                                :class="isSelected(questionType)"
                                :data-selector="questionType.class || questionType.type"
                                :data-key="questionType.type"
                                v-bind="questionType.extraAttributes"
                                @click="selectQuestionType(questionType)"
                            >
                              {{questionType.title}}
                              <em v-if="debug" class="small">{{"Type:" | translate}} {{questionType.type}}</em>
                            </a>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
            </div>
            <div class="col-xs-8">
                <div class="container-center">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3>
                            <b>{{"Question type preview"|translate}}</b><br/>
                            <p id="selector__question_type_selector-currentSelected" v-if="currentlySelectedType"> {{currentlySelectedType.title}} </p>
                            <p id="selector__question_type_selector-currentSelected" v-else></p>
                            </h3>
                        </div>
                    </div>
                    <div class="row" id="selector__question_type_selector-detailPage">
                        <div class="col-sm-12" v-if="currentlySelectedType" v-html="currentlySelectedType.detailpage" />
                        <div class="col-sm-12" v-else />
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel-footer text-right">
        <button type="button" @click="closeButton" class="btn btn-default" data-dismiss="modal">
           {{"Close"|translate}}
        </button>&nbsp;
        <button type="button" @click="selectButton" id="selector__select-this-question_type_selector" class="btn btn-primary">
          {{"Select"|translate}}
        </button>
      </div>
    </div>
</template>

<script>
import foreach from 'lodash/forEach';
import find from 'lodash/find';

export default {
    name: "QuestionTypeSelector",
    props: {
        id: {type: String, default: () => ('QS_' + Math.floor(Math.random()*100000)) },
        title: {type: String, default:''},
        debug: {type: Boolean, default: false}
    },
    data() {
        return {
            currentlySelectedTypeObject : {},
        }
    },
    computed: {
        structureArray() {
            return window.aStructureArray;
        },
        currentlySelectedType: {
            get() { 
                return this.currentlySelectedTypeObject
            },
            set(newValue) {
                this.currentlySelectedTypeObject = newValue;
            },
        },
        currentQuestionType() {
            return this.$store.state.currentQuestion.type;
        },
        currentQuestionTemplate() {
            return this.$store.state.currentQuestionGeneralSettings.question_template.formElementValue || 'core';
        }
    },
    methods: {
        selectQuestionType(newQuestionType) {
            this.currentlySelectedType = newQuestionType;
        },
        beforeOpen() {
            
        },
        beforeClose() {
            if(this.currentlySelectedTypeObject != null) {
                this.$store.dispatch('questionTypeChange', this.currentlySelectedTypeObject);
            }
        },
        isSelected(questionType) {
            if (this.currentlySelectedType) {
                return this.currentlySelectedType.type == questionType.type 
                    && this.currentlySelectedType.name == questionType.name 
                        ? 'selected' 
                        : '';
            } else {
                return '';
            }
        },
        closeButton() {
            this.$emit('close');
        },
        selectButton() {
            this.beforeClose();
            this.$emit('close');
        },
    },
    created() {
        let currentTypeObject = null;
            
        foreach(this.structureArray, (groupArray, key) => {
            
            let objectInThisGroup = find(groupArray.questionTypes, (questionType) => {
                return (questionType.type === this.currentQuestionType)
                && (questionType.name === this.currentQuestionTemplate)
            });
            this.$log.log("Found in group ",groupArray.questionGroupName, "->", objectInThisGroup);
            
            this.structureArray[key].collapsed = (objectInThisGroup == undefined);
            if(objectInThisGroup != undefined) {
                currentTypeObject = objectInThisGroup;
            }
        });
        this.currentlySelectedTypeObject = currentTypeObject;
    }
}
</script>

<style lang="scss" scoped>
    .selected {
        background-color: var(--LS-admintheme-hintedbasecolor);
        &:after {
            position: absolute;
            right: 10px;
            content: "\F054";
            font: normal normal normal 18px/1 FontAwesome;
            text-rendering: auto;
        }
    }
    .scope-attack-padding {
        padding: 1%;
        margin: 2px;
    }
    .scoped-overflow-scroll {
        height:100%;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
