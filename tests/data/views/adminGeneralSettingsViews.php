<?php

/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */
return [

    // general Settings ---------------------------------------
    // --------------------------------------------------

    ['homepageSettings', ['route'=>'homepagesettings']],
    ['createNewBox', ['route'=>'homepagesettings/sa/create']],
    ['pluginManager', ['route'=>'pluginmanager/sa/index']],
    // TODO: Can't hardcode &id=2 if you using other urlFormat (path).
    //['configurePlugin', ['route'=>'pluginmanager/sa/configure&id=2']],
    ['surveyMenus', ['route'=>'menus/sa/view']],
    ['surveyMenuEntries', ['route'=>'menuentries/sa/view']],
    ['templateOptions', ['route'=>'themeoptions']],
    // TODO: Can't hardcode &id=1 if you using other urlFormat (path).
    //['surveyTemplateOptionsUpdate', ['route'=>'themeoptions/sa/update&id=1']],
    ['themeEditor', ['route'=>'themes/sa/view&templatename=fruity']],

];
