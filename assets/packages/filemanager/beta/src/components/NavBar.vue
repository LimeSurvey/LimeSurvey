<template>
  <div class="navbar navbar-default scoped-navbar-fixes">
    <div class="container-fluid">
      <div class="navbar-header">
        <span class="navbar-brand">{{$store.state.currentFolder}}</span>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li id="FileManager--button-fileInTransit--cancel" v-if="fileInTransit"><a href="#" @click.prevent="cancelTransit">{{'Cancel '+transitType | translate}}</a></li>
        <li id="FileManager--button-fileInTransit--submit" v-if="fileInTransit"><a href="#" @click.prevent="runTransit">{{ transitType | translate }}</a></li>
        <li id="FileManager--button-download"><a href="#" @click.prevent="downloadFiles">{{ 'Download' | translate }}</a></li>
        <li><a id="FileManager--button-upload" href="#" @click.prevent="openUploadModal">{{'Upload'|translate}}</a></li>
      </ul>
    </div>
  </div>
</template>
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
      return this.$store.state.transitType != null;
    },
    transitType() {
        return this.$store.state.transitType == 'copy' ? 'Copy' : 'Move';
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
    downloadFiles() {
      this.loadingState = true;
      this.$store.dispatch('downloadFiles').catch( (e) => {
        this.$log.error(e);
        window.LS.notifyFader(
                    `${this.translate("An error has occured and the selected files could not be downloaded.")}
Error:
${error.data.message}`,
                    'well-lg bg-danger text-center'
        );
      }).finally(
        () => {
          this.loadingState = false;
        }
      )
    },
    cancelTransit() {
      this.emitIsBlocked(false);
      this.$store.commit('cancelTransit');
      this.$emit('forceRedraw');
    },
    runTransit() {
      this.emitIsBlocked(true);
      this.loadingState = true;
      let transitType = this.$store.state.transitType+'';
      this.$store.dispatch('applyTransition').then(
        (result) => {},
        (error) => {
          this.$log.error(error);
        }
      ).finally(() => {
        this.loadingState = false;
        this.emitIsBlocked(false);
      });
    },
    emitIsBlocked(blocked) {
      LS.EventBus.$emit('isBlocked', blocked);
    }
  }
}
</script>
<style scoped lang="scss">
  .scoped-navbar-fixes {
        .navbar-nav > li > a {
            border: none;
            background: linear-gradient(to right, transparent 50.03%,  #989898 50%);
            background: linear-gradient(to right, transparent 50.03%,  var(--LS-admintheme-hovercolor) 50%);
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
