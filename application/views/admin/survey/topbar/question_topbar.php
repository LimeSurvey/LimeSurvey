<?php
$languages   = $oSurvey->allLanguages;
$permissions = [];
$buttons     = [];
$topbar      = [
  'alignment' => [
    'left' => [
      'buttons' => []
    ],
  ],
];
$topbarextended = [
  'alignment' => [
    'left' => [
      'buttons' => []
    ],
    'right' => [
      'buttons' => []
    ],
  ]
];

$hasReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
if ($hasReadPermission) {
    $permissions['read'] = ['read' => $hasReadPermission];

    // Preview Survey / Execute Survey Button
    $name  = $oSurvey->active === 'N' ? gT('Preview survey') : gT('Execute survey');
    
    $surveypreview_buttons = [];

    if(safecount($languages) > 1) {

        foreach($languages as $language) {
            $buttons_preview_survey[$language] = [
                'url' => $this->createAbsoluteUrl(
                    "survey/index",
                    array(
                        'sid'     => $sid,
                        'newtest' => "Y",
                        'lang'    => $language
                    )
                ),
                'id' => 'preview_survey_'.$language,
                'name' => gT('Preview in ').getLanguageNameFromCode($language, false),
                'icon' => '',
                'iconclass' => '', 
                'class' => ' external',
                'target' => '_blank'
            ];
        }

        $surveypreview_buttons = [
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
                'items' => $buttons_preview_survey,
            ],
        ];
    } else {
        $surveypreview_buttons = [
            'url' => $this->createAbsoluteUrl(
                "survey/index",
                array(
                    'sid'     => $sid,
                    'newtest' => "Y",
                    'lang'    => $oSurvey->language
                )
            ),
            'id' => 'preview_survey',
            'name' => $name,
            'icon' => 'fa fa-cog',
            'iconclass' => 'fa fa-external-link', 
            'class' => ' external',
            'target' => '_blank'
        ];;
    }
    array_push($topbar['alignment']['left']['buttons'], $surveypreview_buttons);
    array_push($topbarextended['alignment']['left']['buttons'], $surveypreview_buttons);

    // Preview Question Group Button
    $name  = gT('Preview question group');
    
    $questiongrouppreview_buttons = [];

    if (count($languages) > 1) {
        foreach ($languages as $language) {
            $buttonspreview_questiongroup[$language] = [
                'url' => $this->createAbsoluteUrl(
                    "survey/index", 
                    array(
                        'sid' => $sid,
                        'newtest' => "Y",
                        'lang' => $language
                    )
                ),
                'id' => 'preview_questiongroup_'.$language,
                'name' => gT('Preview in ').getLanguageNameFromCode($language, false),
                'icon' => '',
                'iconclass' => '',
                'class' => ' external',
                'target' => '_blank'
            ];
        }
    
        $questiongrouppreview_buttons = [
            'class' => 'btn-group',
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
                'items' => $buttonspreview_questiongroup,
            ],
        ];
    } else {
        $questiongrouppreview_buttons = [
            'url' => $this->createAbsoluteUrl(
                "survey/index", 
                array(
                    'sid' => $sid,
                    'newtest' => "Y",
                    'lang' => $oSurvey->language
                )
            ),
            'id' => 'preview_questiongroup',
            'name' => $name,
            'icon' => 'fa fa-cog',
            'iconclass' => 'fa fa-external-link',
            'class' => ' external',
            'target' => '_blank'
        ];
    
    }
    array_push($topbar['alignment']['left']['buttons'], $questiongrouppreview_buttons);
    array_push($topbarextended['alignment']['left']['buttons'], $questiongrouppreview_buttons);
    
    // Preview Question Button
    $questionpreview_buttons = [];
    if (count($languages) > 1) {

        $buttons_preview_question = [];
        foreach ($languages as $language) {
            $buttons_preview_question[$language] = [
                'url' => $this->createAbsoluteUrl(
                    "survey/index/action/previewquestion/", 
                    array(
                        'sid' => $sid,
                        'gid' => $gid,
                        'qid' => $qid,
                        'lang' => $language
                    )
                ),
                'id' => 'preview_question_'.$language,
                'name' => gT('Preview in ').getLanguageNameFromCode($language, false),
                'icon' => '',
                'iconclass' => '',
                'class' => ' external',
                'target' => '_blank'
            ];
        }
    
        $questionpreview_buttons = [
            'class' => 'btn-group',
            'id' => 'preview_question_dropdown',
            'main_button' => [
                'class' => 'dropdown-toggle',
                'datatoggle' => 'dropdown',
                'ariahaspopup' => true,
                'ariaexpanded' => false,
                'icon' => 'fa fa-cog',
                'name' => gT("Preview question"),
                'iconclass' => 'caret',
            ],
            'dropdown' => [
                'class' => 'dropdown-menu',
                'items' => $buttons_preview_question,
            ],
        ];
    } else {
        $questionpreview_buttons = [
            'url' => $this->createAbsoluteUrl(
                "survey/index/action/previewquestion/", 
                    array(
                        'sid' => $sid,
                        'gid' => $gid,
                        'qid' => $qid,
                    )
            ),
            'id' => 'preview_question',
            'name' => gT("Preview question"),
            'icon' => 'fa fa-cog',
            'iconclass' => 'fa fa-external-link',
            'class' => ' external',
            'target' => '_blank'
        ];
    
    }
    array_push($topbar['alignment']['left']['buttons'], $questionpreview_buttons);
    array_push($topbarextended['alignment']['left']['buttons'], $questionpreview_buttons);
    

    // Check Logic Button
    if (count($languages) > 1) {
        $buttons_check_logic = [];
        foreach ($languages as $language) {
            $buttons_check_logic[$language] = [
                'url' => $this->createAbsoluteUrl(
                    "admin/expressions/sa/survey_logic_file", 
                    array(
                        'sid' => $sid,
                        'gid' => $gid,
                        'qid' => $qid,
                        'lang' => $language
                    )
                ),
                'id' => 'check_logic_'.$language,
                'icon' => '',
                'iconclass' => '',
                'name' => getLanguageNameFromCode($language, false),
                'class' => ' btn-default',
            ];
        }
    
        $buttonsgroup_check_logic = [
            'class' => 'btn-group',
            'id' => 'check_logic_dropdown',
            'main_button' => [
                'class' => 'dropdown-toggle',
                'datatoggle' => 'dropdown',
                'ariahaspopup' => true,
                'ariaexpanded' => false,
                'id' => 'check_logic_button',
                'icon' => 'icon-expressionmanagercheck',
                'name' => gT("Check logic"),
                'iconclass' => 'caret',
            ],
            'dropdown' => [
                'class' => 'dropdown-menu',
                'items' => $buttons_check_logic,
            ],
        ];
        array_push($topbar['alignment']['left']['buttons'], $buttonsgroup_check_logic);
    } else {
        $buttons_check_logic = [
            'url' => $this->createAbsoluteUrl(
                "admin/expressions/sa/survey_logic_file", 
                    array(
                        'sid' => $sid,
                        'gid' => $gid,
                        'qid' => $qid,
                    )
            ),
            'id' => 'check_logic_button',
            'name' => gT("Check logic"),
            'icon' => 'icon-expressionmanagercheck',
            'class' => ' btn-default',
        ];
    
        array_push($topbar['alignment']['left']['buttons'], $buttons_check_logic);
    }

}

