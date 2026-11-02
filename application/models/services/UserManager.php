<?php

namespace LimeSurvey\Models\Services;

use LSWebUser;
use Permission;
use Permissiontemplates;
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
        ?LSWebUser $managingUser = null,
        ?User $targetUser = null
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
        if (empty($this->managingUser) || empty($this->targetUser)) {
            return false;
        }
        if (Permission::model()->hasGlobalPermission('superadmin', 'create', $this->managingUser->id)) {
            return true;
        }
        /* Roles replace the individual permissions: Only a user allowed to create superadmins can update roles of a superadmin */
        if (Permission::model()->hasGlobalPermission('superadmin', 'read', $this->targetUser->uid)) {
            return false;
        }
        return $this->canAssignPermissions();
    }

    /**
     * Returns the roles the managing user is allowed to assign or to remove.
     * A role is assignable if the managing user has all the permissions given by the role.
     * @return Permissiontemplates[] indexed by role id
     */
    public function getAssignableRoles(): array
    {
        if (empty($this->managingUser)) {
            return [];
        }
        $assignableRoles = [];
        foreach (Permissiontemplates::model()->findAll() as $role) {
            if ($this->isRoleAssignable($role)) {
                $assignableRoles[$role->ptid] = $role;
            }
        }
        return $assignableRoles;
    }

    /**
     * Returns true if the managing user has all the permissions given by the role.
     * Roles with any superadmin permission need superadmin/create permission.
     * @param Permissiontemplates $role
     * @return bool
     */
    public function isRoleAssignable(Permissiontemplates $role): bool
    {
        if (empty($this->managingUser)) {
            return false;
        }
        $managingUserId = $this->managingUser->id;
        if (Permission::model()->hasGlobalPermission('superadmin', 'create', $managingUserId)) {
            return true;
        }
        $rolePermissions = Permission::model()->findAllByAttributes([
            'entity' => 'role',
            'entity_id' => $role->ptid,
        ]);
        foreach ($rolePermissions as $rolePermission) {
            foreach (['create', 'read', 'update', 'delete', 'import', 'export'] as $crud) {
                if (empty($rolePermission->getAttribute($crud . '_p'))) {
                    continue;
                }
                if ($rolePermission->permission == 'superadmin') {
                    return false;
                }
                if (!Permission::model()->hasGlobalPermission($rolePermission->permission, $crud, $managingUserId)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Returns true if the target user has any individual global permission set.
     * These permissions are removed when roles are assigned.
     * @return bool
     */
    public function hasIndividualGlobalPermissions(): bool
    {
        if (empty($this->targetUser)) {
            return false;
        }
        $criteria = new \CDbCriteria();
        $criteria->compare('uid', $this->targetUser->uid);
        $criteria->compare('entity', 'global');
        $criteria->addCondition('create_p = 1 OR read_p = 1 OR update_p = 1 OR delete_p = 1 OR import_p = 1 OR export_p = 1');
        return Permission::model()->exists($criteria);
    }

    /**
     * Set the roles of the target user, replacing the current ones.
     * Roles replace the individual global permissions, then these are deleted when the user has or had roles:
     * A user losing their last role doesn't get back their previous individual permissions.
     * Permission checks are not done here.
     * @param int[] $roleIds
     * @return bool
     */
    public function setRoles(array $roleIds): bool
    {
        if (empty($this->targetUser)) {
            return false;
        }
        $userId = $this->targetUser->uid;
        $hadRoles = !empty(Permission::getUserRole($userId));
        $success = Permissiontemplates::model()->clearUser($userId);
        foreach (array_unique($roleIds) as $roleId) {
            $success = Permissiontemplates::model()->applyToUser($userId, (int) $roleId) && $success;
        }
        if ($hadRoles || !empty($roleIds)) {
            Permission::model()->deleteAllByAttributes(['uid' => $userId, 'entity' => 'global']);
        }
        return $success;
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
         * Delete the given User and reassign their owned records to the site administrator.
         *
         * Transfers any UserGroup.owner_id, Participant.owner_uid and SurveysGroups.owner_id owned by the user to the site admin (uid=1), removes participant shares with the user and the user's group memberships, and then deletes the user. The whole operation is performed inside a database transaction and will be rolled back if an error occurs. Note: user permissions are not removed by this method.
         *
         * @param User $user The User model to delete; the method uses $user->uid to identify related records.
         * @return OperationResult An OperationResult whose success flag indicates whether the deletion committed, and whose messages describe actions taken or errors encountered.
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

            // Transfer any Survey Groups owned by this user to site's admin
            $surveyGroupsTransferred = \SurveysGroups::model()->updateAll(['owner_id' => 1], 'owner_id = :owner_id', [':owner_id' => $userId]);
            if ($surveyGroupsTransferred) {
                $messages[] = new TypedMessage(sprintf(gT("All of the user's survey groups were transferred to %s."), $siteAdminName), 'success');
            }

            // Remove participant shares with this user
            \ParticipantShare::model()->deleteAll('share_uid = :share_uid', [':share_uid' => $userId]);

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
