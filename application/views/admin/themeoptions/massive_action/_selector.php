<?php
/**
 * Render the selector for Surveythemes massive actions.
 *
 */
?>

<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
        'pk'          => 'id',
        'gridid'      => 'themeoptions-grid',
        'dropupId'    => 'optionThemesListActions',
        'dropUpText'  => gT('Selected theme(s)...'),
        'aActions'    => array(
            //reset
            array(
                //li element
                'type' => 'action',
                'action' => 'reset',
                'url' => App()->createUrl('/admin/themeoptions/sa/resetMultiple/'),
                'iconClasses' => '',
                'text' => gT('Reset'),
                'grid-reload' => 'yes',

                //modal
                'actionType' => 'modal',
                'modalType'     => 'yes-no',
                'keepopen'      => 'yes',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('/admin/themeoptions/sa/renderSelectedItems/'),
                'yes'           => gT('Reset Themes'),
                'no'            => gT('Cancel'),
                'sModalTitle'   => gT('Reset theme'),
                'htmlModalBody' =>gT('Are you sure you want to reset the selected themes?')
            ),
            //uninstall 
            array(
                //li element
                'type'        => 'action',
                'action'      => 'Uninstall',
                'url'         =>  App()->createUrl('/admin/themeoptions/sa/uninstallMultiple/'),
                'iconClasses' => '',
                'text'        =>  gT('uninstall'),
                'grid-reload' => 'yes',

                // modal
                'actionType'    => 'modal',
                'modalType'     => 'yes-no',
                'keepopen'      => 'yes',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('/admin/themeoptions/sa/renderSelectedItems/'),     
                'sModalTitle'   => gT('Uninstall themes'),
                'htmlModalBody' => gT('Are you sure you want to uninstall the selected themes?'),
            )
        )
    ));



    
?>


