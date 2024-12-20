<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/** @var array $userConfig */


/**
 * Core packages , no vendor
 * sees vendor.php for third party package
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
    return [];
}
return [
    /* expressions manager function and system */
    'expressions'       => [
        'devBaseUrl' => 'assets/packages/expressions/',
        'basePath'   => 'core.expressions',
        'js'         => [
            'em_javascript.js',
        ],
        'depends'    => [
            'jquery-migrate',
            'moment', // Used by LEMval function
            'decimalcustom', // Use by fixnum_checkconditions
        ]
    ],
    /* For public template functionnality */
    'limesurvey-public' => [
        'devBaseUrl' => 'assets/packages/limesurvey/',
        'basePath'   => 'core.limesurvey', /* public part only : rename directory ? */
        'css'        => [
            'survey.css',
        ],
        'js'         => [
            'survey.js',
        ],
        'depends'    => [
            'jquery',
            'expressions',
            'fontawesome',
            'remix',
        ]
    ],

    // TODO: Delete this? See #15108.
    'embeddables'       => [
        'devBaseUrl' => 'assets/packages/embeddables/',
        'basePath'   => 'core.embeddables',
        'position'   => CClientScript::POS_END,
        'css'        => [
            'build/embeddables' . $minVersion . '.css',
        ],
        'js'         => [
            'build/embeddables' . $minVersion . '.js',
        ],
        'depends'    => [
            'jquery',
        ]
    ],

    'themeoptions-core' => [
        'devBaseUrl' => 'assets/packages/themeoptions-core/',
        'basePath'   => 'core.themeoptions-core',
        'position'   => CClientScript::POS_END,
        'css'        => [
            'themeoptions-core.css',
        ],
        'js'         => [
            'themeoptions-core.js',
        ],
        'depends'    => [
            'jquery',
//            'bootstrap'
        ]
    ],
    /* For public template extended functionnality (based on default template) */
    'template-core'     => [
        'devBaseUrl' => 'assets/packages/template-core/',
        'basePath'   => 'core.template-core',
        'css'        => [
            'template-core.css',
        ],
        'js'         => [
            'template-core.js',
        ],
        'depends'    => [
            'limesurvey-public',
            'embeddables'
        ]
    ],
    'template-core-ltr' => [ /* complement for ltr */
        'devBaseUrl' => 'assets/packages/template-core/',
        'basePath'   => 'core.template-core',
        'css'        => [
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        ],
        'depends'    => [
            'template-core',
        ]
    ],
    'template-core-rtl' => [ /* Same but for rtl */
        'devBaseUrl' => 'assets/packages/template-core/',
        'basePath'   => 'core.template-core',
        'css'        => [
            'awesome-bootstrap-checkbox/awesome-bootstrap-checkbox-rtl.css',
        ],
        'depends'    => [
            'template-core',
        ]
    ],

    'ckeditor'          => [
        'devBaseUrl' => 'assets/packages/ckeditor',
        'basePath'   => 'core.ckeditor',
        'js'         => [
            'ckeditor.js',
            'config.js',
        ],
        'depends'    => [
            'adminbasics',
        ],
    ],
    'ckeditoradditions' => [
        'devBaseUrl' => 'assets/packages/ckeditoradditions/',
        'basePath'   => 'core.ckeditoradditions',
        'js'         => [
            'ckeditoradditions.js',
        ],
        'depends'    => [
            'ckeditor'
        ]
    ],
    'modaleditor'       => [
        'devBaseUrl' => 'assets/packages/modaleditor/',
        'basePath'   => 'core.modaleditor',
        'position'   => CClientScript::POS_BEGIN,
        'js'         => [
            'js/modaleditor.js',
        ],
        'depends'    => [
            'adminbasics',
            'ckeditor',
        ]
    ],
    'pjax'              => [
        'devBaseUrl' => 'assets/packages/pjax/',
        'basePath'   => 'core.pjax',
        'js'         => [
            'pjax.js',
        ],
        'depends'    => [
            'lslog',
        ]
    ],
    'pjaxbackend'       => [
        'devBaseUrl' => 'assets/packages/pjax/',
        'basePath'   => 'core.pjax',
        'js'         => (
        $debug > 0 ?
            [
                'pjax.js',
                'loadPjax.js'
            ]
            : [
            'min/pjax.combined.min.js',
        ]
        ),
        'depends'    => [
            'lslog',
        ]
    ],
    'globalsidepanel'   => [
        'devBaseUrl' => 'assets/packages/globalsidepanel/',
        'basePath'   => 'core.globalsidepanel',
        'position'   => CClientScript::POS_END,
        'js'         => (
        $debug > 0
            ? [
            'build/js/globalsidepanel.js',
        ]
            : [
            'build.min/js/globalsidepanel.js'
        ]
        ),
        'css'        => [
            'build.min/css/main.css'
        ],
        'depends'    => [
            'adminbasics'
        ]
    ],
    'adminsidepanel'    => [
        'devBaseUrl' => 'assets/packages/adminsidepanel/',
        'basePath'   => 'core.adminsidepanel',
        'position'   => CClientScript::POS_HEAD,
        'js'         => (
        $debug > 0
            ? [
            'build/js/adminsidepanel.js',
        ]
            : [
            'build.min/js/adminsidepanel.js'
        ]
        ),
        'depends'    => [
            'adminbasics',
        ]
    ],
    'adminsidepanelltr' => [
        'devBaseUrl' => 'assets/packages/adminsidepanel/',
        'basePath'   => 'core.adminsidepanel',
        'css'        => (
        $debug > 0
            ? [
            'build/css/adminsidepanel.css',
        ]
            : [
            'build.min/css/adminsidepanel.css'
        ]
        ),
        'depends'    => [
            'adminbasics',
        ]
    ],
    'adminsidepanelrtl' => [
        'devBaseUrl' => 'assets/packages/adminsidepanel/',
        'basePath'   => 'core.adminsidepanel',
        'css'        => (
        $debug > 0
            ? [
            'build/css/adminsidepanel.rtl.css',
        ]
            : [
            'build.min/css/adminsidepanel.rtl.css'
        ]
        ),
        'depends'    => [
            'adminbasics',
        ]
    ],

    'lstutorial' => [
        'devBaseUrl' => 'assets/packages/lstutorial/',
        'basePath'   => 'core.lstutorial',
        'position'   => CClientScript::POS_END,
        'js'         => [
            'build/lstutorial' . $minVersion . '.js',
        ],
        'css'        => [
            'build/lstutorial.css'
        ],
        'depends'    => [
//            'bootstrap',
            'adminbasics',
        ]
    ],
    'lslog'      => [
        'devBaseUrl' => 'assets/packages/lslog/',
        'basePath'   => 'core.lslog',
        'js'         => [
            // 'build/lslog'.$minVersion.'.js',
            'build/lslog.js',
        ]
    ],
    'panelboxes' => [
        'devBaseUrl' => 'assets/packages/panelboxes/',
        'basePath'   => 'core.panelboxes',
        'css'        => [
            //'build/panelboxes'.$minVersion.'.css',
        ]
    ],

    'adminbasics' => [
        'devBaseUrl' => 'assets/packages/adminbasics/',
        'basePath'   => 'core.adminbasics',
        'position'   => CClientScript::POS_BEGIN,
        'js'         => [
            'build/adminbasics' . $minVersion . '.js',
        ],
        'depends'    => [
            'jquery',
            'pjaxbackend',
            'lslog',
            'select2-bootstrap'
        ]
    ],

    'surveymenufunctions' => [
        'devBaseUrl' => 'assets/packages/surveymenufunctions/',
        'basePath'   => 'core.surveymenufunctions',
        'js'         => [
            'surveymenufunctionswrapper' . $minVersion . '.js',
            'surveymenuentryfunctions' . $minVersion . '.js',
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],

    'surveysummary' => [
        'devBaseUrl' => 'assets/packages/surveysummary/',
        'basePath'   => 'core.surveysummary',
        'position'   => CClientScript::POS_BEGIN,
        'css'        => [
            'surveysummary.css'
        ],
        'js'         => [
            'surveysummary.js',
            'qrcode.js'
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],

    'permissionroles' => [
        'devBaseUrl' => 'assets/packages/permissionroles/',
        'basePath'   => 'core.permissionroles',
        'position'   => CClientScript::POS_BEGIN,
        'css'        => [
            'css/permissionroles.css'
        ],
        'js'         => [
            'js/permissionroles.js',
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],

    'usermanagement' => [
        'devBaseUrl' => 'assets/packages/usermanagement/',
        'basePath'   => 'core.usermanagement',
        'position'   => CClientScript::POS_BEGIN,
        'css'        => [
            'css/usermanagement.css'
        ],
        'js'         => [
            'js/usermanagement.js',
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],

    'printable'         => [
        'devBaseUrl' => 'assets/packages/printable/',
        'basePath'   => 'core.printable',
        'position'   => CClientScript::POS_BEGIN,
        'css'        => [
            'printable.css'
        ],
        'js'         => [
            'printable.js',
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],
    /* An empty package to be extended for EM (after core expressions) */
    'expression-extend' => [
        'depends' => [
            'expressions',
        ]
    ],
    'decimalcustom'     => [
        'devBaseUrl' => 'assets/packages/decimalcustom/',
        'basePath'   => 'core.decimalcustom',
        'position'   => CClientScript::POS_BEGIN,
        'js'         => [
            'decimalcustom.js',
        ],
        'depends'    => [
            'decimal',
        ]
    ],
    'expressionscript'  => [
        'devBaseUrl' => 'assets/packages/expressionscript/',
        'basePath'   => 'core.expressionscript',
        'position'   => CClientScript::POS_END,
        'js'         => [
            'expression.js',
        ],
        'css'        => [
            'expressions.css'
        ]
    ],
    /* Replace bbq package from Yii core to set position */
    'bbq'               => [
        'position' => CClientScript::POS_BEGIN,
        'js'       => [YII_DEBUG ? 'jquery.ba-bbq.js' : 'jquery.ba-bbq.min.js'],
        'depends'  => ['jquery'],
    ],
    // Restored old emailtemplates package (pre Vue)
    'emailtemplates'    => [
        'devBaseUrl' => 'assets/packages/emailtemplates/',
        'basePath'   => 'core.emailtemplates',
        'position'   => CClientScript::POS_BEGIN,
        'css'        => [
            'popup-dialog.css'
        ],
        'js'         => [
            'emailtemplates.js',
        ],
        'depends'    => [
            'adminbasics',
        ]
    ],
    // jQuery Ace
    'jquery-ace'        => [
        'devBaseUrl' => 'assets/packages/jquery-ace/',
        'basePath'   => 'core.jquery-ace',
        'position'   => CClientScript::POS_BEGIN,
        'js'         => [
            'jquery.ace.js',
        ],
        'depends'    => [
            'jquery',
        ]
    ],
    // SortableJS
    'sortablejs'        => [
        'devBaseUrl' => 'assets/packages/sortablejs/',
        'basePath'   => 'core.sortablejs',
        'position'   => CClientScript::POS_BEGIN,
        'js'         => [
            'sortable.min.js',
        ],
        'depends'    => [
            'jquery',
            'jquery-actual',
        ]
    ]
];
