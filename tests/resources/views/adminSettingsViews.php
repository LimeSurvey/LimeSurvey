<?php
/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */
return [

    // General stuff ---------------------------------------
    // --------------------------------------------------

    // Expression Manager

    ['expressionsTest', ['route'=>'expressions']],
    ['expressionsFunctions',['route'=>'expressions/sa/functions']],
    ['expressionsStrings',['route'=>'expressions/sa/strings_with_expressions']],
    ['expressionsRelevance',['route'=>'expressions/sa/relevance']],
    ['expressionsConditions2Relevance',['route'=>'expressions/sa/conditions2relevance']],
    ['expressionsNavigationTest',['route'=>'expressions/sa/navigation_test']],

    // Advanced
    ['checkIntegrity', ['route'=>'checkintegrity']],

];