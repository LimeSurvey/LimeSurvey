<script>
import Vue from 'vue';
import _ from 'lodash';

export default {
    props: {
        'mainHref' : {type: String},
        'mainTitle' : {type: String}
    }
}
</script>
<template>
    <div class='row container-fluid'>
        <div class="col-xs-12 col-md-12">
            <h3 id="survey_title"><a :href="mainHref">{{mainTitle}}</a></h3>
        </div>
    </div>
</template>
<style lang="sass">

</style>
