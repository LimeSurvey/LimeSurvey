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
gT('Default coordinates of the map when the page first loads. Format: latitude [space] longitude');
