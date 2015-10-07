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
            'jquery-1.11.1.min.js'
        ),
    ),

    // jQuery UI
    'jqueryui' => array(
        'baseUrl' => 'third_party/jqueryui/',
        'js' => array(
            'js/jquery-ui-1.10.3.custom.min.js'
        ),
        'css' => array(
            'css/jquery-ui.css'
        ),
        'depends' => array(
            'jquery',
            'bootstrap'
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

    // jQuery Notify
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

    // jQuery timepicker
    'jqueryui-timepicker' => array(
        'baseUrl' => 'third_party/jquery-ui-timepicker-addon/',
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

    // jQuery horizontal scroll
    'jquery-horizontal-scroll' => array(
        'baseUrl' => 'third_party/jquery.horizontal.scroll',
        'js' => array(
            'jquery.horizontal.scroll.js'
        ),
    ),

    // displayParticipants
    'display-participants' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'css' => array(
            'css/displayParticipants.css',
        ),
    ),

    // LimeSurvey Bootstrap Admin Interface
    'lime-bootstrap' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',

        'js'=>array('scripts/notify.js'),

        'css' => array(
            'css/lime-admin.css',
            'css/fonts.css',
            'css/font-awesome/font-awesome-43.min.css',
            'css/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css'
        ),
        'depends' => array(
            'jquery',
            'jquery-notify',
            'bootstrap'
        ),
    ),

    // adminstyle-rtl
    'adminstyle-rtl' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'css' => array(
            'css/adminstyle-rtl.css',
        ),
        'depends' => array(
            'bootstrap',
            'lime-bootstrap'
        )
    ),

    // statistics
    'statistics' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'css' => array(
            'css/statistics.css',
        ),
        'depends' => array(
            'bootstrap',
            'lime-bootstrap'
        )
    ),

    // sidemenu
    'sidemenu' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'js' => array('scripts/sidemenu.js'),
        'css' => array(
            'css/sidemenu.css',
        ),
        'depends' => array(
            'bootstrap',
            'lime-bootstrap',
        ),
    ),

    // Clickable panels
    'panel-clickable' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'js' => array('scripts/panelclickable.js'),
        'depends' => array(
            'bootstrap',
            'lime-bootstrap',
        ),
    ),

    // panels animation
    'panels-animation' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'js' => array('scripts/panelsanimation.js'),
        'depends' => array(
            'panel-clickable',
            'bootstrap',
            'lime-bootstrap',
        ),
    ),

    // select2
    'select2' => array(
        'baseUrl' => 'styles/limebootstrap/assets/',
        'js' => array('scripts/select2.js'),
        'depends' => array(
            'bootstrap',
            'lime-bootstrap',
            //'jquery',
            //'jqueryui',
        ),
    ),

);
