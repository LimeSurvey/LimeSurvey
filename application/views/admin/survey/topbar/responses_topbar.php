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
        'class' => 'btn-outline-secondary pjax',
        'url'   => $this->createUrl("responses/index/", ['surveyId' => $survey->sid]),
        'icon'  => 'ri-list-unordered',
        'iconclass' => 'text-success',
        'name' => gT('Summary'),
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['summary']);

    // Display Responses Button
    $buttons['display_responses'] = [
        'name' => gT('Display responses'),
        'class' => 'btn btn-outline-secondary pjax',
        'icon' => 'ri-list-unordered',
        'iconclass' => 'text-success',
        'url' => $this->createUrl("responses/browse/", ['surveyId' => $survey->sid]),
    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['display_responses']);
}

if ($hasResponsesCreatePermission) {
    $permissions['create'] = $hasResponsesCreatePermission;
    // Dataentry Screen Button
    $buttons['data_entry'] = [
        'name' => gT('Data entry'),
        'class' => 'btn btn-outline-secondary pjax',
        'url' => $this->createUrl("admin/dataentry/sa/view/surveyid/$survey->sid"),
        'icon' => 'ri-list-unordered',
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
        'icon' => 'ri-bar-chart-fill',
        'class' => 'btn btn-outline-secondary pjax',
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
            'url'  => $this->createUrl("responses/time/", ['surveyId' => $survey->sid]),
            'class'=> 'btn btn-outline-secondary pjax',
            'icon' => 'ri-time-line',
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
            'icon' => 'ri-upload-fill text-success',
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
            'name' => gT('Import'),
            'datatoggle' => 'dropdown',
            'ariahaspopup' => true,
            'ariaexpanded' => false,
            'class' => 'btn-outline-secondary',
            'icon' => 'ri-upload-fill text-success',
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
        'class' => 'btn-outline-secondary',
        'name'  => gT('View Saved but not submitted Responses'),
        'url'   => $this->createUrl("admin/saved/sa/view/surveyid/$survey->sid"),
        'icon'  => 'ri-save-line text-success',
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
        'url'  => $this->createUrl("responses/delete/", ["surveyId" => $survey->sid]),
        'data' => [
            'post' => "{}",
            'show-text-area' => true,
            'use-ajax' => true,
            'grid-id' => "response-grid",
            'grid-reload' => 'true',
            'text' => gT('Enter a list of response IDs that are to be deleted, separated by comma.'),
        ],
        'title' => gT('Batch deletion'),
        'class' => 'btn-outline-secondary selector--ConfirmModal',
        'icon'  => 'ri-delete-bin-fill text-danger',

    ];
    array_push($topBar['alignment']['left']['buttons'], $buttons['batch_deletion']);
}
$finalJSON = [
    'permissions' => $permissions,
    'topbar' => $topBar,
];

header('Content-Type: application/json');
echo json_encode($finalJSON);
