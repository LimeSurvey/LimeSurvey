<script>
import Vue from 'vue';
import _ from 'lodash';
import ajaxMethods from '../mixins/runAjax.js';

export default {
    mixins: [ajaxMethods],
    props: {
        'mainTitle' : {type: String},
        'getQuickMenuUrl' : {type: String},
    },
    data(){
        return {
            menuentries : {},
        };
    },
    mounted(){
        const self = this;
        this.get(this.getQuickMenuUrl,{}).then((result) => {

        });
    }
}
</script>
<template>
    <div class='ls-column fill'>
        
    </div>
</template>
<style lang="scss">

 .vertical {
     writing-mode: vertical-rl;
 }

</style>
