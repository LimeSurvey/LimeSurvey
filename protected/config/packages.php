<?php
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
        'basePath' => 'bower.jquery.dist',
        'js' => [
            'jquery.min.js'
        ]
    ],
    'jqgrid' => [
        'basePath' => 'bower.jqgrid',
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
        'basePath' => 'bower.jqgrid.plugins',
        'js' => [
            'grid.addons.js'
        ],
        'depends' => [
            'jqgrid'
        ]

    ],
    'jqueryui' => [
        'basePath' => 'bower.jquery-ui',
        'js' => [
            'jquery-ui.min.js'
        ],
        'depends' => [
            'jquery',
        ]
    ],


    'qTip2' => [
        'basePath' => 'bower.qtip2',
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
        'basePath' => 'bower.tablesorter',
        'js' => [
            'jquery.tablesorter.min.js'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'ace' => [
        'basePath' => 'bower.ace-builds.src-min',
        'js' => [
            'ace.js'
        ],
    ],
     'jquery-ace' => [
        'basePath' => 'bower.jquery-ace',
        'js' => [
            'jquery.ace.js',
        ],
        'depends' => [
            'jquery',
            'ace'
        ]
     ],
    'jquery-actual' => [
        'basePath' => 'bower',
        'js' => [
            'jquery.actual/jquery.actual.min.js'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'jquery-touch-punch' => [
        'basePath' => 'bower.jqueryui-touch-punch',
        'js' => [
            'jquery.ui.touch-punch.min.js'
        ],
        'depends' => [
            'jqueryui'
        ]
    ],
    'jqueryui-timepicker' => [
        'basePath' => 'bower.jqueryui-timepicker-addon.dist',
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
        'basePath' => 'bower.leaflet.dist',
        'js' => [
            'leaflet.js'
        ],
        'css' => [
            'leaflet.css'
        ],
    ],
    'SurveyRuntime' => [
        'baseUrl' => 'scripts/',
        'js' => [
            'survey_runtime.js'
        ],
        'depends' => [
            'jquery-touch-punch',
            'ExpressionManager'
        ]
    ],
    'bootstrap-notify' => [
        'basePath' => 'bower.remarkable-bootstrap-notify',
        'js' => [
            'bootstrap-notify.min.js'
        ]
    ],
    'papaparse' => [
        'basePath' => 'bower.papaparse',
        'js' => [
            'papaparse.js'
        ]
    ],
    'ajaxq' => [
        'basePath' => 'bower.ajaxq',
        'js' => [
            'ajaxq.js'
        ],
        'depends' => [
            'jquery'
        ]
    ],
    'handsontable' => [
        'basePath' => 'bower.handsontable',
        'js' => [
            'dist/handsontable.full.min.js',
            'plugins/jqueryHandsontable.js'
        ],
        'css' => [
            'dist/handsontable.full.css',
            'plugins/bootstrap/handsontable.bootstrap.css'
        ],
        'depends' => [
            'jquery'
        ]
    ]
];
