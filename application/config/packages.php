<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Core packages , no third_party
 *
 * @license GPL v3
 *
 */
return array(
    /* For public template functionnality */
    'limesurvey-public'=>array(
        'basePath' => 'core.limesurvey',
        'css'=> array(
            'survey.css',
        ),
        'js'=>array(
            'survey.js',
        ),
        'depends' => array(
            //'bootstrap',
        )
    )
);
