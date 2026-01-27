<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 *
 * @return void
 */
function dummy_twig_translation_helper()
{
    return;

    gT("Your survey responses have not been recorded. This survey is not yet active."); // From themes/survey/vanilla/views/subviews/content/submit_preview.twig:23 (2020/11/16)
    gT("Search (3 characters minimum)"); // From application/views/survey/questions/answer/shortfreetext/location_mapservice/item_100.twig:29 (2021/02/19)
    gT("Restrict search to map extent"); // From application/views/survey/questions/answer/shortfreetext/location_mapservice/item_100.twig:40 (2021/02/19)
    gT("Latitude:"); // From application/views/survey/questions/answer/shortfreetext/location_mapservice/item_100.twig:66 (2021/02/19)
    gT("Longitude:"); // From application/views/survey/questions/answer/shortfreetext/location_mapservice/item_100.twig:80 (2021/02/19)
    gT("Some example answer option"); // From application/views/questionAdministration/answerOptionRow.twig
    gT("Drag to sort"); // From application/views/questionAdministration/answerOptionRow.twig
    gT("Insert a new answer option after this one"); // From application/views/questionAdministration/answerOptionRow.twig
    gT("Predefined label sets..."); // From application/views/questionAdministration/subquestions.twig
    gT("Save as label set");  // From application/views/questionAdministration/answerOptions.twig
    gT("Short free text"); // From application/views/survey/questions/answer/shortfreetext/config.xml
    gT("Set the size to the input or textarea, the input will be displayed with approximately this size in width."); // From application/views/survey/questions/answer/shortfreetext/config.xml
    gT("Subquestion"); // From /var/www/html/limesurvey/application/views/questionAdministration/subquestions.twig
    gT("Relevance equation"); // From /var/www/html/limesurvey/application/views/questionAdministration/subquestions.twig
    gT("Load label set"); // From /var/www/html/limesurvey/application/views/questionAdministration/subquestions.twig
    gT("Save label set"); // From /var/www/html/limesurvey/application/views/questionAdministration/subquestions.twig
    gT("Quick add"); // From /var/www/html/limesurvey/application/views/questionAdministration/subquestions.twig
    gT("Show link/button to delete response & exit survey"); // From themes/survey/fruity/options/options.twig
    gT("Question help text position"); // From themes/survey/fruity/options/options.twig
    gT("Top"); // From themes/survey/fruity/options/options.twig
    gT("Bottom"); // From themes/survey/fruity/options/options.twig
    gT("Wrap tables"); // From themes/survey/fruity/options/options.twig
    gT("Always on"); // From themes/survey/fruity/options/options.twig
    gT("Small screens"); // From themes/survey/fruity/options/options.twig
    gT("After specific subquestion"); // From application/views/survey/questions/answer/multiplechoice/config.xml
    gT("Relevance help for printable survey"); // From application/views/survey/questions/answer/5pointchoice/config.xml
    gT("Specify how array-filtered sub-questions should be displayed"); // From application/views/survey/questions/answer/arrays/10point/config.xml
    gT("Indicates where the 'Other' option should be placed"); // From application/views/survey/questions/answer/list_dropdown/config.xml
    gT("Answer code for 'After specific answer option'"); // From application/views/survey/questions/answer/list_dropdown/config.xml
    gT("The code of the answer option after which the 'Other:' option will be placed if the position is set to 'After specific answer option'"); // From application/views/survey/questions/answer/list_dropdown/config.xml
    gT("Position for 'Other:' option");  //  From application\views\survey\questions\answer\listradio\config.xml
    gT("Subquestion title for 'After specific subquestion'"); // From application/views/survey/questions/answer/multiplechoice/config.xml
    gT("The title of the subquestion after which the 'Other:' option will be placed if the position is set to 'After specific subquestion'"); // From application/views/survey/questions/answer/multiplechoice/config.xml
    gT("Indicates where the 'Other' option should be placed"); // From application/views/survey/questions/answer/multiplechoice/config.xml
    gT("After specific answer option"); // From application\views\survey\questions\answer\list_dropdown\config.xml
    gT("Before 'No Answer'"); // From application\views\survey\questions\answer\list_dropdown\config.xml
}
