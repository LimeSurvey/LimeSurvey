<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Translatable strings from the config.xml file in this directory.
 *
 * The help texts, captions and option labels of question attributes are translated at runtime with gT(),
 * but the translation script can't pick them up from XML, so they are listed here.
 * This file has no functionality except for being searchable by the translation script.
 * When adding or changing such a text in config.xml, update the matching entry here.
 */
gT('Semicolon-separated list of subquestion codes that keep their original database position when subquestions are randomized');
gT('You can use Expression manager, but this must be a number before showing the page else set to 0. If minimum value is not set, this value is used.');
gT('You can use Expression manager, but this must be a number before showing the page else set to 100. If maximum value is not set, this value is used.');
gT('You can use Expression manager, but this must be a number before showing the page else set to 1.');
gT('Slider start as this value. You can use Expression manager, but this must be a number before showing the page.');
gT('Label wrapper width');
