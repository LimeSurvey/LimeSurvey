<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>

<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'sid',
            'gridid'      => 'survey-grid',
            'dropupId'    => 'surveyListActions',
            'dropUpText'  => gT('Selected survey(s)...'),

            'aActions'    => array(
                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'url'         => App()->createUrl('/admin/survey/sa/deleteMultiple/'),
                    'iconClasses' => 'text-danger glyphicon glyphicon-trash',
                    'text'        =>  gT('Delete'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'keepopen'      => 'yes',
                    'sModalTitle'   => gT('Delete surveys'),
                    'htmlModalBody' => gT('Are you sure you want to delete all those surveys?'),
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
                    'iconClasses' => 'icon-export',
                    'text'        =>  gT("Survey archive (.lsa)"),

                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'yes-no',
                    'keepopen'    => 'yes',
                    'sModalTitle'   => gT('Export survey archive'),
                    'htmlModalBody' => gT('This will export the survey archive (.lsa) for all selected active surveys. They will be provided in a single ZIP archive.').' '.gT('Continue?'),
                ),

                // Export multiple survey archive
                array(

                    // li element
                    'type'        => 'action',
                    'action'      => 'export',
                    'url'         =>  App()->createUrl('/admin/export/sa/exportMultipleStructureSurveys/'),
                    'iconClasses' => 'icon-export',
                    'text'        =>  gT("Survey structure (.lss)"),

                    // modal
                    'actionType'  => 'modal',
                    'modalType'   => 'yes-no',
                    'keepopen'    => 'yes',
                    'sModalTitle'   => gT('Export survey structure'),
                    'htmlModalBody' => gT('This will export the survey structure (.lss) for all selected active surveys. They will be provided in a single ZIP archive.').' '.gT('Continue?'),

                ),

            ),

    ));
?>
