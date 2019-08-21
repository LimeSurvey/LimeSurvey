<?php
$languages = $oSurvey->allLanguages;
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
$topbarextended = [
    'alignment' => [
        'left' => [
            'buttons' => [],
        ],
        'right' => [
            'buttons' => [],
        ],
    ],
];

// Preview Survey Button
$title = ($oSurvey->active == 'N') ? 'preview_survey' : 'execute_survey';
$name = ($oSurvey->active == 'N') ? gT('Preview survey') : gT('Execute survey');

if (count($languages) > 1) {
    $buttons[$title] = [];
    foreach ($languages as $language) {
        $buttons[$title.'_'.$language] = [
            'url' => $this->createAbsoluteUrl(
                "survey/index", 
                array(
                    'sid' => $sid,
                    'newtest' => "Y",
                    'lang' => $language
                )
            ),
            'icon' => 'fa fa-cog',
            'iconclass' => 'fa fa-external-link',
            'name' => $name.' ('.getLanguageNameFromCode($language, false).')',
            'class' => ' external',
            'target' => '_blank'
        ];
    }

    $buttonsurvey_preview_dropdown = [
        'class' => 'btn-group',
        'id' => 'preview_survey_dropdown',
        'main_button' => [
            'class' => 'dropdown-toggle',
            'datatoggle' => 'dropdown',
            'ariahaspopup' => true,
            'ariaexpanded' => false,
            'icon' => 'fa fa-cog',
            'name' => $name,
            'iconclass' => 'caret',
        ],
        'dropdown' => [
            'class' => 'dropdown-menu',
            'items' => $buttons,
        ],
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttonsurvey_preview_dropdown);
} else {
    $buttons[$title] = [
        'url' => $this->createAbsoluteUrl(
            "survey/index", 
            array(
                'sid' => $sid,
                'newtest' => "Y",
            )
        ),
        'name' => $name,
        'icon' => 'fa fa-cog',
        'iconclass' => 'fa fa-external-link',
        'class' => ' external',
        'target' => '_blank'
    ];

    array_push($topbar['alignment']['left']['buttons'], $buttons[$title]);
}

// Preview Questiongroup Button
$title = 'preview_questiongroup';
$name = gT('Preview question group');

if (($hasReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update'))) {
    if (count($languages) > 1) {
        $buttons[$title] = [];
        foreach ($languages as $language) {
            $buttons[$title.'_'.$language] = [
                'url' => $this->createAbsoluteUrl(
                    "survey/index/action/previewgroup", 
                    array(
                        'sid' => $sid,
                        'gid' => $gid,
                        'lang' => $language
                    )
                ),
                'id' => $title.'_'.$language,
                'icon' => 'fa fa-cog',
                'iconclass' => 'fa fa-external-link',
                'name' => $name.' ('.getLanguageNameFromCode($language, false).')',
                'class' => ' external',
                'target' => '_blank'
            ];
        }

        $buttongroup_preview_dropdown = [
            'class' => 'btn-group',
            'id' => 'preview_questiongroup_dropdown',
            'main_button' => [
                'class' => 'dropdown-toggle',
                'datatoggle' => 'dropdown',
                'ariahaspopup' => true,
                'ariaexpanded' => false,
                'icon' => 'fa fa-cog',
                'name' => $name,
                'iconclass' => 'caret',
            ],
            'dropdown' => [
                'class' => 'dropdown-menu',
                'items' => $buttons,
            ],
        ];
        array_push($topbar['alignment']['left']['buttons'], $buttongroup_preview_dropdown);
    } else {
        $buttons[$title] = [
            'url' => $this->createAbsoluteUrl(
                "survey/index/action/previewgroup", 
                array(
                    'sid' => $sid,
                    'gid' => $gid,
                )
            ),
            'id' => $title,
            'name' => $name,
            'icon' => 'fa fa-cog',
            'iconclass' => 'fa fa-external-link',
            'class' => ' external',
            'target' => '_blank'
        ];

        array_push($topbar['alignment']['left']['buttons'], $buttons[$title]);
    }
}

// Right Buttons (only shown for question group
if ($hasReadPermission) {
    // Check Survey Logic Button
    $buttons['check_survey_logic'] = [
        'id' => 'check_survey_logic',
        'url' => $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$sid}/gid/{$gid}/"),
        'name' => gT("Check survey logic for current question group"),
        'icon' => 'icon-expressionmanagercheck',
        'class' => ' ',
    ];

    array_push($topbar['alignment']['right']['buttons'], $buttons['check_survey_logic']);
}

$hasDeletePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'delete');
if ($hasDeletePermission) {
    $permissions['delete'] = ['delete' => $hasDeletePermission];

    if (($sumcount4 == 0 && $activated != "Y") || $activated != "Y") {
        // has question
        if (empty($condarray)) {
            // can delete group and question
            $buttons['delete_current_question_group'] = [
                'id' => 'delete_current_question_group',
                'url' => $this->createUrl("admin/questiongroups/sa/delete/", ["surveyid" => $sid, "gid" => $gid]),
                'type' => 'modal',
                'message' => gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?", "js"),
                'icon' => 'fa fa-trash',
                'name' => gT("Delete current question group"),
                'class' => ' btn-danger ',
            ];
        } else {
            // there is at least one question having a condition on its content
            $buttons['delete_current_question_group'] = [
                'id' => 'delete_current_question_group',
                'url' => '',
                'title' => gT("Impossible to delete this group because there is at least one question having a condition on its content"),
                'icon' => 'fa fa-trash',
                'name' => gT("Delete current question group"),
                'class' => ' btn-danger disabled',
            ];
        }
    } else {
        // Activated
        $buttons['delete_current_question_group'] = [
            'id' => 'delete_current_question_group',
            'title' => gT("You can't delete this question group because the survey is currently active."),
            'icon' => 'fa fa-trash',
            'name' => gT("Delete current question group"),
            'class' => ' btn-danger ',
        ];
    }
}
array_push($topbar['alignment']['right']['buttons'], $buttons['delete_current_question_group']);

$hasExportPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export');
if ($hasExportPermission) {
    $permissions['update'] = ['export' => $hasExportPermission];

    $buttons['export'] = [
        'id' => 'export',
        'url' => $this->createUrl("admin/export/sa/group/surveyid/$sid/gid/$gid"),
        'icon' => 'icon-export',
        'name' => gT("Export this question group"),
        'class' => ' btn-default ',
    ];

    array_push($topbar['alignment']['right']['buttons'], $buttons['export']);
}

// TopBar Extended (second TopBar, which will swap if Event triggered)
$topbarextended['alignment']['left']['buttons'] = $topbar['alignment']['left']['buttons'];

// Save Buttons (right side)
if ($ownsSaveButton == true) {
    // Save Button
    $buttons['save'] = [
        'id' => 'save',
        'name' => gT('Save'),
        'icon' => 'fa fa-floppy-o',
        'url' => '#',
        'id' => 'save-button',
        'isSaveButton' => true,
        'class' => 'btn-success',
    ];
    array_push($topbarextended['alignment']['right']['buttons'], $buttons['save']);
}

// Save and Close Button
if ($ownsSaveAndCloseButton) {
    $button['save_and_close'] = [
        'id' => 'save_and_close',
        'name' => gT('Save and close'),
        'icon' => 'fa fa-check-square',
        'url' => '#',
        'id' => 'save-and-close-button',
        'isSaveButton' => true,
        'class' => 'btn-default',
    ];
    array_push($topbarextended['alignment']['right']['buttons'], $button['save_and_close']);
}

$finalJSON = [
    'permission' => $permissions,
    'topbar' => $topbar,
    'topbarextended' => $topbarextended,
];

header("Content-Type: application/json");
echo json_encode($finalJSON);
