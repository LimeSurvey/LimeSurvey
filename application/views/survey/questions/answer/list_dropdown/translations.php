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
gT('Semicolon-separated list of answer codes that keep their original database position when answers are randomized');
gT('Order - like 3)');
gT("Position for 'Other:' option");
gT("Indicates where the 'Other' option should be placed");
gT('After specific answer option');
gT("Before 'No Answer'");
gT("Answer code for 'After specific answer option'");
gT("The code of the answer option after which the 'Other:' option will be placed if the position is set to 'After specific answer option'");
