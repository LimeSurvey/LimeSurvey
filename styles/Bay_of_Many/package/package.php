<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains package definition for this template.
 * Defining them here allows for easy inclusion in views.
 */
return array(
        'js'=>array(
            'scripts/notify.js',
            'scripts/save.js',
            'scripts/panelclickable.js',
            'scripts/panelsanimation.js',
            'scripts/sidemenu.js'
        ),

        'css' => array(
            'css/lime-admin.css',
            'css/fonts.css',
            'css/font-awesome/font-awesome-43.min.css',
        ),
        'depends' => array(
            'jquery',
        ),
);
