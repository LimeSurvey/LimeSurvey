<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/** @var array $userConfig */

/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */
/* Tag if debug is set : debug is set in user config file and this file is directly required in internal.php where $userConfig var arry is set */
/* This allow us to use minified version according to debug */
$debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
/* To add more easily min version : config > 2 , seems really an core dev issue to fix bootstrap.js ;) */
$minVersion = ($debug > 0) ? "" : ".min";
$minFolder = ($debug > 0) ? "/dev" : "/min";

/* Please : comment the reason, mantis bug link: ajax don't need any package if i don't make error */
/* Ajax must renderPartial (better : always return json) and never render and don't registerScript (IMHO) / Shnoulle on 2016-11-16 */
if (isset($_GET['isAjax'])) {
    return array();
}

return array(

    // jQuery
    'jquery' => array(
        'devBaseUrl' => 'node_modules/jquery/dist',
        'basePath' => 'node_modules.jquery.dist',
        'position' => CClientScript::POS_HEAD,
        'js' => array(
            'jquery' . $minVersion . '.js'
        ),
    ),

    // Bootstrap
    // This package replace the Yiistrap register() function
    // Then instead of using the composer dependency system for themes
    // We can use the package dependency system (easier for now)
    'bootstrap' => array(
        'devBaseUrl' => 'assets/bootstrap_5/build',
        'basePath' => 'bootstrap.build',
        'css' => array(
            'css/bootstrap_5.min.css',
        ),
        'js' => array(
            'js/bootstrap_5.min.js',
        ),
    ),
    'bootstrap-rtl' => array(
        'devBaseUrl' => 'assets/bootstrap_5/',
        'basePath' => 'bootstrap.build',
        'css' => array(
            'css/bootstrap_5-rtl.min.css',
        ),
        'js' => array(
            'js/bootstrap_5.min.js',
        ),
    ),
    'bootstrap-css' => [
        'devBaseUrl' => 'assets/bootstrap_5/build',
        'basePath'   => 'bootstrap.build',
        'css'         => [
            'css/bootstrap_5.min.css',
        ]
    ],
    'bootstrap-js' => [
        'devBaseUrl' => 'assets/bootstrap_5/build',
        'basePath'   => 'bootstrap.build',
        'js'         => [
            'js/bootstrap_5.min.js',
        ]
    ],
    'bootstrap-css-rtl' => [
        'devBaseUrl' => 'assets/bootstrap_5/build',
        'basePath'   => 'bootstrap.build',
        'css'         => [
            'css/bootstrap_5-rtl.min.css',
        ]
    ],

    // bootstrap-slider : for multinumeric with slider
    'bootstrap-slider' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/slider',
        'basePath' => 'core.bootstrap.plugins.slider',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/bootstrap-slider' . $minVersion . '.css'
        ),
        'js' => array(
            'bootstrap-slider' . $minVersion . '.js'
        ),
        'depends' => array(
            'jquery',
//            'bootstrap'
        )
    ),

    // Bootstrap Multiselect
    'bootstrap-multiselect' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/multiselect',
        'basePath' => 'core.bootstrap.plugins.multiselect',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'js/bootstrap-multiselect.js',
        ),
        'css' => array(
            'css/bootstrap-multiselect.css',
        ),
        'depends' => array(
            'jquery',
//            'bootstrap'
        )
    ),

    // Bootstrap select2
    'select2' => array(
        'devBaseUrl' => 'node_modules/select2/dist',
        'basePath' => 'node_modules.select2.dist',
        'js' => array(
            'js/select2.full' . $minVersion . '.js'
        ),
        'css' => array(
            'css/select2' . $minVersion . '.css',
        ),
        'depends' => array(
            'jquery',
//            'bootstrap'
        )
    ),

    'select2-bootstrap' => array(
        'devBaseUrl' => 'node_modules/select2-bootstrap-5-theme/dist',
        'basePath' => 'node_modules.select2-bootstrap-5-theme.dist',
        'css' => array(
            'select2-bootstrap-5-theme' . $minVersion . '.css',
        ),
        'depends' => array(
            'select2'
        )
    ),

    'bootstrap-datetimepicker' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/datetimepicker/build',
        'basePath' => 'core.bootstrap.plugins.datetimepicker.build',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/bootstrap-datetimepicker' . $minVersion . '.css'
        ),
        'js' => array(
            'js/bootstrap-datetimepicker.min.js'
        ),
        'depends' => array(
            'jquery',
//            'bootstrap',
            'moment'
        )
    ),

    'bootstrap-switch' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/switch/',
        'basePath' => 'core.bootstrap.plugins.switch',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/bootstrap-switch' . $minVersion . '.css'
        ),
        'js' => array(
            'js/bootstrap-switch' . $minVersion . '.js'
        ),
        'depends' => array(
            'jquery',
//            'bootstrap',
            'moment'
        )
    ),

    // jQuery UI
    'jqueryui' => array(
        'devBaseUrl' => 'node_modules/jquery-ui-dist',
        'basePath' => 'node_modules.jquery-ui-dist',
        'position' => CClientScript::POS_HEAD,
        'js' => array(
            'jquery-ui' . $minVersion . '.js',
        ),
        'css' => array(
            'jquery-ui' . $minVersion . '.css', /* else autocomplete or other broken */
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jQuery migrate
    'jquery-migrate' => array(
        'devBaseUrl' => 'node_modules/jquery-migrate/dist',
        'basePath' => 'node_modules.jquery-migrate.dist',
        'position' => CClientScript::POS_HEAD,
        'js' => array(
            'jquery-migrate' . $minVersion . '.js',
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jQuery Cookie
    'js-cookie' => array(
        'devBaseUrl' => 'node_modules/js-cookie/dist',
        'basePath' => 'node_modules.js-cookie.dist',
        'js' => array(
            'js.cookie' . $minVersion . '.js'
        )
    ),

    // jQuery json
    'jquery-json' => array(
        'basePath' => 'vendor.jquery-json',
        'js' => array(
            'jquery.json-2.4.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery Table Sorter
    'jquery-tablesorter' => array(
        'basePath' => 'node_modules.tablesorter.dist.js',
        'js' => array(
            'jquery.tablesorter' . $minVersion . '.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery NestedSortable
    'jquery-nestedSortable' => array(
        'devBaseUrl' => 'node_modules/nestedSortable',
        'basePath' => 'node_modules.nestedSortable',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'jquery.mjs.nestedSortable.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // Ace
    'ace' => array(
        'devBaseUrl' => 'node_modules/ace-builds',
        'basePath' => 'node_modules.ace-builds',
        'position' => CClientScript::POS_BEGIN,
        'js' => ($debug > 0) ? array("/src/ace.js") : array("/src-min/ace.js"),
        'depends' => array(
            'jquery-ace'
        )
    ),



    // jQuery touch punch : seems uneended now ?
    'jquery-touch-punch' => array(
        'basePath' => 'node_modules.jquery-ui-touch-punch',
        'js' => array(
            'jquery.ui.touch-punch' . $minVersion . '.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // Decimal.js calculate in js
    'decimal' => array(
        'position' => CClientScript::POS_BEGIN,
        'basePath' => 'node_modules_decimal',
        'js' => array(
            'decimal.js'
        ),
        'depends' => array(
        )
    ),

    // Moment.js use real simple dateTime modification
    'moment' => array(
        'devBaseUrl' => 'node_modules/moment/min',
        'basePath' => 'node_modules.moment.min',
        'js' => array(
            'moment-with-locales' . $minVersion . '.js'
        ),
        'depends' => array(
        )
    ),

    // leaflet, needed for short text question with map (OSM)

    'jsuri' => array(
        'basePath' => 'node_modules.jsuri',
        'js' => array(
            'Uri.js'
        ),
    ),

    'jquery-datatable' => array(
        'basePath' => 'node_modules_datatables',
        'position' => CClientScript::POS_BEGIN,

        'js' => array(
            'js/jquery.dataTables' . $minVersion . '.js'
        ),
        'depends' => array(
            'jquery',
//            'bootstrap'
        )
    ),

    'jquery-datatable-bs5' => array(
        'basePath' => 'node_modules_datatables_bs5',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/dataTables.bootstrap5' . $minVersion . '.css'
        ),
        'js' => array(
            'js/dataTables.bootstrap5' . $minVersion . '.js'
        ),
        'depends' => array(
            'jquery-datatable'
        )
    ),

    'es6promise' => array(
        'basePath' => 'vendor.es6promise',
        'js' => array(
            'es6-promise.auto.min.js'
        )
    ),

    'dom2image' => array(
        'basePath' => 'node_modules.dom-to-image',
        'js' => array(
            'dist/dom-to-image.min.js',
        )
    ),
    'jspdf' => array(
        'basePath' => 'node_modules.jspdf.dist',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'jspdf.min.js'
        ),
        'depends' => array(
            'dom2image',
            'es6promise',
            'jquery',
            'jszip'
        )
    ),
    /* Used for samechoiceheight/samelistheight */
    'jquery-actual' => array(
        'position' => CClientScript::POS_BEGIN,
        'devBaseUrl' => 'node_modules/jquery.actual',
        'basePath' => 'node_modules_jquery_actual',
        'js' => array(
            'jquery.actual' . $minVersion . '.js'
        ),
    ),
    /* Used by short text with map by leaflet */
    'leaflet' => array(
        'devBaseUrl' => 'node_modules/leaflet/dist',
        'basePath' => 'node_modules.leaflet.dist',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'leaflet.js'
        ),
        'css' => array(
            'leaflet.css'
        ),
    ),
    'devbridge-autocomplete' => array(
        'basePath' => 'node_modules.devbridge-autocomplete.dist', /* For geoname search autocomplete without jquery */
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'jquery.autocomplete' . $minVersion . '.js'
        ),
    ),
    'jszip' => array(
        'basePath' => 'node_modules.jszip',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'dist/jszip' . $minVersion . '.js',
            'vendor/FileSaver.js'
        ),
        'depends' => array(
            'jquery',
        )
    ),
    // DateTimePicker for BS5
    'tempus-dominus' => array(
        'devBaseUrl' => 'assets/packages/datetimepicker',
        'basePath' => 'core.datetimepicker',
        'css' => array(
            'build/tempus-dominus.min.css',
        ),
        'js' => array(
            'build/popper-tempus.js',
            'datepickerInit.js'
        ),
        'depends' => array(
            'moment'
        )
    ),
    // Used for Statistics
    'chart.js' => array(
        'devBaseUrl' => 'node_modules/chart.js/dist',
        'basePath' => 'node_modules.chart_js.dist',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'chart.umd.js'
        ),
    ),
);
