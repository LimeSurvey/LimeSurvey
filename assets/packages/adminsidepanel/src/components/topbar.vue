<script>
import ajaxMixin from '../mixins/runAjax.js';
import Dropdown from './subcomponents/_dropdown.vue';
export default {
    components: {
        'dropdown' : Dropdown
    },
    props: {
        'mainHref' : {type: String},
        'mainTitle' : {type: String}
    }
}
</script>
<template>
    <div class="menubar surveybar" id="surveybarid">
        <div class="row container-fluid row-button-margin-bottom">
            <div class="col-md-12 col-xs-6">
                <!-- activate -->
                <a id="ls-activate-survey" class="btn btn-success" href="/index.php/admin/survey/sa/activate/surveyid/229189" role="button">Activate this survey</a>
                <!-- Multinlinguage -->
                <a class="btn btn-default  btntooltip" href="/index.php/229189?newtest=Y&amp;lang=en" role="button" accesskey="d" target="_blank" data-original-title="" title="">
                    <span class="icon-do"></span> Preview survey
                </a>
                <div class="btn-group hidden-xs">
                    <!-- Main button dropdown -->
                    <button type="button" class="btn btn-default dropdown-toggle limebutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-tools"></span> Tools
                        <span class="caret"></span>
                    </button>
                    <!-- dropdown -->
                    <ul class="dropdown-menu">
                        <!-- Delete survey -->
                        <li>
                            <a href="/index.php/admin/survey/sa/delete/surveyid/229189">
                                <span class="fa fa-trash"></span>
                                Delete survey
                            </a>
                        </li>
                        <!-- surveytranslate -->
                        <!-- Quick-translation disabled -->
                        <li>
                            <a href="#" onclick="alert('Currently there are no additional languages configured for this survey.');">
                                <span class="fa fa-language"></span>
                                Quick-translation </a>
                        </li>
                        <li>
                            <!-- condition disabled -->
                            <a href="#" onclick="alert('Currently there are no conditions configured for this survey.');">
                                <span class="icon-resetsurveylogic"></span>
                                Reset conditions
                            </a>
                        </li>
                        <!-- survey content -->
                        <!-- one language -->
                        <!-- Survey logic file -->
                        <li>
                            <a href="/index.php/admin/expressions/sa/survey_logic_file/sid/229189">
                                <span class="icon-expressionmanagercheck"></span>
                                Survey logic file
                            </a>
                        </li>
                        <li role="separator" class="divider"></li>
                        <!-- Regenerate question codes -->
                        <li class="dropdown-header">
                            Regenerate question codes
                        </li>
                        <!-- Straight -->
                        <li>
                            <a href="/index.php/admin/survey/sa/regenquestioncodes/surveyid/229189/subaction/straight">
                                <span class="icon-resetsurveylogic"></span>
                                Straight
                            </a>
                        </li>
                        <!-- By question group -->
                        <li>
                            <a href="/index.php/admin/survey/sa/regenquestioncodes/surveyid/229189/subaction/bygroup">
                                <span class="icon-resetsurveylogic"></span>
                                By question group
                            </a>
                        </li>
                    </ul>
                </div>
    
                <!-- Display / Export -->
                <dropdown></dropdown>
                <!-- Token -->
                <a class="btn btn-default  btntooltip hidden-xs" href="/index.php/admin/tokens/sa/index/surveyid/229189" role="button" data-original-title="" title="">
                    <span class="fa fa-user"></span>
                    Survey participants </a>
                <!-- Statistics -->
                <div class="btn-group">
                    <!-- main  dropdown header -->
                    <button type="button" data-toggle="tooltip" data-placement="bottom" title="" class="readonly btn btn-default limebutton" data-original-title="This survey is not active - no responses are available.">
                        <span class="icon-responses"></span>
                        Responses
                        <span class="caret"></span>
                    </button>
                    <!-- dropdown -->
                    <ul class="dropdown-menu">
                    </ul>
                </div>
    
                <!-- Extra menus from plugins -->
    
                <!-- List Groups -->
                <!-- admin/survey/sa/view/surveyid/838454 listquestiongroups($iSurveyID)-->
                <a class="btn btn-default hidden-sm  hidden-md hidden-lg" href="/index.php/admin/survey/sa/listquestiongroups/surveyid/229189">
                    <span class="fa fa-list"></span>
                    List question groups </a>
    
                <!-- List Questions -->
                <a class="btn btn-default hidden-sm  hidden-md hidden-lg" href="/index.php/admin/survey/sa/listquestions/surveyid/229189">
                    <span class="fa fa-list"></span>
                    List questions </a>
    
            </div>
    
            <!-- right action buttons -->
            <div class=" col-md-4 text-right">
    
            </div>
        </div>
    </div>
</template>
<style lang="sass">

</style>
