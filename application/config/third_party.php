<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// This should use app->params[bower-asset] but that's not yet avialable at this point.
$bowerAssetPath = 'components';
/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */
return array(
    'jquery' => array(
        'baseUrl' => $bowerAssetPath . '/jquery/dist',
        'js' => array(
            'jquery.min.js'
        )
    ),
    'jqgrid' => array(
        'baseUrl' => 'third_party/jqgrid/',
        'js' => array(
            'js/jquery.jqGrid.min.js',
            'js/i18n/grid.locale-en.js',
            'plugins/jquery.searchFilter.js'
        ),
        'css' => array(
            'css/ui.jqgrid.css'
        ),
        'depends' => array(
            'jquery'
        )

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
    'jqueryui' => array(
        'baseUrl' => $bowerAssetPath . '/jquery-ui/',
        'js' => array(
            'jquery-ui.min.js'
        ),
        'depends' => array(
            'jquery',
        )
    ),

    'jquery-cookie' => array(
        'baseUrl' => 'third_party/jquery-cookie',
        'js' => array(
            'jquery.cookie.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),
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
    'jquery-json' => array(
        'baseUrl' => 'third_party/jquery-json',
        'js' => array(
            'jquery.json-2.4.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),
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
    'jquery-tablesorter' => array(
        'baseUrl' => 'third_party/jquery-tablesorter',
        'js' => array(
            'jquery.tablesorter.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),
    'ace' => array(
        'baseUrl' => 'third_party/ace',
        'js' => array(
            'ace.js'
        ),
        'depends' => array(
            'jquery-ace'
        )
    ),
     'jquery-ace' => array(
        'baseUrl' => 'third_party/jquery-ace',
        'js' => array(
            'jquery.ace.js',
        ),
        'depends' => array(
            'jquery',
        )
    ),
    'jquery-actual' => array(
        'baseUrl' => $bowerAssetPath . '/jquery.actual/',
        'js' => array(
            'jquery.actual.min.js'
        ),
        'depends' => array(
            'jquery'
        )
    ),
    'jquery-touch-punch' => array(
        'baseUrl' => 'third_party/jquery-touch-punch/',
        'js' => array(
            'jquery.ui.touch-punch.min.js'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),
    'jqueryui-timepicker' => array(
        'baseUrl' => $bowerAssetPath . '/jqueryui-timepicker-addon/dist/',
        'js' => array(
            'jquery-ui-timepicker-addon.js'
        ),
        'css' => array(
            'jquery-ui-timepicker-addon.css'
        ),
        'depends' => array(
            'jqueryui'
        )
    ),
    'leaflet' => array(
        'baseUrl' => 'third_party/leaflet/',
        'js' => array(
            'leaflet.js'
        ),
        'css' => array(
            'leaflet.css'
        ),
    ),
);
