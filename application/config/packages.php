<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Core packages , no third_party
 * sees third_party.php for third party package
 * @license GPL v3
 * core path is application/core/packages
 *
 */
/* needed ? @see third_party.php */
if(isset($_GET['isAjax'])){
    return array();
}
return array(
    /* For public template functionnality */
    'limesurvey-public'=>array(
        'basePath' => 'core.limesurvey',/* public part only : rename directory ? */
        'css'=> array(
            'survey.css',
        ),
        'js'=>array(
            'js.js',
            'survey.js',
        ),
        'depends' => array(
            'jquery',
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
            'rubaxa-sortable',
            'jquery-actual',
        )
    ),
    /* numeric slider question : numerci question type with slider */
    'question-numeric-slider'=>array(
        'basePath' => 'core.questions.numeric-slider',
        'css'=> array(
            'numeric-slider.css',
        ),
        'js'=>array(
            'numeric-slider.js',
        ),
        'depends' => array(
            'bootstrap-slider',
        )
    ),

);
