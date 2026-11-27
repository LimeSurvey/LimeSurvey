<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Translatable strings from the questiontypes.xml file in this directory.
 *
 * The category titles and the editor-specific question type titles are translated at runtime with gT(),
 * but the translation script can't pick them up from XML, so they are listed here.
 * Titles taken from the question themes are already listed with the themes.
 * This file has no functionality except for being searchable by the translation script.
 * When adding or changing such a text in questiontypes.xml, update the matching entry here.
 */
gT('Single choice');
gT('Multiple choice');
gT('Ranking & rating');
gT('Dates & data');
gT('Text');
gT('Number');
gT('Array');
gT('List with comment (Radio)');
gT('List image select (Radio)');
gT('Dropdown');
gT('Single choice buttons');
gT('Multiple choice buttons');
gT('Rating');
gT('Date/time');
gT('Short text');
gT('Long text');
gT('Array (Point choice)');
