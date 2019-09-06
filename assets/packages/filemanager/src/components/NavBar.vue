<script>
import UploadModal from './subcomponents/_uploadModal';
import applyLoader from '../mixins/applyLoader';

export default {
  name: 'NavBar',
  components: {UploadModal},
  mixins: [applyLoader],
  data()  {
    return {};
  },
  computed: {
    fileInTransit() {
      return this.$store.state.fileInTransit != null;
    },
    transitType() {
        return $store.state.transitType == 'copy' ? 'Copy' : 'Move';
    }
  },
  methods: {
    onModalUploadFinished() {
        this.loadingState = true;
        this.$store.dispatch('getFileList').finally(
        ()=>{this.loadingState = false;}
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
      this.loadingState = true;
      let transitType = this.$store.state.transitType+'';
      this.$store.dispatch('applyTransition').then(
        (result) => {
            if(transitType == 'move') {
                this.$store.commit('cancelTransit');
            }
        },
        (error) => {
          this.$log.error(error);
        }
      ).finally(() => { this.loadingState = false; });
    }
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
        <li v-if="fileInTransit"><a  href="#" @click.prevent="cancelTransit">{{'Cancel '+transitType | translate}}</a></li>
        <li v-if="fileInTransit"><a  href="#" @click.prevent="runTransit">{{ transitType | translate }}</a></li>
        <li><a href="#" @click.prevent="openUploadModal">{{'Upload'|translate}}</a></li>
      </ul>
    </div>
  </div>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
  .scoped-navbar-fixes {
        .navbar-nav > li > a {
            background: linear-gradient(to right, transparent 50,03%,  var(--LS-admintheme-hovercolor) 50%);
            background-size: 200%;
            background-position: 0;    
            transition: all 0.3s;
            &:hover,
            &:active,
            &:focus {
                background-position: -99.9%;
                color: white
            }
        }
  }
</style>
