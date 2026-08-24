<?php

namespace actions;

use Yii;

class QuestionListMassiveActions
{
    public static function getActions($model, $oSurvey): array
    {
        $actions = [];

        $surveyId  = $oSurvey->sid;
        $hasUpdate = \Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'update');
        $hasDelete = \Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'delete');

        if (!$oSurvey->isActive && $hasUpdate) {
            $actions[] = [
                'type' => 'action',
                'action' => 'set-group-position',
                'url' => App()->createUrl('questionAdministration/setMultipleQuestionGroup/'),
                'iconClasses' => 'ri-folder-line',
                'text' => gT('Set question group and position'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-apply',
                'keepopen' => 'no',
                'yes' => gT('Apply'),
                'no' => gT('Cancel'),
                'sModalTitle' => gT('Set question group'),
                'htmlModalBody' => Yii::app()->getController()->renderPartial(
                    '/admin/survey/Question/massive_actions/_set_question_group_position',
                    ['model' => $model, 'oSurvey' => $oSurvey],
                    true
                ),
            ];
        }

        if ($hasUpdate) {
            $advancedItems = [
                [
                    'type' => 'dropdown-header',
                    'text' => gT('ADVANCED OPTIONS'),
                ],
                [
                    'action' => 'set-mandatory',
                    'url' => App()->createUrl('questionAdministration/changeMultipleQuestionMandatoryState/'),
                    'iconClasses' => '',
                    'text' => gT('Set') . ' "' . gT('Mandatory') . '" ' . gT("state"),
                    'grid-reload' => 'yes',
                    'actionType' => 'modal',
                    'modalType' => 'cancel-apply',
                    'keepopen' => 'no',
                    'sModalTitle' => gT('Set "Mandatory" state'),
                    'htmlModalBody' => Yii::app()->getController()->renderPartial(
                        '/admin/survey/Question/massive_actions/_set_questions_mandatory',
                        ['model' => $model, 'oSurvey' => $oSurvey],
                        true
                    ),
                ],
                [
                    'action' => 'set-css',
                    'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
                    'iconClasses' => '',
                    'text' => gT('Set CSS class'),
                    'grid-reload' => 'yes',
                    'actionType' => 'modal',
                    'modalType' => 'cancel-apply',
                    'keepopen' => 'no',
                    'sModalTitle' => gT('Set CSS class'),
                    'htmlModalBody' => Yii::app()->getController()->renderPartial(
                        '/admin/survey/Question/massive_actions/_set_css_class',
                        ['model' => $model],
                        true
                    ),
                ],
                [
                    'action' => 'set-statistics',
                    'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
                    'iconClasses' => '',
                    'text' => gT('Set statistics options'),
                    'grid-reload' => 'yes',
                    'actionType' => 'modal',
                    'modalType' => 'cancel-apply',
                    'keepopen' => 'no',
                    'sModalTitle' => gT('Set statistics options'),
                    'htmlModalBody' => Yii::app()->getController()->renderPartial(
                        '/admin/survey/Question/massive_actions/_set_statistics_options',
                        ['model' => $model],
                        true
                    ),
                ],
            ];

            if (!$oSurvey->isActive) {
                $advancedItems[] = [
                    'action' => 'set-other',
                    'url' => App()->createUrl('questionAdministration/changeMultipleQuestionOtherState'),
                    'iconClasses' => '',
                    'text' => gT("Set ") . ' "' . gT('Other') . '" ' . gT('state'),
                    'grid-reload' => 'yes',
                    'actionType' => 'modal',
                    'modalType' => 'cancel-apply',
                    'keepopen' => 'no',
                    'sModalTitle' => gT('Set "Other" state'),
                    'htmlModalBody' => Yii::app()->getController()->renderPartial(
                        '/admin/survey/Question/massive_actions/_set_questions_other',
                        ['model' => $model],
                        true
                    ),
                ];
            }

            $advancedItems[] = [
                'action' => 'set-subquestions-answers-sort',
                'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
                'iconClasses' => '',
                'text' => gT('Present subquestions/answer options in random order'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-apply',
                'keepopen' => 'false',
                'sModalTitle' => gT('Present subquestions/answer options in random order'),
                'htmlModalBody' => Yii::app()->getController()->renderPartial(
                    '/admin/survey/Question/massive_actions/_set_subquestansw_order',
                    ['model' => $model],
                    true
                ),
            ];

            $actions[] = [
                'type' => 'dropdown',
                'icon' => 'ri-settings-3-line',
                'text' => gT('Advanced options'),
                'items' => $advancedItems,
            ];
        }

        if (!$oSurvey->isActive && $hasDelete) {
            if (!empty($actions)) {
                $actions[] = ['type' => 'separator'];
            }
            $actions[] = [
                'type' => 'action',
                'action' => 'delete',
                'url' => App()->createUrl('questionAdministration/deleteMultiple/'),
                'iconClasses' => 'ri-delete-bin-fill',
                'btnClass' => 'floating-delete-button',
                'text' => gT('Delete'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-delete',
                'keepopen' => 'yes',
                'showSelected' => 'yes',
                'selectedUrl' => App()->createUrl('questionAdministration/renderItemsSelected/'),
                'sModalTitle' => gT('Delete question(s)'),
                'htmlModalBody' => gT('Deleting these questions will also delete their corresponding answer options and subquestions. Are you sure you want to continue??'),
            ];
        }

        return $actions;
    }
}
