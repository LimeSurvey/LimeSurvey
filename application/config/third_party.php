<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */
return array(

    // jQuery
    'jquery' => array(
        'baseUrl' => 'third_party/jquery/',
        'js' => array(
            'jquery-2.2.3.min.js'
        ),
    ),

    // Bootstrap
    // This package replace the Yiistrap register() function
    // Then instead of using the composer dependency system for templates (will be used for LS3)
    // We can use the package dependency system (easier for now)
    'bootstrap' => array(
        'basePath' => 'bootstrap',
        'css'=> array(
            /* Not needed for templates ! they use their own theme !
            'css/bootstrap.css',
            'css/yiistrap.css',
            */
        ),
        'depends' => array(
            'jquery',
        )
    ),

    'fontawesome' => array(
        //'basePath' => 'third_party.bootstrap', // Need fix third_party alias
        'baseUrl' => 'third_party/fontawesome/',
        'css'=> array(
            'css/font-awesome.min.css',
        ),
    ),

    // jQuery UI
    'jqueryui' => array(
        'baseUrl' => 'third_party/jqueryui/',
        'js' => array(
            'js/jquery-ui-1.11.4.min.js'
        ),
        'css' => array(
            //'css/jquery-ui.css'
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jQgrid
    'jqgrid' => array(
        'baseUrl' => 'third_party/jqgrid/',
        'js' => array(
            'js/jquery.jqGrid.min.js',
            'js/i18n/grid.locale-en.js',
            'plugins/jquery.searchFilter.js'
        ),
        'css' => array(
            //'css/ui.jqgrid.css'
        ),
        'depends' => array(
            'jquery'
        )

    ),


        'jquery-price-format' => array(
            'baseUrl' => 'third_party/jquery-price-format/',
            'js' => array(
                'jquery_price_format.js'
            ),
        ),

    'jqgrid.addons' => array(
        'baseUrl' => 'third_party/jqgrid/plugins/',
        'js' => array(
            'grid.addons.js'
        ),
        'depends' => array(
            'jqgrid'
        )

    ),

    // jquery bindWithDelay
    'jquery-bindWithDelay' => array(
        'baseUrl' => 'third_party/jquery-bindWithDelay',
        'js' => array(
            'bindWithDelay.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery Cookie
    'jquery-cookie' => array(
        'baseUrl' => 'third_party/jquery-cookie',
        'js' => array(
            'jquery.cookie.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery Superfish
    'jquery-superfish' => array(
        'baseUrl' => 'third_party/jquery-superfish',
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
        'baseUrl' => 'third_party/jquery-json',
        'js' => array(
            'jquery.json-2.4.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery blockUI
    'jquery-blockUI' => array(
        'baseUrl' => 'third_party/jquery-blockui',
        'js' => array(
            'jquery.blockUI.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // qTip2
    'qTip2' => array(
        'baseUrl' => 'third_party/qTip2',
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
        'baseUrl' => 'third_party/jquery-tablesorter',
        'js' => array(
            'jquery.tablesorter.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery NestedSortable
    'jquery-nestedSortable' => array(
        'baseUrl' => 'third_party/jquery-nestedSortable',
        'js' => array(
            'jquery.mjs.nestedSortable.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // Bootstrap Multiselect
    'bootstrap-multiselect' => array(
        'baseUrl' => 'third_party/bootstrap-multiselect',
        'js' => array(
            'js/bootstrap-multiselect.js',
        ),
        'css' => array(
            'css/bootstrap-multiselect.css',
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // Ace
    'ace' => array(
        'baseUrl' => 'third_party/ace',
        'js' => array(
            'ace.js'
        ),
        'depends' => array(
            'jquery-ace'
        )
    ),

    // jQuery Ace
     'jquery-ace' => array(
        'baseUrl' => 'third_party/jquery-ace',
        'js' => array(
            'jquery.ace.js',
        ),
        'depends' => array(
            'jquery',
        )
    ),

    // jQuery selectboxes
    'jquery-selectboxes' => array(
        'baseUrl' => 'third_party/jquery-selectboxes/selectboxes',
        'js' => array(
            'jquery.selectboxes.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery actual
    'jquery-actual' => array(
        'baseUrl' => 'third_party/jquery-actual/',
        'js' => array(
            'jquery.actual.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery touch punch
    'jquery-touch-punch' => array(
        'baseUrl' => 'third_party/jquery-touch-punch/',
        'js' => array(
            'jquery.ui.touch-punch.min.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // select2
    'select2' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'js' => array('scripts/select2.js'),
        'depends' => array(
        ),
    ),

    // leaflet, needed for short text question with map (OSM)
    'leaflet' => array(
        'baseUrl' => 'third_party/leaflet/',
        'js' => array(
            'leaflet.js'
        ),
        'css' => array(
            'leaflet.css'
        ),
    )
);
