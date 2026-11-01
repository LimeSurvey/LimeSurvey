<?php

namespace actions;

/**
 * Massive (floating bar) actions for the "Failed email notifications" grid.
 *
 * Returns the action definitions consumed by FloatingActionsWidget for the
 * failed email-grid. Only the actions the current user is permitted to run are
 * included.
 */
class FailedEmailMassiveActions
{
    /**
     * @param int   $surveyId
     * @param array $permissions ['update' => bool, 'delete' => bool, 'read' => bool]
     * @return array
     */
    public static function getActions(int $surveyId, array $permissions): array
    {
        $actions = [];

        // ------------------------------------------------------------------ resend
        if (!empty($permissions['update'])) {
            $actions[] = [
                'type'          => 'action',
                'action'        => 'resend',
                'url'           => \Yii::app()->createUrl('failedEmail/resend/', ['surveyid' => $surveyId]),
                'iconClasses'   => 'ri-loop-right-line',
                'text'          => gT('Resend emails'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-resend',
                'keepopen'      => 'yes',
                'sModalTitle'   => gT('Resend selected emails'),
                'htmlModalBody' => \Yii::app()->getController()->renderPartial(
                    '/failedEmail/partials/modal/resend_body',
                    [],
                    true
                ),
                'aCustomDatas'  => [
                    ['name' => 'surveyid', 'value' => $surveyId],
                ],
            ];
        }

        // ------------------------------------------------------------------ separator
        if (!empty($permissions['update']) && !empty($permissions['delete'])) {
            $actions[] = ['type' => 'separator'];
        }

        // ------------------------------------------------------------------ delete
        if (!empty($permissions['delete'])) {
            $actions[] = [
                'type'          => 'action',
                'action'        => 'delete',
                'url'           => \Yii::app()->createUrl('failedEmail/delete/', ['surveyid' => $surveyId]),
                'iconClasses'   => 'ri-delete-bin-fill',
                'btnClass'      => 'floating-delete-button',
                'text'          => gT('Delete'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-delete',
                'keepopen'      => 'yes',
                'sModalTitle'   => gT('Delete failed email notifications'),
                'htmlModalBody' => gT('Are you sure you want to delete the selected notifications?'),
                'aCustomDatas'  => [
                    ['name' => 'surveyid', 'value' => $surveyId],
                ],
            ];
        }

        return $actions;
    }
}
