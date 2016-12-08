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
    ),

    'bootstrap-datetimepicker' => array(
        'basePath' => 'third_party.bootstrap-datetimepicker',
        'css' => array(
            'css/bootstrap-datetimepicker.min.css'
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
        'basePath' => 'third_party.bootstrap-switch',
        'css' => array(
            'css/bootstrap-switch.min.css'
        ),
        'js' => array(
            'js/bootstrap-switch.min.js'
        ),
        'depends' => array(
            'jquery',
            'bootstrap',
            'moment'
        )
    ),

    'jquery-datatable' => array(
        'basePath' => 'third_party.jquery-datatable',
        'css' => array(
            'datatables.min.css'
        ),
        'js' => array(
            'datatables.js'
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
    )


);
