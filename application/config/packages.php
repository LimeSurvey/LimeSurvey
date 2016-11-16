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
            'fontawesome',
            //'bootstrap', //limesurvey in future must work without boostrap
        )
    ),
    /* Ranking question type */
    'question-ranking'=>array(
        'basePath' => 'core.questions.ranking',
        'css'=> array(
            'ranking.css',
        ),
        'js'=>array(
            'ranking.js',
        ),
        'depends' => array(
            'jqueryui',
            'jquery-touch-punch',
            'jquery-actual',
        )
    ),
);
