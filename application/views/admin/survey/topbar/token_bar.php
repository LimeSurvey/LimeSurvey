<?php
/**
 * Renderer for the json based topbar definition in tokens
 */

$permissions = [];
$buttonsgroup = [];
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
if (!$onlyclose) {
    ###### Left aligned
    // Display participants
    if (Permission::model()->hasSurveyPermission($sid, 'tokens', 'read')) {
        $buttons['display_participants'] = [
            'url' => $this->createUrl("admin/tokens/sa/browse/surveyid/$sid"),
            'name' => gT('Display participants'),
            'icon' => 'fa fa-list-alt',
            'id' => 'display-participants-button',
            'class' => 'btn btn-default pjax',
        ];
        array_push($topbar['alignment']['left']['buttons'], $buttons['display_participants']);
    }

    // Create (Dropdown) ############### START
    $buttonsgroup['create'] = [
        'class' => 'btn-group hidden-xs',
        'main_button' => [
            'id' => 'ls-create-participants-button',
            'class' => 'dropdown-toggle',
            'datatoggle' => 'dropdown',
            'ariahaspopup' => 'true',
            'ariaexpanded' => 'false',
            'icon' => 'fa fa-plus-circle',
            'iconclass' => 'caret',
            'name' => gT('Create...'),
        ],
        'dropdown' => [
            'class' => 'dropdown-menu',
            'arialabelledby' => 'ls-create-participants-button',
            'items' => [],
        ],
    ];
    // Create (Dropdown) -> Add Participant
    if ((Permission::model()->hasSurveyPermission($sid, 'tokens', 'create'))) {
        $buttons['add_participant'] = [
            'url' => $this->createUrl("admin/tokens/sa/addnew/surveyid/$sid"),
            'icon' => 'fa fa-plus',
            'name' => gT('Add participant'),
            'class' => 'pjax',
        ];

        array_push(
            $buttonsgroup['create']['dropdown']['items'],
            $buttons['add_participant']
        );

        // Create (Dropdown) -> Add dummy participants
        $buttons['add_dummy_participants'] = [
            'url' => $this->createUrl("admin/tokens/sa/adddummies/surveyid/$sid"),
            'icon' => 'fa fa-plus-square',
            'name' => gT('Create dummy participants'),
            'class' => 'pjax',
        ];
        array_push($buttonsgroup['create']['dropdown']['items'], $buttons['add_dummy_participants']);
    }
    // Create (Dropdown) -> Divider
    if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens', 'create') && Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens', 'import')) {
        $buttons['divider'] = [
            'role' => 'seperator',
            'class' => 'divider',
        ];
        array_push($buttonsgroup['create']['dropdown']['items'], $buttons['divider']);
    }
    // Create (Dropdown) -> Headline
    if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens', 'import')) {
        $buttons['divider'] = [
            'role' => 'seperator',
            'class' => 'divider',
            'name' => gT("Import participants from:"),
        ];
        // Create (Dropdown) -> Import from CSV file
        $buttons['import_from_csv'] = [
            'url' => $this->createUrl("admin/tokens/sa/import/surveyid/$sid"),
            'icon' => 'icon-importcsv',
            'name' => gT('CSV file'),
            'class' => 'pjax',
        ];
        array_push($buttonsgroup['create']['dropdown']['items'], $buttons['import_from_csv']);
        // Create (Dropdown) -> Import from LDAP query
        $buttons['import_from_csv_ldap'] = [
            'url' => $this->createUrl("admin/tokens/sa/importldap/surveyid/$sid"),
            'icon' => 'icon-importldap',
            'name' => gT('LDAP query'),
            'class' => 'pjax',
        ];
        array_push($buttonsgroup['create']['dropdown']['items'], $buttons['import_from_csv_ldap']);

    }
    array_push($topbar['alignment']['left']['buttons'], $buttonsgroup['create']);

    // Create (Dropdown) ############### END
    // Manage attributes
    if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens', 'update')
        || Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveysettings', 'update')
    ) {
        $buttons['manage_attributes'] = [
            'url' => $this->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$sid"),
            'name' => gT('Manage attributes'),
            'icon' => 'fa fa-server',
            'class' => 'btn btn-default pjax',
        ];
        array_push($topbar['alignment']['left']['buttons'], $buttons['manage_attributes']);
    }

    // Export Participants
    if (Permission::model()->hasSurveyPermission($sid, 'tokens', 'export')) {
        $buttons['export_participants'] = [
            'url' => $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$sid"),
            'name' => gT('Export'),
            'icon' => 'icon-exportcsv',
            'id' => 'export-participants-button',
            'class' => 'btn btn-default pjax',
        ];
        array_push($topbar['alignment']['left']['buttons'], $buttons['export_participants']);
    }

    // Invitation and reminders (Dropdown) ############### START
    if (Permission::model()->hasSurveyPermission($sid, 'tokens', 'update')) {
        $buttonsgroup['emails'] = [
            'class' => 'btn-group hidden-xs',
            'main_button' => [
                'id' => 'ls-email-participants-button',
                'class' => 'dropdown-toggle',
                'datatoggle' => 'dropdown',
                'ariahaspopup' => 'true',
                'ariaexpanded' => 'false',
                'icon' => 'fa fa-envelope',
                'iconclass' => 'caret',
                'name' => gT('Invitations & reminders'),
            ],
            'dropdown' => [
                'class' => 'dropdown-menu',
                'arialabelledby' => 'ls-email-participants-button',
                'items' => [],
            ],
        ];
        // Invitation and reminders (Dropdown) -> Invite participants
        $buttons['invite_participant'] = [
            'url' => $this->createUrl("admin/tokens/sa/email/surveyid/$sid"),
            'icon' => 'icon-invite',
            'name' => gT('Send email invitation'),
            'class' => 'pjax',
        ];
        array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['invite_participant']);

        // Invitation and reminders (Dropdown) -> Remind participants
        $buttons['remind_partipant'] = [
            'url' => $this->createUrl("admin/tokens/sa/email/action/remind/surveyid/$sid"),
            'icon' => 'icon-remind',
            'name' => gT('Send email reminder'),
            'class' => 'pjax',
        ];
        array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['remind_partipant']);

        // Invitation and reminders (Dropdown) -> Edit email templates
        if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveylocale', 'read')) {
            $buttons['edit_email_templates'] = [
                'url' => $this->createUrl("admin/emailtemplates/sa/index/surveyid/$sid"),
                'icon' => 'fa fa-envelope-o',
                'name' => gT('Edit email templates'),
                'class' => 'pjax',
            ];
            array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['edit_email_templates']);
        }

        // Invitation and reminders (Dropdown) -> Divider
        $buttons['divider'] = [
            'role' => 'seperator',
            'class' => 'divider',
        ];
        array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['divider']);

        // Invitation and reminders (Dropdown) -> Bounce processing
        $errorMessages = [];
        $isDisabled = false;

        if (!function_exists('imap_open')) {
            $errorMessages[] = gT("The imap PHP library is not installed or not activated. Please contact your system administrator.");
            $isDisabled = true;
        }

        if (($oSurvey->bounceprocessing != 'N' || ($oSurvey->bounceprocessing == 'G' && getGlobalSetting('bounceaccounttype') != 'off'))) {
            $errorMessages[] = gT("Bounce processing is deactivated either application-wide or for this survey in particular.");
            $isDisabled = true;
        }

        $errorMessage = join("\n", $errorMessages);

        $buttons['bounce_processing'] = [
            'url' => '#',
            'alerttext' => $errorMessage,
            'icon' => 'fa fa-cogs',
            'id' => 'startbounceprocessing',
            'data-url' => $isDisabled ? '' : $this->createUrl("admin/tokens/sa/bounceprocessing/surveyid/$sid"),
            'name' => gT('Start bounce processing'),
            'class' => 'pjax ' . ($isDisabled ? ' disabled' : ' '),
        ];
        array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['bounce_processing']);

        // Invitation and reminders (Dropdown) -> Bounce settings
        $buttons['bounce_settings'] = [
            'url' => $this->createUrl("admin/tokens/sa/bouncesettings/surveyid/$sid"),
            'icon' => 'fa fa-wrench',
            'name' => gT('Bounce settings'),
            'class' => '',
        ];
        array_push($buttonsgroup['emails']['dropdown']['items'], $buttons['bounce_settings']);

        //Finally combine in output array
        array_push($topbar['alignment']['left']['buttons'], $buttonsgroup['emails']);
    }
    // Invitation and reminders (Dropdown) ############### END

    //Generate Tokens
    $buttons['generate_tokens'] = [
        'url' => $this->createUrl("admin/tokens/sa/tokenify/surveyid/$sid"),
        'name' => gT('Generate tokens'),
        'icon' => 'fa fa-cog',
        'id' => 'participants-generatetoken-button',
        'class' => 'btn btn-default',
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['generate_tokens']);
    //CPDB
    $buttons['view_in_cpdb'] = [
        'url' => $this->createUrl("/admin/participants/sa/displayParticipants"),
        'name' => gT('View in CPDB'),
        'icon' => 'fa fa-users',
        'class' => 'btn btn-default',
        'triggerpost' => true,
        'payload' => json_encode(['searchcondition' => 'surveyid||equal||' . $sid]),
    ];
    array_push($topbar['alignment']['left']['buttons'], $buttons['view_in_cpdb']);

    //@TODO New feature -> Import from CPDB
    // $buttons['import_from'] = [
    //     'url'  => $this->createUrl("/admin/participants/sa/displayParticipants"),
    //     'name' => gT('View in CPDB'),
    //     'icon' => 'fa fa-users',
    //     'class' => 'btn btn-default',
    //     'triggerpost' => true,
    //     'payload' => json_encode(['searchcondition' => 'surveyid||equal||'.$sid])
    //   ];
    //   array_push($topbar['alignment']['left']['buttons'], $buttons['import_from']);

    ###### Right aligned

    if (Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')
        || Permission::model()->hasSurveyPermission($sid, 'tokens', 'delete')
    ) {
        $buttons['delete_tokens'] = [
            'url' => $this->createUrl("admin/tokens/sa/kill/surveyid/$sid"),
            'name' => gT('Delete participants table'),
            'icon' => 'fa fa-trash-o',
            'class' => 'btn btn-danger',
        ];
        array_push($topbar['alignment']['right']['buttons'], $buttons['delete_tokens']);
    }
    $buttons['divider'] = [
        'role' => 'seperator',
        'class' => 'divider',
    ];
    array_push($topbar['alignment']['right']['buttons'], $buttons['divider']);

    $buttons['save'] = [
        'name' => gT('Save'),
        'id' => 'save-button',
        'class' => 'btn-success',
        'icon' => 'fa fa-floppy-o',
        'url' => '#',
        'isSaveButton' => true,
    ];
    array_push($topbar['alignment']['right']['buttons'], $buttons['save']);
}

$buttons['close'] = [
    'name' => gT('Close'),
    'id' => 'close-button',
    'class' => 'btn-danger',
    'icon' => 'fa fa-times',
    'url' => '#',
    'isCloseButton' => true,
];
array_push($topbar['alignment']['right']['buttons'], $buttons['close']);

$finalJSON = [
    'permission' => $permissions,
    'topbar' => $topbar,
    'topbarextended' => $topbarextended,
];

header("Content-Type: application/json");
echo json_encode($finalJSON);
