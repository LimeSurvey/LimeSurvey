<script>
import Languageselector from './helperComponents/LanguageSelector.vue'
import Labellist from './components/labellist.vue'
import eventRoot from './mixins/eventRoot.js';

export default {
  name: 'app',
  mixins: [eventRoot],
  components: {
    Languageselector,
    Labellist,
  },
  data(){
    return {
      loading: true,
    };
  },
  computed: {
    labelSetType: {
      get() { return this.$store.state.labelSetType; },
      set(newType) { this.$store.commit('setLabelSetType', newType) }
    }
  },
  methods: {
      selectLanguage(sLanguage) {
          this.$log.log('LANGUAGE CHANGED', sLanguage);
          this.$store.commit('setActiveLanguage', sLanguage);
      },
      setModalEvent(payload) {
          this.$log.log('New event set', payload);
          this.event = payload;
      },
      triggerEvent(payload) {
          this.$log.log('New event set', payload);
          this.event = payload;
      },
      eventSet(eventRoot=false) {
          this.event = null;
      },
      submitCurrentState(redirect = false) {
          this.loading = true;
          this.$store.dispatch('saveLabelSetData').then(
              (result) => {
                  if(result === false) {
                      return;
                  }
                  this.loading = false;
                  window.LS.notifyFader(result.data.message, 'well-lg text-center ' + (result.data.success ? 'bg-primary' : 'bg-danger'));
                  if(redirect == true) {
                      window.location.href = result.data.redirect || window.location.href;
                  }
              },
              (reject) => {
                  this.loading = false;
                  $('.lsLoadingStateIndicator').remove();
                  window.LS.notifyFader("Question could not be stored. Reloading page.", 'well-lg bg-danger text-center');
                  //setTimeout(()=>{window.location.reload();}, 1500);
              }
          )
      },
  },
  created() {
    this.$store.dispatch('getLabelSetData').then(
      () => { this.loading = false; },
      (error) => { this.$log.error(error); this.loading = false; },
    );
  },
  mounted(){
      $('#save-form-button').on('click', (e)=>{
          this.submitCurrentState();
      });
  }
}
</script>

<template>
  <div id="LabelSetEditorContainer" class="row">
    <div class="container-fluid" v-if="!loading">
      <div class="row">
        <languageselector
            :elId="'labelseteditor-language-changer'" 
            :aLanguages="$store.state.languages" 
            :parentCurrentLanguage="$store.state.activeLanguage" 
            @change="selectLanguage"
        />
      </div>
      <div class="row">
        <labellist :loading="loading" :event="event" v-on:triggerEvent="triggerEvent" v-on:eventSet="eventSet" />
      </div>
    </div>
    <loader-widget id="labelset-loader-widget" v-if="loading" />
    <modals-container @modalEvent="setModalEvent" />
  </div>
</template>

<style lang="scss" scoped>

</style>
