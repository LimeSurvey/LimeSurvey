<?php

$permissions = [];
$buttonsGroup = [];
$buttons = [];
$topBar = [
    'alignment' => [
        'left' => [
            'buttons' => [],
        ],
        'right' => [
            'buttons' => [],
        ]
    ]
];

if ($hasResponsesReadPermission) {
    $permissions['read'] = $hasResponsesReadPermission;
    // Summary Button
    $buttons['summary'] = [
        'class' => 'btn-default pjax',
        'url'   => $this->createUrl("admin/responses/sa/index/surveyid/$survey->sid"),
        'icon'  => 'fa fa-list-alt',
        'iconclass' => 'text-success',
        'name' => gT('Summary'),
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['summary']);

    // Display Responses Button
    $buttons['display_responses'] = [
        'name' => gT('Display responses'),
        'class' => 'btn btn-default pjax',
        'icon' => 'fa fa-list-alt',
        'iconclass' => 'text-success',
        'url' => $this->createUrl("admin/responses/sa/browse/surveyid/$survey->sid"),
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['display_responses']);
}

if ($hasResponsesCreatePermission) {
    $permissions['create'] = $hasResponsesCreatePermission;
    // Dataentry Screen Button
    $buttons['data_entry'] = [
        'name' => gT('Data entry'),
        'class' => 'btn btn-default pjax',
        'url' => $this->createUrl("admin/dataentry/sa/view/surveyid/$survey->sid"),
        'icon' => 'fa fa-list-alt',
        'iconclass' => 'text-success',
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['data_entry']);
}

if ($hasStatisticsReadPermission) {
    $permissions['statistics'] = $hasStatisticsReadPermission;
    // Statistics Button
    $buttons['statistics'] = [
        'name' => gT('Statistics'),
        'url' => $this->createUrl("admin/statistics/sa/index/surveyid/$survey->sid"),
        'icon' => 'fa fa-bar-chart',
        'class' => 'btn btn-default pjax',
        'iconclass' => 'text-success',
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['statistics']);
}

if($isActive) {
    // If 'save timings' is enabled (from Notifications & Data).
    // Original view: views/admin/responses/browsemenubar_view.php
    if ($isTimingEnabled) {
        $buttons['timing_statistics'] = [
            'name' => gT('Timing statistics'),
            'url'  => $this->createUrl("admin/responses/sa/time/surveyid/$survey->sid"),
            'class'=> 'btn btn-default pjax',
            'icon' => 'fa fa-clock-o',
            'iconclass' => 'text-success',
        ];
        array_push($topBar['alignment']['left']['buttons'], $buttons['timing_statistics']);
    }
}

if ($hasResponsesExportPermission) {
    $permissions['export'] = $hasResponsesExportPermission;
    // Export Button
    $exportItems = [
        'export_responses' => [
            'url' => $this->createUrl("admin/export/sa/exportresults/surveyid/$survey->sid"),
            'name' => gT("Export responses"),
        ],
        'export_responses_spss' => [
            'url' => $this->createUrl("admin/export/sa/exportspss/sid/$survey->sid"),
            'name' => gT("Export responses to SPSS"),
        ],
        'export_responses_vv' => [
            'url' => $this->createUrl("admin/export/sa/vvexport/surveyid/$survey->sid"),
            'name' => gT("Export a VV survey file"),
        ],
    ];
    $buttonsGroup['export'] = [
        'class' => 'btn-group',
        'main_button' => [
            'name' => gT('Export'),
            'datatoggle' => 'dropdown',
            'ariahaspopup' => true,
            'ariaexpanded' => false,
            'icon' => 'icon-export text-success',
            'iconclass' => 'caret',
            'id' => 'export-button',
            'class' => 'dropdown-toggle',
        ],
        'dropdown' => [
            'class' => 'dropdown-menu',
            'items' => $exportItems,
        ]
    ];

    array_push($topBar['alignment']['left']['buttons'], $buttonsGroup['export']);
}

if ($hasResponsesCreatePermission) {
    $importItems = [
        'import_responses' => [
            'name' => gT('Import responses from deactivated survey table'),
            'url'  => $this->createUrl("admin/dataentry/sa/import/surveyid/$survey->sid"),
        ],
        'import_vv_survey' => [
            'name' => gT("Import a VV survey file"),
            'url'  => $this->createUrl("admin/dataentry/sa/vvimport/surveyid/$survey->sid"),
        ],
    ];
    // Import DropDown
    $buttonsGroup['import'] = [
        'class' => 'btn-group',
        'main_button' => [
            'name' => gt('Import'),
            'datatoggle' => 'dropdown',
            'ariahaspopup' => true,
            'ariaexpanded' => false,
            'class' => 'btn-default',
            'icon' => 'icon-import text-success',
            'iconclass' => 'caret'
        ],
        'dropdown' => [
            'class' => 'dropdown-menu',
            'items' => $importItems,
        ],
    ];

    array_push($topBar['alignment']['left']['buttons'], $buttonsGroup['import']);
}

if ($hasResponsesReadPermission) {
    // View Saved but not submitted Responses Button
    $buttons['view_saved_but_not_submitted_responses'] = [
        'class' => 'btn-default',
        'name'  => gT('View Saved but not submitted Responses'),
        'url'   => $this->createUrl("admin/saved/sa/view/surveyid/$survey->sid"),
        'icon'  => 'icon-saved text-success',
    ];

    array_push($topBar['alignment']['left']['buttons'], $buttons['view_saved_but_not_submitted_responses']);
}

if ($hasResponsesDeletePermission) {
    $permissions['delete'] = $hasResponsesDeletePermission;

    // TODO: Iterate Survey Button (see browsemenubar_view.php line: 121 - 129

    // Batch Deletion Button
    // TODO: the given attributes are not working at the moment. This needs to be fixed when there is time.
    $buttons['batch_deletion'] = [
        'name' => gT("Batch deletion"),
        'id'   => 'response-batch-deletion',
        'url'  => $this->createUrl("admin/responses/sa/actionDelete/", array("surveyid" => $survey->sid)),
        'data' => [
            'post' => "{}",
            'show-text-area' => true,
            'use-ajax' => true,
            'grid-id' => "response-grid",
            'grid-reload' => 'true',
            'text' => gT('Enter a list of response IDs that are to be deleted, separated by comma.'),
        ],
        'title' => gT('Batch deletion'),
        'class' => 'btn-default selector--ConfirmModal',
        'icon'  => 'fa fa-trash text-danger',

    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['batch_deletion']);
}
$finalJSON = [
    'permissions' => $permissions,
    'topbar' => $topBar,
];

header('Content-Type: application/json');
echo json_encode($finalJSON);
