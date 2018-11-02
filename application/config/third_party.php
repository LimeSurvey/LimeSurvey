<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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
        'devBaseUrl' => 'third_party/jquery',
        'basePath' => 'third_party.jquery',
        'position' =>CClientScript::POS_HEAD,
        'js' => array(
            'jquery-3.1.1'.$minVersion.'.js',
            'jquery-migrate-3.0.0'.$minVersion.'.js',
        )
    ),

    // Bootstrap
    // This package replace the Yiistrap register() function
    // Then instead of using the composer dependency system for themes
    // We can use the package dependency system (easier for now)
    'bootstrap' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/',
        'basePath' => 'core.bootstrap',
        'css'=> array(
            'bootstrap'.$minVersion.'.css', /* Admin need it, not public */
            'yiistrap'.$minVersion.'.css',
        ),
        'js'=>array(
            'bootstrap'.$minVersion.'.js',
            'plugins/bootstrapconfirm/bootstrapconfirm'.$minVersion.'.js'
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // Bootstrap admin
    // must be different for theme editor not to colide with theme files replacement
    'bootstrap-admin' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/',
        'basePath' => 'core.bootstrap',
        'css'=> array(
            'bootstrap'.$minVersion.'.css', /* Admin need it, not public */
            'yiistrap'.$minVersion.'.css',
        ),
        'js'=>array(
            'bootstrap'.$minVersion.'.js'
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // bootstrap-slider : for multinumeric with slider
    'bootstrap-slider' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/slider',
        'basePath' => 'core.bootstrap.plugins.slider',
        'position' => CClientScript::POS_BEGIN,
        'css'=> array(
            'css/bootstrap-slider'.$minVersion.'.css'
        ),
        'js' => array(
            'bootstrap-slider'.$minVersion.'.js'
        ),
        'depends' => array(
            'jquery',
            'bootstrap'
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
            'bootstrap'
        )
    ),

    // Bootstrap Multiselect2
    'bootstrap-select2' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/select2',
        'basePath' => 'core.bootstrap.plugins.select2',
        'js' => array(
            'js/select2.full'.$minVersion.'.js',
        ),
        'css' => array(
            'css/select2.css',
            'css/select2-bootstrap.css',
        ),
        'depends' => array(
            'jquery',
            'bootstrap'
        )
    ),

    'bootstrap-datetimepicker' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/datetimepicker/build',
        'basePath' => 'core.bootstrap.plugins.datetimepicker.build',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/bootstrap-datetimepicker'.$minVersion.'.css'
        ),
        'js' => array(
            'js/bootstrap-datetimepicker.min.js'
        ),
        'depends' => array(
            'jquery',
            'bootstrap',
            'moment'
        )
    ),

    'bootstrap-switch' => array(
        'devBaseUrl' => 'assets/packages/bootstrap/plugins/switch/',
        'basePath' => 'core.bootstrap.plugins.switch',
        'position' => CClientScript::POS_BEGIN,
        'css' => array(
            'css/bootstrap-switch'.$minVersion.'.css'
        ),
        'js' => array(
            'js/bootstrap-switch'.$minVersion.'.js'
        ),
        'depends' => array(
            'jquery',
            'bootstrap',
            'moment'
        )
    ),

    // jQuery UI
    'jqueryui' => array(
        'devBaseUrl' => 'third_party/jquery-ui',
        'basePath' => 'third_party.jquery-ui',
        'position' =>CClientScript::POS_HEAD,
        'js' => array(
            'jquery-ui'.$minVersion.'.js',
        ),
        'css' => array(
            'jquery-ui.structure.css', /* else autocomplete or other broken */
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jquery bindWithDelay
    'jquery-bindWithDelay' => array(
        'basePath' => 'third_party.jquery-bindWithDelay',
        'js' => array(
            'bindWithDelay.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery Cookie
    'js-cookie' => array(
        'basePath' => 'third_party.js-cookie',
        'js' => array(
            'js.cookie.js'
        )
    ),

    // jQuery Superfish
    'jquery-superfish' => array(
        'basePath' => 'third_party.jquery-superfish',
        'js' => array(
            'js/superfish.js',
            'js/hoverIntent.js'
        ),
        'css' => array(
            'css/superfish.css'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery json
    'jquery-json' => array(
        'basePath' => 'third_party.jquery-json',
        'js' => array(
            'jquery.json-2.4.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery blockUI
    'jquery-blockUI' => array(
        'basePath' => 'third_party.jquery-blockui',
        'js' => array(
            'jquery.blockUI.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // qTip2
    'qTip2' => array(
        'basePath' => 'third_party.qTip2',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'dist/jquery.qtip.min.js'
        ),
        'css' => array(
            'dist/jquery.qtip.min.css'
        ),
        'depends' => array(
            'jquery'
        )

    ),

    // jQuery Table Sorter
    'jquery-tablesorter' => array(
        'basePath' => 'third_party.jquery-tablesorter',
        'js' => array(
            'jquery.tablesorter.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery NestedSortable
    'jquery-nestedSortable' => array(
        'basePath' => 'third_party.jquery-nestedSortable',
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
        'devBaseUrl' => 'third_party/ace',
        'basePath' => 'third_party.ace',
        'js' => array(
            $minFolder.'/ace.js'
        ),
        'depends' => array(
            'jquery-ace'
        )
    ),

    // jQuery Ace
        'jquery-ace' => array(
            'devBaseUrl' => 'third_party/jquery-ace',
            'basePath' => 'third_party.jquery-ace',
        'js' => array(
            'jquery.ace.js',
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jQuery selectboxes
    'jquery-selectboxes' => array(
        'basePath' => 'third_party.jquery-selectboxes.selectboxes',
        'js' => array(
            'jquery.selectboxes.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery touch punch : seems uneended now ?
    'jquery-touch-punch' => array(
        'basePath' => 'third_party.jquery-touch-punch',
        'js' => array(
            'jquery.ui.touch-punch.min.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // Decimal.js calculate in js
    'decimal' => array(
        'position' => CClientScript::POS_BEGIN,
        'devBaseUrl' => 'third_party/decimal',
        'basePath' => 'third_party.decimal',
        'js' => array(
            'decimal.js'
        ),
        'depends' => array(
        )
    ),

    // Moment.js use real simple dateTime modification
    'moment' => array(
        'devBaseUrl' => 'third_party/moment',
        'basePath' => 'third_party.moment',
        'js' => array(
            'moment-with-locales'.$minVersion.'.js'
        ),
        'depends' => array(
        )
    ),

    // leaflet, needed for short text question with map (OSM)

    'jsuri' => array(
        'basePath' => 'third_party.jsUri',
        'js' => array(
            'Uri.js'
        ),
    ),

    'jquery-datatable' => array(
        'basePath' => 'third_party.datatables',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'js/jquery.dataTables'.$minVersion.'.js',
            'js/dataTables.bootstrap'.$minVersion.'.js'
        ),
        'depends' => array(
            'jquery',
            'bootstrap'
        )
    ),
    'es6promise' => array(
        'basePath' => 'third_party.es6promise',
        'js' => array(
            'es6-promise.auto.min.js'
        )
    ),

    'dom2image' => array(
        'basePath' => 'third_party.dom-to-image',
        'js' => array(
            'dist/dom-to-image.min.js',
        )
    ),

    'jspdf' => array(
        'basePath' => 'third_party.jspdf',
        'js' => array(
            'jspdf.min.js',
            'createpdf_worker.js'
        ),
        'depends' => array(
            'dom2image',
            'es6promise',
            'jquery',
            'bootstrap'
        )
    ),
    /* Used by ranking question type */
    'sortable' => array(
        'devBaseUrl' => 'third_party/sortable',
        'basePath' => 'third_party.sortable', /* for sorting ability */
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'jquery.fn.sortable'.$minVersion.'.js'
        )
    ),
    'jquery-actual' => array(
        'position' => CClientScript::POS_BEGIN,
        'basePath' => 'third_party.jquery-actual', /* for samechoiceheight/samelistheight */
        'js' => array(
            'jquery.actual'.$minVersion.'.js'
        ),
    ),
    /* Used by short text with map by leaflet */
    'leaflet' => array(
        'basePath' => 'third_party.leaflet',
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'leaflet.js'
        ),
        'css' => array(
            'leaflet.css'
        ),
    ),
    'devbridge-autocomplete' => array(
        'basePath' => 'third_party.devbridge-autocomplete.dist', /* For geoname search autocomplete without jquery */
        'position' => CClientScript::POS_BEGIN,
        'js' => array(
            'jquery.autocomplete'.$minVersion.'.js'
        ),
    ),
    'jszip' => array(
        'basePath' => 'third_party.jszip',
        'js' => array(
            'jszip.js',
            'fileSaver.js',
        ),
        'depends' => array(
            'jquery',
        )
    )
);
