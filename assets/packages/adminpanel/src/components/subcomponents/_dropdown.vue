<script>

import ajaxMixin from '../../mixins/runAjax.js';

export default {
    props: {
        'mainHref' : {type: String},
        'mainTitle' : {type: String}
    }
}
</script>

<template>
    <div class="btn-group hidden-xs">
        <!-- Main dropdown -->
        <button type="button" class="btn btn-default dropdown-toggle limebutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="icon-display_export"></span>
            Display / Export
            <span class="caret"></span>
        </button>
        <!-- dropdown -->
        <ul class="dropdown-menu">
            <!-- survey export -->
            <!-- Export -->
            <li class="dropdown-header"> Export...</li>
            <!-- Survey structure -->
            <li>
                <a href="/index.php/admin/export/sa/survey/action/exportstructurexml/surveyid/229189">
                    <span class="icon-export"></span>
                    Survey structure (.lss) </a>
            </li>
            <!-- Survey archive unactivated -->
            <li>
                <a href="#" onclick="alert('You can only archive active surveys.');">
                    <span class="icon-export"></span>
                    Survey archive (.lsa) </a>
            </li>
            <!-- queXML -->
            <li>
                <a href="/index.php/admin/export/sa/survey/action/exportstructurequexml/surveyid/229189">
                    <span class="icon-export"></span>
                    queXML format (*.xml) </a>
            </li>
            <!-- queXMLPDF -->
            <li>
                <a href="/index.php/admin/export/sa/quexml/surveyid/229189">
                    <span class="icon-export"></span>
                    queXML PDF export </a>
            </li>
            <!-- Tab-separated-values -->
            <li>
                <a href="/index.php/admin/export/sa/survey/action/exportstructuretsv/surveyid/229189">
                    <span class="icon-export"></span>
                    Tab-separated-values format (*.txt) </a>
            </li>
            <!-- Survey printable version  -->
            <li>
                <a href="/index.php/admin/export/sa/survey/action/exportprintables/surveyid/229189">
                    <span class="icon-export"></span>
                    Printable survey (*.html) </a>
            </li>
            <!-- Printable version -->
            <li>
                <a target="_blank" href="/index.php/admin/printablesurvey/sa/index/surveyid/229189">
                    <span class="fa fa-print"></span>
                    Printable survey </a>
            </li>
        </ul>
    </div>
</template>
<style lang="sass">

</style>
