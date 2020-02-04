<template>
    <div class="scoped-folder-list" :class="'col-md-'+cols">
        <div><input v-model="search" @input="setPage(0)" class="form-control folder-search-bar"/></div>
        <treeview
                key="root-folder"
                :folders="filteredCurrentPageFileList"
                @setLoading="setLoading"
                :loading="loading"
                :preset-folder="presetFolder"
        />
        <div v-if="filteredCurrentPageFileList.length" class="ls-ba">
            <div class="ls-ba pager" aria-label="Folder navigation">
                <ul class="pagination">
                    <li v-bind:class="[currentPage + 1 === 1 || pages === 1 ? 'disabled' : '']">
                        <a v-on="currentPage + 1 > 1 ? {click:() => setPage(0)} : ''">&laquo;</a>
                    </li>
                    <li v-bind:class="[currentPage + 1 === 1 || pages === 1 ? 'disabled' : '']">
                        <a v-on="currentPage + 1 > 1 ? {click:() => setPage(currentPage - 1)}: ''">&lsaquo;</a>
                    </li>
                    <li v-for="pageI in pageArray" :key="'set-page-'+pageI" v-bind:class="[pageI === currentPage ? 'active' : '']">
                        <a @click="setPage(pageI)">{{(pageI+1)}}</a>
                    </li>
                    <li v-bind:class="[pages === currentPage + 1 || pages === 1 ? 'disabled' : '']">
                        <a v-on="currentPage + 1 < pages ? {click:() => setPage(currentPage + 1)}: ''">&rsaquo;</a>
                    </li>
                    <li v-bind:class="[pages === currentPage + 1 || pages === 1 ? 'disabled' : '']">
                        <a v-on="currentPage + 1 < pages ? {click:() => setPage(pages -1)}: ''">&raquo;</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>

    import Treeview from './subcomponents/_treeView';
    import applyLoader from '../mixins/applyLoader';
    import slice from 'lodash/slice';
    import filter from 'lodash/filter';

    export default {
        name: 'FolderList',
        components: {
            Treeview
        },
        mixins: [applyLoader],
        props: {
            cols: {type: Number, default: 6},
            presetFolder: {type: String, default: null}
        },
        data()
        {
            return {
                pageSize: 20,
                currentPage: 0,
                search: ''
            }
        },
        computed: {
            pages()
            {
                return Math.ceil(this.filteredFolderList.length / this.pageSize);
            },
            pageArray()
            {
                const pageArray = [];
                for (let pageI = 0; pageI < this.pages; pageI++)
                {
                    pageArray.push(pageI);
                }
                return pageArray;
            },
            filteredFolderList()
            {
                return filter(this.$store.state.folderList, (folder) => new RegExp(this.search).test(folder.shortName));
            },
            filteredCurrentPageFileList()
            {
                return slice(this.filteredFolderList, this.currentPage * this.pageSize, (this.currentPage + 1) * this.pageSize);
            }
        },
        methods: {
            setPage(selPage)
            {
                this.currentPage = selPage;
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .scoped-folder-list {
        box-shadow: 1px 3px 6px solid #939393;
        min-height: 480px;
    }

    .scoped-bordermecrazy {
        border-right: 1px solid grey;
    }
</style>
