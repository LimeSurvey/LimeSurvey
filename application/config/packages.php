<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/** @var array $userConfig */


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
$debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
/* To add more easily min version : config > 2 , seems really an core dev issue to fix bootstrap.js ;) */
$minVersion = ($debug > 0) ? "" : ".min";
/* needed ? @see third_party.php */
if (isset($_GET['isAjax'])) {
    return array();
}
return array(
    /* expressions manager function and system */
    'expressions'=>array(
        'devBaseUrl'  => 'assets/packages/expressions/',
        'basePath' => 'core.expressions',
        'js'=>array(
            'em_javascript.js',
        ),
        'depends' => array(
            'jquery',
            'moment', // Used by LEMval function
            'decimalcustom', // Use by fixnum_checkconditions
        )
    ),
    /* For public template functionnality */
    'limesurvey-public'=>array(
        'devBaseUrl'  => 'assets/packages/limesurvey/',
        'basePath' => 'core.limesurvey', /* public part only : rename directory ? */
        'css'=> array(
            'survey.css',
        ),
        'js'=>array(
            'survey.js',
        ),
        'depends' => array(
            'jquery',
            'expressions',
            'fontawesome',
        )
    ),

    // TODO: Delete this? See #15108.
    'embeddables' => array(
        'devBaseUrl'  => 'assets/packages/embeddables/',
        'basePath' => 'core.embeddables',
        'position' =>CClientScript::POS_END,
        'css'=> array(
            'build/embeddables'.$minVersion.'.css',
        ),
        'js'=>array(
            'build/embeddables'.$minVersion.'.js',
        ),
        'depends' => array(
            'jquery',
        )
    ),

    'themeoptions-core' => [
        'devBaseUrl'  => 'assets/packages/themeoptions-core/',
        'basePath' => 'core.themeoptions-core',
        'position' =>CClientScript::POS_END,
        'css'=> [
            'themeoptions-core.css',
        ],
        'js'=>[
            'themeoptions-core.js',
        ],
        'depends' => [
            'jquery',
            'bootstrap'
        ]
    ],
    /* For public template extended functionnality (based on default template) */
    'template-core'=>array(
        'devBaseUrl'  => 'assets/packages/template-core/',
        'basePath' => 'core.template-core',
        'css'=> array(
            'template-core.css',
        ),
        'js'=>array(
            'template-core.js',
        ),
        'depends' => array(
            'limesurvey-public',
            'embeddables'
        )
    ),
    'template-core-ltr'=>array( /* complement for ltr */
        'devBaseUrl'  => 'assets/packages/template-core/',
        'basePath' => 'core.template-core',
        'css'=> array(
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        ),
        'depends' => array(
            'template-core',
        )
    ),
    'template-core-rtl'=>array( /* Same but for rtl */
        'devBaseUrl'  => 'assets/packages/template-core/',
        'basePath' => 'core.template-core',
        'css'=> array(
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox-rtl.css',
        ),
        'depends' => array(
            'template-core',
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
    'ckeditor' => array(
        'devBaseUrl'  => 'assets/packages/ckeditor',
        'basePath' => 'core.ckeditor',
        'js' => array(
            'ckeditor.js',
            'config.js',
        ),
        'depends' => array(
            'adminbasics',
        ),
    ),
    'ckeditoradditions' => array(
        'devBaseUrl'  => 'assets/packages/ckeditoradditions/',
        'basePath' => 'core.ckeditoradditions',
        'js' => array(
            'ckeditoradditions.js',
        ),
        'depends' => array(
            'ckeditor'
        )
    ),
    'modaleditor' => array(
        'devBaseUrl' => 'assets/packages/modaleditor/',
        'basePath' => 'core.modaleditor',
        'position' =>CClientScript::POS_BEGIN,
        'js' => array(
            'js/modaleditor.js',
        ),
        'depends' => array(
            'adminbasics',
            'ckeditor',
        )
    ),
    'pjax' => array(
        'devBaseUrl' => 'assets/packages/pjax/',
        'basePath' => 'core.pjax',
        'js' => array(
            'pjax.js',
        ),
        'depends' => array(
            'lslog',
        )
    ),
    'pjaxbackend' => array(
        'devBaseUrl' => 'assets/packages/pjax/',
        'basePath' => 'core.pjax',
        'js' => (
            $debug > 0 ?
            array(
                'pjax.js',
                'loadPjax.js'
            )
            : array(
                'min/pjax.combined.min.js',
            )
        ),
        'depends' => array(
            'lslog',
        )
    ),
    'globalsidepanel' => array(
       'devBaseUrl' => 'assets/packages/globalsidepanel/',
       'basePath' => 'core.globalsidepanel',
       'position' =>CClientScript::POS_END,
       'js' => (
           $debug > 0
            ? array(
                'build/js/globalsidepanel.js',
            )
            : array(
                'build.min/js/globalsidepanel.js'
            )
        ),
       'css' => array(
           'build.min/css/main.css'
       ),
       'depends' => array(
           'adminbasics'
       )
    ),
    'adminsidepanel' => array(
       'devBaseUrl' => 'assets/packages/adminsidepanel/',
       'basePath' => 'core.adminsidepanel',
       'position' =>CClientScript::POS_HEAD,
       'js' => (
           $debug > 0
            ? array(
                'build/js/adminsidepanel.js',
            )
            : array(
                'build.min/js/adminsidepanel.js'
            )
        ),
        'depends' => array(
            'adminbasics',
        )
    ),
    'adminsidepanelltr' => array(
       'devBaseUrl' => 'assets/packages/adminsidepanel/',
       'basePath' => 'core.adminsidepanel',
       'css' => (
           $debug > 0
            ? array(
                'build/css/adminsidepanel.css',
            )
            : array(
                'build.min/css/adminsidepanel.css'
            )
        ),
        'depends' => array(
            'adminbasics',
        )
    ),
    'adminsidepanelrtl' => array(
       'devBaseUrl' => 'assets/packages/adminsidepanel/',
       'basePath' => 'core.adminsidepanel',
       'css' => (
           $debug > 0
            ? array(
                'build/css/adminsidepanel.rtl.css',
            )
            : array(
                'build.min/css/adminsidepanel.rtl.css'
            )
        ),
        'depends' => array(
            'adminbasics',
        )
    ),
    
    'lstutorial' => array(
        'devBaseUrl' => 'assets/packages/lstutorial/',
        'basePath' => 'core.lstutorial',
        'position' =>CClientScript::POS_END,
        'js' => array(
            'build/lstutorial'.$minVersion.'.js',
        ),
        'css' => array(
            'build/lstutorial.css'
        ),
        'depends' => array(
            'bootstrap',
            'adminbasics',
        )
    ),
    'lslog' => array(
        'devBaseUrl' => 'assets/packages/lslog/',
        'basePath' => 'core.lslog',
        'js' => array(
            // 'build/lslog'.$minVersion.'.js',
            'build/lslog.js',
        )
    ),
    'panelboxes' => array(
        'devBaseUrl' => 'assets/packages/panelboxes/',
        'basePath' => 'core.panelboxes',
        'css' => array(
            'build/panelboxes'.$minVersion.'.css',
        )
    ),
    'adminbasicsrtl' => array(
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath' => 'core.adminbasics',
        'css' => array(
            'build/adminbasics.rtl'.$minVersion.'.css'
        ),
        'depends' => array(
            'adminbasics'
        )
    ),
    'adminbasicsltr' => array(
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath' => 'core.adminbasics',
        'css' => array(
            'build/adminbasics'.$minVersion.'.css'
        ),
        'depends' => array(
            'adminbasics'
        )
    ),

    'adminbasics' => array(
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath' => 'core.adminbasics',
        'position' =>CClientScript::POS_BEGIN,
        'js' => array(
            'build/adminbasics'.$minVersion.'.js',
        ),
        'depends' => array(
            'jquery',
            'pjaxbackend',
            'lslog'
        )
    ),

    'surveymenufunctions' => array(
        'devBaseUrl' => 'assets/packages/surveymenufunctions/',
        'basePath' => 'core.surveymenufunctions',
        'js' => array(
            'surveymenufunctionswrapper'.$minVersion.'.js',
            'surveymenuentryfunctions'.$minVersion.'.js',
        ),
        'depends' => array(
            'adminbasics',
        )
    ),

    'surveysummary' => array(
        'devBaseUrl' => 'assets/packages/surveysummary/',
        'basePath' => 'core.surveysummary',
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            'surveysummary.css'
        ),
        'js' => array(
            'surveysummary.js',
            'qrcode.js'
        ),
        'depends' => array(
            'adminbasics',
        )
    ),

    'permissionroles' => array(
        'devBaseUrl' => 'assets/packages/permissionroles/',
        'basePath' => 'core.permissionroles',
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            'css/permissionroles.css'
        ),
        'js' => array(
            'js/permissionroles.js',
        ),
        'depends' => array(
            'adminbasics',
        )
    ),

    'usermanagement' => array(
        'devBaseUrl' => 'assets/packages/usermanagement/',
        'basePath' => 'core.usermanagement',
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            'css/usermanagement.css'
        ),
        'js' => array(
            'js/usermanagement.js',
        ),
        'depends' => array(
            'adminbasics',
        )
    ),

    'printable' => array(
        'devBaseUrl' => 'assets/packages/printable/',
        'basePath' => 'core.printable',
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            'printable.css'
        ),
        'js' => array(
            'printable.js',
        ),
        'depends' => array(
            'adminbasics',
        )
    ),
    /* An empty package to be extended for EM (after core expressions) */
    'expression-extend' =>array(
        'depends' => array(
            'expressions',
        )
    ),
    'decimalcustom' => array(
        'devBaseUrl' => 'assets/packages/decimalcustom/',
        'basePath' => 'core.decimalcustom',
        'position' =>CClientScript::POS_BEGIN,
        'js' => array(
            'decimalcustom.js',
        ),
        'depends' => array(
            'decimal',
        )
    ),
    'expressionscript' => array(
        'devBaseUrl' => 'assets/packages/expressionscript/',
        'basePath' => 'core.expressionscript',
        'position' =>CClientScript::POS_END,
        'js' => array(
            'expression.js',
        ),
        'css' => array(
            'expressions.css'
        )  
    ),    
    /* Replace bbq package from Yii core to set position */
    'bbq'=>array(
        'position' => CClientScript::POS_BEGIN,
        'js'=>array(YII_DEBUG ? 'jquery.ba-bbq.js' : 'jquery.ba-bbq.min.js'),
        'depends'=>array('jquery'),
    ),
    // Restored old emailtemplates package (pre Vue)
    'emailtemplates' => array(
        'devBaseUrl' => 'assets/packages/emailtemplates/',
        'basePath' => 'core.emailtemplates',
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            'popup-dialog.css'
        ),
        'js' => array(
            'emailtemplates'.$minVersion.'.js',
        ),
        'depends' => array(
            'adminbasics',
        )
    ),
);
