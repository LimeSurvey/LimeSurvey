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
    'adminpanel' => array(
       'devBaseUrl' => 'assets/packages/adminpanel/',
       'basePath' => 'core.adminpanel',
       'js' => array(
           'build/lsadminpanel'.(($debug > 0) ? '' : '.min').'.js',
           'build/surveysettings'.$minVersion.'.js',
           //'build/hammer'.$minVersion.'.js'
       ),
       'css' => array(
           'build/lsadminpanel'.$minVersion.'.css'
       ),
       'depends' => array(
           'adminbasics'
       )
    ),
    'textelements' => array(
        'devBaseUrl' => 'assets/packages/textelements/',
        'basePath' => 'core.textelements',
        'position' =>CClientScript::POS_END,
        'js' => array(
            'build/lstextelements'.(($debug > 0) ? '' : '.min').'.js'
        ),
        'css' => array(
            'build/lstextelements'.$minVersion.'.css'
        ),
        'depends' => array(
            'adminbasics'
        )
    ),
    'datasectextelements' => array(
        'devBaseUrl' => 'assets/packages/datasecuritysettings/',
        'basePath' => 'core.datasecuritysettings',
        'position' =>CClientScript::POS_END,
        'js' => array(
            'build/datasecuritysettings'.(($debug > 0) ? '' : '.min').'.js'
        ),
        'css' => array(
            'build/datasecuritysettings'.$minVersion.'.css'
        ),
        'depends' => array(
            'adminbasics'
        )
    ),
    'questioneditor' => array(
        'devBaseUrl' => 'assets/packages/questioneditor/',
        'basePath' => 'core.questioneditor',
        'position' =>CClientScript::POS_END,
        'js' => array(
            'build/lsquestioneditor'.(($debug > 0) ? '' : '.min').'.js'
        ),
        'css' => array(
            'build/lsquestioneditor'.$minVersion.'.css'
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
    'lshelp' => array(
        'devBaseUrl' => 'assets/packages/lshelp/',
        'basePath' => 'core.lshelp',
        'position' =>CClientScript::POS_BEGIN,
        'js' => array(
            'build/lshelper'.$minVersion.'.js',
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
        'css' => array(
            // 'css/lime-admin-common.css',
            // 'css/jcarousel.responsive.css',
            // 'css/attributeMap.css',
            // 'css/attributeMapToken.css',
            // 'css/displayParticipants.css',
            'build/adminbasics'.(($debug > 0) ? '' : '.min').'.css',
        ),
        'js' => array(
            'build/adminbasics'.(($debug > 0) ? '' : '.min').'.js',
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
        'position' =>CClientScript::POS_BEGIN,
        'css' => array(
            // 'css/rtl/adminstyle-rtl.css',
            // 'css/rtl/lime-admin-common-rtl.css',
            // 'css/rtl/jcarousel.responsive-rtl.css',
            // 'css/rtl/attributeMap-rtl.css',
            // 'css/rtl/attributeMapToken-rtl.css',
            // 'css/rtl/displayParticipants-rtl.css',
            'build/adminbasics.rtl'.(($debug > 0) ? '' : '.min').'.css',
        ),
        'js' => array(
            'build/adminbasics.js',
        ),
        'depends' => array(
            'jquery',
            'pjaxbackend',
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
    /* Replace bbq package from Yii core to set position */
    'bbq'=>array(
        'position' => CClientScript::POS_BEGIN,
        'js'=>array(YII_DEBUG ? 'jquery.ba-bbq.js' : 'jquery.ba-bbq.min.js'),
        'depends'=>array('jquery'),
    ),
);
