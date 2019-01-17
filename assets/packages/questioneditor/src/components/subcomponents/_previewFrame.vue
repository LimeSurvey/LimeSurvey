
<script>
import forEach from 'lodash/foreach';

export default {
    name: 'previewFrame',
    props: {
        rootUrl: {type: String, required: true},
        id: {type: String, required: true},
        content: {type: String, default: ''},
        htmlClass: {type: String, default: ''},
        loading: {type: Boolean, default: true},
    },
    data() {
        return {
            src: '#',
            documentIframe: null,
            iframeId: '',
            iFrameContent: '',
        };
    },
    methods: {
        getRandomId(){
            const random = Math.random();
            const date = (new Date()).getTime();
            return this.id+'-'+Math.floor(((random*10000000000000) + date)/10000);
        },
    },
    watch: {
        content(newContent){
            try {
                const contents = this.documentIframe.contents();
                this.$log.log(this.$documentIframe);
                this.$log.log(contents);
                this.documentIframe.contents().find('html').text('');
                this.documentIframe.contents().find('html').html(newContent);
                this.documentIframe[0].contentWindow.jQuery(document).trigger('pjax:scriptcomplete');
            } catch(e){
                this.$log.error(e);
            }
        }
    },
    mounted() {
        $('#'+this.id).append(this.documentIframe);
    },
    created(){
        const iframeID = this.getRandomId();
        this.iframeId = iframeID;
        this.documentIframe = $(`<iframe src="${this.rootUrl}/sLanguage/${this.$store.state.activeLanguage}/root/1" id='${iframeID}' style='width:100%;height:100%;border:none;' />`);
    }
}
</script>

<template>
    <div class="scope-iframe-fill" border="0" :class="htmlClass" :id="id">
        <div class='ls-flex align-content-center align-items-center scope-loader' v-if="loading">
            <div class='loader-advancedquestionsettings text-center'>
                <div class='contain-pulse animate-pulse'>
                    <div class='square'></div>
                    <div class='square'></div>
                    <div class='square'></div>
                    <div class='square'></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
  .scope-loader {
      position: absolute;
      left: 20%;
      top: 20%;
      height: 60%;
      width: 60%;
      background-color: rgba(245,245,245,1);
  }
  .scope-iframe-fill {
      width: 100%;
      height: 100%;
      padding: 0;
      margin:0;
  }
</style>
