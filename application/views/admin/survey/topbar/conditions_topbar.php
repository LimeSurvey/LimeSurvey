<?php

$permissions = [];
$buttons = [];
$topbar = [
    'alignment' => [
        'left' => [
            'buttons' => [],
        ],
        'right' => [
            'buttons' => [],
        ],
    ],
];

if ($hasUpdatePermission) {
    $permissions['update'] = ['update' => $hasUpdatePermission];
/*
    // Add conditions Button
    $buttons['add_conditions'] = [
        'url' => $this->createUrl(
            "admin/conditions/sa/index/subaction/editconditionsform",
            array(
                "surveyid" => $sid,
                "gid" => $gid,
                "qid" => $qid
            )
        ),
        'name' => gT("Add and edit conditions"),
        'id' => 'add_conditions_button',
        'icon' => 'icon-conditions_add',
        'class' => ' btn-default',
    ];
    array_push($topbar['alignment']['right']['buttons'], $buttons['add_conditions']);*/
}

if ($hasCopyPermission) {
    $permissions['copy'] = ['copy' => $hasCopyPermission];
/*
    // Copy conditions Button
    $buttons['copy_conditions'] = [
        'url' => $this->createUrl(
            "admin/conditions/sa/index/subaction/copyconditionsform",
            array(
                "surveyid" => $sid,
                "gid" => $gid,
                "qid" => $qid
            )
        ),
        'name' => gT("Copy conditions"),
        'id' => 'copy_conditions_button',
        'icon' => 'icon-copy',
        'class' => ' btn-default',
    ];
    array_push($topbar['alignment']['right']['buttons'], $buttons['copy_conditions']);*/
}

// Close Button
$buttons['close'] = [
    'url' => $this->createUrl(
        "questionEditor/view",
        array(
            "surveyid" => $sid,
            "gid" => $gid,
            "qid" => $qid
        )
    ),
    'name' => gT("Close"),
    'id' => 'close_conditionsdesigner_button',
    'icon' => 'fa fa-close',
    'class' => ' btn-danger',
];
array_push($topbar['alignment']['right']['buttons'], $buttons['close']);


$finalJSON = [
    'permission' => $permissions,
    'topbar' => $topbar,
];

header("Content-Type: application/json");
echo json_encode($finalJSON);
