<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// This should use app->params[bower-asset] but that's not yet avialable at this point.
$bowerAssetPath = 'application/vendor/bower-asset';
/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */
return [
    'ExpressionManager' => [
        'baseUrl' => 'scripts/expressions',
        'js' => [
            'em_javascript.js',
            'ExpressionManager.js'
        ],
        'depends' => ['jquery']
    ],
    'jquery' => [
        'baseUrl' => $bowerAssetPath . '/jquery/dist',
        'js' => [
            'jquery.min.js'
        ]
    ],
    'jqgrid' => [
        'baseUrl' => $bowerAssetPath . '/jqgrid/',
        'js' => [
            'js/minified/jquery.jqGrid.min.js',
            'js/minified/i18n/grid.locale-en.js',
            'plugins/jquery.searchFilter.js'
        ],
        'css' => [
            'css/ui.jqgrid.css'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'jqgrid.addons' => [
        'baseUrl' => $bowerAssetPath . '/jqgrid/plugins/',
        'js' => [
            'grid.addons.js'
        ],
        'depends' => [
            'jqgrid'
        ]

    ],
    'jqueryui' => [
        'baseUrl' => $bowerAssetPath . '/jquery-ui/',
        'js' => [
            'jquery-ui.min.js'
        ],
        'depends' => [
            'jquery',
        ]
    ],


    'qTip2' => [
        'baseUrl' => $bowerAssetPath. '/qtip2',
        'js' => [
            'jquery.qtip.min.js'
        ],
        'css' => [
            'jquery.qtip.min.css'
        ],
        'depends' => [
            'jquery'
        ]

    ],
    'jquery-tablesorter' => [
        'baseUrl' => $bowerAssetPath . '/tablesorter',
        'js' => [
            'jquery.tablesorter.min.js'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'ace' => [
        'baseUrl' => $bowerAssetPath . '/ace-builds-bower-patched/src',
        'js' => [
            'ace.js'
        ],
    ],
     'jquery-ace' => [
        'baseUrl' => 'third_party/jquery-ace',
        'js' => [
            'jquery.ace.js',
        ],
        'depends' => [
            'jquery',
            'ace'
        ]
     ],
    'jquery-actual' => [
        'baseUrl' => $bowerAssetPath . '/jquery.actual/',
        'js' => [
            'jquery.actual.min.js'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'jquery-touch-punch' => [
        'baseUrl' => $bowerAssetPath . '/jqueryui-touch-punch/',
        'js' => [
            'jquery.ui.touch-punch.min.js'
        ],
        'depends' => [
            'jqueryui'
        ]
    ],
    'jqueryui-timepicker' => [
        'baseUrl' => $bowerAssetPath . '/jqueryui-timepicker-addon/dist/',
        'js' => [
            'jquery-ui-timepicker-addon.js'
        ],
        'css' => [
            'jquery-ui-timepicker-addon.css'
        ],
        'depends' => [
            'jqueryui'
        ]
    ],
    'jquery-price-format' => array(
        'baseUrl' => 'third_party/jquery-price-format/',
        'js' => array(
            'jquery_price_format.js'
        ),
    ),
    'leaflet' => [
        'baseUrl' => $bowerAssetPath . '/leaflet/dist/',
        'js' => [
            'leaflet.js'
        ],
        'css' => [
            'leaflet.css'
        ],
    ],
];