$hasDeletePermission = Permission::model()->hasSurveyPermission($sid,'surveycontent','delete' );
if ($hasDeletePermission) {
    $permissions['delete'] = ['delete' => $hasDeletePermission];

    // Delete Button
    $buttons['delete'] = [
        'url'  => $this->createUrl("admin/questions/sa/delete/", ["surveyid" => $sid, "qid" => $qid, "gid" => $gid]),
        'name' => gT("Delete"),
        'id' => 'delete_button',
        'icon' => 'fa fa-trash text-danger',
        'class' => ' btn-danger'
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['delete']);
}

$hasExportPermission = Permission::model()->hasSurveyPermission($sid,'surveycontent','export');
if ($hasExportPermission) {
    $permissions['export'] = ['export' => $hasExportPermission];

    // Export Button
    $buttons['export'] = [
        'url'  => $this->createUrl("admin/export/sa/question/surveyid/$sid/gid/$gid/qid/$qid"),
        'name' => gT("Export"),
        'id' => 'export_button',
        'icon' => 'icon-export',
        'class' => ' btn-default'
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['export']);
}

$hasCopyPermission = Permission::model()->hasSurveyPermission($sid,'surveycontent','create');
if ($hasCopyPermission) {
    $permissions['copy'] = ['copy' => $hasCopyPermission];

    // Copy Button
    $buttons['copy'] = [
        'url'  => $this->createUrl("admin/questions/sa/copyquestion/surveyid/$sid/gid/$gid/qid/$qid"),
        'name' => gT("Copy"),
        'icon' => 'icon-copy',
        'id' => 'copy_button',
        'class' => ' btn-default'
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['copy']);
}

