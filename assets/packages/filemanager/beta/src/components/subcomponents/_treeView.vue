
<template>
    <div class="col-12">
        <ul class="scoped-root-list">
            <li v-for="folder in folders" :key="folder.key" :class="getHtmlClasses(folder)">
                <div class="ls-flex ls-flex-row" :id="folder.key" @click.stop="selectFolder(folder)">
                    <div class="ls-flex-item grow-1 text-center">
                        <i
                            :class="$store.state.currentFolder == folder.folder ? 'fa fa-folder-open fa-lg' : 'fa fa-folder fa-lg'"
                        ></i>
                    </div>
                    <div class="ls-flex-item grow-6">
                        <span class="scope-apply-hover">{{folder.shortName}}</span>
                    </div>
                    <div class="ls-flex-item grow-1 text-right">
                        <button
                            v-if="folder.children.length > 0"
                            class="btn btn-xs btn-default toggle-collapse-children"
                            @click.stop="toggleCollapse(folder.key)"
                        >
                            <i
                                :class=" isCollapsed(folder.key) ? 'fa fa-caret-down fa-lg' : 'fa fa-caret-up fa-lg'"
                            ></i>
                        </button>
                    </div>
                </div>
                <treeview
                    :key="folder.folder+'-children'"
                    v-show="folder.children.length > 0 && !isCollapsed(folder.key)"
                    :folders="folder.children"
                    :loading="loading"
                    :preset-folder="presetFolder"
                    @setLoading="setLoading"
                />
            </li>
        </ul>
    </div>
</template>

<script>
import applyLoader from '../../mixins/applyLoader';

export default {
    name: "treeview",
  mixins: [applyLoader],
    props: {
        folders: {
            type: [Object, Array],
            default: () => {
                return [];
            }
        },
        presetFolder: {type: String, default: null},
    },
    methods: {
        toggleCollapse(folderKey) {
            this.$store.commit('toggleCollapseFolder', folderKey);
        },
        isCollapsed(folderKey) {
            return this.$store.state.uncollapsedFolders.indexOf(folderKey) == -1
        },
        selectFolder(folderObject) {
            this.$store.state.currentSurveyId = folderObject.surveyId;
            this.$store.state.currentFolderType = folderObject.folderType;
            this.loadingState = true;
            this.$store
                .dispatch("folderSelected", folderObject)
                .then(result => {
                    this.selectFolderResult = result;
                    this.loadingState = false;
                });
        },
        getHtmlClasses(folder) {
            let classes =
                "ls-flex ls-flex-row scoped-tree-folder ls-space bottom-5";
            if (folder.children.length > 0) {
                classes += " scoped-has-children text-bold";
            }
            if (this.$store.state.currentFolder == folder.folder) {
                classes += " scoped-selected";
            }

            if (this.presetFolder == folder.folder) {
                classes += " FileManager--preselected-folder";
            }
            return classes;
        }
    }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" scoped>
.FileManager--preselected-folder {
    background:rgba(220, 220, 220, 0.5);
}
.scoped-root-list {
    position: relative;
    padding-left: 18px;
    padding-top: 18px;
    &:before {
        content: "\2510";
        width: 100%;
        text-align: left;
        font-size: 18px;
        font-weight: bold;
        position: absolute;
        left: 0;
        top: 0;
    }
}
.scope-apply-hover {
    transition: all 0.4s ease;
    border-bottom: 0px solid black;

    &:hover {
        border-bottom: 1px solid black;
    }
}
.scoped-tree-folder {
    font-size: 16px;
    margin: 5px 1px;
    cursor: pointer;
    flex-wrap: wrap;
    border: 0;
    box-shadow: 3px 2px 3px #dedede;
    padding: 1.2rem;

    &:before {
        content: "\22A2";
        text-align: left;
        width: 1.2rem;
        position: absolute;
        font-size: 18px;
        font-weight: bold;
        left: 5px;
    }
    &div:first-of-type {
        padding-left: 0.3rem;
    }
    &.scoped-has-children {
        border-left: 3px solid rgba(120, 120, 120, 0.5);
    }
    &.scoped-selected {
        &:before {
            content: "\22A2>";
        }
        & > div > .scope-apply-hover {
            padding-left: 1.5rem;
            font-weight: bold;
        }
    }
}
</style>
