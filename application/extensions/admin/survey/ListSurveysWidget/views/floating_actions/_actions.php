<?php
/**
 * Floating action bar configuration for the survey list.
 *
 * This file is require()'d from listSurveys.php and MUST return a PHP array.
 * Each entry is an action definition consumed by FloatingActionsWidget.
 *
 * Supported types:
 *   'action'    – single button (actionType: 'modal' or 'redirect')
 *   'dropdown'  – a button that expands a list of sub-actions
 *   'separator' – thin vertical divider
 */

return [
    // ------------------------------------------------------------------ theme
    [
        'type'          => 'action',
        'action'        => 'updateTheme',
        'url'           => App()->createUrl('/surveyAdministration/changeMultipleTheme/'),
        'iconClasses'   => 'ri-brush-fill',
        'text'          => gT('Set survey theme'),
        'grid-reload'   => 'no',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-apply',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
        'keepopen'      => 'yes',
        'sModalTitle'   => gT('Apply survey theme'),
        'htmlModalBody' => Yii::app()->getController()->renderFile(
            dirname(__FILE__) . '/../massive_actions/_select_survey_theme.php',
            [],
            true
        ),
    ],

    // ------------------------------------------------------------------ group
    [
        'type'          => 'action',
        'action'        => 'updateSurveygroup',
        'url'           => App()->createUrl('/surveyAdministration/changeMultipleSurveyGroup/'),
        'iconClasses'   => 'ri-group-fill',
        'text'          => gT('Set survey group'),
        'grid-reload'   => 'yes',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-change',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
        'keepopen'      => 'yes',
        'sModalTitle'   => gT('Change survey group'),
        'htmlModalBody' => Yii::app()->getController()->renderFile(
            dirname(__FILE__) . '/../massive_actions/_change_survey_group.php',
            [],
            true
        ),
    ],

    // ------------------------------------------------------------------ export (dropdown)
    [
        'type' => 'dropdown',
        'icon' => 'ri-download-fill',
        'text' => gT('Export'),
        'items' => [
            [
                'action'        => 'export',
                'url'           => App()->createUrl('/admin/export/sa/exportMultipleArchiveSurveys/'),
                'iconClasses'   => 'ri-download-fill',
                'text'          => gT('Survey archive (*.lsa)'),
                'grid-reload'   => 'no',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-export',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                'keepopen'      => 'yes',
                'sModalTitle'   => gT('Export survey archive'),
                'htmlModalBody' =>
                    gT('This will export the survey archive (.lsa) for all selected active surveys. They will be provided in a single ZIP archive.')
                    . ' ' . gT('Continue?'),
            ],
            [
                'action'        => 'export',
                'url'           => App()->createUrl('/admin/export/sa/exportMultipleStructureSurveys/'),
                'iconClasses'   => 'ri-download-fill',
                'text'          => gT('Survey structure (*.lss)'),
                'grid-reload'   => 'no',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-export',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                'keepopen'      => 'yes',
                'sModalTitle'   => gT('Export survey structure'),
                'htmlModalBody' =>
                    gT('This will export the survey structure (.lss) for all selected active surveys. They will be provided in a single ZIP archive.')
                    . ' ' . gT('Continue?'),
            ],
            [
                'action'        => 'export',
                'url'           => App()->createUrl('/admin/export/sa/exportMultiplePrintableSurveys/'),
                'iconClasses'   => 'ri-download-fill',
                'text'          => gT('Printable survey (*.html)'),
                'grid-reload'   => 'no',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-export',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                'keepopen'      => 'yes',
                'sModalTitle'   => gT('Export printable survey'),
                'htmlModalBody' =>
                    gT('This will export a printable version of your survey.')
                    . ' ' . gT('Continue?'),
            ],
        ],
    ],

    // ------------------------------------------------------------------ separator
    ['type' => 'separator'],

    // ------------------------------------------------------------------ delete
    [
        'type'          => 'action',
        'action'        => 'delete',
        'url'           => App()->createUrl('/surveyAdministration/deleteMultiple/'),
        'iconClasses'   => 'ri-delete-bin-fill',
        'btnClass'      => 'text-danger',
        'text'          => gT('Delete'),
        'grid-reload'   => 'yes',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-delete',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
        'keepopen'      => 'yes',
        'sModalTitle'   => gT('Delete surveys'),
        'htmlModalBody' => gT('Are you sure you want to delete all those surveys?'),
    ],
];

