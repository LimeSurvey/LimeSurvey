<script>

import Treeview from './subcomponents/_treeView';

export default {
  name: 'FolderList',
  components: {
    Treeview
  },
  props: {
    cols: {type: Number, default: 6}
  },
  methods: {
    loading(){
      
    }
  }
}
</script>

<template>
  <div class="scoped-folder-list" :class="'col-xs-'+cols">
    <treeview :folders="$store.state.folderList" @loading="$emit('loading')" @endloading="$emit('endloading')"/>
  </div>
</template>


<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .scoped-folder-list{
    box-shadow: 1px 3px 6px solid #939393;
    min-height:480px;
  }
</style>
