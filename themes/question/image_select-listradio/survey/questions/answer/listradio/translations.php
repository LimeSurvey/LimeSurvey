<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Translatable strings from the config.xml file in this directory.
 *
 * The theme title and description, and the help texts, captions and option labels of its question
 * attributes are translated at runtime with gT(), but the translation script can't pick them up from XML,
 * so they are listed here.
 * This file has no functionality except for being searchable by the translation script.
 * When adding or changing such a text in config.xml, update the matching entry here.
 */
gT("Image select list (Radio)");
gT('Show an horizontal scroll for the images instead of a vertical list. Needs JavaScript enabled.');
gT('Horizontal scroll:');
gT('Keep images aspect ratio. Can be achieved by not setting both width and height. Needs JavaScript enabled.');
gT('Keep aspect-ratio');
gT('Crop images to fit into size. Needs JavaScript enabled.');
gT('Crop or Resize');
gT('Resize');
gT('Crop');
gT('Fix width of the images to this value. Leave empty to not change them.');
gT('Fix width');
gT('Fix height of the images to this value. Leave empty to not change them.');
gT('Fix height');
