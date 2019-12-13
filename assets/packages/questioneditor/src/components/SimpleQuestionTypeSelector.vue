<template>
    <select 
        class="form-control" 
        :id="'simple_question_type_selector_' + id"
        v-model="currentlySelectedTypeObject"
    >
    <optgroup 
        v-for="(groupStructureArray, groupTitle) in structureArray" 
        :key="groupTitle" 
        :label="groupStructureArray.questionGroupName"
    >
        <option 
            v-for="questionType in groupStructureArray.questionTypes"
            :key="questionType.type+questionType.name"
            :value="questionType"
        >
            {{questionType.title}}&nbsp;
            <em v-if="debug" class="small">{{"Type:" | translate}} {{questionType.type}}</em>
        </option>
    </optgroup>
    </select>
</template>

<script>
import foreach from 'lodash/forEach';
import find from 'lodash/find';

export default {
    name: "SimpleQuestionTypeSelector",
    props: {
        id: {type: String, default: () => ('SQS_' + Math.floor(Math.random()*100000)) },
        debug: {type: Boolean, default: false}
    },
    data() {
        return {
            selectedTypeObject : {},
        }
    },
    computed: {
        currentlySelectedTypeObject: {
            get() {
                return this.selectedTypeObject;
            },
            set(newValue) {
                this.selectedTypeObject = newValue;
                this.$store.dispatch('questionTypeChange', newValue);
            }
        },
        structureArray() {
            return window.aStructureArray;
        },
        currentQuestionType() {
            return this.$store.state.currentQuestion.type;
        },
        currentQuestionTemplate() {
            return this.$store.state.currentQuestionGeneralSettings.question_template.formElementValue;
        }
    },
    methods: {
        selectQuestionType(newQuestionType) {
            this.currentlySelectedType = newQuestionType;
        },
        getTypeObject() {
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
            return currentTypeObject;
        }
    },
    created() {
        this.selectedTypeObject = this.getTypeObject();
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