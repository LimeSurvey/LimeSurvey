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
gT('Limit the number of possible answers fixed by number of columns in database');
gT('Semicolon-separated list of answer codes that keep their original database position when answers are randomized');
gT('Force each answer option to have the same height');
gT('Force the choice list and the rank list to have the same height');
gT('Replace choice header (default: "Available items")');
gT('Replace rank header (default: "Your ranking")');
