<?php
/**
 * This contains a list of admin views that we can loop for bulk testing.
 * Each element conntains an ID as key and the admin route that we can test
 * // TODO currtnly these only contain pages that do not depend on opening a survey!
 */

return [
    ['login' ,['route'=>'authentication/sa/login']],
    ['index' ,['route'=>'index']],
    ['createSurvey' ,['route'=>'survey/sa/newsurvey']],
    ['listSurveys' ,['route'=>'survey/sa/listsurvey']],
    ['createSurveyGroups' ,['route'=>'surveysgroups/sa/create']],
    ['globalsettings' ,['route'=>'globalsettings']],
    //['update' ,['route'=>'update']],
    ['viewLabelSets' ,['route'=>'labels/sa/view']],
    ['createLabelSets' ,['route'=>'labels/sa/newlabelset']],

    // FIXME this is broken
    //['templateOptions' ,['route'=>'templateoptions']],
];
