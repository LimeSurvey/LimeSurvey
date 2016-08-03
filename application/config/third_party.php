<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */


if (!isset($_GET['isAjax']))
{
    $aJquery = array(
        'basePath' => 'third_party.jquery',
        'js' => array(
            'jquery-2.2.4.min.js'
        ));
}
else
{
    $aJquery = array(
        'basePath' => 'third_party.jquery',
        'js' => array(

        ),
        );
}

return array(

    // jQuery
    'jquery' => $aJquery,


    // Bootstrap
    // This package replace the Yiistrap register() function
    // Then instead of using the composer dependency system for templates (will be used for LS3)
    // We can use the package dependency system (easier for now)
    'bootstrap' => array(
        'basePath' => 'bootstrap',
        'css'=> array(
            'css/bootstrap.css',
            'css/yiistrap.css',
        ),
        'depends' => array(
            'jquery',
        )
    ),

    'fontawesome' => array(
        //'basePath' => 'third_party.bootstrap', // Need fix third_party alias
        'basePath' => 'third_party.fontawesome',
        'css'=> array(
            'css/font-awesome.min.css',
        ),
    ),

    // jQuery UI
    'jqueryui' => array(
        'basePath' => 'third_party.jqueryui',
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
        'basePath' => 'third_party.jqgrid',
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

    'jqgrid.addons' => array(
        'basePath' => 'third_party.jqgrid.plugins',
        'js' => array(
            'grid.addons.js'
        ),
        'depends' => array(
            'jqgrid'
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
    'jquery-cookie' => array(
        'basePath' => 'third_party.jquery-cookie',
        'js' => array(
            'jquery.cookie.js'
        ),
        'depends' => array(
            'jquery'
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
        'js' => array(
            'jquery.mjs.nestedSortable.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),

    // Bootstrap Multiselect
    'bootstrap-multiselect' => array(
        'basePath' => 'third_party.bootstrap-multiselect',
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
        'basePath' => 'third_party.ace',
        'js' => array(
            'ace.js'
        ),
        'depends' => array(
            'jquery-ace'
        )
    ),

    // jQuery Ace
     'jquery-ace' => array(
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

    // jQuery actual
    'jquery-actual' => array(
        'basePath' => 'third_party.jquery-actual',
        'js' => array(
            'jquery.actual.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),

    // jQuery touch punch
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
        'basePath' => 'third_party.decimal',
        'js' => array(
            'decimal.js'
        ),
        'depends' => array(
        )
    ),

    // Moment.js use real simple dateTime modification 
    'moment' => array(
        'basePath' => 'third_party.moment',
        'js' => array(
            'moment-with-locales.min.js'
        ),
        'depends' => array(
        )
    ),

    // leaflet, needed for short text question with map (OSM)
    'leaflet' => array(
        'basePath' => 'third_party.leaflet',
        'js' => array(
            'leaflet.js'
        ),
        'css' => array(
            'leaflet.css'
        ),
    ),
    'jsuri' => array(
        'basePath' => 'third_party.jsUri',
        'js' => array(
            'Uri.js'
        ),
    )

);
