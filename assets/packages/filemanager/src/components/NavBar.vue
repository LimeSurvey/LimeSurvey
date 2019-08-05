<script>
import UploadModal from './subcomponents/_uploadModal';

export default {
  name: 'NavBar',
  components: {UploadModal},
  data()  {
    return {};
  },
  computed: {
    fileInTransit() {
      return this.$store.state.fileInTransit != null;
    }
  },
  methods: {
    onModalUploadFinished() {
        this.$emit('loading');
        this.$store.dispatch('getFileList').finally(
        ()=>{this.$emit('endloading');}
      );
    },
    openUploadModal() {
      this.$modal.show(
        UploadModal,
        {},
        {
            width: '75%',
            height: '75%',
            scrollable: true,
            resizable: false
        },
        {
            'before-close': this.onModalUploadFinished
        }
      );
    },
    cancelTransit(){
      this.$store.commit('cancelTransit');
    },
    runTransit() {
      this.$emit('loading');
      let transitType = this.$store.state.transitType+'';
      this.$store.dispatch('applyTransition').then(
        (result) => {
          this.$store.dispatch('getFileList').then(
            (result) => {
              this.$emit('endloading');
              if(transitType == 'move') {
                this.$store.commit('cancelTransit');
              }
            },
            (error) => {
              this.$emit('endloading');
              this.$log.error(error)
            }
          )},
        (error) => {
          this.$emit('endloading');
          this.$log.error(error)
        }
      )
    },
  }
}
</script>

<template>
  <div class="navbar navbar-default scoped-navbar-fixes">
    <div class="container-fluid">
      <div class="navbar-header">
        <span class="navbar-brand">{{$store.state.currentFolder}}</span>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li v-if="fileInTransit"><a  href="#" @click.prevent="cancelTransit">{{'Cancel transit' | translate}}</a></li>
        <li v-if="fileInTransit"><a  href="#" @click.prevent="runTransit">{{'Copy/Move'|translate}}</a></li>
        <li><a href="#" @click.prevent="openUploadModal">{{'Upload'|translate}}</a></li>
      </ul>
    </div>
  </div>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
  .scoped-navbar-fixes {
    .navbar-nav > li > a:hover,
    .navbar-nav > li > a:active
     {
      text-decoration: 'underline';
      color: rgb(80,80,80);
    }
  }
</style>
