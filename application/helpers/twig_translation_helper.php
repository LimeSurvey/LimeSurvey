<?php
/*
* LimeSurvey
* Copyright (C) 2007-2020 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


/**
 * Dummy helper intended to facilitate "twig only" strings to be picked by Translations Bot.
 * 
 * Since twig files are not scanned by the bot, translatable strings that only appear in twig
 * files can be placed here in order to be scanned.
 */


function dummy_twig_translation_helper() {
    return;

    gT("Your survey responses have not been recorded. This survey is not yet active."); // From themes/survey/vanilla/views/subviews/content/submit_preview.twig:23 (2020/11/16)
}
