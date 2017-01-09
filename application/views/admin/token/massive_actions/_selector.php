<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'tid',
            'gridid'      => 'token-grid',
            'dropupId'    => 'tokenListActions',
            'dropUpText'  => gT('Selected participant(s)...'),

            'aActions'    => array(

                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'url'         =>  App()->createUrl('/admin/tokens/sa/deleteMultiple/'),
                    'iconClasses' => 'text-danger glyphicon glyphicon-trash',
                    'text'        =>  gT('Delete'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'keepopen'      => 'no',
                    'sModalTitle'   => gT('Delete survey participants'),
                    'htmlModalBody' => gT('Are you sure you want to delete the selected participants?'),
                    'aCustomDatas'  => array(
                        array( 'name'=>'sid',  'value'=> $_GET['surveyid']),
                    ),
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
                    'text' => gT("Email"),
                ),

                // Send email invitation
                array(
                    // li element
                    'type'            => 'action',
                    'action'          => 'invite',
                    'url'             =>  App()->createUrl('/admin/tokens/sa/email/surveyid/'.$_GET['surveyid']),
                    'iconClasses'     => 'icon-invite text-success',
                    'text'            =>  gT('Send email invitations'),

                    'aLinkSpecificDatas'  => array(
                        'input-name'     => 'tokenids',
                    ),

                    // modal
                    'actionType'    => 'redirect',
                ),

                // Send email reminder
                array(
                    // li element
                    'type'            => 'action',
                    'action'          => 'remind',
                    'url'             =>  App()->createUrl('/admin/tokens/sa/email/action/remind/surveyid/'.$_GET['surveyid']),
                    'iconClasses'     => 'icon-remind text-success',
                    'text'            =>  gT('Send email reminder'),

                    'aLinkSpecificDatas'  => array(
                        'input-name'     => 'tokenids',
                    ),

                    // modal
                    'actionType'    => 'redirect',
                ),

                // Separator
                array(

                    // li element
                    'type'  => 'separator',
                ),

                // Central participant database header
                array(

                    // li element
                    'type' => 'dropdown-header',
                    'text' => gT("Central participant database"),
                ),

                // Send email reminder
                array(
                    // li element
                    'type'            => 'action',
                    'action'          => 'addCPDB',
                    'url'             =>  App()->createUrl('admin/participants/sa/attributeMapToken/sid/'.$_GET['surveyid']),
                    'iconClasses'     => 'ui-icon ui-add-to-cpdb-link',
                    'text'            =>  gT('Add participants to central database'),

                    'aLinkSpecificDatas'  => array(
                        'input-name'     => 'tokenids',
                    ),

                    // modal
                    'actionType'    => 'fill-session-and-redirect',
                ),

            ),

    ));
?>
