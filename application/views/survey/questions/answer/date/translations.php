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
gT('Minimum date, valid date in YYYY-MM-DD format or any English textual datetime description. Expression Managed can be used (only with YYYY-MM-DD format). For dropdown : only the year is restricted if date use variable not in same page.');
gT('Maximum date, valid date in any English textual datetime description (YYYY-MM-DD for example). Expression Managed can be used (only with YYYY-MM-DD format) value. For dropdown : only the year is restricted if date use variable not in same page.');
