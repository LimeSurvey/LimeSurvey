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
    // TODO: This tend to fails randomly. ID should probably not be hardcoded.
    //['configurePlugin', ['route'=>'pluginmanager/sa/configure&id=3']],
    ['surveyMenus', ['route'=>'menus/sa/view']],
    ['surveyMenuEntries', ['route'=>'menuentries/sa/view']],


];
