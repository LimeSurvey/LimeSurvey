<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains package definition for third party libraries.
 * Defining them here allows for easy inclusion in views.
 */
return array(
    'jquery' => array(
        'baseUrl' => 'third_party/jquery/',
        'js' => array(
            'jquery-1.9.1.min.js'
        )
    ),
    'jqgrid' => array(
        'baseUrl' => 'third_party/jqgrid/',
        'js' => array(
            'js/jquery.jqGrid.min.js',
            'js/i18n/grid.locale-en.js',
            'plugins/jquery.searchFilter.js',
            'src/grid.celledit.js',

        ),
        'css' => array(
            'css/ui.jqgrid.css'
        ),
        'depends' => array(
            'jquery'
        )

    ),
    'jquery-notify' => array(
        'baseUrl' => 'third_party/jquery-notify',
        'js' => array(
            'src/jquery.notify.min.js'
        ),
        'css' => array(
            'ui.notify.css'
        ),
        'depends' => array(
            'jqueryui'
        )
        
    ),
    'jqueryui' => array(
        'baseUrl' => 'third_party/jqueryui/',
        'js' => array(
            'js/jquery-ui-1.10.0.custom.min.js'
        ),
        'css' => array(
            //'css/smoothness/jquery-ui-1.10.0.custom.min.css'
        ),
        'depends' => array(
            'jquery'
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
            'js/superfish.js'
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
    
    'jquery-qtip' => array(
        'baseUrl' => 'third_party/jquery-qtip',
        'js' => array(
            'dist/jquery.qtip.js'
        ),
        'css' => array(
            'dist/jquery.qtip.css'
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
    )
);