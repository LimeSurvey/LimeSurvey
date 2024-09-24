<?php
/** @var ListSurveysWidget $this */
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<!-- Set hidden url for ajax post in listActions JS.   -->
<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'sid',
            'gridid'      => 'survey-grid',
            'dropupId'    => 'surveyListActions',
            'dropUpText'  => gT('Edit selected surveys'),

            'aActions'    => array(
                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'url'         => App()->createUrl('/surveyAdministration/deleteMultiple/'),
                    'iconClasses' => 'ri-delete-bin-fill text-danger',
                    'text'        =>  gT('Delete'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'cancel-delete',
                    'keepopen'      => 'yes',
                    'showSelected'  => 'yes',
                    'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'sModalTitle'   => gT('Delete surveys'),
                    'htmlModalBody' => gT('Are you sure you want to delete all those surveys?'),
                ),

                // Separator
                array(

                    // li element
                    'type'  => 'separator',
                ),

                // Theme selector
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'updateTheme',
                    'url'         =>  App()->createUrl('/surveyAdministration/changeMultipleTheme/'),
                    'iconClasses' => 'ri-brush-fill',
                    'text'        =>  gT("Set survey theme"),
                    'grid-reload' => 'no',
                    // modal
                    'actionType'   => 'modal',
                    'modalType'    => 'cancel-apply',
                    'showSelected' => 'yes',
                    'selectedUrl'  => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'keepopen'     => 'yes',
                    'sModalTitle'  => gT('Apply survey theme'),
                    'htmlModalBody' => $this->controller->renderFile(__DIR__.'/_select_survey_theme.php', array(), true),
                ),

                // Change survey group selector
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'updateSurveygroup',
                    'url'         =>  App()->createUrl('/surveyAdministration/changeMultipleSurveyGroup/'),
                    'iconClasses' => 'ri-group-fill',
                    'text'        =>  gT("Set survey group"),
                    'grid-reload' => 'yes',
                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'cancel-change',
                    'keepopen'    => 'yes',
                    'showSelected'  => 'yes',
                    'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'sModalTitle'   => gT('Change survey group'),
                    'htmlModalBody' => $this->controller->renderFile(__DIR__.'/_change_survey_group.php',array(),true),
                ),
                // Publication multiple
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'expire',
                    'url'         =>  App()->createUrl('/surveyAdministration/expireMultipleSurveys/'),
                    'iconClasses' => 'ri-skip-forward-fill',
                    'text'        =>  gT("Set expiry date"),
                    'grid-reload' => 'yes',
                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'cancel-apply',
                    'showSelected' => 'yes',
                    'selectedUrl'  => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'keepopen'    => 'yes',
                    'sModalTitle'   => gT('Set expiry date'),
                    'htmlModalBody' => $this->controller->renderFile(__DIR__.'/_expiry_dialog.php', array(), true),
                ),

                // Separator
                array(

                    // li element
                    'type'  => 'separator',
                ),

                // Download header
                array(

                    // li element
                    'type' => 'dropdown-header',
                    'text' => gT("Export as..."),
                ),

                // Export multiple survey archive
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'export',
                    'url'         => App()->createUrl('/admin/export/sa/exportMultipleArchiveSurveys/'),
                    'iconClasses' => 'ri-download-fill',
                    'text'        =>  gT("Survey archive (*.lsa)"),

                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'cancel-export',
                    'keepopen'    => 'yes',
                    'showSelected'  => 'yes',
                    'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'sModalTitle'   => gT('Export survey archive'),
                    'htmlModalBody' => gT('This will export the survey archive (.lsa) for all selected active surveys. They will be provided in a single ZIP archive.').' '.gT('Continue?'),
                ),

                // Export multiple survey archive
                array(

                    // li element
                    'type'        => 'action',
                    'action'      => 'export',
                    'url'         =>  App()->createUrl('/admin/export/sa/exportMultipleStructureSurveys/'),
                    'iconClasses' => 'ri-download-fill',
                    'text'        =>  gT("Survey structure (*.lss)"),

                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'cancel-export',
                    'keepopen'    => 'yes',
                    'showSelected'  => 'yes',
                    'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'sModalTitle'   => gT('Export survey structure'),
                    'htmlModalBody' => gT('This will export the survey structure (.lss) for all selected active surveys. They will be provided in a single ZIP archive.').' '.gT('Continue?'),

                ),
                // Export multiple printable
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'export',
                    'url'         =>  App()->createUrl('/admin/export/sa/exportMultiplePrintableSurveys/'),
                    'iconClasses' => 'ri-download-fill',
                    'text'        =>  gT("Printable survey (*.html)"),
                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'cancel-export',
                    'keepopen'    => 'yes',
                    'showSelected'  => 'yes',
                    'selectedUrl'   => App()->createUrl('/surveyAdministration/renderItemsSelected/'),
                    'sModalTitle'   => gT('Export printable survey'),
                    'htmlModalBody' => gT('This will export a printable version of your survey.').' '.gT('Continue?'),
                ),
            ),

    ));
?>
