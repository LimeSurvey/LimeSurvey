<script>

import Treeview from './subcomponents/_treeView';
import applyLoader from '../mixins/applyLoader';

export default {
  name: 'FolderList',
  components: {
    Treeview
  },
  mixins: [applyLoader],
  props: {
    cols: {type: Number, default: 6},
    presetFolder: {type: String|null, default: null}
  },
}
</script>

<template>
  <div class="scoped-folder-list" :class="'col-md-'+cols">
    <treeview 
        key="root-folder"
        :folders="$store.state.folderList" 
        @setLoading="setLoading" 
        :loading="loading" 
        :preset-folder="presetFolder"
    />
  </div>
</template>


<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .scoped-folder-list{
        box-shadow: 1px 3px 6px solid #939393;
        min-height:480px;
    }
    .scoped-bordermecrazy {
        border-right: 1px solid grey;
    }
</style>
