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

            'aActions' => array(
                // Massive update
                array(
                    // li element
                    'type'           => 'action',
                    'action'         => 'edit',
                    'disabled'       => !Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update'),
                    'url'            => App()->createUrl('/admin/tokens/sa/editMultiple/'),
                    'iconClasses'    => 'ri-pencil-fill',
                    'text'           => gT('Batch-edit participants'),
                    'grid-reload'    => 'yes',
                    // modal
                    'actionType'     => 'modal',
                    'largeModalView' => true,
                    'modalType'      => 'cancel-save',
                    'keepopen'       => 'yes',
                    'sModalTitle'    => gT('Batch-edit participants'),
                    'htmlModalBody'  => $this->renderPartial(
                        './token/massive_actions/_update',
                        array(
                            'dateformatdetails' => getDateFormatData(Yii::app()->session['dateformat']),
                            'aLanguages'        => $aLanguages
                        ),
                        true
                    ),
                ),

                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'disabled'     => !Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'delete'),
                    'url'         =>  App()->createUrl('/admin/tokens/sa/deleteMultiple/'),
                    'iconClasses' => 'ri-delete-bin-fill text-danger',
                    'text'        =>  gT('Delete'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'cancel-delete',
                    'keepopen'      => 'no',
                    'sModalTitle'   => gT('Delete survey participants'),
                    'htmlModalBody' => gT('Are you sure you want to delete the selected participants?'),
                    'aCustomDatas'  => array(
                        array( 'name'=>'sid',  'value'=> $surveyid),
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
                    'disabled'         => !Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update'),
                    'url'             =>  App()->createUrl('/admin/tokens/sa/email/surveyid/'.$surveyid),
                    'iconClasses'     => 'ri-mail-send-fill',
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
                    'disabled'         => !Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update'),
                    'url'             =>  App()->createUrl('/admin/tokens/sa/email/action/remind/surveyid/'.$surveyid),
                    'iconClasses'     => 'ri-mail-volume-fill',
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
                    'url'             =>  App()->createUrl('admin/participants/sa/attributeMapToken/sid/'.$surveyid),
                    'iconClasses'     => 'ui-icon ui-add-to-cpdb-link',
                    'text'            =>  gT('Add participants to central database'),
                    'disabled'         => !Permission::model()->hasGlobalPermission('participantpanel', 'create'),
                    'aLinkSpecificDatas'  => array(
                        'input-name'     => 'tokenids',
                    ),

                    // modal
                    'actionType'    => 'fill-session-and-redirect',
                ),

            ),

    ));
?>
