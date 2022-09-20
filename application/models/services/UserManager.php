<?php

namespace LimeSurvey\Models\Services;

use LSWebUser;
use Permission;
use User;

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
     * @param LSWebUser $managingUser
     * @param User|null $targetUser
     */
    public function __construct(
        LSWebUser $managingUser,
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
        return Permission::model()->hasGlobalPermission('superadmin', 'read', $this->managingUser->id);
    }

    /**
     * Returns true if the managing user can edit the target user
     * @return bool
     */
    public function canEdit()
    {
        return
            Permission::model()->hasGlobalPermission('superadmin', 'read', $this->managingUser->id)
            || $this->targetUser->uid == $this->managingUser->id
            || (
                Permission::model()->hasGlobalPermission('users', 'update', $this->managingUser->id)
                && $this->targetUser->parent_id == $this->managingUser->id
            );
    }
}
