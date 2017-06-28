<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Core packages , no third_party
 * sees third_party.php for third party package
 * @license GPL v3
 * core path is application/core/packages
 *
 * Note: When debug mode, asset manager is turned off by default.
 * To enjoy this feature, add to your package definition a 'devBaseUrl' with the relative url to your package
 *
 */
/* needed ? @see third_party.php */
if(isset($_GET['isAjax'])){
    return array();
}
return array(
    /* For public template functionnality */
    'limesurvey-public'=>array(
        'devBaseUrl'  => 'assets/packages/limesurvey/',
        'basePath' => 'core.limesurvey',/* public part only : rename directory ? */
        'css'=> array(
            'survey.css',
        ),
        'js'=>array(
            'survey.js',
        ),
        'depends' => array(
            'jquery',
            'fontawesome',
        )
    ),
    /* For public template extended functionnality (based on default template) */
    'template-default'=>array(
        'devBaseUrl'  => 'assets/packages/template-default/',
        'basePath' => 'core.template-default',
        'css'=> array(
            'template-core.css',
        ),
        'js'=>array(
            'template-core.js',
        ),
        'depends' => array(
            'limesurvey-public',
        )
    ),
    'template-default-ltr'=>array( /* complement for ltr */
        'devBaseUrl'  => 'assets/packages/template-default/',
        'basePath' => 'core.template-default',
        'css'=> array(
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        ),
        'depends' => array(
            'template-default',
        )
    ),
    'template-default-rtl'=>array( /* Same but for rtl */
        'devBaseUrl'  => 'assets/packages/template-default/',
        'basePath' => 'core.template-default',
        'css'=> array(
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox-rtl.css',
        ),
        'depends' => array(
            'template-default',
        )
    ),

    'bootstrap-rtl'=>array( /* Adding boostrap rtl package */
        'devBaseUrl'  => 'assets/packages/bootstrap/',
        'basePath' => 'core.bootstrap',
        'css'=> array(
            'bootstrap-rtl.css',
        ),
        'depends' => array(
            'bootstrap',
        )
    ),
    /* Ranking question type */
    'question-ranking'=>array(
        'devBaseUrl'  => 'assets/packages/questions/ranking/',
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
        'devBaseUrl'  => 'assets/packages/questions/numeric-slider/',
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
    'adminpanel' => array(
        //'devBaseUrl' => 'assets/packages/adminpanel/build/',
        'basePath' => 'core.adminpanel.build',
        'coreScriptPosition'=>CClientScript::POS_END,
        'defaultScriptFilePosition' =>CClientScript::POS_END,
        'defaultScriptPosition' =>CClientScript::POS_END,
        'position' =>CClientScript::POS_END,
        'js' => array(
            'lsadminpanel.js'
        )
    )

);
