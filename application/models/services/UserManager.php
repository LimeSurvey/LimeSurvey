<?php

namespace LimeSurvey\Models\Services;

use LSWebUser;
use Permission;
use User;
use LimeSurvey\Datavalueobjects\OperationResult;
use LimeSurvey\Datavalueobjects\TypedMessage;

/**
 * Service class for managing users and their permissions
 */
class UserManager
{
    /** @var LSWebUser the user managing other users */
    private $managingUser;

    /** @var User the user being handled */
    private $targetUser;

    /**
     * @param LSWebUser|null $managingUser
     * @param User|null $targetUser
     */
    public function __construct(
        LSWebUser $managingUser = null,
        User $targetUser = null
    ) {
        $this->managingUser = $managingUser;
        $this->targetUser = $targetUser;
    }

    /**
     * Returns true if the managing user can assign permissions to the target user.
     * @return boolean
     */
    public function canAssignPermissions()
    {
        if (empty($this->managingUser) || empty($this->targetUser)) {
            return false;
        }

        if (
            Permission::model()->hasGlobalPermission('superadmin', 'read', $this->managingUser->id)
            || (
                Permission::model()->hasGlobalPermission('users', 'update', $this->managingUser->id)
                && $this->targetUser->parent_id == $this->managingUser->id
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the managing user can assign roles to the target user.
     * @return boolean
     */
    public function canAssignRole()
    {
        if (empty($this->managingUser)) {
            return false;
        }
        /* roles can have superadmin permission, then need superadmin/create permission */
        return Permission::model()->hasGlobalPermission('superadmin', 'create', $this->managingUser->id);
    }

    /**
     * Returns true if the managing user can edit the attribute of the target user
     * Return true if target is same then managing (user can always update himself)
     * @return bool
     */
    public function canEdit()
    {
        if (empty($this->managingUser) || empty($this->targetUser)) {
            return false;
        }
        return $this->targetUser->canEdit($this->managingUser->id);
    }

    /**
     * Deletes the user with the given id.
     * @param User $user
     * @return OperationResult
     */
    public function deleteUser($user)
    {
        $messages = [];

        $siteAdminName = \User::model()->findByPk(1)->users_name;
        $userId = $user->uid;

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
