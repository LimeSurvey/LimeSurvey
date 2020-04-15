<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
            'fontawesome',
        )
    ),
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
        'js' => ($debug > 0 ?
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
            'adminsidepanel',
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
            'adminsidepanel',
        )
    ),
    'panelintegration' => array(
        'devBaseUrl' => 'assets/packages/panelintegration/',
        'basePath' => 'core.panelintegration',
        'position' =>CClientScript::POS_END,
        'js' => (
            $debug > 0
             ? array(
                 'build/js/panelintegration.js',
             )
             : array(
                 'build.min/js/panelintegration.js'
             )
         ),
        'css' => array(
            'build.min/css/main.css'
        ),
        'depends' => array(
            'adminbasics'
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
    'adminbasics' => array(
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath' => 'core.adminbasics',
        'position' =>CClientScript::POS_HEAD,
        'js' => array(
            'build/adminbasics'.$minVersion.'.js',
        ),
        'depends' => array(
            'jquery',
            'pjaxbackend',
            'lslog',
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

    'adminbasicjs' => array(
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath' => 'core.adminbasics',
        'position' =>CClientScript::POS_BEGIN,
        'js' => array(
        ),
        'depends' => array(
            'jquery',
            'pjaxbackend',
            'adminbasics'
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

);