$hasUpdatePermission = Permission::model()->hasSurveyPermission($sid,'surveycontent','update');
if ($hasUpdatePermission) {
    $permissions['update'] = ['update' => $hasUpdatePermission];

    // Conditions Button
    $buttons['conditions'] = [
        'url'  => $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$sid/gid/$gid/qid/$qid"),
        'name' => gT("Set conditions"),
        'id' => 'conditions_button',
        'icon' => 'icon-conditions',
        'class' => ' btn-default'
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['conditions']);

    if($qtypes[$qrrow['type']]['hasdefaultvalues'] > 0) {
        $buttons['default_values'] = [
            'url'  => $this->createUrl("admin/questions/sa/editdefaultvalues/suveyid/".$sid."/gid/".$gid."/qid/".$qid),
            'name' => gT("Edit default anwers"),
            'id' => 'default_value_button',
            'icon' => 'icon-defaultanswers',
            'class' => ' btn-default'
        ];
    }
    array_push($topbar['alignment']['left']['buttons'], $buttons['default_values']);
}
// Extended Topbar
  // Left Side
 
    // Save Button
    $buttons['save'] = [
      'url'  => '#',
      'icon' => 'fa fa-floppy-o',
      'name' => gT('Save'),
      'id'   => 'save-button',
      'class' => 'btn-success',
      'isSaveButton' => true
    ];
    array_push($topbarextended['alignment']['right']['buttons'], $buttons['save']);
  

  // Save and Close Button
  if ($ownsSaveAndCloseButton) {
    $buttons['save_and_close'] = [
      'url'  => $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/{$sid}"),
      'icon' => 'fa fa-check-square',
      'name' => gT('Save and close'),
      'id' => 'save_and_close',
      'class' => 'btn-default',
      'isSaveButton' => true
    ];
    array_push($topbarextended['alignment']['right']['buttons'], $buttons['save_and_close']);
  }

  // Close Button
  if ($ownsCloseButton) {
    $buttons['close'] = [
      'url'  => '/admin/survey/sa/listquestions/surveyid/'.$sid,
      'name' => gT('Close'),
      'icon' => 'fa fa-close',
      'id' => 'close',
      'class' => 'btn-danger pull-right margin-left',
      'isCloseButton' => true
    ];
    array_push($topbarextended['alignment']['right']['buttons'], $buttons['close']);
  }

$finalJSON = [
  'permission' => $permissions,
  'topbar' => $topbar,
  'topbarextended' => $topbarextended,
];

header("Content-Type: application/json");
echo json_encode($finalJSON);
?>
