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
gT("Set the size of the 'Other:' input. The input will be displayed approximately this size in width.");
gT("'Other:' text input box size");
gT("Maximum characters allowed for 'Other:' option");
gT("'Other:' option maximum characters");
gT('Semicolon-separated list of subquestion codes that keep their original database position when subquestions are randomized');
gT('The answer options will be distributed across the number of columns set here. Any number up to 1 can be entered, but we only support systems up to 16.');
