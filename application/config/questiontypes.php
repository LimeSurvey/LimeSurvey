<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * This is only for packages regarding the questiontypes
 * sees vendor.php for third party package
 * or packages.php for core packages
 * @license GPL v3
 * core path is application/core/packages
 *
 * Note: When debug mode, asset manager is turned off by default.
 * To enjoy this feature, add to your package definition a 'devBaseUrl' with the relative url to your package
 *
 */

$debug = $userConfig['config']['debug'] ?? 0;
/* To add more easily min version : config > 2 , seems really an core dev issue to fix bootstrap.js ;) */
$minVersion = ($debug > 0) ? "" : ".min";
/* needed ? @see vendor.php */
if (isset($_GET['isAjax'])) {
    return array();
}
return array(
    /* Ranking question type */
    'question-ranking'=>array(
        'devBaseUrl'  => 'assets/packages/questions/ranking/',
        'basePath' => 'core.questions.ranking',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'css/ranking.css',
        ),
        'js'=>array(
            'scripts/ranking.js',
        ),
        'depends' => array(
            'jquery','jquery-actual',
        )
    ),
    /* numeric slider question : numerci question type with slider */
    'question-numeric-slider'=>array(
        'devBaseUrl'  => 'assets/packages/questions/numeric-slider/',
        'basePath' => 'core.questions.numeric-slider',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'css/numeric-slider.css',
        ),
        'js'=>array(
            'scripts/numeric-slider.js',
        ),
        'depends' => array(
            'bootstrap-slider',
        )
    ),
    /* five point singlechoice slider rating question */
    'question-5pointchoice-slider'=>array(
        'devBaseUrl'  => 'assets/packages/questions/5pointchoice/',
        'basePath' => 'core.questions.5pointchoice',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'css/slider-rating.css',
            'css/emoji.css',
            'css/ss-emoji.css'
        ),
        'js'=>array(
            'scripts/slider-rating.js',
        )
    ),
    /* five point singlechoice star rating question */
    'question-5pointchoice-star'=>array(
        'devBaseUrl'  => 'assets/packages/questions/5pointchoice/',
        'basePath' => 'core.questions.5pointchoice',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'css/star-rating.css',
        ),
        'js'=>array(
            'scripts/star-rating.js',
        )
    ),
    /* file upload question */
    'question-file-upload'=>array(
        'devBaseUrl'  => 'assets/packages/questions/upload/',
        'basePath' => 'core.questions.upload',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'styles/uploader-files.css',
            'styles/uploader.css',
        ),
        'js'=>array(
            'build/uploadquestion'.$minVersion.'.js',
        )
    ),
    /* array-numeric question */
    'question-array-numeric'=>array(
        'devBaseUrl'  => 'assets/packages/questions/arraynumeric/',
        'basePath' => 'core.questions.arraynumeric',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
        ),
        'js'=>array(
            'scripts/array-totalsum'.$minVersion.'.js',
        ),
    ),
    /* array-numeric question */
    'timer-addition'=>array(
        'devBaseUrl'  => 'assets/packages/questions/timer/',
        'basePath' => 'core.questions.timer',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
        ),
        'js'=>array(
            'timer'.$minVersion.'.js',
        ),
    ),
);
