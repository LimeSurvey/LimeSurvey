<script>
export default {
  name: 'treeview',
  props: {
    folders: {type: [Object,Array], default: ()=>{return []}},
    initiallyCollapsed: {type: Boolean, default: false}
  },
  data() {
      return {
          collapsed: this.initiallyCollapsed
      }
  },
  methods: {
      selectFolder(folderObject){
          this.$emit('loading');
          this.$store.dispatch('folderSelected', folderObject).then(
              (result) => {
                  this.$emit('endloading');
              }
          );
      },
      getHtmlClasses(folder){
          let classes = 'ls-flex ls-flex-row scoped-tree-folder ls-space bottom-5'
          if(folder.children.length >0) {
              classes += "scoped-has-children text-bold";
          }
          if(this.$store.state.currentFolder == folder.folder) {
              classes += ' scoped-selected'
          }
          return classes;
      }
  }

}
</script>

<template>
    <div class="col-12">
        <ul class="scoped-root-list">
            <li 
                v-for="folder in folders"
                :key="folder.key"
                :class="getHtmlClasses(folder)"
            >
                <div class="ls-flex ls-flex-row">
                    <div class="ls-flex-item grow-6 scope-apply-hover" @click.stop="selectFolder(folder)" >
                        {{folder.shortName}}
                    </div>
                    <div class="ls-flex-item grow-1 text-right">
                        <button
                            v-if="folder.children.length > 0"
                            @click="collapsed=!collapsed"
                            class="btn btn-xs btn-default"
                        >
                            <i :class=" collapsed ? 'fa fa-caret-down fa-lg' : 'fa fa-caret-up fa-lg'" ></i>
                        </button>
                    </div>
                </div>
                <treeview
                    v-show="folder.children.length > 0 && !collapsed"
                    :collapsed="collapsed"
                    :folders="folder.children"
                    @loading="$emit('loading')"
                    @endloading="$emit('endloading')"
                />
            </li>
        </ul>
    </div>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" scoped>
    .scoped-root-list {
        position: relative;
        padding-left:18px;
        padding-top:18px;
        &:before{
            content: "\2510";
            width:100%;
            text-align: left;
            font-size: 18px;
            font-weight: bold;
            position: absolute;
            left:0;
            top:0;
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
        &:before{
            content: "\22A2";
            text-align: left;
            width: 1.2rem;
            position: absolute;
            font-size: 18px;
            font-weight: bold;
            left:5px;
        }
        &div:first-of-type{
            padding-left: 0.3rem;
        }
        &.scoped-has-children {
            border-left: 3px solid rgba(120,120,120,0.5);
        }
        &.scoped-selected {
            &:before{
                content: "\22A2>";
            }
            &>div>.scope-apply-hover {
            padding-left: 1.5rem;   
            font-weight: bold;
            }
        }
    }
</style>
