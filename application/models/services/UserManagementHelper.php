<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Datavalueobjects\OperationResult;
use LimeSurvey\Datavalueobjects\TypedMessage;

/**
 * Class UserManagementHelper
 *
 * This class provides methods to manage users.
 *
 * @package LimeSurvey\Models\Services
 */
class UserManagementHelper
{
    /**
     * Deletes the user with the given id.
     * @param int $userId
     * @return OperationResult
     */
    public function deleteUser($userId)
    {
        $messages = [];

        $siteAdminName = \User::model()->findByPk(1)->users_name;

        $transaction = \Yii::app()->db->beginTransaction();
        try {
            // Transfer any User Groups owned by this user to site's admin
            $userGroupsTranferred = \UserGroup::model()->updateAll(['owner_id' => 1], 'owner_id = :owner_id', [':owner_id' => $userId]);
            if ($userGroupsTranferred) {
                $messages[] = new TypedMessage(sprintf(gT("All of the user's user groups were transferred to %s."), $siteAdminName), 'success');
            }

            // Transfer any Participants owned by this user to site's admin
            $participantsTranferred = \Participant::model()->updateAll(['owner_uid' => 1], 'owner_uid = :owner_uid', [':owner_uid' => $userId]);
            if ($participantsTranferred) {
                $messages[] = new TypedMessage(sprintf(gT("All participants owned by this user were transferred to %s."), $siteAdminName), 'success');
            }

            // Remove from groups
            $userAndGroupRelations = \UserInGroup::model()->findAllByAttributes(['uid' => $userId]);
            if (count($userAndGroupRelations)) {
                foreach ($userAndGroupRelations as $userAndGroupRelation) {
                    $userAndGroupRelation->delete();
                }
            }

            // TODO: User permissions should be deleted also...

            // Delete the user
            $user = \User::model()->findByPk($userId);
            $success = $user->delete();
            if (!$success) {
                $messages = [new TypedMessage(gT("User could not be deleted."), 'error')];
                $transaction->rollback();
            } else {
                $messages[] = new TypedMessage(gT("User successfully deleted."), 'success');
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollback();
            $messages = [new TypedMessage(gT("An error occurred while deleting the user."), 'error')];
            $success = false;
        }
        return new OperationResult($success, $messages);
    }
}
